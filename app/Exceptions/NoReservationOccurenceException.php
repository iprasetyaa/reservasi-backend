<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;

class NoReservationOccurenceException extends \Exception
{
    public function validationException()
    {
        return ValidationException::withMessages([
            'days' => __('message.no_reservation')
        ]);
    }
}
