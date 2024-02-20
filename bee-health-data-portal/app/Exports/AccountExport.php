<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccountExport implements FromQuery, WithTitle, WithHeadings, WithMapping
{
    use Exportable;

    public function forUser(string $id)
    {
        $this->id = $id;
        return $this;
    }

    public function query()
    {
        return User::query()->where('id', $this->id);
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->firstname,
            $user->lastname,
            $user->email,
            $user->accepted_terms_and_conditions,
            $user->email_verified_at,
            $user->last_login,
            $user->api_token,
            $user->remember_token,
            $user->created_at,
            $user->updated_at,
        ];
    }



    public function headings(): array
    {
        return [
            '#',
            'firstname',
            'lastname',
            'email',
            'accepted_terms_and_conditions',
            'email_verified_at',
            'last_login',
            'api_token',
            'remember_token',
            'created_at',
            'updated_at',
        ];
    }

    public function title() :string
    {
        return 'Account data';
    }
}
