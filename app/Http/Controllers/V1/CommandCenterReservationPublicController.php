<?php

namespace App\Http\Controllers\V1;

use App\Enums\CommandCenterReservationStatusEnum;
use App\Events\CCReservationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommandCenterReservationCreateRequest;
use App\Http\Requests\CommandCenterReservationShowRequest;
use App\Http\Resources\CCReservationResource;
use App\Models\CommandCenterReservation;
use Illuminate\Support\Str;

class CommandCenterReservationPublicController extends Controller
{
    public function store(CommandCenterReservationCreateRequest $request)
    {
        // App::makeWith(GoogleRecaptcha::class, ['request' => $request]);

        $reservation = CommandCenterReservation::create($request->validated() + [
            'reservation_code' => 'JCC' . Str::upper(Str::random(4)),
            'approval_status' => CommandCenterReservationStatusEnum::NOT_YET_APPROVED(),
        ]);

        event(new CCReservationCreated($reservation, 'store'));

        return new CCReservationResource($reservation);
    }

    public function show(CommandCenterReservationShowRequest $request, CommandCenterReservation $reservation)
    {
        return new CCReservationResource($reservation);
    }
}
