<?php

namespace Modules\RedmineIntegration\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\RedmineIntegration\Entities\RedmineInstance;

class Unauthorized extends Notification
{
    use Queueable;

    protected RedmineInstance $instance;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(RedmineInstance $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line("Can't authorize in the Redmine at " . $this->instance->url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
