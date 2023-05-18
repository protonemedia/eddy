<?php

namespace App\Notifications;

use App\Models\Deployment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeploymentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Deployment $deployment)
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
        $output = $this->deployment->task->tailOutput();

        return (new MailMessage)
            ->error()
            ->subject(__('Deployment failed'))
            ->line(__("The deployment of the site ':site' on server ':server' failed. ", [
                'site' => $this->deployment->site->address,
                'server' => $this->deployment->site->server->name,
            ]))
            ->when($output, function (MailMessage $message, $output) {
                $message
                    ->line(__("Here you'll find the 10 last lines of the task that failed:"))
                    ->line(Markdown::parse("```{$output}```"));
            })
            ->action(__('View Site'), route('servers.sites.show', [$this->deployment->site->server, $this->deployment->site]));
    }
}
