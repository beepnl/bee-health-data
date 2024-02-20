<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory, Uuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'license';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['label', 'order', 'active'];

        /**
     * Scope a query to only include single notification.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $frequency
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrder($query)
    {
        return $query->orderBy('order', 'ASC');
    }

    public function datasets()
    {
        return $this->hasMany(Dataset::class, 'license_id', 'id');
    }
}
