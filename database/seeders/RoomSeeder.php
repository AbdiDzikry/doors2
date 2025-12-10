<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::firstOrCreate(
            ['name' => 'Meeting Room 1'],
            ['capacity' => 10, 'description' => 'A small meeting room for up to 10 people.', 'facilities' => 'Whiteboard, Projector', 'image_path' => 'rooms/EEVTvJMpawW1seNsgc5NwkQDNHlMr5SWjnJ5laHv.png']
        );
        Room::firstOrCreate(
            ['name' => 'Meeting Room 2'],
            ['capacity' => 20, 'description' => 'A medium-sized meeting room for up to 20 people.', 'facilities' => 'Whiteboard, Projector, Video Conferencing', 'image_path' => 'rooms/tO1lrvhpvI22ZagIkpa8avpHdWYbLkZs9wrTG9m2.jpg']
        );
        Room::firstOrCreate(
            ['name' => 'Conference Room'],
            ['capacity' => 50, 'description' => 'A large conference room for up to 50 people.', 'facilities' => 'Whiteboard, Projector, Video Conferencing, Sound System', 'image_path' => 'rooms/Z1ZHe0pRFJKfcC8iOhBjGWbIQYWahr3rLzEbvX8m.jpg']
        );
    }
}
