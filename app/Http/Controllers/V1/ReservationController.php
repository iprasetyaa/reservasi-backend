<?php

namespace App\Http\Controllers\V1;

use App\Enums\ReservationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Events\AfterReservation;
use App\Events\AfterReservationCreated;
use App\Exceptions\NotAvailableAssetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReservationRequest;
use App\Http\Requests\EditReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Asset;
use App\Models\Reservation;
use App\Traits\ReservationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ReservationController extends Controller
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
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function index(Request $request)
    {
        $records = Reservation::query();
        $sortBy = $request->input('sortBy', 'created_at');
        $orderBy = $request->input('orderBy', 'desc');
        $perPage = $request->input('perPage', 10);
        $perPage = $this->getPaginationSize($perPage);

        //search
        if ($request->has('search')) {
            $records->where('title', 'LIKE', '%' . $request->input('search') . '%');
        }

        //filter
        $records = $this->filterList($request, $records);

        //order
        $records = $this->sortBy($sortBy, $orderBy, $records);
        if ($request->user()->hasRole(UserRoleEnum::employee_reservasi())) {
            $records->byUser($request->user());
        }
        $records = $perPage == 'all' ? $records->get() : $records->paginate($perPage);
        return ReservationResource::collection($records);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(CreateReservationRequest $request)
    {
        $date = Carbon::parse($request->start_date);

        try {
            DB::beginTransaction();

            $timeDetails = $this->createTimeDetails($date, $request->from, $request->to);
            throw_if(!$this->isAvailableAsset($request->asset_ids, $timeDetails), new NotAvailableAssetException());

            $assets = Asset::whereIn('id', $request->asset_ids)->get();
            $reservations = [];

            foreach ($assets as $asset) {
                $reservation = $this->storeData($request, $asset);
                array_push($reservations, $reservation->id);
            }

            event(new AfterReservationCreated($reservations));

            DB::commit();
            return response(null, Response::HTTP_CREATED);
        } catch (NotAvailableAssetException $e) {
            DB::rollback();
            throw $e->validationException();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'internal_server_error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * update
     *
     * @param  mixed $request
     * @return void
     */
    public function update(EditReservationRequest $request, Reservation $reservation)
    {
        $asset = Asset::find($request->asset_id);
        $reservation->update($request->validated() + [
            'asset_name' => $asset->name,
            'asset_description' => $asset->description,
            'user_id_updated' => $request->user()->uuid,
            'join_url' => ($asset->resource_type == 'offline') ? null :  $reservation->join_url
        ]);

        $reservations = [$reservation->id];
        event(new AfterReservationCreated($reservations));

        return response()->json(null, Response::HTTP_CREATED);
    }

    /**
     * destroy
     *
     * @param  mixed $reservation
     * @return void
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * show
     *
     * @param  mixed $reservation
     * @return void
     */
    public function show(Reservation $reservation)
    {
        return new ReservationResource($reservation);
    }

    /**
     * getPaginationSize
     *
     * @param  mixed $perPage
     * @return void
     */
    protected function getPaginationSize($perPage)
    {
        $perPageAllowed = [50, 100, 500, 'all'];

        if (in_array($perPage, $perPageAllowed)) {
            return $perPage;
        }
        return 10;
    }

    /**
     * filterList
     *
     * @param  mixed $request
     * @param  mixed $records
     * @return void
     */
    protected function filterList(Request $request, $records)
    {
        if ($request->has('asset_id')) {
            $records->where('asset_id', $request->input('asset_id'));
        }
        if ($request->has('approval_status')) {
            $records->where('approval_status', 'LIKE', '%' . $request->input('approval_status') . '%');
        }
        if ($request->has('start_date')) {
            $records->whereDate('date', '>=', Carbon::parse($request->input('start_date')));
        }
        if ($request->has('end_date')) {
            $records->whereDate('date', '<=', Carbon::parse($request->input('end_date')));
        }
        return $records;
    }

    /**
     * sortBy
     *
     * @param  mixed $sortBy
     * @param  mixed $orderBy
     * @param  mixed $records
     * @return Collection
     */
    protected function sortBy($sortBy, $orderBy, $records)
    {
        if ($sortBy === 'reservation_time') {
            return $records->orderBy('date', $orderBy)
                ->orderBy('start_time', $orderBy)
                ->orderBy('end_time', $orderBy);
        }
        $sortByAllowed = ['user_fullname', 'username', 'title', 'approval_status', 'date'];
        if (!in_array($sortBy, $sortByAllowed)) {
            $sortBy = 'created_at';
        }
        return $records->orderBy($sortBy, $orderBy);
    }
}
