<?php
// test_email.php
use App\Models\User;
use App\Models\Room;
use App\Models\Meeting;
use App\Mail\MeetingInvitation;
use App\Helpers\IcsGenerator;
use Illuminate\Support\Facades\Mail;

// 1. Get/Create Dummy Data
$user = User::first();
$room = Room::first();

if (!$user || !$room) {
    echo "User or Room missing. Populate DB first.\n";
    exit;
}

// 2. Mock Meeting
$meeting = new Meeting([
    'topic' => 'Test Notification System',
    'start_time' => now()->addDay()->setHour(10)->setMinute(0),
    'end_time' => now()->addDay()->setHour(11)->setMinute(0),
    'description' => 'This is a test meeting to verify Outlook integration.',
    'room_id' => $room->id,
    'user_id' => $user->id,
]);
$meeting->id = 99999; // Mock ID
// Need to set relation for IcsGenerator manually or ensure model can fetch it?
// Relationships in Laravel need the model to be saved or hydated.
// Saving to DB for relationship to work (e.g. $meeting->user)
$meeting->save();

echo "Meeting created: " . $meeting->topic . "\n";

// 3. Test ICS Generation
try {
    $ics = IcsGenerator::generate($meeting);
    echo "ICS Generated " . strlen($ics) . " bytes.\n";
    if (strpos($ics, 'BEGIN:VCALENDAR') !== false) {
        echo "ICS Format Valid.\n";
    } else {
        echo "ICS Format INVALID.\n";
    }
} catch (\Exception $e) {
    echo "ICS Generation Failed: " . $e->getMessage() . "\n";
    exit;
}

// 4. Test Email Sending
try {
    // GANTI EMAIL INI DENGAN EMAIL ANDA SENDIRI UNTUK TESTING
    $myEmail = 'sulthanabdi1@gmail.com';
    Mail::to($myEmail)->send(new MeetingInvitation($meeting));
    echo "Email Sent to: $myEmail\n";
} catch (\Exception $e) {
    echo "Email Sending Failed: " . $e->getMessage() . "\n";
}

// Cleanup
$meeting->delete();
echo "Cleanup done.\n";
