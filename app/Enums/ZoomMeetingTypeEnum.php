<?php

namespace App\Enums;

use Spatie\Enum\Enum;

class ZoomMeetingTypeEnum extends Enum
{
    public static function INSTANTMEETING(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
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

    public static function SCHEDULEDMEETING(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
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

    public static function RECURRINGMEETINGNOFIXEDTIME(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
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

    public static function RECURRINGMEETINGFIXEDTIME(): ZoomMeetingTypeEnum
    {
        return new class () extends ZoomMeetingTypeEnum
        {
            public function getIndex(): int
            {
                return 3;
            }

            public function getValue(): string
            {
                return 8;
            }
        };
    }
}
