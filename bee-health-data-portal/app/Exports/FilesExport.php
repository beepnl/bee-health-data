<?php

namespace App\Exports;

use App\Models\FileVersion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class FilesExport implements FromQuery, WithMapping, WithHeadings, WithTitle
{
    use Exportable;

    public function forUser(string $id)
    {
        $this->id = $id;
        return $this;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        $user_id = $this->id;
        return FileVersion::whereHas('datasets', function($query) use ($user_id){
            return $query->where('user_id', $user_id);
        });
    }

    public function map($file): array
    {
        return [
            $file->id,
            $file->filename,
            $file->description,
            $file->file_format,
            $file->size,
            $file->version,
            $this->getFileTemporaryUrl($file),
            $file->created_at,
            $file->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'filename',
            'description',
            'file_format',
            'size',
            'version',
            'url',
            'created_at',
            'updated_at',
        ];
    }

    public function title(): string
    {
        return 'My access requests';
    }

    private function getFileTemporaryUrl($file)
    {
        $adapter = Storage::getDriver()->getAdapter();

        if($adapter instanceof AwsS3Adapter){
            return Storage::temporaryUrl(
                $file->name,
                now()->addMinutes(config('filesystems.temporary_url_expiration')),
                [
                    'ResponseContentType' => 'application/octet-stream',
                    'ResponseContentDisposition' => 'attachment; filename=' . $file->name,
                ]
            );
        }else{
            return URL::route('files.show', ['file_version'=>$file]);
        }
    }
}
