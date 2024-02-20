<?php

namespace App\Notifications;

use App\Models\Dataset;
use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestNotification extends Notification
{
    use Queueable;

    private $dataset;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Dataset $dataset)
    {
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
                    ->subject('Request for dataset access in '. config('app.name'))
                    ->line("Dear {$notifiable->fullname},")
                    ->line('Access requests for datasets are pending for your organisation \''. $this->dataset->organisation->name . '\'.')
                    ->line('As you are the administrator for your organisation you can approve or reject the request in the '.config('app.name').'.')
                    ->line('Click on')
                    ->action('Open access request', route('authorization_requests.index'))
                    ->line('to manage the pending requests')
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
