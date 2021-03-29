<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;
use App\Enums\CommandCenterShiftStatusEnum;

class CommandCenterShiftUpdateRequest extends FormRequest
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
        return [
            'name' => 'required|unique:command_center_shifts,name,' . $this->command_center_shift->id . ',id',
            'time' => 'required|unique:command_center_shifts,time,' . $this->command_center_shift->id . ',id',
            'status' => new EnumRule(CommandCenterShiftStatusEnum::class),
            'capacity' => 'required|numeric'
        ];
    }
}
