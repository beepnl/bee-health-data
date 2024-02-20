<?php

namespace App\Events;

use App\Models\RegistrationInvitation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Invited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $registration_invitation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RegistrationInvitation $registration_invitation)
    {
        $this->registration_invitation = $registration_invitation;
    }
}
