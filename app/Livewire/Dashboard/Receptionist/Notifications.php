<?php

namespace App\Livewire\Dashboard\Receptionist;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;

class Notifications extends Component
{
    #[Livewire\Attributes\On('echo:private-App.Models.User.{Auth::id()},.Illuminate\Notifications\Events\BroadcastNotificationCreated')] 
    public function getListeners()
    {
        return [
            // If using Reverb/Echo, we can listen for events. 
            // For now, we'll stick to polling for simplicity as per request constraints or just poll.
        ];
    }

    public function render()
    {
        $notifications = Auth::user()->unreadNotifications()
            ->where('type', 'App\Notifications\TabletBatteryLow')
            ->get();

        return view('livewire.dashboard.receptionist.notifications', [
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }
}
