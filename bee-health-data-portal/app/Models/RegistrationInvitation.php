<?php

namespace App\Models;

use App\Events\Invited;
use App\Notifications\VerifyInvitation;
use App\Traits\MustVerifyInvitation;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RegistrationInvitation extends Model 
{
    use HasFactory, Uuid, Notifiable, MustVerifyInvitation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'registration_invitation';

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
    protected $hidden = [
        'token',
        'user_role_id',
        'organisation_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'token',
        'expires_at',
        'user_role_id',
        'organisation_id',
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
    protected $appends = [];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
    }

    protected static function booted()
    {
        parent::boot();
        static::updating(function ($registrationInvitation) {
            $registrationInvitation->sendEmailVerificationNotification();
        });
        static::creating(function ($registrationInvitation) {
            $registrationInvitation->token = Str::random(100);
            $registrationInvitation->expires_at = Carbon::now()->addMinutes(config('auth.membership_invitation_expires_after'));
            $registrationInvitation->sendEmailVerificationNotification();
        });
    }

    public function scopeOfEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeOfOrganisation($query, Organisation $organisation)
    {
        return $query->where('organisation_id', $organisation->id);
    }

    public function scopeIsNotExpired($query)
    {
        return $query->where('expires_at', '>=', Carbon::now());
    }

    public function user_role()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id', 'id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
    }

    public function hasToken()
    {
        return !is_null($this->token);
    }

    public function isNotExpired()
    {
        return !is_null($this->expires_at) && $this->expires_at >= Carbon::now();
    }
    public function getStatusAttribute()
    {
        return 'invitation sent';
    }
    public function getFullnameAttribute()
    {
        return Str::before($this->email, '@');
    }
    public function getRoleAttribute()
    {
        return $this->user_role;
    }
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = Str::lower($value);
    }

}
