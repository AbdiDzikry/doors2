<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();
        $users = User::all();

        if ($rooms->isEmpty() || $users->isEmpty()) {
            $this->command->info('Skipping MeetingSeeder: No rooms or users found. Please run RoomSeeder and UserSeeder first.');
            return;
        }

        Meeting::truncate();

        $faker = \Faker\Factory::create();
        $rooms = Room::all();
        $users = User::all();
        $statuses = ['scheduled', 'cancelled'];
        $meetingTypes = ['internal', 'external'];

        foreach (range(1, 20) as $index) {
            $startTime = Carbon::now()->addDays($faker->numberBetween(1, 30))->addHours($faker->numberBetween(1, 12));
            $endTime = $startTime->copy()->addHours($faker->numberBetween(1, 3));

            Meeting::create([
                'user_id' => $users->random()->id,
                'room_id' => $rooms->random()->id,
                'topic' => $faker->sentence(3),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $faker->randomElement($statuses),
                'meeting_type' => $faker->randomElement($meetingTypes),
            ]);
        }

        // Create 10 meetings for the current week
        foreach (range(1, 10) as $index) {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $randomDate = $faker->dateTimeBetween($startOfWeek, $endOfWeek);
            $startTime = Carbon::instance($randomDate);
            $endTime = $startTime->copy()->addHours($faker->numberBetween(1, 3));

            Meeting::create([
                'user_id' => $users->random()->id,
                'room_id' => $rooms->random()->id,
                'topic' => $faker->sentence(3),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $faker->randomElement($statuses),
                'meeting_type' => $faker->randomElement($meetingTypes),
            ]);
        }

        // Create a specific meeting for testing purposes
        Meeting::create([
            'user_id' => $users->random()->id,
            'room_id' => $rooms->random()->id,
            'topic' => 'Test Meeting for Day Filter',
            'start_time' => '2025-11-17 10:00:00',
            'end_time' => '2025-11-17 11:00:00',
            'status' => 'scheduled',
            'meeting_type' => 'internal',
        ]);

        $this->command->info('MeetingSeeder: 31 dummy meetings created.');
    }
}