<?php

namespace App\Notifications;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerConnectionLost extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Server $server, public string $reference)
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
            ->error()
            ->subject(__('Server connection lost'))
            ->line(__("We could not connect to your server ':server' while performing the following action", [
                'server' => $this->server->name,
            ]))
            ->line(Markdown::parse($this->reference))
            ->line(__('Please check your server\'s connection details and try again.'))
            ->action(__('View Server'), route('servers.show', $this->server));
    }
}
