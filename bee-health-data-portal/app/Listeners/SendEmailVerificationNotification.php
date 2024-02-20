<?php

namespace App\Listeners;

use App\Events\Invited;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerificationNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Invited $event)
    {
        if ($event->registration_invitation instanceof Model 
                && $event->registration_invitation->hasToken()
                && $event->registration_invitation->isNotExpired()
                ) {
            $event->registration_invitation->sendEmailVerificationNotification();
        }
    }
}
