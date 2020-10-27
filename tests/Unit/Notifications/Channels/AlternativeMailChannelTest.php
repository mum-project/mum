<?php

namespace Tests\Unit\Notifications\Channels;

use App\Domain;
use App\Mailbox;
use App\Notifications\Channels\AlternativeMailChannel;
use function factory;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Markdown;
use Illuminate\Mail\Message;
use Illuminate\Notifications\Notification;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AlternativeMailChannelTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
    }

    public function testSend()
    {
        $user = Mailbox::factory()->create();
        $notification = Mockery::mock(Notification::class);
        $message = Mockery::mock(Message::class);
        $message->shouldReceive(['data' => []]);
        $message->view = '';
        $message->markdown = '';
        $notification->shouldReceive(['toMail' => $message]);
        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('send');
        $markdown = Mockery::mock(Markdown::class);
        $markdown->shouldReceive('render');
        $markdown->shouldReceive('renderText');
        $channel = new AlternativeMailChannel($mailer, $markdown);
        $channel->send($user, $notification);
    }

    public function testSendWithMailable()
    {
        $user = Mailbox::factory()->create();
        $notification = Mockery::mock(Notification::class);
        $message = Mockery::mock(Mailable::class);
        $message->shouldReceive(['data' => []]);
        $message->shouldReceive('send');
        $message->view = '';
        $notification->shouldReceive(['toMail' => $message]);
        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('send');
        $markdown = Mockery::mock(Markdown::class);
        $markdown->shouldReceive('render');
        $markdown->shouldReceive('renderText');
        $channel = new AlternativeMailChannel($mailer, $markdown);
        $channel->send($user, $notification);
    }
}
