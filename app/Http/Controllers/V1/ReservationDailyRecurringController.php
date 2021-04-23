<?php

namespace App\Http\Controllers\V1;

use App\Enums\ReservationStatusEnum;
use App\Events\AfterReservation;
use App\Events\AfterReservationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRecurringRequest;
use App\Models\Asset;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReservationDailyRecurringController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Reservation::class);
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(ReservationRecurringRequest $request)
    {
        DB::beginTransaction();
        try {
            $reservationCreated = $this->storeReservation($request);

            if (!count($reservationCreated)) {
                return $this->unprocessableEntity(['errors' => __('message.no_reservation')]);
            }

            event(new AfterReservationCreated(Arr::first($reservationCreated)));

            DB::commit();
            return response(null, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return response(['message' => 'internal_server_error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Method to create reservation
     *
     * @param  Request $request
     * @param  Array $timeDetails
     * @param  Int $count
     * @return Int
     */
    protected function createReservation($request, $timeDetails)
    {
        $assets = Asset::whereIn('id', $request->asset_ids)->get();

        $date = Carbon::parse($timeDetails['date']);

        $reservations = [];
        if (in_array($date->dayOfWeek, $request->days)) {
            foreach ($assets as $asset) {
                $reservation = Reservation::create($request->validated() + $timeDetails + [
                    'user_id_reservation' => $request->user()->uuid,
                    'user_fullname' => $request->user()->name,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'asset_id' => $asset->id,
                    'asset_name' => $asset->name,
                    'asset_description' => $asset->description,
                    'approval_status' => ReservationStatusEnum::already_approved()
                ]);

                array_push($reservations, $reservation->id);
                event(new AfterReservation($reservation, $asset));
            }
        }

        return $reservations;
    }

    /**
     * Method to create time details
     *
     * @return Array
     */
    protected function createTimeDetails($date, $from, $to)
    {
        $date = $date->format('Y-m-d');

        return [
            'date' => $date,
            'start_time' => Carbon::parse($date . $from),
            'end_time' => Carbon::parse($date . $to)
        ];
    }

    /**
     * Function to check asset availability
     *
     * @param  [String] $asset_id
     * @param  [Array] $timeDetails
     * @return Boolean
     */
    protected function isAvailableAsset($asset_ids, $timeDetails)
    {
        return Reservation::whereIn('asset_id', $asset_ids)
            ->validateTime((object) $timeDetails)
            ->alreadyApproved()
            ->doesntExist();
    }

    /**
     * unprocessableEntity
     *
     * @param  mixed $message
     * @return void
     */
    protected function unprocessableEntity($message)
    {
        return response($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * storeReccuringDay
     *
     * @param  mixed $request
     * @return void
     */
    protected function storeReservation($request)
    {
        $date = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $reservationCreated = [];

        while ($date->lte($endDate)) {
            $timeDetails = $this->createTimeDetails($date, $request->from, $request->to);

            if (!$this->isAvailableAsset($request->asset_ids, $timeDetails)) {
                return $this->unprocessableEntity(['errors' => __('validation.asset_reserved', ['attribute' => 'asset_id'])]);
            }

            $reservation = $this->createReservation($request, $timeDetails);
            if (count($reservation)) {
                $reservationCreated[] = $reservation;
            }

            $date->addDays(1);
        }

        return $reservationCreated;
    }
}
