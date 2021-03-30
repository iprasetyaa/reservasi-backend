<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommandCenterShiftRequest;
use App\Http\Resources\CommandCenterShiftResource;
use App\Models\CommandCenterShift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommandCenterShiftController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('can:isAdmin')->except('index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $records = CommandCenterShift::query();
        $sortBy = $request->input('sortBy', 'created_at');
        $orderDirection = $request->input('orderDirection', 'desc');
        $perPage = $request->input('perPage', 10);

        //filter
        $records = $this->filterList($request, $records);

        // sort and order
        $records = $this->sortList($sortBy, $orderDirection, $records);

        return CommandCenterShiftResource::collection($records->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommandCenterShiftRequest $request)
    {
        $record = CommandCenterShift::create($request->validated());

        return new CommandCenterShiftResource($record);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(CommandCenterShift $commandCenterShift)
    {
        return new CommandCenterShiftResource($commandCenterShift);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CommandCenterShiftRequest $request, CommandCenterShift $commandCenterShift)
    {
        $commandCenterShift->update($request->validated());

        return new CommandCenterShiftResource($commandCenterShift);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CommandCenterShift $commandCenterShift)
    {
        $commandCenterShift->delete();

        return response()->json(['message' => 'deleted'], Response::HTTP_OK);
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
        if ($request->has('status')) {
            $records->where('status', $request->status);
        }

        return $records;
    }

    /**
     * Function to sort the list
     *
     * @param [String] $sortBy
     * @param [String] $orderDirection
     * @param [Collection] $records
     * @return Collection
     */
    protected function sortList($sortBy, $orderDirection, $records)
    {
        return $records->orderBy($sortBy, $orderDirection);
    }
}
