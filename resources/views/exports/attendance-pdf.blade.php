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
        @page { margin: 20px; size: A4 portrait; }
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        td, th { border: 1px solid black; padding: 5px; vertical-align: middle; }
        .header-bg { background-color: #E6B8B7; } /* Salmon Pink */
        .title { font-size: 16pt; font-weight: bold; text-align: center; }
        .label { font-weight: bold; width: 150px; display: inline-block; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .logo-cell { text-align: center; vertical-align: middle; background-color: white; border: 1px solid black; }
        .sig-box { vertical-align: top; height: 50px; }
        .sig-number { font-size: 8pt; float: left; margin-right: 5px; font-weight: bold; }
        .sig-content { font-size: 10pt; text-align: center; margin-top: 15px; font-weight: bold; color: #089244; }
        
        /* Layout Header Table */
        .info-table td { border: none; padding: 2px 5px; }
        .main-header-table td { border: 1px solid black; }
    </style>
</head>
<body>
    
    <!-- Main Header (Kop Surat Style) -->
    <table class="main-header-table" style="border: none; margin-bottom: 0;">
        <tr>
            <!-- Logo Column -->
            <td width="15%" style="border: none; text-align: left; vertical-align: middle;">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" style="max-width: 100px; height: auto;" />
                @else
                    <div style="font-weight: bold; font-size: 20pt; color: #089244;">DHARMA</div>
                @endif
            </td>
            <!-- Company Info -->
            <td width="85%" style="border: none; text-align: left; vertical-align: middle; padding-left: 20px;">
                <div style="font-size: 16pt; font-weight: bold; color: #333; margin-bottom: 2px;">PT DHARMA POLIMETAL Tbk</div>
                <div style="font-size: 9pt; color: #666; line-height: 1.4;">
                    Kawasan Industri Jababeka I, Jl. Jababeka V No. 1, Cikarang, Bekasi, Jawa Barat 17530<br>
                    Phone: (021) 8935010 | Email: corporate.secretary@dharmap.com | Website: www.dharmap.com
                </div>
            </td>
        </tr>
    </table>
    
    <!-- Double Horizontal Line for Kop Surat -->
    <div style="border-top: 3px solid black; margin-top: 10px; margin-bottom: 1px;"></div>
    <div style="border-top: 1px solid black; margin-bottom: 20px;"></div>

    <div style="text-align: center; margin-bottom: 20px;">
        <div style="display: inline-block; padding: 5px 30px; border: 2px solid black; background-color: #f3f4f6;">
            <span style="font-size: 16pt; font-weight: bold; letter-spacing: 2px;">DAFTAR HADIR</span>
        </div>
    </div>

    <!-- Meeting Info -->
    <table class="info-table" style="margin-bottom: 20px; border: 1px solid black; padding: 10px;">
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
                <th class="text-center" width="25%">DIVISION/DEPT</th>
                <th class="text-center" width="15%">JAM</th>
                <th class="text-center" colspan="2" width="25%">TANDA TANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $index => $mp)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $mp->participant ? $mp->participant->name : 'Guest' }}
                </td>
                <td class="text-center">
                    @if($mp->participant_type == 'App\Models\User')
                        {{ $mp->participant->department ?? '-' }}
                    @else
                        {{ $mp->participant->agency ?? $mp->participant->institution ?? 'Eksternal' }}
                    @endif
                </td>
                <td class="text-center">
                    {{ $mp->attended_at ? \Carbon\Carbon::parse($mp->attended_at)->format('H:i') : '-' }}
                </td>

                <!-- Zig Zag Signature -->
                @if( ($index + 1) % 2 != 0 )
                    <td class="sig-box" width="12.5%">
                        <span class="sig-number">{{ $index + 1 }}</span>
                        @if($mp->attended_at)
                            <div class="sig-content">HADIR ✔</div>
                        @endif
                    </td>
                    <td class="sig-box" width="12.5%"></td>
                @else
                    <td class="sig-box" width="12.5%"></td>
                    <td class="sig-box" width="12.5%">
                        <span class="sig-number">{{ $index + 1 }}</span>
                        @if($mp->attended_at)
                            <div class="sig-content">HADIR ✔</div>
                        @endif
                    </td>
                @endif
            </tr>
            @endforeach

            <!-- Empty Rows Filler (Ensure at least 15 rows or fill page) -->
            @for($i = 0; $i < (15 - count($participants)); $i++)
            @php $currNo = count($participants) + $i + 1; @endphp
            <tr>
                <td class="text-center">{{ $currNo }}</td>
                <td></td>
                <td></td>
                <td></td>
                @if( $currNo % 2 != 0 )
                    <td class="sig-box"><span class="sig-number">{{ $currNo }}</span></td>
                    <td class="sig-box"></td>
                @else
                    <td class="sig-box"></td>
                    <td class="sig-box"><span class="sig-number">{{ $currNo }}</span></td>
                @endif
            </tr>
            @endfor
        </tbody>
    </table>

</body>
</html>
