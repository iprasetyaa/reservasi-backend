<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;
use App\Enums\CommandCenterShiftStatusEnum;
use Illuminate\Validation\Rule;

class CommandCenterShiftRequest extends FormRequest
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
            'name' => [
                'required',
                Rule::unique('command_center_shifts')->ignore($this->commanCenterShift),
            ],
            'time' => [
                'required',
                Rule::unique('command_center_shifts')->ignore($this->commanCenterShift),
            ],
            'status' => new EnumRule(CommandCenterShiftStatusEnum::class),
            'capacity' => 'required|numeric'
        ];
    }
}
