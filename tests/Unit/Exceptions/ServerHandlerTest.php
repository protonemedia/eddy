<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\ServerHandler;
use App\Models\CouldNotConnectToServerException;
use App\Models\Server;
use App\Notifications\JobOnServerFailed;
use App\Notifications\ServerConnectionLost;
use Exception;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ServerHandlerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function it_does_not_notify_when_notifiable_is_not_provided()
    {
        $server = new Server();
        $handler = new ServerHandler($server);

        $handler->about(new Exception())->send();

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_sends_notification_when_notifiable_is_provided()
    {
        Notification::fake();

        $server = new Server();
        $handler = new ServerHandler($server);
        $notifiable = new AnonymousNotifiable();

        $handler->notify($notifiable)
            ->about(new Exception())
            ->send();

        Notification::assertSentTo(
            $notifiable,
            JobOnServerFailed::class,
            function ($notification, $channels, $notifiable) use ($server) {
                return $notification->server->id === $server->id;
            }
        );
    }

    /** @test */
    public function it_sends_correct_notification_based_on_exception_type()
    {
        Notification::fake();

        $server = new Server();
        $handler = new ServerHandler($server);
        $notifiable = new AnonymousNotifiable();

        $handler->notify($notifiable)
            ->about(new CouldNotConnectToServerException($server))
            ->send();

        Notification::assertSentTo(
            $notifiable,
            ServerConnectionLost::class,
            function ($notification, $channels, $notifiable) use ($server) {
                return $notification->server->id === $server->id;
            }
        );
    }

    /** @test */
    public function it_passes_reference_to_notification()
    {
        Notification::fake();

        $server = new Server();
        $handler = new ServerHandler($server);
        $notifiable = new AnonymousNotifiable();
        $reference = 'test reference';

        $handler->notify($notifiable)
            ->about(new Exception())
            ->withReference($reference)
            ->send();

        Notification::assertSentTo(
            $notifiable,
            JobOnServerFailed::class,
            function ($notification, $channels, $notifiable) use ($server, $reference) {
                return $notification->server->id === $server->id && $notification->reference === $reference;
            }
        );
    }
}
