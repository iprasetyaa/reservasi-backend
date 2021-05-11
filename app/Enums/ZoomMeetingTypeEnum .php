<?php

namespace App\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self INSTANT_MEETING()
 * @method static self SCHEDULED_MEETING()
 * @method static self RECURRING_MEETING_NO_FIXED_TIME()
 * @method static self RECURRING_MEETING_FIXED_TIME()
 */

final class ZoomMeetingTypeEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'INSTANT_MEETING' => 1,
            'SCHEDULED_MEETING' => 2,
            'RECURRING_MEETING_NO_FIXED_TIME' => 3,
            'RECURRING_MEETING_FIXED_TIME' => 8,
        ];
    }
}
