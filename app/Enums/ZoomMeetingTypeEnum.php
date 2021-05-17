<?php

namespace App\Enums;

use Spatie\Enum\Enum;

class ZoomMeetingTypeEnum extends Enum
{
    public static function INSTANTMEETING(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
        {
            public function getValue(): string
            {
                return 1;
            }
        };
    }

    public static function SCHEDULEDMEETING(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
        {

            public function getValue(): string
            {
                return 2;
            }
        };
    }

    public static function RECURRINGMEETINGNOFIX(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
        {
            public function getValue(): string
            {
                return 3;
            }
        };
    }

    public static function RECURRINGMEETINGFIX(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
        {
            public function getValue(): string
            {
                return 8;
            }
        };
    }
}
