<?php

namespace App\Http\Requests;

use App\Rules\CreateAssetReservationRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateReservationRequest extends FormRequest
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
            'asset_ids' => 'required|array',
            'asset_ids.*' => [
                'exists:assets,id,deleted_at,NULL',
                new CreateAssetReservationRule(
                    $this->asset_ids,
                    $this->date,
                    $this->start_time,
                    $this->end_time,
                )
            ],
            'date' => "required|date|date_format:Y-m-d|after:{$date}",
            'start_time' => "required|date|date_format:Y-m-d H:i:s",
            'end_time' => 'required|date|date_format:Y-m-d H:i:s|after:start_time',
            'description' => 'nullable'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'start_time' => $this->start_date . ' ' . $this->from,
            'end_time' => $this->start_date . ' ' . $this->to,
            'date' => $this->start_date
        ]);
    }
}
