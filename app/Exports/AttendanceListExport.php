<?php

namespace App\Exports;

use App\Models\Meeting;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceListExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithDrawings, WithCustomStartCell
{
    protected $meeting;
    protected $rowNumber = 0;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function collection()
    {
        return $this->meeting->meetingParticipants()->with('participant')->get();
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA',
            'DIVISION/DEPT',
            'JAM REGISTRASI',
            'KET (DIGITAL)'
        ];
    }

    public function map($participant): array
    {
        $this->rowNumber++;
        
        $name = $participant->participant->name ?? 'N/A';
        $division = '-';
        if ($participant->participant_type === User::class && $participant->participant) {
            $division = $participant->participant->department ?? '-';
        } elseif ($participant->participant) {
            // Check if ExternalParticipant has 'agency' or similar
            $division = $participant->participant->agency ?? $participant->participant->institution ?? 'External';
        }

        $time = $participant->attended_at ? \Carbon\Carbon::parse($participant->attended_at)->format('H:i') : '';
        $ket = $participant->attended_at ? 'Hadir' : 'Belum Hadir';

        return [
            $this->rowNumber,
            $name,
            $division,
            $time,
            $ket
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        // Use standard public path structure
        $path = public_path('logo_dharma.png'); 
        
        // Fallback if specific logo missing
        if (!file_exists($path)) {
             // Try other common paths or allow empty
             $path = public_path('images/pwa-icon-192x192.png');
        }

        if (file_exists($path)) {
            $drawing->setPath($path);
            $drawing->setHeight(50);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(10);
        }

        return $drawing ? [$drawing] : [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']]], // Header Row
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $meeting = $this->meeting;

                // Merge Cells for Header Layout
                // Left Box: Logo & Company Name (A1:A4) -> Actually A1:B4 if logo is wide? No, User image has simple columns.
                // Let's assume A1:A4 is for Logo/ Company Name
                // B1:E5 are details.
                // Replicating EXACT image: 
                // Left Header Box (Logo + PT Name): A1:A4
                // Right Header Box:
                //   Row 1 (B1:E1): "DAFTAR HADIR"
                //   Row 2 (B2:E2): Topik
                //   Row 3 (B3:E3): Hari/Tanggal
                //   Row 4 (B4:E4): Waktu / Tempat

                // Merge Cells
                $sheet->mergeCells('A1:A4'); // Logo Area
                $sheet->mergeCells('B1:E1'); // Title
                $sheet->mergeCells('B2:E2'); // Topik
                $sheet->mergeCells('B3:E3'); // Hari
                $sheet->mergeCells('B4:E4'); // Waktu

                // Set Text
                $sheet->setCellValue('A1', "PT Dharma Polimetal Tbk"); 
                $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM)->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('B1', 'DAFTAR HADIR');
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B1')->getFill()->setFillType('solid')->getStartColor()->setRGB('E0E0E0'); // Light Gray

                $sheet->setCellValue('B2', 'Topik : ' . $meeting->topic);
                $sheet->setCellValue('B3', 'Hari/Tanggal : ' . \Carbon\Carbon::parse($meeting->start_time)->translatedFormat('l, d F Y'));
                
                $startTime = \Carbon\Carbon::parse($meeting->start_time)->format('H:i');
                $roomName = $meeting->room->name ?? '-';
                $sheet->setCellValue('B4', "Waktu : {$startTime}   Tempat : {$roomName}");

                // Borders for Header
                $headerStyleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];
                $sheet->getStyle('A1:E4')->applyFromArray($headerStyleArray);

                // Borders for Table (Starting Row 6 to End)
                $highestRow = $sheet->getHighestRow();
                $tableStyleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];
                $sheet->getStyle('A6:E'.$highestRow)->applyFromArray($tableStyleArray);

                // Auto Size Columns
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
