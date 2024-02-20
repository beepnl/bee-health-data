<?php

namespace App\Exports;

use App\AuthorizationRequest;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;

class MyAccessRequestsExport implements FromQuery, WithMapping, WithHeadings, WithTitle
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
        return User::find($this->id)->authorization_requests()->latest();
    }

    public function map($authorization_request): array
    {
        return [
            $authorization_request->id,
            $authorization_request->reference,
            $authorization_request->notes,
            $authorization_request->is_pending ? 'pending' : ($authorization_request->is_approved ? 'approved' : 'rejected'),
            $authorization_request->response_note,
            $authorization_request->requested_at,
            $authorization_request->requesting_organisation ? $authorization_request->requesting_organisation->name : '',
            $authorization_request->requesting_dataset ? $authorization_request->requesting_dataset->name : '',
            $authorization_request->created_at,
            $authorization_request->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'reference',
            'notes',
            'status',
            'response_note',
            'requested_at',
            'organisation',
            'dataset',
            'created_at',
            'updated_at',
        ];
    }

    public function title(): string
    {
        return 'My access requests';
    }
}
