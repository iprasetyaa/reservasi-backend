<?php

namespace App\Http\Controllers\V1;

use App\Enums\CommandCenterReservationStatusEnum;
use App\Events\CCReservationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommandCenterReservationCreateRequest;
use App\Http\Resources\CCReservationResource;
use App\Models\CommandCenterReservation;
use App\Recaptchas\GoogleRecaptcha;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CommandCenterReservationPublicController extends Controller
{
    public function store(CommandCenterReservationCreateRequest $request)
    {
        $reservation = CommandCenterReservation::create($request->validated() + [
            'reservation_code' => 'JCC' . Str::upper(Str::random(4)),
            'approval_status' => CommandCenterReservationStatusEnum::NOT_YET_APPROVED(),
        ]);

        event(new CCReservationCreated($reservation, 'store'));

        return new CCReservationResource($reservation);
    }

    public function show(Request $request, CommandCenterReservation $reservation)
    {
        $recaptchaToken = $request->header('recaptcha-token');

        $recaptcha = new GoogleRecaptcha(['token' => $recaptchaToken]);

        if ($recaptcha->response->success) {
            return new CCReservationResource($reservation);
        }

        return response(null, Response::HTTP_FORBIDDEN);
    }
}
