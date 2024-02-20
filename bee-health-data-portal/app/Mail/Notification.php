<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class Notification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The datasets of the collection.
     *
     * @var \Illuminate\Support\Collection
     */
    private $datasets;

    /**
     * The user of the model.
     *
     * @var \App\Models\User
     */
    private $user;

    /**
     * Create a new message instance.
     *
     * @param App\Models\User $user
     * @param Illuminate\Support\Collection $datasets
     * @return void
     */
    public function __construct(User $user, Collection $datasets)
    {
        $this->user = $user;
        $this->datasets = $datasets;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = $this->user;
        $datasets = $this->datasets;
        return $this
            ->markdown('emails.notifications.index', compact('user', 'datasets'));
    }
}
