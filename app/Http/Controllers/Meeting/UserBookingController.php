<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserBookingController extends Controller
{
    /**
     * Show the public user booking search page.
     */
    public function index(Request $request)
    {
        // optionally pass room_id from querystring to the view
        $roomId = $request->query('room_id');
        return view('meetings.user-booking', ['room_id' => $roomId]);
    }

    /**
     * Search users by NPK or name and return JSON.
     */
    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');

        if ($q === '') {
            return response()->json([]);
        }

        $users = User::query()
            ->where('npk', 'like', "%{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->limit(20)
            ->get(['id', 'npk', 'name']);

        return response()->json($users);
    }

    /**
     * Select an NPK and store it in session, then redirect to public booking create.
     */
    public function select(Request $request)
    {
        $request->validate([
            'npk' => 'required|string',
        ]);

        $npk = $request->input('npk');
        $roomId = $request->input('room_id');

        // store chosen npk in session so booking form can use it
        Session::put('user_booking_npk', $npk);

        $target = '/public-booking/create';
        if ($roomId) {
            $target .= '?room_id=' . urlencode($roomId);
        }

        return redirect($target);
    }
}
