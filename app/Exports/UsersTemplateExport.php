<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class UsersTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection();
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
