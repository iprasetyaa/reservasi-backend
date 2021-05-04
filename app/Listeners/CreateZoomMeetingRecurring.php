<?php

namespace App\Listeners;

use App\Events\AfterReservation;
use App\Enums\ResourceTypeEnum;
use MacsiDigital\Zoom\Facades\Zoom;

class CreateZoomMeetingRecurring
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
     * @param  AfterReservation  $event
     * @return void
     */
    public function handle(AfterReservation $event)
    {
        $reservation = $event->reservations;
        $request = $event->request;
        // $asset = Asset::where
        // if ($asset->resource_type == ResourceTypeEnum::online()) {
        //     // Membuat Meeting Baru
        //     $timeInMinute = $reservation->end_time->diffInMinutes($reservation->start_time);
        //     $meetings = Zoom::user()->find($asset->zoom_email)->meetings()->create([
        //         'topic' => $reservation->title,
        //         'duration' => $timeInMinute,
        //         'type' => '2',
        //         'start_time' => $reservation->start_time,
        //         'timezone' => 'Asia/Jakarta',
        //         'password' => config('zoom.join_password'),
        //         'settings' => [
        //             'join_before_host' => true,
        //             'jbh_time' => 0,
        //             'auto_recording' => 'cloud',
        //         ]
        //     ]);

        //     // Update join_url from this reservation
        //     $reservation->join_url = $meetings->join_url;
        //     $reservation->save();
        // }

        return $reservation;
    }
}
