<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'accepted_terms_and_conditions',
        'is_admin',
        'email_verified_at',
        'last_login',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['account_is_activated'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted()
    {
        parent::boot();
        static::deleting(function ($user) {
            $datasets = $user->datasets();
            if ($datasets->count()) {
                foreach($user->datasets()->draft()->get() as $dataset){
                    $dataset->delete();
                }
                foreach ($user->datasets()->inactive()->get() as $dataset) {
                    $dataset->delete();
                }

                $user->datasets()->published()->get()->map(function($dataset) use ($user){
                    $users = $dataset->organisation()->first()->admin_users()->where('id', '!=', $user->id);
                    if($users->count()){
                        $firstUser = $users->first();
                        $dataset->update(['user_id' => $firstUser->id]);
                    }else{
                        $adminUsers = (new self)->admins()->first();
                        $dataset->update(['user_id' => $adminUsers->id]);
                    }
                });
            }
            $user->authorizations()->delete();
            $user->authorization_requests()->delete();
            (new RegistrationInvitation())->ofEmail($user->email)->delete();
            $user->session()->delete();
            $user->organisations()->detach();
        });
    }

    public function scopeOfEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'id', 'user_id');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_user')->withPivot('user_role');
    }

    public function admin_organisations()
    {
        $user_role = UserRole::ofRole(UserRole::ORGANISATION_ADMIN)->first();
        return $this->belongsToMany(Organisation::class, 'organisation_user')->wherePivot('user_role', $user_role->id)->withPivot('user_role', 'organisation_id');
    }

    public function organisation($id)
    {
        return $this->belongsToMany(Organisation::class, 'organisation_user')->wherePivot('organisation_id', $id)->withPivot(['user_role']);
    }
    
    public function datasets()
    {
        return $this->hasMany(Dataset::class);
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }

    public function authorization_requests()
    {
        return $this->hasMany(AuthorizationRequest::class, 'requesting_user_id', 'id');
    }

    public function authorization_requests_user()
    {
        return $this->hasMany(AuthorizationRequest::class, 'requesting_user_id', 'id')->userRequests();
    }

    public function authorization_requests_organisation()
    {
        return $this->hasMany(AuthorizationRequest::class, 'requesting_user_id', 'id')->organisationRequests();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function check_organisation_user_role($organisation_id, $role=null)
    {
        if (is_null($role)) {
            return !!$this->organisation($organisation_id)->count();
        }
        $user_role = UserRole::ofRole($role)->first();
        return !!$this->organisation($organisation_id)->wherePivot('user_role', $user_role->id)->count();
    }

    public function getIsBgoodPartnerAttribute()
    {
        return !!$this->organisations()->get()->filter(function($organisation){
            return $organisation->is_bgood_partner;
        })->count();
    }

    public function getAccountIsActivatedAttribute()
    {
        return $this->hasFirstname() &&
            $this->hasLastname() &&
            $this->hasPassword() &&
            $this->hasAcceptedTermsAndConditions();
    }

    public function getRoleAttribute()
    {
        return UserRole::findOrFail($this->pivot->user_role);
    }

    public function getOrganisationAttribute()
    {
        return Organisation::findOrFail($this->pivot->organisation_id);
    }

    public function getFullnameAttribute()
    {
        if(empty($this->firstname) && empty($this->lastname)){
            $emailName = Str::before($this->email, '@');
            return Str::ucfirst($emailName);
        }
        $firstname = Str::ucfirst($this->firstname);
        $lastname = Str::ucfirst($this->lastname);
        return implode(" ", [$firstname, $lastname]);
    }

    public function hasFirstname()
    {
        return !empty($this->firstname);
    }

    public function hasLastname()
    {
        return !empty($this->lastname);
    }

    public function hasAcceptedTermsAndConditions()
    {
        return !empty($this->accepted_terms_and_conditions);
    }

    public function hasPassword()
    {
        return !empty($this->password);
    }

    public function isMember()
    {
        return !!$this->organisations()->count();
    }

    public function isMemberOf(Organisation $organisation)
    {
        return $this->check_organisation_user_role($organisation->id);
    }

    public function isUserMemberOf(Organisation $organisation)
    {
        return $this->check_organisation_user_role($organisation->id, UserRole::USER);
    }

    public function isAdminMemberOf(Organisation $organisation)
    {
        return $this->check_organisation_user_role($organisation->id, UserRole::ORGANISATION_ADMIN);
    }

    public function isAdminMember()
    {
        return !!$this->admin_organisations()->count();
    }

    public function isSuperAdmin()
    {
        return $this->is_admin;
    }

    public function getStatusAttribute()
    {
        if($this->account_is_activated){
            return 'Active';
        }
        return 'Awaiting activation';
    }
}
