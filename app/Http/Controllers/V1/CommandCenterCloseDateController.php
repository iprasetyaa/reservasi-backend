<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommandCenterCloseDateRequest;
use App\Http\Requests\UpdateCommandCenterCloseDateRequest;
use App\Http\Resources\CCCloseDateResource;
use App\Models\CommandCenterCloseDate;
use Illuminate\Http\Request;

class CommandCenterCloseDateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $records = CommandCenterCloseDate::query();
        $perPage = $request->input('perPage', 10);
        $perPage = $this->getPaginationSize($perPage);

        return CCCloseDateResource::collection($records->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCommandCenterCloseDateRequest $request)
    {
        $this->authorize('create', CommandCenterCloseDate::class);

        $record = new CommandCenterCloseDate();
        $record->fill($request->validated());
        $record->save();

        return new CCCloseDateResource($record);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(CommandCenterCloseDate $closeDay)
    {
        return new CCCloseDateResource($closeDay);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCommandCenterCloseDateRequest $request, CommandCenterCloseDate $closeDay)
    {
        $this->authorize('update', $closeDay);

        $closeDay->update($request->only('date', 'note'));

        return new CCCloseDateResource($closeDay);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CommandCenterCloseDate $closeDay)
    {
        $this->authorize('delete', $closeDay);

        $closeDay->delete();
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
}
