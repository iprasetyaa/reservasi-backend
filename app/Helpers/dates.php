<?php

use Carbon\Carbon;

function dayOfWeekId($day)
{
    return Carbon::create(Carbon::getDays()[$day])->locale('id_ID')->dayName . ' ';
}
