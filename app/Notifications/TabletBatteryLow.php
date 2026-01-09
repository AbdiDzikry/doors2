<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TabletBatteryLow extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $room;
    public $level;

    /**
     * Create a new notification instance.
     */
    public function __construct($room, $level)
    {
        $this->room = $room;
        $this->level = $level;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Low Battery Alert',
            'message' => "Tablet {$this->room->name} battery is at {$this->level}%",
            'room_id' => $this->room->id ?? null,
            'room_name' => $this->room->name ?? 'Unknown Room',
            'level' => $this->level,
            'type' => 'battery_alert',
            'timestamp' => now()->toISOString(),
        ];
    }
}
