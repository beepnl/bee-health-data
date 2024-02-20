<?php

namespace App\Notifications;

use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyInvitation extends Notification
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
        $verificationUrl = $this->verificationUrl($notifiable);
        $organisationName = $this->getOrganisationName($notifiable);
        return (new MailMessage)
                    ->subject(config('app.name') .' account.')
                    ->line('You are hereby invited to activate your account as a user of organisation '. $organisationName .' on the '. config('app.name'))
                    ->line('You will need to set your password on the portal to be able to start using your account to add, search for and download and use datasets')
                    ->action(Lang::get('Activate my account on the data portal'), $verificationUrl)
                    ->line('This is an automatically generated email. Replies to this email will not be read.
                    In case of issues, please use the contact form on the portal');
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'invitation.verify',
            Carbon::now()->addMinutes(Config::get('auth.membership_invitation_expires_after')),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->email),
            ]
        );
    }

    protected function getOrganisationName($notifiable)
    {
        return Organisation::find($notifiable->organisation_id)->name;
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
