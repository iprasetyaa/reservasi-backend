<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CommandCenterReservationRule;
use Carbon\Carbon;
use App\Models\CommandCenterShift;

class CommandCenterReservationCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $oneMonthLater =  Carbon::now()->addMonth();
        $shift = CommandCenterShift::find($this->command_center_shift_id);
        $maxShift = ($shift) ? $shift->capacity : config('shift.default_max_visitor');
        return [
            'name' => 'string|required|max:100',
            'nik' => 'required|max:16|regex:/[0-9]{16}/',
            'organization_name' => 'string|nullable|max:100',
            'address' => 'string|nullable|max:100',
            'phone_number' => 'required|min:10|max:13|regex:/(0)[0-9]/',
            'email' => 'required|email:rfc,dns',
            'purpose' => 'string|required|max:255',
            'reservation_date' => ['required|date|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . $oneMonthLater],
            'command_center_shift_id' => 'required|exists:command_center_shifts,id,deleted_at,NULL',
            'visitors' => [
                'integer',
                'required',
                'min:1',
                'max:' . $maxShift,
                new CommandCenterReservationRule(
                    $this->command_center_shift_id,
                    $this->reservation_date,
                    $this->visitors,
                    $maxShift
                )
            ]
        ];
    }
}
