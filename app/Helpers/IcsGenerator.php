<?php

namespace App\Helpers;

use App\Models\Meeting;
use Carbon\Carbon;

class IcsGenerator
{
    /**
     * Generate ICS content for a meeting
     *
     * @param Meeting $meeting
     * @return string
     */
    public static function generate(Meeting $meeting)
    {
        $dtStart = Carbon::parse($meeting->start_time)->format('Ymd\THis');
        $dtEnd = Carbon::parse($meeting->end_time)->format('Ymd\THis');
        $now = Carbon::now()->format('Ymd\THis\Z');

        $summary = $meeting->topic;
        $description = $meeting->description ?? 'Meeting via Doors System';
        $location = $meeting->room->name ?? 'TBA';

        // Try to find PIC from participants pivot, fallback to creator
        $pic = $meeting->participants()->wherePivot('is_pic', true)->first() ?? $meeting->user;

        $organizer = $pic->name ?? 'Doors Admin';
        $organizerEmail = $pic->email ?? 'noreply@doors.dharmap.com';
        $uid = $meeting->id . '@doors.dharmap.com';

        // VCALENDAR Format
        return "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-//Doors System//Meeting//EN\r\n" .
            "METHOD:REQUEST\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:{$uid}\r\n" .
            "DTSTAMP:{$now}\r\n" .
            "DTSTART;TZID=Asia/Jakarta:{$dtStart}\r\n" . // Assuming Jakarta Timezone
            "DTEND;TZID=Asia/Jakarta:{$dtEnd}\r\n" .
            "SUMMARY:{$summary}\r\n" .
            "DESCRIPTION:{$description}\r\n" .
            "LOCATION:{$location}\r\n" .
            "ORGANIZER;CN={$organizer}:mailto:{$organizerEmail}\r\n" .
            "STATUS:CONFIRMED\r\n" .
            "SEQUENCE:0\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR";
    }

    public static function generateGoogleLink(Meeting $meeting)
    {
        $start = Carbon::parse($meeting->start_time)->format('Ymd\THis');
        $end = Carbon::parse($meeting->end_time)->format('Ymd\THis');
        $text = urlencode($meeting->topic);
        $details = urlencode($meeting->description ?? 'Meeting via Doors System');
        $location = urlencode($meeting->room->name ?? 'TBA');

        return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$text}&dates={$start}/{$end}&details={$details}&location={$location}";
    }

    public static function generateOutlookLink(Meeting $meeting)
    {
        $start = Carbon::parse($meeting->start_time)->format('Y-m-d\TH:i:s');
        $end = Carbon::parse($meeting->end_time)->format('Y-m-d\TH:i:s');
        $subject = urlencode($meeting->topic);
        $body = urlencode($meeting->description ?? 'Meeting via Doors System');
        $location = urlencode($meeting->room->name ?? 'TBA');

        return "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&startdt={$start}&enddt={$end}&subject={$subject}&body={$body}&location={$location}";
    }
}
