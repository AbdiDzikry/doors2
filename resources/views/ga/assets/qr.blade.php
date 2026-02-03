<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR - {{ $asset->sku }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .print-container {
                border: 1px solid #ccc;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col items-center min-h-screen pt-10">

    <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-sm w-full print-container">
        <h2 class="text-xl font-bold text-gray-800 mb-1">LAPOR KERUSAKAN</h2>
        <p class="text-sm text-gray-500 mb-4">Scan QR code ini untuk melapor</p>

        <div class="flex justify-center mb-4">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($url) }}" alt="QR Code"
                class="border p-2 rounded">
        </div>

        <div class="text-left border-t pt-4 mt-2">
            <p class="text-sm"><strong>Unit:</strong> {{ $asset->name }}</p>
            <p class="text-sm"><strong>Lokasi:</strong> {{ $asset->location }}</p>
            <p class="text-xs text-gray-400 mt-2">SKU: {{ $asset->sku }}</p>
        </div>
    </div>

    <div class="mt-8 no-print flex gap-4">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded shadow hover:bg-blue-700">Print
            Sticker</button>
        <button onclick="window.close()"
            class="bg-gray-500 text-white px-6 py-2 rounded shadow hover:bg-gray-600">Close</button>
    </div>

</body>

</html>