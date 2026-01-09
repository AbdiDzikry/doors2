<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

use Illuminate\Support\Facades\Schema;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing rooms
        Schema::disableForeignKeyConstraints();
        Room::truncate();
        Schema::enableForeignKeyConstraints();

        $rooms = [
            [
                'id' => 1,
                'name' => 'Ruang Galunggung',
                'capacity' => 12,
                'floor' => '1',
                'description' => null,
                'facilities' => 'TV,Mind Board',
                'image_path' => 'rooms/Svu3TcusUtZhwnc9AO9ayv7DHTy6D0EytYmlmZl1.jpg',
                'status' => 'available',
            ],
            [
                'id' => 2,
                'name' => 'Ruang Kerinci',
                'capacity' => 9,
                'floor' => '1',
                'description' => null,
                'facilities' => 'TV, AC, Electric Socket, Whiteboard',
                'image_path' => 'rooms/JF6ehB8wsVLYlYhGDRFqYWgrF0rte6IsJqenfNAI.jpg',
                'status' => 'available',
            ],
            [
                'id' => 3,
                'name' => 'Ruang Arjuno',
                'capacity' => 8,
                'floor' => '2',
                'description' => null,
                'facilities' => 'Mindboard,AC',
                'image_path' => 'rooms/w5Y1oLhUqDNb9sttD0jI2GlS6IWeLEXHF81AYDr0.jpg',
                'status' => 'available',
            ],
            [
                'id' => 4,
                'name' => 'Ruang Kencana',
                'capacity' => 8,
                'floor' => '2',
                'description' => null,
                'facilities' => 'Whiteboard,TV,Projector,AC',
                'image_path' => 'rooms/HQonsoeybgP0RBSlQJqoL6a5KBddBluJQQf5wGuA.jpg',
                'status' => 'available',
            ],
            [
                'id' => 5,
                'name' => 'Ruang Merbabu',
                'capacity' => 23,
                'floor' => '2',
                'description' => null,
                'facilities' => 'Whiteboard, TV, Webcam, AC',
                'image_path' => 'rooms/MCBDmEsKVUum7rlTh3ggT573oSuhXRiDm6kQtmIF.jpg',
                'status' => 'available',
            ],
            [
                'id' => 6,
                'name' => 'Ruang Kelud',
                'capacity' => 6,
                'floor' => '2',
                'description' => null,
                'facilities' => 'Television, Air Conditioner',
                'image_path' => 'rooms/Kr9zDQ9Ke9H5NuNH03HYC893WLwir8qpuDWOP8iZ.jpg',
                'status' => 'available',
            ],
            [
                'id' => 7,
                'name' => 'Ruang Semeru',
                'capacity' => 20,
                'floor' => '2',
                'description' => null,
                'facilities' => 'TV, Webcam, Jabra, AC',
                'image_path' => 'rooms/Cf0BmUuaM5ltoOPVdDhIiwc34XumMeapV7duZITz.jpg',
                'status' => 'available',
            ],
            [
                'id' => 8,
                'name' => 'Ruang Auditorium',
                'capacity' => 200,
                'floor' => '2',
                'description' => null,
                'facilities' => 'Projector,Screen,AC,Speaker',
                'image_path' => 'rooms/Yf1l5gD3KF6tQ2TilBgjwPcmz2KWsSyzlSIWSSR4.jpg',
                'status' => 'available',
            ],
            [
                'id' => 9,
                'name' => 'Ruang Ciremai',
                'capacity' => 5,
                'floor' => '1',
                'description' => null,
                'facilities' => 'Mindboard,AC',
                'image_path' => 'rooms/hWuRvFElXSVkt8zRJEvCBJL02edyFELTyQF5xhD8.webp',
                'status' => 'available',
            ],
            [
                'id' => 24,
                'name' => 'Ruang Rinjani',
                'capacity' => 8,
                'floor' => '1',
                'description' => null,
                'facilities' => 'TV, Whiteboard, AC, Electric Socket',
                'image_path' => 'rooms/A4XkSYynMPPDDYfSuCCYsWZPm0a1Rf6g4RjN5Dig.jpg',
                'status' => 'available',
            ],
            [
                'id' => 25,
                'name' => 'Ruang Merapi',
                'capacity' => 5,
                'floor' => '1',
                'description' => null,
                'facilities' => 'Television, Air Conditioner',
                'image_path' => 'rooms/YHYOvPbbz3U1eFjxo9jhviid87wbt0vqqjyCflJG.jpg',
                'status' => 'available',
            ],
            [
                'id' => 26,
                'name' => 'Ruang Papandayan',
                'capacity' => 6,
                'floor' => '1',
                'description' => null,
                'facilities' => 'Air Conditioner, Television',
                'image_path' => 'rooms/ys860LTl1IzMExTeeGLD50cLtA46KB7mlpVBK7df.jpg',
                'status' => 'available',
            ],
            [
                'id' => 27,
                'name' => 'Ruang Raung',
                'capacity' => 14,
                'floor' => '2',
                'description' => null,
                'facilities' => 'TV, AC, Jabra, Electric Socket',
                'image_path' => 'rooms/iwSevIJyH9W37lBIkKbMG8XlMf9Z55VCZNV0Qi7M.jpg',
                'status' => 'available',
            ],
            [
                'id' => 28,
                'name' => 'Ruang Sindoro',
                'capacity' => 8,
                'floor' => null,
                'description' => null,
                'facilities' => 'TV, AC, Jabra, Webcam',
                'image_path' => 'rooms/SNJrEHf7zq2xgl7tDwaZHztkexU3n4wYyuJ3aJSd.jpg',
                'status' => 'available',
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['id' => $room['id']], $room);
        }
    }
}
