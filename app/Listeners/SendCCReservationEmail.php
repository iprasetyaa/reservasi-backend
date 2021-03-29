<?php

namespace App\Listeners;

use App\Events\CCReservationCreated;
use App\Mail\CCReservationNotificationMailPublic;
use Illuminate\Support\Facades\Mail;

class SendCCReservationEmail
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
     * @param  CCReservationCreated  $event
     * @return void
     */
    public function handle(CCReservationCreated $event)
    {
        Mail::to($event->reservation->email)->send(new CCReservationNotificationMailPublic($event));
    }
}
