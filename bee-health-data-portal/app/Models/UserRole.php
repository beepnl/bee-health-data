<?php

namespace App\Models;

use Exception;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory, Uuid;

    const ORGANISATION_ADMIN = 'organisation admin';
    const USER = 'user';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_role';

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
    protected $fillable = ['name'];

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
    protected $appends = ['is_admin'];

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

    public function getIsAdminAttribute(){
        return $this->name === self::ORGANISATION_ADMIN;
    }

    public function getIsUserAttribute()
    {
        return $this->name === self::USER;
    }

    public function scopeOfRole($query, $name)
    {
        if(!in_array($name, [self::USER, self::ORGANISATION_ADMIN])){
            throw new Exception('The role name is wrong.');
        }
        return $query->where('name', $name);
    }
}

