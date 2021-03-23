<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\CommandCenterReservation;
use App\Enums\CommandCenterReservationStatusEnum;
use App\Events\CCReservationCreated;
use App\Http\Requests\CommandCenterReservationApprovalRequest;
use App\Http\Requests\CommandCenterReservationCreateRequest;
use App\Http\Resources\CCReservationResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommandCenterReservationController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('can:isAdmin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $records = CommandCenterReservation::query();
        $sortBy = $request->input('sortBy', 'reservation_date');
        $orderBy = $request->input('orderBy', 'desc');
        $perPage = $request->input('perPage', 10);
        $perPage = $this->getPaginationSize($perPage);

        //search
        $records = $this->searchList($request, $records);

        //filter
        $records = $this->filterList($request, $records);

        // sort and order
        $records = $this->sortList($sortBy, $orderBy, $records);

        return CCReservationResource::collection($records->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommandCenterReservationCreateRequest $request)
    {
        $reservation = CommandCenterReservation::create($request->validated() + [
            'reservation_code' => 'JCC' . time(),
            'user_id_reservation' => $request->user()->uuid,
            'approval_status' => CommandCenterReservationStatusEnum::NOT_YET_APPROVED(),
        ]);

        event(new CCReservationCreated($reservation));

        return response()->json(['data' => new CCReservationResource($reservation)], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CommandCenterReservation  $commandCenterReservation
     * @return \Illuminate\Http\Response
     */
    public function show(CommandCenterReservation $commandCenterReservation)
    {
        return new CCReservationResource($commandCenterReservation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CommandCenterReservation  $commandCenterReservation
     * @return \Illuminate\Http\Response
     */
    public function update(CommandCenterReservationCreateRequest $request, CommandCenterReservation $commandCenterReservation)
    {
        $commandCenterReservation->update($request->validated());

        return new CCReservationResource($commandCenterReservation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CommandCenterReservation  $commandCenterReservation
     * @return \Illuminate\Http\Response
     */
    public function destroy(CommandCenterReservation $commandCenterReservation)
    {
        $commandCenterReservation->delete();
    }

    /**
     * Function to pagination
     * @author SedekahCode
     * @since Januari 2021
     * @param Array $perPage
     * @return void
     */
    protected function getPaginationSize($perPage)
    {
        $perPageAllowed = [50, 100, 500];

        if (in_array($perPage, $perPageAllowed)) {
            return $perPage;
        }
        return 10;
    }

    /**
     * searchList
     *
     * @param  [String] $request
     * @param  [Collection] $records
     * @return Collection
     */
    protected function searchList(Request $request, $records)
    {
        if ($request->has('by') and $request->has('keyword')) {
            if ($request->by == 'reservation_code') {
                $records->where('reservation_code', $request->keyword);
                return $records;
            }

            $records->where($request->by, 'LIKE', '%' . $request->keyword . '%');
            return $records;
        }

        return $records;
    }

    /**
     * filterList
     *
     * @param  mixed $request
     * @param  [Collection] $records
     * @return Collection
     */
    protected function filterList(Request $request, $records)
    {
        if ($request->has('approval_status')) {
            $records->where('approval_status', 'LIKE', '%' . $request->approval_status . '%');
        }

        if ($request->has('start_date')) {
            $records->whereDate('reservation_date', '>=', Carbon::parse($request->start_date));
        }

        if ($request->has('end_date')) {
            $records->whereDate('reservation_date', '<=', Carbon::parse($request->end_date));
        }

        return $records;
    }

    /**
     * Function to sort by status
     *
     * @param  mixed $request
     * @param [String] $sortBy
     * @param [String] $orderBy
     * @param [Collection] $records
     * @return Collection
     */
    protected function sortList($sortBy, $orderBy, $records)
    {
        return $records->orderBy($sortBy, $orderBy);
    }
}
