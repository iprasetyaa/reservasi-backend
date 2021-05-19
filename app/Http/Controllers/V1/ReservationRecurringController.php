<?php

namespace App\Http\Controllers\V1;

use App\Enums\ReservationRecurringTypeEnum;
use App\Events\AfterReservationRecurringCreated;
use App\Exceptions\NoReservationOccurenceException;
use App\Exceptions\NotAvailableAssetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRecurringRequest;
use App\Models\Asset;
use App\Models\Reservation;
use App\Traits\ReservationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

            $recurringId = $this->generateRecurringId();

            $reservationCreated = $this->storeReservation($request, $recurringId);
            throw_if(!count($reservationCreated), new NoReservationOccurenceException());
            event(new AfterReservationRecurringCreated($reservationCreated, $request, $recurringId));

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
    protected function createReservation($request, $timeDetails, $recurringId)
    {
        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        $reservations = [];

        foreach ($assets as $asset) {
            $reservation = $this->storeData($request, $asset, $recurringId, $timeDetails);
            $reservations[] = ['id' => $reservation->id, 'date' => $reservation->date];
        }

        return $reservations;
    }

    /**
     * storeReccuringDay
     *
     * @param  mixed $request
     * @return array
     */
    protected function storeReservation($request, $recurringId)
    {
        $initDates = $this->createInitialDates($request->start_date, $request->days);
        $reservationCreated = [];

        foreach ($initDates as $date) {
            $reservationCreated = $this->listCreatedReservation($request, $date, $reservationCreated, $recurringId);
        }

        usort($reservationCreated, function ($a, $b) {
            return $a[0]['date'] <=> $b[0]['date'];
        });

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
    protected function listCreatedReservation($request, $date, $created, $recurringId)
    {
        $endDate = Carbon::parse($request->end_date);

        while ($date->lte($endDate)) {
            if (strcasecmp($request->recurringType, ReservationRecurringTypeEnum::MONTHLY()) == 0) {
                $date = $date->nthOfMonth($request->week, $request->days[0]);
            }

            $reservation = $this->listReservation($request, $date, $recurringId);

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
    protected function listReservation($request, $date, $recurringId)
    {
        $timeDetails = $this->createTimeDetails($date, $request->from, $request->to);

        if ($date->gte($request->start_date)) {
            throw_if(
                !$this->isAvailableAsset($request->asset_ids, $timeDetails) &&
                in_array($date->dayOfWeek, $request->days),
                new NotAvailableAssetException()
            );

            $reservation = $this->createReservation($request, $timeDetails, $recurringId);

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

    /**
     * generateRecurringId
     *
     * @return void
     */
    public function generateRecurringId()
    {
        $getLastReservation = Reservation::orderBy('recurring_id', 'desc')->value('recurring_id');
        $recurringId = $getLastReservation->recurring_id ?? 0;

        do {
            $recurringId++;
        } while (Reservation::where('recurring_id', $recurringId)->exists());

        return $recurringId;
    }
}
