<?php

namespace App\Exports;

use App\Models\User;

class UsersExport
{
    public function collection(): \Illuminate\Support\Collection
    {
        return User::select('name', 'npk', 'division', 'department', 'position', 'email', 'phone')->get();
    }

    public function headings(): array
    {
        return [
            'FULL NAME',
            'NPK',
            'DIVISION',
            'DEPARTMENT',
            'POSITION',
            'EMAIL',
            'PHONE',
        ];
    }
}
