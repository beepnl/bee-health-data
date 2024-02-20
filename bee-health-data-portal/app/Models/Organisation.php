<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Organisation extends Model
{
    use HasFactory, Uuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organisation';

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
    protected $hidden = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'is_bgood_partner'];

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
    protected $appends = [];

    protected $keyType = 'string';

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    
    public function scopeOfName($query, $value)
    {
        return $query->where('name', 'ilike', '%'. $value.'%');
    }

    public function scopeBgoodPartner($query)
    {
        return $query->where('is_bgood_partner', true);
    }

    public function scopeExceptDatasetOrganisation($query, Dataset $dataset)
    {
        return $query->where('id', '!=', $dataset->organisation_id);
    }
    
    public function getCountAttribute()
    {
        return $this->users()->count();
    }

    public function datasets()
    {
        return $this->hasMany(Dataset::class, 'organisation_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organisation_user', 'organisation_id', 'user_id')->withPivot('user_role')->withTimestamps();
    }

    public function admin_users()
    {
        $user_role = UserRole::ofRole(UserRole::ORGANISATION_ADMIN)->first();
        return $this->users()->wherePivot('user_role', $user_role->id);
    }

    public function roles()
    {
        return $this->belongsToMany(UserRole::class, 'organisation_user', 'organisation_id', 'user_id')->withTimestamps();
    }

    public function usersById(User $user)
    {
        return $this->users()->where($user->getKeyName(), $user->getKey());
    }

    public function register_invitations()
    {
        return $this->hasMany(RegistrationInvitation::class,  'organisation_id','id');
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class, 'organisation_id', 'id');
    }

    public function authorization_requests()
    {
        return $this->hasMany(AuthorizationRequest::class, 'requesting_organisation_id', 'id');
    }

    public function canUpdateMember(User $user)
    {
        return !!$this->admin_users->filter(function ($admin_user) use ($user) {
            return $admin_user->id != $user->id;
        })->count();
    }

    public function used_organisations()
    {
        return $this
            ->join('dataset', 'dataset.organisation_id', 'organisation.id')
            ->whereHas('datasets', function($query){
                $query->published();
            })
            ->select('organisation.id', 'organisation.name', DB::raw('count(*) as total'))
            ->groupBy('organisation.name', 'organisation.id');
    }
}
