<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyMeetingsSupplementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cleanup old data starting from the new data's range (2026-01-27 onwards)
        // This preserves history/legacy data before this date (e.g. the Jan 26 meetings)
        $cutoffDate = '2026-01-27 00:00:00';
        
        // Get IDs to delete participants
        $meetingIdsToDelete = DB::table('meetings')
            ->where('start_time', '>=', $cutoffDate)
            ->pluck('id');
            
        DB::table('meeting_participants')
            ->whereIn('meeting_id', $meetingIdsToDelete)
            ->delete();

        DB::table('meetings')
            ->where('start_time', '>=', $cutoffDate)
            ->delete();

        $meetings = [
            // 2026-01-27
            ['room_id' => 4, 'user_id' => 1337, 'topic' => 'meeting IPP infra', 'start_time' => '2026-01-27 13:30:00', 'end_time' => '2026-01-27 16:00:00'],
            ['room_id' => 27, 'user_id' => 1368, 'topic' => 'proses rpa yg di jalankan di adm', 'start_time' => '2026-01-27 10:00:00', 'end_time' => '2026-01-27 12:00:00'],
            ['room_id' => 4, 'user_id' => 535, 'topic' => 'Meeting Marketing 4W', 'start_time' => '2026-01-27 07:00:00', 'end_time' => '2026-01-27 11:00:00'],
            ['room_id' => 24, 'user_id' => 454, 'topic' => 'Meeting dengan PT. HMMI & POSCO IJPC', 'start_time' => '2026-01-27 10:00:00', 'end_time' => '2026-01-27 16:00:00'],
            ['room_id' => 25, 'user_id' => 454, 'topic' => 'meeting dengan supplier baru (PIC Emanuel)', 'start_time' => '2026-01-27 09:00:00', 'end_time' => '2026-01-27 12:00:00'],
            ['room_id' => 7, 'user_id' => 478, 'topic' => 'Meeting Internal', 'start_time' => '2026-01-27 13:00:00', 'end_time' => '2026-01-27 17:00:00'],
            ['room_id' => 1, 'user_id' => 522, 'topic' => 'Review Sosialisasi STO vendor tahun 2026', 'start_time' => '2026-01-27 09:30:00', 'end_time' => '2026-01-27 12:00:00'],
            ['room_id' => 5, 'user_id' => 1368, 'topic' => 'Meeting Weekly RPA', 'start_time' => '2026-01-27 10:00:00', 'end_time' => '2026-01-27 11:30:00'],

            // 2026-01-28
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-01-28 14:00:00', 'end_time' => '2026-01-28 17:00:00'],
            ['room_id' => 4, 'user_id' => 1259, 'topic' => 'PSP', 'start_time' => '2026-01-28 10:00:00', 'end_time' => '2026-01-28 11:30:00'],
            ['room_id' => 8, 'user_id' => 521, 'topic' => 'Eksternal', 'start_time' => '2026-01-28 10:00:00', 'end_time' => '2026-01-28 16:00:00'],
            ['room_id' => 7, 'user_id' => 478, 'topic' => 'Meeting Internal', 'start_time' => '2026-01-28 08:00:00', 'end_time' => '2026-01-28 12:00:00'],

            // 2026-01-29
            ['room_id' => 7, 'user_id' => 535, 'topic' => 'Meeting with HMC & Ge-stamp', 'start_time' => '2026-01-29 10:00:00', 'end_time' => '2026-01-29 16:00:00'],
            ['room_id' => 25, 'user_id' => 454, 'topic' => 'Meeting dengan DAEHO (PIC: NUEL)', 'start_time' => '2026-01-29 13:00:00', 'end_time' => '2026-01-29 16:00:00'],
            ['room_id' => 8, 'user_id' => 591, 'topic' => 'Training Ms.Excel Intermediate', 'start_time' => '2026-01-29 07:00:00', 'end_time' => '2026-01-29 17:00:00'],

            // 2026-01-30
            ['room_id' => 4, 'user_id' => 1125, 'topic' => 'Internal', 'start_time' => '2026-01-30 09:00:00', 'end_time' => '2026-01-30 11:00:00'],
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-01-30 08:00:00', 'end_time' => '2026-01-30 12:00:00'],

            // 2026-02-02
            ['room_id' => 27, 'user_id' => 475, 'topic' => 'Internal', 'start_time' => '2026-02-02 13:30:00', 'end_time' => '2026-02-02 17:30:00'],

            // 2026-02-04
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-02-04 14:00:00', 'end_time' => '2026-02-04 17:00:00'],

            // 2026-02-05
            ['room_id' => 8, 'user_id' => 710, 'topic' => 'Konsultan ESG', 'start_time' => '2026-02-05 09:00:00', 'end_time' => '2026-02-05 12:00:00'],

            // 2026-02-06
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-02-06 08:00:00', 'end_time' => '2026-02-06 12:00:00'],
            ['room_id' => 5, 'user_id' => 475, 'topic' => 'Internal', 'start_time' => '2026-02-06 08:00:00', 'end_time' => '2026-02-06 12:00:00'],
            ['room_id' => 8, 'user_id' => 364, 'topic' => 'DONOR DARAH', 'start_time' => '2026-02-06 08:30:00', 'end_time' => '2026-02-06 11:30:00'],

            // 2026-02-09
            ['room_id' => 4, 'user_id' => 1259, 'topic' => 'PSP', 'start_time' => '2026-02-09 08:00:00', 'end_time' => '2026-02-09 09:30:00'],

            // 2026-02-10
            ['room_id' => 8, 'user_id' => 1260, 'topic' => 'training', 'start_time' => '2026-02-10 07:00:00', 'end_time' => '2026-02-12 18:00:00'],

            // 2026-02-11
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-02-11 14:00:00', 'end_time' => '2026-02-11 17:00:00'],

            // 2026-02-13
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-02-13 08:00:00', 'end_time' => '2026-02-13 12:00:00'],
            ['room_id' => 7, 'user_id' => 1020, 'topic' => 'HPM Visit', 'start_time' => '2026-02-13 08:30:00', 'end_time' => '2026-02-13 12:00:00'],

            // 2026-02-18
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-02-18 14:00:00', 'end_time' => '2026-02-18 17:00:00'],

            // 2026-02-20
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-02-20 08:00:00', 'end_time' => '2026-02-20 12:00:00'],
            ['room_id' => 5, 'user_id' => 475, 'topic' => 'Internal', 'start_time' => '2026-02-20 08:00:00', 'end_time' => '2026-02-20 14:00:00'],

            // 2026-02-25
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-02-25 13:00:00', 'end_time' => '2026-02-25 16:00:00'],

            // 2026-02-27
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-02-27 08:00:00', 'end_time' => '2026-02-27 12:00:00'],

            // 2026-03-04
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-03-04 13:00:00', 'end_time' => '2026-03-04 16:00:00'],

            // 2026-03-06
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-03-06 07:30:00', 'end_time' => '2026-03-06 12:00:00'],

            // 2026-03-11
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-03-11 13:00:00', 'end_time' => '2026-03-11 16:00:00'],

            // 2026-03-13
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-03-13 07:30:00', 'end_time' => '2026-03-13 12:00:00'],

            // 2026-03-27
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-03-27 08:00:00', 'end_time' => '2026-03-27 12:00:00'],

            // 2026-04-01
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly CR SR Meeting', 'start_time' => '2026-04-01 14:00:00', 'end_time' => '2026-04-01 17:00:00'],

            // 2026-04-10
            ['room_id' => 27, 'user_id' => 980, 'topic' => 'Weekly Meeting Koordinasi FA & IT', 'start_time' => '2026-04-10 08:00:00', 'end_time' => '2026-04-10 12:00:00'],
        ];

        foreach ($meetings as $meetingData) {
            $meetingId = DB::table('meetings')->insertGetId(array_merge($meetingData, [
                'status' => 'scheduled',
                'meeting_type' => 'internal', // Default value
                'created_at' => '2026-01-26 15:14:00',
                'updated_at' => '2026-01-26 15:14:00',
            ]));
            
            DB::table('meeting_participants')->insert([
                'meeting_id' => $meetingId,
                'participant_type' => 'App\Models\User',
                'participant_id' => $meetingData['user_id'],
                'is_pic' => true,
                'status' => 2, // Confirmed
                'created_at' => '2026-01-26 15:14:00',
                'updated_at' => '2026-01-26 15:14:00',
            ]);
        }
    }
}
