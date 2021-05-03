<?php

namespace App\Http\Controllers\V1;

use App\Enums\ReservationStatusEnum;
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
        DB::beginTransaction();
        try {
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
        $date = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $reservationCreated = [];

        while ($date->lte($endDate)) {
            $timeDetails = $this->createTimeDetails($date, $request->from, $request->to);
            $dayInWhile = $date->dayOfWeek;

            if (in_array($dayInWhile, $request->days)) {
                throw_if(!$this->isAvailableAsset($request->asset_ids, $timeDetails), new NotAvailableAssetException());
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
