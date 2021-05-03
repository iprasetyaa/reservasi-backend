<?php

namespace App\Http\Controllers\V1;

use App\Events\AfterReservation;
use App\Events\AfterReservationCreated;
use App\Exceptions\NoReservationOccurenceException;
use App\Exceptions\NotAvailableAssetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRecurringRequest;
use App\Models\Asset;
use App\Models\Reservation;
use App\Traits\ReservationTrait;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReservationDailyRecurringController extends Controller
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
        $date = Carbon::parse($timeDetails['date']);
        $reservations = [];

        if ($date->gte($request->start_date)) {
            foreach ($assets as $asset) {
                $reservation = $this->storeData($request, $asset, $timeDetails);

                $reservations[] = $reservation->id;

                event(new AfterReservation($reservation, $asset));
            }
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
        $endDate = Carbon::parse($request->end_date);
        $reservationCreated = [];

        foreach ($initDates as $date) {
            while ($date->lte($endDate)) {
                $timeDetails = $this->createTimeDetails($date, $request->from, $request->to);

                throw_if(
                    !$this->isAvailableAsset($request->asset_ids, $timeDetails) &&
                    in_array($date->dayOfWeek, $request->days),
                    new NotAvailableAssetException()
                );

                $reservation = $this->createReservation($request, $timeDetails);

                if (count($reservation)) {
                    $reservationCreated[] = $reservation;
                }

                // $date->addDays(1);
                $date->addWeeks(1);
            }
        }

        return $reservationCreated;
    }
}
