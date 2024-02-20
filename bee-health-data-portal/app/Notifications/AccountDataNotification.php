<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AccountDataNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $accountDataUrl = $this->accountDataUrl($notifiable);
        return (new MailMessage)
                    ->subject('Account data on '. config('app.name'))
                    ->line("Dear {$notifiable->fullname},")
                    ->line("You have requested a copy of your account data. See below links to download data and information which is part of or linked to your account.")
                    ->action('Account data', $accountDataUrl)
                    ->line('This is an automatically generated email. Replies to this email will not be read. In case of issues, please use the contact form on the portal.');
    }

    protected function accountDataUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'account.download',
            Carbon::now()->addMinutes(Config::get('auth.request_of_your_account_data_expires_after')),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
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
