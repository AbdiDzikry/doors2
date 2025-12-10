<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Meeting;
use App\Models\PantryOrder;
use App\Models\Room;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            $data['totalRooms'] = Room::count();
            $data['roomsInUse'] = Meeting::where('start_time', '<=', now())
                                        ->where('end_time', '>=', now())
                                        ->count();
            $data['totalUsers'] = User::count();
            $data['totalMeetingsToday'] = Meeting::whereDate('start_time', today())->count();
            
            // Data for Chart: Meetings in the last 7 days per room
            $meetingsPerRoom = Room::withCount(['meetings' => function ($query) {
                $query->where('start_time', '>=', now()->subDays(7));
            }])->get();

            $data['chartLabels'] = $meetingsPerRoom->pluck('name');
            $data['chartData'] = $meetingsPerRoom->pluck('meetings_count');

            $view = $user->hasRole('Super Admin') ? 'dashboards.superadmin' : 'dashboards.admin';
            return view($view, $data);

        } elseif ($user->hasRole('Karyawan')) {
            $upcomingMeetings = Meeting::whereHas('meetingParticipants', function ($query) use ($user) {
                $query->where('participant_id', $user->id)
                      ->where('participant_type', User::class);
            })
            ->orWhere('user_id', $user->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->with('room')
            ->get();

            $data['nextMeeting'] = $upcomingMeetings->first();
            $data['otherMeetings'] = $upcomingMeetings->slice(1, 4); // Get the next 4 after the first one

            return view('dashboards.karyawan', $data);
        } elseif ($user->hasRole('Resepsionis')) {
            return redirect()->route('dashboard.receptionist');
        }

        // Fallback for any other user, or if you want a default dashboard
        return view('dashboard');
    }
}
