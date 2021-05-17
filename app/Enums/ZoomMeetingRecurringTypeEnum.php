<?php

namespace App\Enums;

use Spatie\Enum\Enum;

class ZoomMeetingRecurringTypeEnum extends Enum
{
    public static function DAILY(): ZoomMeetingRecurringTypeEnum
    {
        return new class () extends ZoomMeetingRecurringTypeEnum
        {
            public function getIndex(): int
            {
                return 0;
            }

            public function getValue(): string
            {
                return 1;
            }
        };
    }

    public static function WEEKLY(): ZoomMeetingRecurringTypeEnum
    {
        return new class () extends ZoomMeetingRecurringTypeEnum
        {
            public function getIndex(): int
            {
                return 1;
            }

            public function getValue(): string
            {
                return 2;
            }
        };
    }

    public static function MONTHLY(): ZoomMeetingRecurringTypeEnum
    {
        return new class () extends ZoomMeetingRecurringTypeEnum
        {
            public function getIndex(): int
            {
                return 2;
            }

            public function getValue(): string
            {
                return 3;
            }
        };
    }
}
