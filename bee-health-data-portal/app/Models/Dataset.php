<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class Dataset extends Model
{
    use HasFactory, Uuid;



    const ACCESS_TYPE_OWNING_ORGANISATION_ONLY = 'owning_organisation_only';
    const ACCESS_TYPE_BY_REQUEST = 'by_request';
    const ACCESS_TYPE_BGOOD_PARTNERS = 'bgood_partners';
    const ACCESS_TYPE_REGISTERED_USERS = 'registered_users';
    const ACCESS_TYPE_OPEN_ACCESS = 'open_access';
    const PUBLICATION_STATES_INACTIVE = 'inactive';
    const PUBLICATION_STATES_DRAFT = 'draft';
    const PUBLICATION_STATES_PUBLISHED = 'published';
    
    public $allowAccessTypes = [
        'ORGANISATION_ONLY' => self::ACCESS_TYPE_OWNING_ORGANISATION_ONLY,
        'BY_REQUEST' => self::ACCESS_TYPE_BY_REQUEST,
        'BGOOD_PARTNERS' => self::ACCESS_TYPE_BGOOD_PARTNERS,
        'REGISTERED_USERS' => self::ACCESS_TYPE_REGISTERED_USERS,
        'OPEN_ACCESS' => self::ACCESS_TYPE_OPEN_ACCESS,
    ];

    public $publicationStates = [
        self::PUBLICATION_STATES_DRAFT,
        self::PUBLICATION_STATES_PUBLISHED,
    ];

    protected static function booted()
    {
        parent::boot();
        static::deleting(function ($dataset) {
            $dataset->keywords()->detach();
            $dataset->files()->delete();
            $dataset->authors()->delete();
        });
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dataset';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['organisation_id', 'user_id', 'publication_state', 'access_type'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'digital_object_identifier', 'license_id', 'organisation_id', 'user_id', 'publication_state', 'access_type', 'number_files', 'published_at'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array|bool
     */
    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_owner', 'is_edit', 'is_editable', 'is_downloadable'];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
    }

    protected $keyType = 'string';

    private function getUser()
    {
        $user = User::active()->find(Auth::id());
        return $user ? $user : new User();
    }

    public function scopeOfName($query, $value)
    {
        if(strlen($value) == 0){
            return $query;
        }
        return $query->where('name', 'ilike', '%'.$value.'%');
    }
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'DESC');
    }
    public function scopeActive($query)
    {
        return $query->whereIn('publication_state', [self::PUBLICATION_STATES_PUBLISHED, self::PUBLICATION_STATES_DRAFT]);
    }
    public function scopeDraft($query)
    {
        return $query->where('publication_state', self::PUBLICATION_STATES_DRAFT);
    }
    public function scopePublished($query)
    {
        return $query->where('publication_state', self::PUBLICATION_STATES_PUBLISHED);
    }
    public function scopeOpenAccess($query)
    {
        return $query->where('access_type', self::ACCESS_TYPE_OPEN_ACCESS);
    }
    public function scopeInactive($query)
    {
        return $query->where('publication_state', self::PUBLICATION_STATES_INACTIVE);
    }

    public function scopeOfOrganisations($query, $organisation_ids)
    {
        return $query->whereIn('organisation_id', $organisation_ids);
    }

    public function getIsRequestedAttribute()
    {
        return $this->authorization_requests()->where('requesting_user_id', Auth::id())->exists();
    }

    public function getIsOwnerAttribute()
    {
        return $this->isOwner($this->getUser());
    }

    public function isOwner(User $user)
    {
        if (!isset($this->user_id) || !$user) {
            return false;
        }
        $me = $user;

        // check is admin
        if ($me->isSuperAdmin()) {
            return true;
        }

        if(!$me->isMember()){
            return false;
        }
        // check is member of (organisation admin)
        if (!is_null($this->organisation_id) && $me->isAdminMemberOf(Organisation::find($this->organisation_id))) {
            return true;
        }
        // check is owner
        if ($this->user_id === $me->id) {
            return true;
        }
        return false;
    }

    public function getIsEditableAttribute()
    {
        return $this->getIsOwnerAttribute();
    }

    public function getIsDownloadableAttribute()
    {
        return $this->isDownloadable($this->getUser());
    }

    public function isDownloadable(User $user){
        if(!in_array($this->access_type, $this->allowAccessTypes)){
            throw new Exception($this->access_type);
        }
        
        // if (!isset($this->user_id) || !$user) {
        //     return false;
        // }
        $me = $user;

        if($this->access_type === self::ACCESS_TYPE_OPEN_ACCESS){
            return true;
        }
        if($this->access_type === self::ACCESS_TYPE_REGISTERED_USERS){
            return true;
        }
        if ($this->access_type === self::ACCESS_TYPE_BGOOD_PARTNERS && $me->is_bgood_partner) {
            return true;
        }
        if ($this->access_type === self::ACCESS_TYPE_OWNING_ORGANISATION_ONLY 
                && ($me->isMemberOf(Organisation::find($this->organisation_id)) || $this->ownAuthorizationOfDataset()->count()) ) {
            return true;
        }
        if ($this->access_type === self::ACCESS_TYPE_BY_REQUEST 
                && $me->isMemberOf(Organisation::find($this->organisation_id)) ) {
            return true;
        }
        if ($this->access_type === self::ACCESS_TYPE_BY_REQUEST
                && $this->ownAuthorizationOfDataset()->count() ) {
            return true;
        }

        if ($this->access_type === self::ACCESS_TYPE_BY_REQUEST) {
            foreach ($this->authorizationsOfDataset()->get() as $authorization) {
                if (!is_null($authorization->organisation_id) && $me->isMemberOf(Organisation::find($authorization->organisation_id))) {
                    return true;
                }
            }
        }

        // Admins, organisation admin and owner
        if ($this->isOwner($me)) {
            return true;
        }

        return false;
    }

    public function getIsEditAttribute()
    {
        return !$this->getIsInactiveAttribute();
    }

    public function getAuthorsAttribute()
    {
        return $this->authors()->get();
    }

    public function getAuthorsGroupByOrganisationsAttribute()
    {
        return $this->authors->groupBy('organisation');
    }

    public function getShortDescriptionAttribute()
    {
        return Str::words($this->description, 20, '...');
    }

    public function getExtensionsAttribute()
    {
        return $this->files()->pluck('file_format')->toArray();
    }

    public function getUniqueExtensionsAttribute()
    {
        return collect($this->getExtensionsAttribute())->unique();
    }

    public function getLastModifiedAttribute()
    {
        return (new Carbon($this->updated_at))->isoFormat('D-MMMM-YYYY');
    }

    public function getIsInactiveAttribute()
    {
        return $this->publication_state === self::PUBLICATION_STATES_INACTIVE;
    }

    public function getIsDraftAttribute()
    {
        return $this->publication_state === self::PUBLICATION_STATES_DRAFT;
    }

    public function getIsPublishedAttribute()
    {
        return $this->publication_state === self::PUBLICATION_STATES_PUBLISHED;
    }

    public function getIsPublishedSoonAttribute()
    {
        if(empty($this->published_at)){
            return false;
        }
        $lastWeek = Carbon::today()->subWeek();
        $now = Carbon::now();
        return Carbon::parse($this->published_at)->between($lastWeek, $now);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organisation(){
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class, 'dataset_id', 'id');
    }

    public function authorization_organisations()
    {
        $table = (new Authorization)->getTable();
        return $this->belongsToMany(Organisation::class, $table, 'dataset_id', 'organisation_id')->orderBy($table.'.updated_at', 'desc')->withTimestamps();
    }
    public function authorization_users()
    {
        return $this->belongsToMany(User::class, (new Authorization)->getTable(), 'dataset_id', 'user_id')->withTimestamps();
    }

    public function license()
    {
        return $this->belongsTo(License::class, 'license_id', 'id');
    }

    public function ownAuthorizations()
    {
        return $this->authorizations()->owner();
    }

    public function ownAuthorizationOfDataset()
    {
        return $this->ownAuthorizations()->ofDataset($this->id);
    }

    public function authorizationsOfDataset()
    {
        return $this->authorizations()->ofDataset($this->id);
    }

    public function authorization_requests()
    {
        return $this->hasMany(AuthorizationRequest::class, 'requesting_dataset_id', 'id')->where('authorization_type', AuthorizationRequest::AUTHORIZATION_TYPE_USER_REQUESTS);
    }

    public function authors(){
        return $this->belongsToMany(Author::class, 'dataset_author', 'dataset_id', 'author_id')->withPivot('order')->orderBy('order', 'ASC');
    }

    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'dataset_keyword', 'dataset_id', 'keyword_id')->withPivot('order', 'keyword_id')->orderBy('order', 'ASC');
    }

    public function files()
    {
        return $this->belongsToMany(FileVersion::class, 'dataset_file', 'dataset_id', 'file_version_id');
    }

}
