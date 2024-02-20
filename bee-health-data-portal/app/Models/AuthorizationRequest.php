<?php

namespace App\Models;

use App\Notifications\AccessRequesterNotification;
use App\Notifications\AccessRequestNotification;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AuthorizationRequest extends Model
{
    use HasFactory, Uuid;
    const AUTHORIZATION_TYPE_ORGANISATION_REQUESTS = 'organisation requests';
    const AUTHORIZATION_TYPE_USER_REQUESTS = 'user requests';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'authorization_request';

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
    protected $fillable = [
        'reference',
        'notes',
        'approved_at',
        'rejected_at',
        'response_note',
        'requested_at',
        'requesting_user_id',
        'requesting_organisation_id',
        'requesting_dataset_id',
        'authorization_type',
    ];

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
    protected $appends = [
        'is_approved'
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function($authorization_request){
            if($authorization_request->requesting_dataset_id){
                $dataset = $authorization_request->requesting_dataset;
                Notification::send($dataset->organisation->admin_users, new AccessRequestNotification($dataset));
            }
        });
        static::updated(function($authorization_request){

            if (!is_null($authorization_request->approved_at) || !is_null($authorization_request->rejected_at)) {
                $dataset = $authorization_request->requesting_dataset;
                Notification::send($authorization_request->requesting_user, new AccessRequesterNotification($authorization_request, $dataset));
            }
            if($authorization_request->is_approved){
                $authorization_request->authorization()->create([
                    'user_id' => $authorization_request->requesting_user_id,
                    'dataset_id' => $authorization_request->requesting_dataset_id,
                ]);
            }else{
                $authorization_request->authorization()->delete();
            }
        });
    }

    public function scopeOwn($query)
    {
        return $query->where('requesting_user_id', Auth::id());
    }

    public function scopeUserRequests($query)
    {
        return $query->where('authorization_type', self::AUTHORIZATION_TYPE_USER_REQUESTS);
    }

    public function scopeOrganisationRequests($query)
    {
        return $query->where('authorization_type', self::AUTHORIZATION_TYPE_ORGANISATION_REQUESTS);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function requesting_user(){
        return $this->hasOne(User::class, 'id', 'requesting_user_id');
    }

    public function requesting_organisation()
    {
        return $this->hasOne(Organisation::class, 'id', 'requesting_organisation_id');
    }

    public function requesting_dataset()
    {
        return $this->hasOne(Dataset::class, 'id', 'requesting_dataset_id');
    }

    public function authorization(){
        return $this->hasOne(Authorization::class, 'authorization_request_id', 'id');
    }

    public function authorization_type()
    {
        // return $this->belongsTo(AuthorizationType::class, 'authorization_type_id', 'id');
    }

    public function isPending(){
        return is_null($this->approved_at) && is_null($this->rejected_at);
    }

    public function isRejected()
    {
        return !is_null($this->rejected_at);
    }
    
    public function isApproved(){
        if($this->isPending()){
            return false;
        }
        return !is_null($this->approved_at);
    }

    public function getRequestedAtAttribute($value)
    {
        return (new Carbon($value))->format('d-M-Y');
    }

    public function getIsApprovedAttribute()
    {
        return $this->isApproved();
    }

    public function getIsPendingAttribute()
    {
        return $this->isPending();
    }

    public function getIsRejectedAttribute()
    {
        return $this->isRejected();
    }

    public function setRequestingDatasetIdAttribute($value)
    {
        if (empty($value)) {
            return;
        }
        $this->attributes['requesting_dataset_id'] = $value;
        $this->attributes['authorization_type'] = self::AUTHORIZATION_TYPE_USER_REQUESTS;
    }

    public function setRequestingOrganisationIdAttribute($value)
    {
        if(empty($value)){
            return;
        }
        $this->attributes['requesting_organisation_id'] = $value;
        $this->attributes['authorization_type'] = self::AUTHORIZATION_TYPE_ORGANISATION_REQUESTS;
    }
}
