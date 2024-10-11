<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LandInspectionScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    // Constructor to accept data for the notification
    public function __construct($data)
    {
        $this->data = $data;
    }

    // The delivery channels for the notification
    public function via($notifiable)
    {
        return ['database']; // You can also add 'mail', 'broadcast', etc.
    }

    // Prepare the notification for storage in the database
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->data['message'],
            'notifiable_type' => $this->data['notifiable_type'],
            'notifiable_id' => $this->data['notifiable_id'],
        ];
    }

    // Optional: You can define how the notification is sent via mail
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Land Inspection Scheduled')
            ->line($this->data['message'])
            ->action('View Details', url('/'))
            ->line('Thank you for using our application!');
    }
}
