<?php

namespace App\Exports;

use App\Models\ExternalParticipant;

class ExternalParticipantsExport
{
    public function collection()
    {
        return ExternalParticipant::select('name', 'email', 'phone', 'company', 'department', 'address')->get();
    }

    public function headings(): array
    {
        return [
            'NAME',
            'EMAIL',
            'PHONE',
            'COMPANY',
            'DEPARTMENT',
            'ADDRESS',
        ];
    }
}
