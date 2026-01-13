<?php

namespace App\Livewire\Dashboard\Receptionist;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;

class Notifications extends Component
{
    public function getListeners()
    {
        return [
            "echo-private:App.Models.User." . Auth::id() . ",.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'refreshNotifications',
            // Or simple polling fallback
        ];
    }
    
    public function refreshNotifications()
    {
        $this->render();
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
