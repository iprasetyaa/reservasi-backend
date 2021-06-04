<?php

namespace App\Listeners;

use App\Enums\ResourceTypeEnum;
use App\Enums\ZoomMeetingTypeEnum;
use App\Events\AfterReservationCreated;
use App\Mail\ReservationApprovalMail;
use App\Models\Reservation;
use App\Traits\ReservationTrait;
use Illuminate\Support\Facades\Mail;
use MacsiDigital\Zoom\Facades\Zoom;

class CreateZoomMeeting
{
    use ReservationTrait;

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
     * @param  AfterReservationCreated  $event
     * @return void
     */
    public function handle(AfterReservationCreated $event)
    {
        $reservations = $event->reservations;
        $data = [];

        foreach ($reservations as $item) {
            $reservation = Reservation::where('id', $item)->first();
            $asset = $reservation->asset;
            $zoomData = null;

            if ($asset->resource_type == ResourceTypeEnum::online()) {
                // Membuat Meeting Baru
                $timeInMinute   = $reservation->end_time->diffInMinutes($reservation->start_time);
                $zoomData       = $this->createZoom($asset, $reservation, $timeInMinute);
            }

            array_push($data, [
                'reservation' => $reservation,
                'zoom_data' => $zoomData,
            ]);
        }

        $this->sendMail($data);
        return $reservations;
    }

    /**
     * createZoom
     *
     * @param  mixed $asset
     * @param  mixed $reservation
     * @param  mixed $timeInMinute
     * @return void
     */
    public function createZoom($asset, $reservation, $timeInMinute)
    {
        $user = Zoom::user()->find($asset->zoom_email);
        $meeting = $user->meetings()->create([
            'topic' => $reservation->title,
            'duration' => $timeInMinute,
            'type' => ZoomMeetingTypeEnum::SCHEDULEDMEETING(),
            'start_time' => $reservation->start_time,
            'timezone' => 'Asia/Jakarta',
            'password' => config('zoom.join_password'),
            'settings' => [
                'join_before_host' => true,
                'jbh_time' => 0,
                'auto_recording' => 'cloud',
            ]
        ]);

        // Update join_url from this reservation
        $reservation->join_url = $meeting->join_url;
        $reservation->save();

        $zoomResponse = $this->zoomResponse($user, $meeting);

        return $zoomResponse;
    }

    public function sendMail($data)
    {
        Mail::to($data[0]['reservation']->email)->send(new ReservationApprovalMail($data));

        if ($data[0]['reservation']->holder != null) {
            Mail::to($data[0]['reservation']->holder)->send(new ReservationApprovalMail($data));
        }
    }
}
