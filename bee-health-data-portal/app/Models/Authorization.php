<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Authorization extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'authorization';

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
    protected $primaryKey = null;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'authorization_request_id',
        'organisation_id',
        'dataset_id',
        'user_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'authorization_request_id',
        'organisation_id',
        'dataset_id',
        'user_id',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array|bool
     */
    protected $guarded = [];

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

    public function getOrganisationAttribute()
    {
        if($this->organisation_id){
            return $this->organisation()->first();
        }
    }

    public function getDatasetAttribute()
    {
        if ($this->dataset_id) {
            return $this->dataset()->first();
        }
    }

    public function scopeOwner($query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeOfDataset($query, $dataset_id)
    {
        return $query->where('dataset_id', $dataset_id);
    }

    public function scopeOfOrganisation($query, $id)
    {
        return $query->where('organisation_id', $id);
    }

    public function authorization_request()
    {
        return $this->belongsTo(AuthorizationRequest::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
