<?php

namespace App\Mail;

use App\Enums\CommandCenterReservationStatusEnum;
use App\Events\CCReservationCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CCReservationNotificationMailPublic extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CCReservationCreated $event)
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
            case CommandCenterReservationStatusEnum::ALREADY_APPROVED():
                $this->subject_status = __('message.mail_accepted');
                break;
            case CommandCenterReservationStatusEnum::REJECTED():
                $this->subject_status = __('message.mail_rejected');
                break;
        }

        return $this->getContent();
    }

    public function getContent()
    {
        return $this->markdown('emails.ccReservationNotificationPublic')
                    ->subject('[Command Center Reservation] ' . $this->subject_status)
                    ->with([
                        'from' => config('mail.from.name'),
                        'url' => config('app.web_microsite_url') . '/cek-reservasi'
                    ]);
    }
}
