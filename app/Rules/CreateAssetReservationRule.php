<?php

namespace App\Rules;

use App\Models\Reservation;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use stdClass;

class CreateAssetReservationRule implements Rule
{

    public $reservation;
    public $ids;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($ids, $date, $startTime, $endTime)
    {
        $this->reservation = new stdClass();
        $this->reservation->date = $date;
        $this->reservation->start_time = Carbon::parse($startTime);
        $this->reservation->end_time = Carbon::parse($endTime);
        $this->ids = $ids;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Reservation::validateTime($this->reservation)
            ->alreadyApproved()
            ->where(function ($query) {
                if ($this->ids) {
                    $query->whereIn('asset_id', $this->ids);
                }
            })->doesntExist();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.asset_reserved');
    }
}
