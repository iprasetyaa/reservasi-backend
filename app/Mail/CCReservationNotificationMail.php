<?php

namespace App\Mail;

use App\Events\CCReservatoinCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CCReservationNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CCReservatoinCreated $event)
    {
        $this->reservation = $event->reservation;
        $this->approval_status = $event->reservation->approval_status;
        $this->subject_status = 'Permohonan Reservasi';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        switch ($this->approval_status) {
            case "ALREADY_APPROVED":
                $this->subject_status = __('message.accepted');
                break;
            case "REJECTED":
                $this->subject_status = __('message.rejected');
                break;
        }

        return $this->getContent();
    }

    public function getContent()
    {
        return $this->markdown('emails.ccReservationNotification')
                    ->subject('[Command Center Reservation] ' . $this->subject_status)
                    ->with([
                        'from' => config('mail.from.name'),
                    ]);
    }
}
