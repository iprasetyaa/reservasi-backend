<?php

namespace App\Listeners;

use App\Events\AfterReservationRecurringCreated;
use App\Mail\ReservationApprovalMail;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use MacsiDigital\Zoom\Facades\Zoom;

class SendReservationEmailRecurring
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
     * @param AfterReservation  $event
     * @return void
     */
    public function handle(AfterReservationRecurringCreated $event)
    {
        try {
            $reservations = Reservation::whereIn('id', $event->reservations)->get();

            $data = [];
            foreach ($reservations as $reservation) {
                $user = Zoom::user()->find($reservation->asset->zoom_email);
                array_push($data, [
                    'reservation' => $reservation,
                    'user' => $user
                ]);
            }

            Mail::to($reservations[0]->email)->send(new ReservationApprovalMail($data));
        } catch (\Exception $e) {
            return response()->json(["message" => $e]);
        }
    }
}
