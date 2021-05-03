<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;

class NotAvailableAssetException extends \Exception
{
    public function validationException()
    {
        return ValidationException::withMessages([
            'asset_ids' => __('message.already_reserved', ['attribute' => 'asset_ids'])
        ]);
    }
}
