<?php

namespace App\Traits;

use App\Enums\ReservationStatusEnum;
use App\Models\Reservation;
use Carbon\Carbon;

trait ReservationTrait
{
    /**
     * Method to create time details
     *
     * @return Array
     */
    public function createTimeDetails($date, $from, $to)
    {
        $date = $date->format('Y-m-d');

        return [
            'date' => $date,
            'start_time' => Carbon::parse($date . $from),
            'end_time' => Carbon::parse($date . $to)
        ];
    }

    /**
     * Function to check asset availability
     *
     * @param  [String] $asset_id
     * @param  [Array] $timeDetails
     * @return Boolean
     */
    public function isAvailableAsset($asset_ids, $timeDetails)
    {
        return Reservation::whereIn('asset_id', $asset_ids)
            ->validateTime((object) $timeDetails)
            ->alreadyApproved()
            ->doesntExist();
    }

    /**
     * Method to create reservation
     *
     * @param  Request $request
     * @param  Asset $asset
     * @param  Array $timeDetails
     * @return Reservation
     */
    public function storeData($request, $asset, $reccuringId = null, $timeDetails = [])
    {
        return Reservation::create($request->validated() + $timeDetails + [
            'recurring_id' => $reccuringId,
            'user_id_reservation' => $request->user()->uuid,
            'user_fullname' => $request->user()->name,
            'username' => $request->user()->username,
            'email' => $request->user()->email,
            'asset_id' => $asset->id,
            'asset_name' => $asset->name,
            'asset_description' => $asset->description,
            'approval_status' => ReservationStatusEnum::already_approved()
        ]);
    }

    /**
     * Functoin to create the initial dates in a week
     *
     * The week started by Sunday with index 0, finished by Saturday with index 6
     *
     * @param  Date $startDate
     * @param  Array $days
     * @return Array
     */
    protected function createInitialDates($startDate, $days)
    {
        $initDates = [];
        $date = Carbon::parse($startDate)
            ->copy()
            ->startOfWeek()
            ->addDays(-1);

        foreach ($days as $day) {
            $initDates[] = $date->copy()->addDays($day);
        }

        return $initDates;
    }
}
