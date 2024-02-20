<?php

namespace App\Notifications;

use App\Models\AuthorizationRequest;
use App\Models\Dataset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequesterNotification extends Notification
{
    use Queueable;

    private $authorizationRequest, $dataset;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(AuthorizationRequest $authorizationRequest, Dataset $dataset)
    {
        $this->authorizationRequest = $authorizationRequest;
        $this->dataset = $dataset;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(config('app.name') . 'dataset access')
            ->line("Dear {$notifiable->fullname},")
            ->line('Your request for access to dataset \'' . $this->dataset->name . '\' has been '. ($this->authorizationRequest->is_approved ? 'approved' : 'rejected') . '.')
            ->line("Note provided by the reviewer: ". $this->authorizationRequest->response_note)
            ->line('You can find the dataset in the')
            ->action(config('app.name'), route('my_access_requests.index'))
            ->line('This is an automatically generated email. Replies to this email will not be read.')
            ->line('In case of issues, please use the contact form on the portal.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
