<?php

namespace App\Traits;

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
}
