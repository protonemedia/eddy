<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerProvisioningFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $serverName,
        public string $output = '',
        public string $errorMessage = '',
    ) {
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
            ->line(__("The server ':name' failed to provision. We've deleted it for you, but you might have to manually remove it from your provider.", ['name' => $this->serverName]))
            ->when($this->output, function (MailMessage $message) {
                $message
                    ->line(__("Here you'll find the 10 last lines of the task that failed:"))
                    ->line(Markdown::parse("```{$this->output}```"));
            })
            ->when($this->errorMessage, function (MailMessage $message) {
                $message
                    ->line(__('This is the error message we received:'))
                    ->line(Markdown::parse("```{$this->errorMessage}```"));
            });
    }
}
