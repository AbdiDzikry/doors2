<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        $filter = $request->input('filter', 'month');
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : today();

        // Admin Filters
        $divisionFilter = $request->input('division');
        $departmentFilter = $request->input('department');
        $searchFilter = $request->input('search');

        // Date Range Logic
        switch ($filter) {
            case 'day':
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
                break;
            case 'week':
                $startDate = $date->copy()->startOfWeek();
                $endDate = $date->copy()->endOfWeek();
                break;
            case 'month':
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
                break;
            case 'year':
                $startDate = $date->copy()->startOfYear();
                $endDate = $date->copy()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : today()->startOfMonth();
                $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : today()->endOfMonth();
                break;
            default:
                $startDate = today()->startOfMonth();
                $endDate = today()->endOfMonth();
                break;
        }

        // Base Query
        $query = Meeting::query();
        $query->whereBetween('meetings.start_time', [$startDate, $endDate]);

        // Role Scope
        if (!$isSuperAdmin) {
            // Employee: Own meetings (Booker or Participant)
            $query->where(function($q) use ($user) {
                $q->where('meetings.user_id', $user->id)
                  ->orWhereHas('meetingParticipants', function($sq) use ($user) {
                      $sq->where('participant_type', \App\Models\User::class)
                        ->where('participant_id', $user->id);
                  });
            });
        } else {
            // Admin Filters
            if ($divisionFilter) {
                $query->whereHas('user', function($q) use ($divisionFilter) {
                    $q->where('division', $divisionFilter);
                });
            }
            if ($departmentFilter) {
                $query->whereHas('user', function($q) use ($departmentFilter) {
                    $q->where('department', $departmentFilter);
                });
            }
            if ($searchFilter) {
                 $query->where(function($q) use ($searchFilter) {
                    $q->where('meetings.topic', 'like', "%{$searchFilter}%")
                      ->orWhereHas('user', function($uq) use ($searchFilter) {
                          $uq->where('name', 'like', "%{$searchFilter}%");
                      })
                      ->orWhereHas('room', function($rq) use ($searchFilter) {
                          $rq->where('name', 'like', "%{$searchFilter}%");
                      });
                 });
            }
        }

        // Fetch Data for Calculation
        // Cloning query for different metrics to avoid interference if needed, 
        // but 'get()' returns collection which we can process.
        // However, for aggregation queries (like busy hours), we need DB builder.
        
        // 1. Busy Hours
        $meetingsForBusyHours = (clone $query)->select(DB::raw('HOUR(start_time) as hour, count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')->all();
            
        $busyHours = [];
        for ($i = 0; $i < 24; $i++) {
            $busyHours[strval($i)] = $meetingsForBusyHours[$i] ?? 0;
        }

        // 2. Department Metrics
        $departmentUsage = [];
        if ($isSuperAdmin) {
            // Admin: Booking by Department
             $departmentUsageRaw = (clone $query)->join('users', 'meetings.user_id', '=', 'users.id')
                ->select('users.department', DB::raw('count(meetings.id) as count'))
                ->groupBy('users.department')
                ->pluck('count', 'department')->all();
                
            foreach ($departmentUsageRaw as $department => $count) {
                $key = (empty($department) || $department === 'N/A') ? 'No Department' : $department;
                $departmentUsage[$key] = ($departmentUsage[$key] ?? 0) + $count;
            }
        } else {
            // Employee: "Invited by Department"
            // Shows the departments of the organizers who invited the current user
            
            // Filter meetings where user is NOT the organizer (so they were invited)
            $invitedStats = (clone $query)
                ->where('meetings.user_id', '!=', $user->id) 
                ->join('users', 'meetings.user_id', '=', 'users.id')
                ->select('users.department', DB::raw('count(meetings.id) as count'))
                ->groupBy('users.department')
                ->pluck('count', 'department')->all();

            foreach ($invitedStats as $department => $count) {
                $key = (empty($department) || $department === 'N/A') ? 'No Department' : $department;
                $departmentUsage[$key] = $count;
            }
        }

        // 3. Room Usage
        $roomUsage = (clone $query)->join('rooms', 'meetings.room_id', '=', 'rooms.id')
            ->select('rooms.name', DB::raw('count(meetings.id) as count'))
            ->groupBy('rooms.name')
            ->pluck('count', 'name')->all();

        // 4. Meeting Status Distribution & Duration
        // We can fetch collection for these 
        $meetingsCollection = $query->get();
        
        $meetingStatusDistribution = $meetingsCollection->groupBy('calculated_status')->map->count()->all();

        // 5. Peak Days
        $peakDays = array_fill_keys(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'], 0);
        foreach ($meetingsCollection as $meeting) {
            $peakDays[$meeting->start_time->format('l')]++;
        }

        // 6. Meeting Duration
        $meetingDuration = [
            'Short (< 1h)' => 0,
            'Medium (1-2h)' => 0,
            'Long (> 2h)' => 0
        ];

        foreach ($meetingsCollection as $meeting) {
            $durationInMinutes = $meeting->start_time->diffInMinutes($meeting->end_time);
            if ($durationInMinutes < 60) {
                $meetingDuration['Short (< 1h)']++;
            } elseif ($durationInMinutes >= 60 && $durationInMinutes <= 120) {
                $meetingDuration['Medium (1-2h)']++;
            } else {
                $meetingDuration['Long (> 2h)']++;
            }
        }
        
        // Fetch unique Divisions and Departments for Filter Dropdowns (Admin only)
        $divisions = [];
        $departments = [];
        if ($isSuperAdmin) {
            $divisions = \App\Models\User::distinct()->whereNotNull('division')->pluck('division');
            $departments = \App\Models\User::distinct()->whereNotNull('department')->pluck('department');
        }

        return view('meetings.analytics.index', compact(
            'busyHours',
            'departmentUsage',
            'roomUsage',
            'meetingStatusDistribution',
            'peakDays',
            'meetingDuration',
            'filter',
            'date',
            'startDate',
            'endDate',
            'isSuperAdmin',
            'divisions',
            'departments'
        ));
    }
}