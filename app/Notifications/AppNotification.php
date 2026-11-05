<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public string $category;
    public ?string $actionUrl;
    public string $icon;
    public array $relatedEntity;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $title,
        string $message,
        string $category = 'academic',
        ?string $actionUrl = null,
        string $icon = 'bx-bell',
        array $relatedEntity = []
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->category = $category;
        $this->actionUrl = $actionUrl;
        $this->icon = $icon;
        $this->relatedEntity = $relatedEntity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'          => $this->title,
            'message'        => $this->message,
            'category'       => $this->category,
            'action_url'     => $this->actionUrl,
            'icon'           => $this->icon,
            'related_entity' => $this->relatedEntity,
        ];
    }
}
