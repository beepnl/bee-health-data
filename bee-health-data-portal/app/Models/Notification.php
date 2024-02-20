<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class Notification extends Model
{
    use HasFactory, Uuid;

    const ALL_NEW_DATASETS = 'ALL_NEW_DATASETS';
    const NEW_DATASETS_I_HAVE_ACCESS_TO = 'NEW_DATASETS_I_HAVE_ACCESS_TO';
    const UPDATES_TO_DATASETS = 'UPDATES_TO_DATASETS';
    const UPDATES_TO_DATASETS_I_HAVE_ACCESS_TO = 'UPDATES_TO_DATASETS_I_HAVE_ACCESS_TO';

    const IMMEDIATELY = 'IMMEDIATELY';
    const WEEKLY = 'WEEKLY';

    /**
     * The attributes that are notifications.
     *
     * @var array
     */
    public static $allowNotificationsType = [
        self::ALL_NEW_DATASETS => [
            'label' => 'All new datasets',
        ],
        self::NEW_DATASETS_I_HAVE_ACCESS_TO => [
            'label' => 'New datasets I have access to',
            'description' => 'This includes changes in access to existing datasets'
        ],
        self::UPDATES_TO_DATASETS => [
            'label' => 'Updates to datasets',
        ],
        self::UPDATES_TO_DATASETS_I_HAVE_ACCESS_TO => [
            'label' => 'Updates to datasets I have access to',
        ],
    ];

    /**
     * The attributes that are frequencies.
     *
     * @var array
     */
    public static $allowFrequenciesType = [
        self::IMMEDIATELY => [
            'label' => 'Immediately'
        ],
        self::WEEKLY => [
            'label' => 'Weekly',
            'description' => 'Notifications are bundled and sent weekly',
            'checked' => true
        ],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'frequency', 'user_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['user_id'];

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include own user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  App\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOwn($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to only include single notification.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeName($query, string $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Scope a query to only include single notification.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $frequency
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFrequency($query, string $frequency)
    {
        return $query->where('frequency', $frequency);
    }
}
