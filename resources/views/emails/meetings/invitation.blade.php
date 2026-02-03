<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f6;
            padding-bottom: 40px;
        }

        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .header {
            background: linear-gradient(135deg, #089244 0%, #067236 100%);
            padding: 30px 40px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .content {
            padding: 40px;
            text-align: left;
            color: #333333;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1a1a1a;
            font-weight: 600;
        }

        .intro-text {
            font-size: 15px;
            line-height: 1.6;
            color: #555555;
            margin-bottom: 30px;
        }

        .details-box {
            background-color: #f8f9fa;
            border-left: 5px solid #089244;
            padding: 25px;
            border-radius: 4px;
            margin-bottom: 30px;
        }

        .detail-row {
            margin-bottom: 12px;
            font-size: 15px;
            display: flex;
            align-items: flex-start;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 700;
            color: #444;
            width: 100px;
            flex-shrink: 0;
        }

        .detail-value {
            color: #222;
            flex-grow: 1;
        }

        .cta-container {
            text-align: center;
            margin-top: 35px;
            margin-bottom: 20px;
        }

        .button {
            background-color: #089244;
            color: #ffffff !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            display: inline-block;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(8, 146, 68, 0.2);
        }

        .button:hover {
            background-color: #067236;
        }

        .footer {
            background-color: #eeeeee;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #e0e0e0;
        }

        .footer p {
            margin: 5px 0;
        }

        @media only screen and (max-width: 600px) {
            .content {
                padding: 25px;
            }

            .header {
                padding: 25px 20px;
            }

            .detail-row {
                flex-direction: column;
            }

            .detail-label {
                width: 100%;
                margin-bottom: 3px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <br>
        <table class="main-table">
            <!-- Header -->
            <tr>
                @php
                    $headerBg = match ($type ?? 'invitation') {
                        'cancellation' => 'linear-gradient(135deg, #d32f2f 0%, #c62828 100%)', // Red
                        'update' => 'linear-gradient(135deg, #1976d2 0%, #1565c0 100%)',       // Blue
                        default => 'linear-gradient(135deg, #089244 0%, #067236 100%)',        // Green
                    };
                    $headerTitle = match ($type ?? 'invitation') {
                        'cancellation' => 'Meeting Dibatalkan',
                        'update' => 'Update Meeting',
                        default => 'Meeting Invitation',
                    };
                @endphp
                <td class="header" style="background: {{ $headerBg }};">
                    <h1>{{ $headerTitle }}</h1>
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td class="content">
                    <div class="greeting">Halo,</div>
                    <p class="intro-text">
                        @if(($type ?? 'invitation') === 'cancellation')
                            Mohon maaf, agenda meeting berikut telah <strong>DIBATALKAN</strong> oleh penyelenggara.
                        @elseif(($type ?? 'invitation') === 'update')
                            Terdapat <strong>perubahan/update</strong> pada detail agenda meeting berikut.
                        @else
                            Anda telah diundang untuk menghadiri agenda meeting berikut melalui <strong>Doors
                                System</strong>.
                        @endif
                    </p>

                    <!-- Details Card -->
                    <div class="details-box"
                        style="border-left-color: {{ ($type ?? 'invitation') === 'cancellation' ? '#d32f2f' : (($type ?? 'invitation') === 'update' ? '#1976d2' : '#089244') }};">
                        <div class="detail-row">
                            <div class="detail-label">Topik</div>
                            <div class="detail-value">{{ $meeting->topic }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Waktu</div>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($meeting->start_time)->format('d M Y') }}<br>
                                <span
                                    style="font-weight:600; color: {{ ($type ?? 'invitation') === 'cancellation' ? '#d32f2f' : '#089244' }};">
                                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                                </span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Ruangan</div>
                            <div class="detail-value">{{ $meeting->room->name ?? 'TBA' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Deskripsi</div>
                            <div class="detail-value">{{ $meeting->description ?? '-' }}</div>
                        </div>
                    </div>

                    @if(($type ?? 'invitation') !== 'cancellation')
                        <div class="cta-container">
                            @php
                                $googleLink = \App\Helpers\IcsGenerator::generateGoogleLink($meeting);
                                $outlookLink = \App\Helpers\IcsGenerator::generateOutlookLink($meeting);
                            @endphp

                            <!-- Bulletproof Buttons -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" style="border-radius: 50px; padding-right: 10px;"
                                                    bgcolor="#089244">
                                                    <a href="{{ $googleLink }}" target="_blank"
                                                        style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 50px; border: 1px solid #089244; display: inline-block; font-weight: bold;">
                                                        Add to Google
                                                    </a>
                                                </td>
                                                <td align="center" style="border-radius: 50px; padding-left: 10px;"
                                                    bgcolor="#0078d4">
                                                    <a href="{{ $outlookLink }}" target="_blank"
                                                        style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 50px; border: 1px solid #0078d4; display: inline-block; font-weight: bold;">
                                                        Add to Outlook
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 13px; color: #666; margin-top: 15px; margin-bottom: 10px;">
                                (Untuk Outlook Desktop: Buka file lampiran <strong>invite.ics</strong>)
                            </p>
                        </div>
                    @endif
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p>&copy; {{ date('Y') }} Dharma Polimetal - Doors System</p>
                    <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                </td>
            </tr>
        </table>
        <br>
    </div>
</body>

</html>