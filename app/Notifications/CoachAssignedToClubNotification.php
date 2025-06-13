<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoachAssignedToClubNotification extends Notification
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
            ->subject(__('notification/club/sign_coach.subject'))
            ->greeting(__('notification/club/sign_coach.greeting', [
                'full_name' => $notifiable->full_name,
            ]))
            ->line(__('notification/club/sign_coach.line_1'))
            ->action($notifiable->club->name, route('v1.clubs.show', [
                'club' => $notifiable->club->id,
            ]))
            ->line(__('notification/club/sign_coach.line_2', [
                'salary' => formatCurrencyLocalized($notifiable->salary),
            ]))
            ->line(__('notification/club/sign_coach.line_3'))
            ->salutation(__('notification/club/sign_coach.farewell'));
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
