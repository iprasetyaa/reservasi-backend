<?php

namespace App\Listeners;

use App\Enums\ResourceTypeEnum;
use App\Events\AfterReservationRecurringCreated;
use MacsiDigital\Zoom\Facades\Zoom;
use App\Models\Asset;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Arr;

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
    public function handle(AfterReservationRecurringCreated $event)
    {
        $reservations = $event->reservations;
        $firstReservation = Arr::first($reservations);
        $request = $event->request;

        foreach ($firstReservation as $item) {
            $reservation = Reservation::findOrFail($item);
            $asset = $reservation->asset;

            if ($asset->resource_type == ResourceTypeEnum::online()) {
                $weekIteration = $this->weekIteration($reservations, $request);
                $zoomDay = $this->zoomDay($request);
                $recurrence = $this->zoomRecurrence($request, $zoomDay, $weekIteration);

                $createZoomMeeting = $this->createZoom($asset, $reservation, $recurrence);

                Reservation::where('recurring_id', $reservation->recurring_id)
                        ->where('asset_id', $reservation->asset->id)
                        ->update([
                            'join_url' => $createZoomMeeting
                        ]);
            }
        }

        return $reservations;
    }

    /**
     * weekIteration
     *
     * @param  mixed $reservations
     * @param  mixed $request
     * @return integer
     */
    public function weekIteration($reservations, $request)
    {
        $reservationsTotal  = count($reservations);
        $daysTotal          = count((array)$request->days);
        $assetIdsTotal      = count((array) $request->asset_ids);

        return (int) round(($reservationsTotal / $daysTotal) / $assetIdsTotal);
    }

    /**
     * timeInMinute
     *
     * @param  mixed $reservation
     * @return void
     */
    public function timeInMinute($reservation)
    {
        return $reservation->end_time->diffInMinutes($reservation->start_time);
    }

    /**
     * zoomDay
     *
     * @param  mixed $request
     * @return void
     */
    public function zoomDay($request)
    {
        return array_map(function ($val) {
            return $val += 1;
        }, $request->days);
    }

    /**
     * zoomRecurrence
     *
     * @param  mixed $request
     * @param  mixed $zoomDay
     * @param  mixed $weekIteration
     * @return array
     */
    public function zoomRecurrence($request, $zoomDay, $weekIteration)
    {
        switch ($request->repeat_type) {
            case 'DAILY':
            case 'WEEKLY':
                $recurrence = [
                    'type' => 2,
                    'repeat_interval' => ($request->type == 'daily') ? 1 : $request->week,
                    "weekly_days" => join(",", $zoomDay),
                    "end_times" => $weekIteration
                ];
                break;

            case 'MONTHLY':
                $recurrence = [
                    "type" => 3,
                    "repeat_interval" => $request->month,
                    "monthly_week" => $request->week,
                    "monthly_week_day" => $zoomDay[0],
                    "end_times" => $weekIteration
                ];
                break;
        }

        return $recurrence;
    }

    /**
     * createZoom
     *
     * @param  mixed $asset
     * @param  mixed $reservation
     * @param  mixed $recurrence
     * @return void
     */
    public function createZoom($asset, $reservation, $recurrence)
    {
        $user = Zoom::user()->find($asset->zoom_email);
        $meeting = Zoom::user()->find($asset->zoom_email)->meetings()->make([
            'topic' => $reservation->title,
            'duration' => $this->timeInMinute($reservation),
            'type' => 8,
            'start_time' => $reservation->start_time,
            'timezone' => 'Asia/Jakarta',
            'password' => config('zoom.join_password'),
            ]);

        $meeting->recurrence()->make($recurrence);
        $meeting->settings()->make([
            'join_before_host' => true,
            'jbh_time' => 0,
            'auto_recording' => 'cloud',
        ]);

        $user->meetings()->save($meeting);

        return $meeting->join_url;
    }
}
