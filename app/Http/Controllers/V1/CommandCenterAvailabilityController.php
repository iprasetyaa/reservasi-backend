<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommandCenterShift;
use App\Models\CommandCenterReservation;
use App\Enums\CommandCenterReservationStatusEnum;

class CommandCenterAvailabilityController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $shift = CommandCenterShift::findOrFail($request->input('command_center_shift_id'));
        $ccReservation = CommandCenterReservation::whereDate('reservation_date', $request->input('reservation_date'))
                                            ->where('command_center_shift_id', $request->input('command_center_shift_id'))
                                            ->where('approval_status', '<>', CommandCenterReservationStatusEnum::REJECTED())
                                            ->sum('visitors');

        return response()->json([
            'data' => [
                'capacity' => $shift->capacity,
                'available' => $shift->capacity - (int) $ccReservation
            ]
        ]);
    }
}
