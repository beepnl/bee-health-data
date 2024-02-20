<?php

namespace App\Models;

use Carbon\Carbon;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileVersion extends Model
{
    use HasFactory, Uuid;

    const AVAILABLE_FILE_FORMATS = ['jpg', 'png', 'xlsx', 'xml', 'json','pdf', 'doc', 'docx'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_version';

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
        'id',
        'filename',
        'description',
        'file_format',
        'size',
        'version',
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
    protected $appends = ['lastModified'];
    
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
    public function datasets()
    {
        return $this->belongsToMany(Dataset::class, 'dataset_file', 'file_version_id', 'dataset_id');
    }

    public function getAvailableFileFormatsAttribute()
    {
        return self::AVAILABLE_FILE_FORMATS;
    }

    public function getLastModifiedAttribute(){
        $date = new Carbon($this->updated_at);
        return "{$date->day}-{$date->shortEnglishMonth}-{$date->year}";
    }
    public function getNextVersionAttribute(){
        return $this->version + 1;
    }
    public function getShortDescriptionAttribute(){
        return Str::words($this->description, 2, '...');
    }
    public function getNameAttribute()
    {
        return "{$this->filename}.{$this->file_format}";
    }

    public function used_files()
    {
        return $this
            ->join('dataset_file', 'dataset_file.file_version_id', 'file_version.id')
            ->whereHas('datasets', function($query){
                $query->published();
            })
            ->select('file_format', DB::raw('count(*) as total'))
            ->groupBy('file_format');
    }

    public function used_files_total_size()
    {
        return $this
            ->join('dataset_file', 'dataset_file.file_version_id', 'file_version.id')
            ->whereHas('datasets', function($query){
                $query->published();
            })
            ->select(DB::raw('sum(size) as total_size'));
    }

    public function used_files_avg_size()
    {
        return $this
            ->join('dataset_file', 'dataset_file.file_version_id', 'file_version.id')
            ->whereHas('datasets', function($query){
                $query->published();
            })
            ->select(DB::raw('ceil(avg(size)) as avg_size'));
    }

    public function used_files_total_per_dataset()
    {
        return $this
            ->join('dataset_file', 'dataset_file.file_version_id', 'file_version.id')
            ->whereHas('datasets', function($query){
                $query->published();
            })
            ->select(DB::raw('count(id) as total_per_dataset'))
            ->orderBy('total_per_dataset', 'desc')
            ->groupBy('dataset_file.dataset_id')
            ->limit(1);
    }

    public function used_files_avg_per_dataset()
    {
        return $this
            ->join('dataset_file', 'dataset_file.file_version_id', 'file_version.id')
            ->whereHas('datasets', function($query){
                $query->published();
            })
            ->select(DB::raw('round(count(dataset_file.file_version_id)::NUMERIC / count(DISTINCT dataset_file.dataset_id), 1) as avg_per_dataset'));
    }
}
