<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkdayExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly float $totalHours,
        private readonly string $date,
        private readonly array $timeLogs = []
    ) {}

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
            ->subject('Workday Alert: 8+ Hours Logged on ' . $this->date)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have logged **' . number_format($this->totalHours, 2) . ' hours** of work on ' . $this->date . '.')
            ->line('This is a friendly reminder that you\'ve exceeded 8 hours of logged work today.')
            ->when(count($this->timeLogs) > 0, function ($message) {
                $message->line('**Time Log Summary:**');
                foreach ($this->timeLogs as $log) {
                    $message->line('• ' . $log['project'] . ': ' . number_format($log['hours'], 2) . ' hours - ' . $log['description']);
                }
                return $message;
            })
            ->line('Remember to take breaks and maintain a healthy work-life balance!')
            ->line('Thank you for using our time tracking system.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'total_hours' => $this->totalHours,
            'date' => $this->date,
            'time_logs_count' => count($this->timeLogs),
        ];
    }
}
