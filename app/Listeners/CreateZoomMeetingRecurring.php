<?php

namespace App\Listeners;

use App\Enums\ResourceTypeEnum;
use App\Enums\ZoomMeetingTypeEnum;
use App\Enums\ZoomMeetingRecurringTypeEnum;
use App\Events\AfterReservationRecurringCreated;
use App\Mail\ReservationApprovalMail;
use MacsiDigital\Zoom\Facades\Zoom;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

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
        $reservations       = $event->reservations;
        $firstReservation   = Arr::first($reservations);
        $request            = $event->request;
        $recurringId        = $event->recurringId;
        $data = [];

        foreach ($firstReservation as $item) {
            $reservation = Reservation::where('id', $item['id'])->first();
            $asset = $reservation->asset;
            $userZoom = null;

            if ($asset->resource_type == ResourceTypeEnum::online()) {
                $endTimes           = count($reservations);
                $zoomDay            = $this->zoomDay($request);
                $recurrence         = $this->zoomRecurrence($request, $zoomDay, $endTimes);
                $createZoomMeeting  = $this->createZoom($asset, $reservation, $recurrence);
                $userZoom           = Zoom::user()->find($reservation->asset->zoom_email);
                $reservation        = $this->updateReservation($reservation, $createZoomMeeting);
            }

            array_push($data, [
                'reservation' => $reservation,
                'user' => $userZoom
            ]);
        }

        $this->sendMailRecurring($recurringId, $request, $data);
        return $reservations;
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
     * @param  mixed $endTimes
     * @return array
     */
    public function zoomRecurrence($request, $zoomDay, $endTimes)
    {
        switch ($request->repeat_type) {
            case 'DAILY':
            case 'WEEKLY':
                $recurrence = [
                    'type' => ZoomMeetingRecurringTypeEnum::WEEKLY(),
                    'repeat_interval' => ($request->type == 'daily') ? 1 : $request->week,
                    "weekly_days" => join(",", $zoomDay),
                    "end_times" => $endTimes
                ];
                break;

            case 'MONTHLY':
                $recurrence = [
                    "type" => ZoomMeetingRecurringTypeEnum::MONTHLY(),
                    "repeat_interval" => $request->month,
                    "monthly_week" => $request->week,
                    "monthly_week_day" => $zoomDay[0],
                    "end_times" => $endTimes
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
     * @return object
     */
    public function createZoom($asset, $reservation, $recurrence)
    {
        $user = Zoom::user()->find($asset->zoom_email);
        $meeting = Zoom::user()->find($asset->zoom_email)->meetings()->make([
            'topic' => $reservation->title,
            'duration' => $this->timeInMinute($reservation),
            'type' => ZoomMeetingTypeEnum::RECURRINGMEETINGFIX(),
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

        return $meeting;
    }

    /**
     * updateReservation
     *
     * @param  mixed $reservation
     * @param  mixed $createZoomMeeting
     * @return void
     */
    public function updateReservation($reservation, $createZoomMeeting)
    {
        return tap(Reservation::where('recurring_id', $reservation->recurring_id)
                    ->where('asset_id', $reservation->asset->id))
                    ->update(['join_url' => $createZoomMeeting->join_url])
                    ->first();
    }

    /**
     * sendMailRecurring
     *
     * @param  mixed $recurringId
     * @param  mixed $request
     * @param  mixed $data
     * @return void
     */
    public function sendMailRecurring($recurringId, $request, $data)
    {
        $lastRecurring = Reservation::where('recurring_id', $recurringId)->orderBy('date', 'desc')->first();
        Mail::to($request->user()->email)->send(new ReservationApprovalMail($data, $lastRecurring, $request->all()));
    }
}
