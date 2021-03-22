<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CommandCenterReservation;
use App\Enums\CommandCenterReservationStatusEnum;

class CommandCenterReservationRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($command_center_shift_id, $reservation_date, $visitors, $maxShift)
    {
        $this->command_center_shift_id = $command_center_shift_id;
        $this->reservation_date = $reservation_date;
        $this->visitors = $visitors;
        $this->maxShift = $maxShift;
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
        $ccReservation = CommandCenterReservation::whereDate('reservation_date', $this->reservation_date)
                                            ->where('command_center_shift_id', $this->command_center_shift_id)
                                            ->where('approval_status', '<>', CommandCenterReservationStatusEnum::REJECTED())
                                            ->sum('visitors');

        return ($ccReservation + $this->visitors) <= $this->maxShift;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.quota_full');
    }
}
