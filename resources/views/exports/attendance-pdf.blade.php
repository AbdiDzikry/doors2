@php
    $bgPath = public_path('images/kop_surat_lengkap.jpeg');
    $bgData = '';
    if (file_exists($bgPath)) {
        $bgData = base64_encode(file_get_contents($bgPath));
    }
    $bgSrc = $bgData ? 'data:image/jpeg;base64,' . $bgData : '';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        /** 
            Margins: 0 to allow full background
        */
        @page { margin: 0px; size: A4 portrait; }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10pt; /* Reduced from 11pt */
            /* Content Safe Zone */
            padding-top: 170px;
            padding-bottom: 180px; /* Reduced from 240px */
            padding-left: 30px;
            padding-right: 30px;
        }
        
        /* Background Image Container */
        .page-background {
            position: fixed;
            top: 0px;
            left: 0px;
            right: 0px;
            bottom: 0px;
            z-index: -1000;
        }
        .page-background img {
            width: 100%;
            height: 100%;
        }

        table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
        td, th { border: 1px solid black; padding: 3px; vertical-align: middle; } /* Padding 3px */
        .header-bg { background-color: #E6B8B7; }
        .label { font-weight: bold; width: 150px; display: inline-block; }
        .text-center { text-align: center; }
        .sig-box { vertical-align: top; height: 40px; } /* Reduced height */
        .sig-number { font-size: 8pt; float: left; margin-right: 5px; font-weight: bold; }
        
        /* Specific Table Styles */
        .info-table td { border: none; padding: 2px 5px; }
    </style>
</head>
<body>
    
    <!-- Full Page Background -->
    <div class="page-background">
        @if($bgSrc)
            <img src="{{ $bgSrc }}" />
        @endif
    </div>

    <!-- Main Content -->
    <!-- Wrapped in a div to ensure padding behavior if body is quirky in dompdf 0.8 -->
    <div style="width: 100%;">
        <div style="text-align: center; margin-bottom: 10px;">
            <div style="display: inline-block; padding: 5px 30px; border: 2px solid black; background-color: white;">
                <span style="font-size: 16pt; font-weight: bold; letter-spacing: 2px;">DAFTAR HADIR</span>
            </div>
        </div>

        <!-- Meeting Info -->
        <table class="info-table" style="margin-bottom: 10px; border: 1px solid black; padding: 5px; background-color: white;">
            <tr>
                <td width="150" class="label">Topik</td>
                <td>: {{ $meeting->topic }}</td>
            </tr>
            <tr>
                <td class="label">Hari, Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($meeting->start_time)->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td>: {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }} WIB</td>
            </tr>
            <tr>
                <td class="label">Tempat</td>
                <td>: {{ str_replace('Ruang ', '', $meeting->room->name ?? '-') }}</td>
            </tr>
        </table>

        <!-- Attendance Table -->
        <table>
            <thead>
                <tr class="header-bg">
                    <th class="text-center" width="5%">NO</th>
                    <th class="text-center" width="30%">NAMA</th>
                    <th class="text-center" width="20%">DIVISION/DEPT</th>
                    <th class="text-center" width="10%">JAM</th>
                    <th class="text-center" width="10%">STATUS</th>
                    <th class="text-center" width="25%">TANDA TANGAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $index => $participant)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $participant->participant ? $participant->participant->name : 'Guest' }}
                    </td>
                    <td class="text-center">
                        @if($participant->participant_type == 'App\Models\User')
                            {{ $participant->participant->department ?? '-' }}
                        @else
                            {{ $participant->participant->agency ?? $participant->participant->institution ?? 'Eksternal' }}
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $participant->attended_at ? \Carbon\Carbon::parse($participant->attended_at)->format('H:i') : '-' }}
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        {{ $participant->attended_at ? 'HADIR' : '-' }}
                    </td>
                    <td class="sig-box">
                        @if($participant->attended_at)
                            <div style="text-align: center; font-style: italic; color: #555; margin-top: 15px;">Already Assigned</div>
                        @else
                            <span class="sig-number">{{ $index + 1 }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach

                <!-- Empty Rows Filler -->
                @for($i = 0; $i < (10 - count($participants)); $i++)
                @php $currNo = count($participants) + $i + 1; @endphp
                <tr>
                    <td class="text-center">{{ $currNo }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="sig-box"><span class="sig-number">{{ $currNo }}</span></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <!-- Page Numbering Script -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Arial, sans-serif", "normal");
            $size = 9;
            $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $y = 800;
            $x = 520;
            $pdf->page_text($x, $y, $pageText, $font, $size);
        }
    </script>
</body>
</html>
