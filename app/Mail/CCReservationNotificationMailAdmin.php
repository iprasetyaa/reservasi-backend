<?php

namespace App\Mail;

use App\Events\CCReservationCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CCReservationNotificationMailAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CCReservationCreated $event)
    {
        $this->reservation = $event->reservation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.ccReservationNotificationAdmin')
                    ->subject('[Command Center Reservation] Permohonan Reservasi')
                    ->with([
                        'url' => config('app.web_url') . '/reservasi-command-center',
                    ]);
    }
}
