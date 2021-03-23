<?php

namespace App\Http\Controllers\V1;

use App\Enums\CommandCenterReservationStatusEnum;
use App\Events\CCReservationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommandCenterReservationCreateRequest;
use App\Http\Resources\CCReservationResource;
use App\Models\CommandCenterReservation;

class CommandCenterReservationPublicController extends Controller
{
    public function store(CommandCenterReservationCreateRequest $request)
    {
        $reservation = CommandCenterReservation::create($request->validated() + [
            'reservation_code' => 'JCC' . time(),
            'approval_status' => CommandCenterReservationStatusEnum::NOT_YET_APPROVED(),
        ]);

        event(new CCReservationCreated($reservation));

        return new CCReservationResource($reservation);
    }

    public function show(CommandCenterReservation $reservation)
    {
        return new CCReservationResource($reservation);
    }
}
