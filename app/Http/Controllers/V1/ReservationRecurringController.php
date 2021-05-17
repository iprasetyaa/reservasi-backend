<?php

namespace App\Http\Controllers\V1;

use App\Enums\ReservationRecurringTypeEnum;
use App\Events\AfterReservation;
use App\Events\AfterReservationCreated;
use App\Exceptions\NoReservationOccurenceException;
use App\Exceptions\NotAvailableAssetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRecurringRequest;
use App\Models\Asset;
use App\Traits\ReservationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReservationRecurringController extends Controller
{
    use ReservationTrait;

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
        try {
            DB::beginTransaction();

            $reservationCreated = $this->storeReservation($request);
            throw_if(!count($reservationCreated), new NoReservationOccurenceException());
            event(new AfterReservationCreated(Arr::first($reservationCreated)));

            DB::commit();
            return response(null, Response::HTTP_CREATED);
        } catch (NoReservationOccurenceException $e) {
            throw $e->validationException();
        } catch (NotAvailableAssetException $e) {
            DB::rollback();
            throw $e->validationException();
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
     * @return array
     */
    protected function createReservation($request, $timeDetails)
    {
        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        $reservations = [];

        foreach ($assets as $asset) {
            $reservation = $this->storeData($request, $asset, $timeDetails);

            $reservations[] = $reservation->id;

            event(new AfterReservation($reservation, $asset));
        }

        return $reservations;
    }

    /**
     * storeReccuringDay
     *
     * @param  mixed $request
     * @return array
     */
    protected function storeReservation($request)
    {
        $initDates = $this->createInitialDates($request->start_date, $request->days);
        $reservationCreated = [];

        foreach ($initDates as $date) {
            $reservationCreated = $this->listCreatedReservation($request, $date, $reservationCreated);
        }

        return $reservationCreated;
    }

    /**
     * Function to list the created reservations
     *
     * @param  Request $request
     * @param  Array $timeDetails
     * @param  Int $count
     * @return array
     */
    protected function listCreatedReservation($request, $date, $created)
    {
        $endDate = Carbon::parse($request->end_date);

        while ($date->lte($endDate)) {
            if (strcasecmp($request->recurringType, ReservationRecurringTypeEnum::MONTHLY()) == 0) {
                $date = $date->nthOfMonth($request->week, $request->days[0]);
            }

            $reservation = $this->listReservation($request, $date);

            if ($reservation) {
                $created[] = $reservation;
            }

            $this->incrementDate($request, $date);
        }

        return $created;
    }

    /**
     * List reservation
     *
     * @param  Request $request
     * @param  Date $date
     * @return array
     */
    protected function listReservation($request, $date)
    {
        $timeDetails = $this->createTimeDetails($date, $request->from, $request->to);

        if ($date->gte($request->start_date)) {
            throw_if(
                !$this->isAvailableAsset($request->asset_ids, $timeDetails) &&
                in_array($date->dayOfWeek, $request->days),
                new NotAvailableAssetException()
            );

            $reservation = $this->createReservation($request, $timeDetails);

            return $reservation;
        }
    }

    /**
     * Date increment
     *
     * @param  Request $request
     * @param  Date $date
     * @return Date
     */
    protected function incrementDate($request, $date)
    {
        switch (strtoupper($request->recurringType)) {
            case ReservationRecurringTypeEnum::WEEKLY():
                return $date->addWeeks($request->week);

            case ReservationRecurringTypeEnum::MONTHLY():
                return $date->addMonths($request->month);

            default:
                return $date->addWeeks(1);
        }
    }
}
