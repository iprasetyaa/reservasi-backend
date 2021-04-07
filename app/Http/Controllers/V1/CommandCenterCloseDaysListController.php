<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CCCloseDateListResource;
use App\Models\CommandCenterCloseDate;
use Illuminate\Http\Request;

class CommandCenterCloseDaysListController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $records = CommandCenterCloseDate::query();
        $records = $records->orderBy('date')->get();

        return CCCloseDateListResource::collection($records);
    }

}
