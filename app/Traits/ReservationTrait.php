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
    public function storeData($request, $asset, $timeDetails = [])
    {
        return Reservation::create($request->validated() + $timeDetails + [
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
     * The week started by Monday with index 1, finished by Sunday with index 0
     * Therefore, the addition number forced to be 6 when the day is Sunday
     *
     * @param  Date $startDate
     * @param  Array $days
     * @return Array
     */
    protected function createInitialDates($startDate, $days)
    {
        $initDates = [];
        $date = Carbon::parse($startDate)->copy()->startOfWeek();

        foreach ($days as $day) {
            $day === 0 ? $additionNumber = 6 : $additionNumber = $day - 1;

            $initDates[] = $date->copy()->addDays($additionNumber);
        }

        return $initDates;
    }
}
