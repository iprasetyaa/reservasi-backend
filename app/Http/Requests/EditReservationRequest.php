<?php

namespace App\Http\Requests;

use App\Rules\EditAssetReservationRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class EditReservationRequest extends FormRequest
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
        $date = Carbon::now()->subDay()->format('Y-m-d');
        return [
            'title' => 'required',
            'asset_id' => [
                'required',
                'exists:assets,id,deleted_at,NULL',
                new EditAssetReservationRule(
                    $this->date,
                    $this->start_time,
                    $this->end_time,
                    optional($this->reservation)->id
                )
            ],
            'date' => "required|date|date_format:Y-m-d|after:{$date}",
            'start_time' => "required|date|date_format:Y-m-d H:i",
            'end_time' => 'required|date|date_format:Y-m-d H:i|after:start_time',
            'description' => 'nullable',
            'holder' => 'nullable|email:rfc,dns'
        ];
    }
}
