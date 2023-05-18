<?php

namespace App\Exceptions;

use App\Models\CouldNotConnectToServerException;
use App\Models\Server;
use App\Notifications\JobOnServerFailed;
use App\Notifications\ServerConnectionLost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Throwable;

class ServerHandler
{
    private $notifiable;

    private Throwable $exception;

    private string $reference = '';

    private array $exceptionMailMap = [
        CouldNotConnectToServerException::class => ServerConnectionLost::class,
    ];

    public function __construct(private Server $server)
    {
    }

    /**
     * Setter for the notifiable.
     *
     * @param  mixed  $notifiable
     */
    public function notify($notifiable): self
    {
        if ($notifiable instanceof Model && ! $notifiable->exists) {
            $notifiable = null;
        }

        $this->notifiable = $notifiable;

        return $this;
    }

    /**
     * Setter for the Throwable.
     */
    public function about(Throwable $exception): self
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Setter for the reference.
     */
    public function withReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Creates a Notification instance based on the exception type.
     */
    private function buildNotification(): Notification
    {
        $exceptionClass = get_class($this->exception);

        $notificationClass = $this->exceptionMailMap[$exceptionClass] ?? JobOnServerFailed::class;

        return new $notificationClass($this->server, $this->reference);
    }

    /**
     * Sends the notification if the notifiable is set.
     */
    public function send(): void
    {
        if (! $this->notifiable) {
            return;
        }

        $this->notifiable->notify($this->buildNotification());
    }
}
