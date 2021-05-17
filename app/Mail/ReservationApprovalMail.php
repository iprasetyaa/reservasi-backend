<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationApprovalMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $data;
    public $request;
    public $lastRecurring;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $lastRecurring = null, $request = null)
    {
        $this->data             = $data;
        $this->request          = $request;
        $this->lastRecurring    = $lastRecurring;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emailTemplate = 'emails.reservationApproval';
        if ($this->request != null) {
            $emailTemplate = 'emails.reservationApprovalRecurring';
        }

        return $this->markdown($emailTemplate)
                    ->subject('[Digiteam Reservasi Aset] Persetujuan Reservasi Aset')
                    ->with([
                        'url' => config('app.web_url') . '/reservasi'
                    ]);
    }
}
