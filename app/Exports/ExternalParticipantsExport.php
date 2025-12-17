<?php

namespace App\Exports;

use App\Models\ExternalParticipant;

class ExternalParticipantsExport
{
    public function collection()
    {
        return ExternalParticipant::select('name', 'email', 'phone', 'company', 'address')->get();
    }

    public function headings(): array
    {
        return [
            'NAME',
            'EMAIL',
            'PHONE',
            'COMPANY',
            'ADDRESS',
        ];
    }
}
