<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlayerAssignedToClubNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notification/sign_player.subject'))
            ->greeting(__('notification/sign_player.greeting', [
                'full_name' => $notifiable->full_name,
            ]))
            ->line(__('notification/sign_player.line_1'))
            ->action($notifiable->club->name, route('v1.clubs.show', [
                'club' => $notifiable->club->id,
            ]))
            ->line(__('notification/sign_player.line_2', [
                'salary' => $notifiable->salary,
            ]))
            ->line(__('notification/sign_player.line_3'))
            ->salutation(__('notification/sign_player.farewell'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
