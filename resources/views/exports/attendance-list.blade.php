@php
    $logoPath = public_path('logo_dharma.png');
    $logoData = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
    }
    // Ensure properly formatted Base64 string for HTML
    $logoSrc = $logoData ? 'data:image/png;base64,' . $logoData : '';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid black; padding: 5px; vertical-align: middle; font-size: 11pt; }
        .header-bg { background-color: #E6B8B7; } /* Salmon Pink */
        .title { font-size: 16pt; font-weight: bold; text-align: center; }
        .label { font-weight: bold; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .logo-cell { text-align: center; vertical-align: middle; background-color: white; }
        .sig-box { vertical-align: top; height: 40px; }
        .sig-number { font-size: 8pt; float: left; margin-right: 5px; font-weight: bold; }
        .sig-content { font-size: 10pt; text-align: center; margin-top: 5px; }
    </style>
</head>
<body>
    <table>
        <!-- HEADERS -->
        <tr>
            <!-- Logo: Spans 6 Rows -->
            <td rowspan="6" class="logo-cell" width="180" style="width: 180px;">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" width="150" height="auto" />
                    <br><br>
                @endif
                <strong>PT Dharma Polimetal Tbk</strong>
            </td>
            <!-- Title -->
            <td colspan="5" class="header-bg title" height="40">DAFTAR HADIR</td>
        </tr>
        
        <!-- Info Rows -->
        <tr>
            <td colspan="5" class="text-left"><span class="label">Topik &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</span> {{ $meeting->topic }}</td>
        </tr>
        <tr>
            <td colspan="5" class="text-left"><span class="label">Hari, Tanggal :</span> {{ \Carbon\Carbon::parse($meeting->start_time)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="5" class="text-left"><span class="label">Waktu &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</span> {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }}</td>
        </tr>
        <tr>
            <td colspan="5" class="text-left"><span class="label">Tempat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</span> {{ $meeting->room->name ?? '-' }}</td>
        </tr>
        
        <!-- Table Column Headers -->
        <tr class="header-bg">
            <th class="text-center" width="50" style="width: 50px;">NO</th>
            <th class="text-center" width="250" style="width: 250px;">NAMA</th>
            <th class="text-center" width="200" style="width: 200px;">DIVISION/DEPT</th>
            <th class="text-center" width="150" style="width: 150px;">JAM REGISTRASI</th>
            <!-- Signature Column Spans 2 -->
            <th class="text-center" colspan="2" width="200" style="width: 200px;">TANDA TANGAN</th>
        </tr>

        <!-- DATA ROWS -->
        @foreach($participants as $index => $mp)
        <tr>
            <td class="text-center" height="40">{{ $index + 1 }}</td>
            <td>{{ $mp->participant->name ?? '-' }}</td>
            <td class="text-center">
                @if($mp->participant_type == 'App\Models\User')
                    {{ $mp->participant->department ?? '-' }}
                @else
                    {{ $mp->participant->agency ?? $mp->participant->institution ?? 'Eksternal' }}
                @endif
            </td>
            <td class="text-center">
                {{ $mp->attended_at ? \Carbon\Carbon::parse($mp->attended_at)->format('H:i') : '' }}
            </td>

            <!-- Signature Logic: Zig-Zag -->
            @if( ($index + 1) % 2 != 0 )
                <!-- ODD Row (1, 3, 5...): Sign Left -->
                <td class="sig-box" width="100" style="width: 100px;">
                    <span class="sig-number">{{ $index + 1 }}</span>
                    <div class="sig-content">
                        @if($mp->attended_at) (Hadir) ✅ @endif
                    </div>
                </td>
                <td class="sig-box" width="100" style="width: 100px;"></td> <!-- Right Empty -->
            @else
                <!-- EVEN Row (2, 4, 6...): Sign Right -->
                <td class="sig-box" width="100" style="width: 100px;"></td> <!-- Left Empty -->
                <td class="sig-box" width="100" style="width: 100px;">
                    <span class="sig-number">{{ $index + 1 }}</span>
                    <div class="sig-content">
                        @if($mp->attended_at) (Hadir) ✅ @endif
                    </div>
                </td>
            @endif
        </tr>
        @endforeach

        <!-- FILLER ROWS -->
        @for($i = 0; $i < (15 - count($participants)); $i++)
        @php $currNo = count($participants) + $i + 1; @endphp
        <tr>
            <td class="text-center" height="40">{{ $currNo }}</td>
            <td></td>
            <td></td>
            <td></td>
            
            @if( $currNo % 2 != 0 )
                <!-- ODD -->
                <td class="sig-box"><span class="sig-number">{{ $currNo }}</span></td>
                <td class="sig-box"></td>
            @else
                <!-- EVEN -->
                <td class="sig-box"></td>
                <td class="sig-box"><span class="sig-number">{{ $currNo }}</span></td>
            @endif
        </tr>
        @endfor
    </table>
</body>
</html>
