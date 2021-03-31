<?php

namespace App\Http\Controllers\V1;

use App\Enums\CommandCenterReservationStatusEnum;
use App\Events\CCReservationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommandCenterReservationCreateRequest;
use App\Http\Resources\CCReservationResource;
use App\Models\CommandCenterReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class CommandCenterReservationPublicController extends Controller
{
    public function store(CommandCenterReservationCreateRequest $request)
    {
        $reservation = CommandCenterReservation::create($request->validated() + [
            'reservation_code' => 'JCC' . time(),
            'approval_status' => CommandCenterReservationStatusEnum::NOT_YET_APPROVED(),
        ]);

        event(new CCReservationCreated($reservation, 'store'));

        return new CCReservationResource($reservation);
    }

    public function show(Request $request, CommandCenterReservation $reservation)
    {
        $recaptchaToken = $request->header('recaptcha-token');

        $isValid = $this->validateRecaptcha($recaptchaToken);

        if ($isValid) {
            return new CCReservationResource($reservation);
        }

        return response(null, Response::HTTP_FORBIDDEN);
    }

    /**
     * Function to validate the google recaptcha
     *
     * @param [String] $token
     * @return Boolean
     */
    protected function validateRecaptcha($token)
    {
        $recaptchaResponse = Http::post(
            config('recaptcha.base_url'),
            [
                'query' => [
                    'secret' => config('recaptcha.secret_key'),
                    'response' => $token
                ]
            ]
        );

        $result = json_decode($recaptchaResponse);

        return $result->success;
    }
}
