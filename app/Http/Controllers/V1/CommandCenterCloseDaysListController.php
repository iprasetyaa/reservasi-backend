<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CCCloseDateListResource;
use App\Models\CommandCenterCloseDate;

class CommandCenterCloseDaysListController extends Controller
{
    /**
     * List disabled days, dates only.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $records = CommandCenterCloseDate::filterAMonth()->orderBy('date');

        return CCCloseDateListResource::collection($records->get());
    }
}
