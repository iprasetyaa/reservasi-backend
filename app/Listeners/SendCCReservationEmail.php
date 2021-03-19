<?php

namespace App\Listeners;

use App\Events\CCReservatoinCreated;
use App\Mail\CCReservationNotificationMail;
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
     * @param  CCReservatoinCreated  $event
     * @return void
     */
    public function handle(CCReservatoinCreated $event)
    {
        Mail::to($event->reservation->email)->send(new CCReservationNotificationMail($event));
    }
}
