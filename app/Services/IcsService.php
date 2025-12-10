<?php

namespace App\Services;

use App\Models\Meeting;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class IcsService
{
    public function generateIcsFile(Meeting $meeting): string
    {
        $event = Event::create()
            ->name($meeting->topic)
            ->description($meeting->topic)
            ->startsAt($meeting->start_time)
            ->endsAt($meeting->end_time)
            ->address($meeting->room->name);

        $calendar = Calendar::create()
            ->event($event);

        return $calendar->get();
    }
}
