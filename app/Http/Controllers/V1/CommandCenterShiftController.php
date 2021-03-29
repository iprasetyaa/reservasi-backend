<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommandCenterShiftCreateRequest;
use App\Http\Requests\CommandCenterShiftUpdateRequest;
use App\Http\Resources\CommandCenterShiftResource;
use App\Models\CommandCenterShift;

class CommandCenterShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CommandCenterShift::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommandCenterShiftCreateRequest $request)
    {
        $record = new CommandCenterShift();
        $record->fill($request->validated());
        $record->save();

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
    public function update(CommandCenterShiftUpdateRequest $request, CommandCenterShift $commandCenterShift)
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
    }
}
