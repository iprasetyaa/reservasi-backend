<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\CommandCenterReservation;
use App\Events\CCReservationCreated;
use App\Http\Requests\CommandCenterReservationApprovalRequest;
use App\Http\Resources\CCReservationResource;
use Carbon\Carbon;

class CommandCenterReservationApprovalController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(CommandCenterReservationApprovalRequest $request, CommandCenterReservation $commandCenterReservation)
    {
        $commandCenterReservation->update($request->validated() + [
            'approval_date' => Carbon::now(),
        ]);

        event(new CCReservationCreated($commandCenterReservation));

        return new CCReservationResource($commandCenterReservation);
    }
}
