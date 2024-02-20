<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;

class MyDatasetsExport implements FromQuery, WithMapping, WithHeadings, WithTitle
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
        return User::find($this->id)->datasets()->with('authors')->with('keywords')->latest();
    }

    public function map($dataset): array
    {
        return [
            $dataset->id,
            $dataset->name,
            $dataset->files->pluck('id')->implode(', '),
            $dataset->keywords->pluck('name')->implode(', '),
            $dataset->organisation ? $dataset->organisation->name : '',
            $dataset->authors->append('fullname')->pluck('fullname')->implode(', '),
            $dataset->description,
            $dataset->digital_object_identifier,
            $dataset->publication_state,
            $dataset->access_type,
            $dataset->number_files ? $dataset->number_files : 0,
            $dataset->created_at,
            $dataset->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'name',
            'files',
            'keywords',
            'organisation',
            'authors',
            'description',
            'digital_object_identifier',
            'publication_state',
            'access_type',
            'number_files',
            'created_at',
            'updated_at',
        ];
    }

    public function title(): string
    {
        return 'My datasets';
    }
}
