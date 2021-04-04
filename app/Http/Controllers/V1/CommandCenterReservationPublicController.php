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
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CommandCenterReservationPublicController extends Controller
{
    public function store(CommandCenterReservationCreateRequest $request)
    {
        $this->recaptchaValidation($request);

        $reservation = CommandCenterReservation::create($request->validated() + [
            'reservation_code' => 'JCC' . Str::upper(Str::random(4)),
            'approval_status' => CommandCenterReservationStatusEnum::NOT_YET_APPROVED(),
        ]);

        event(new CCReservationCreated($reservation, 'store'));

        return new CCReservationResource($reservation);
    }

    public function show(Request $request, CommandCenterReservation $reservation)
    {
        $this->recaptchaValidation($request);

        return new CCReservationResource($reservation);
    }

    /**
     * Google recaptcha validation method
     *
     * @param  Request $request
     */
    public function recaptchaValidation($request)
    {
        $recaptchaToken = $request->header('recaptcha-token');

        $recaptcha = App::makeWith(GoogleRecaptcha::class, ['token' => $recaptchaToken]);

        abort_if(! $recaptcha->response->success, Response::HTTP_FORBIDDEN);
    }
}
