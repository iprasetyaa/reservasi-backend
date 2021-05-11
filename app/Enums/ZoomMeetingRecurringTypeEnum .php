<?php

namespace App\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self DAILY()
 * @method static self WEEKLY()
 * @method static self MONTHLY()
 */

final class ZoomMeetingRecurringTypeEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'DAILY' => 1,
            'WEEKLY' => 2,
            'MONTHLY' => 3,
        ];
    }
}
