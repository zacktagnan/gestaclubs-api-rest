<?php

namespace App\Notifications;

use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoachRemovedFromClubNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly Club $club) {}

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
            ->subject(__('notification/coach/remove_from_club.subject'))
            ->greeting(__('notification/coach/remove_from_club.greeting', [
                'full_name' => $notifiable->full_name,
            ]))
            ->line(__('notification/coach/remove_from_club.line_1'))
            ->action($this->club->name, route('v1.clubs.show', [
                'club' => $this->club->id,
            ]))
            ->line(__('notification/coach/remove_from_club.line_2'))
            ->salutation(__('notification/coach/remove_from_club.farewell'));
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
