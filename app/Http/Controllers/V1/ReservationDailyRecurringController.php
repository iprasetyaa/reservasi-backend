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
        $date = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $reservationCreated = 0;

        try {
            DB::beginTransaction();

            $index = 0;
            while ($date->lte($endDate)) {
                $timeDetails = $this->createTimeDetails($date, $request->from, $request->to);

                if (!$this->isAvailableAsset($request->asset_ids, $timeDetails)) {
                    return response(['errors' => __('validation.asset_reserved', ['attribute' => 'asset_id'])], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $reservationCreated += $this->createReservation($request, $timeDetails, $index++);

                $date->addDays(1);
            }

            if ($reservationCreated === 0) {
                return response(['errors' => __('message.no_reservation')], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response(['message' => 'internal_server_error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Method to create reservation
     *
     * @param  Request $request
     * @param  Array $timeDetails
     * @param  Int $count
     * @return Int
     */
    protected function createReservation($request, $timeDetails, $index, $count = 0)
    {
        $assets = Asset::whereIn('id', $request->asset_ids)->get();

        $date = Carbon::parse($timeDetails['date']);

        if (in_array($date->dayOfWeek, $request->days)) {
            $reservations = [];
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

                $count += 1;
            }

            //send first item with email (temporary logic)
            if ($index == 1) {
                event(new AfterReservationCreated($reservations));
            }
        }

        return $count;
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
}
