<?php

namespace App\Http\Requests;

use App\Enums\ReservationRecurringTypeEnum;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumValueRule;

class ReservationRecurringRequest extends FormRequest
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
        $maxDate = Carbon::now()->addYear()->format('Y-m-d');

        $weeklyType = ReservationRecurringTypeEnum::WEEKLY();
        $monthlyType = ReservationRecurringTypeEnum::MONTHLY();

        return [
            'title' => 'required',
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id,deleted_at,NULL',
            'start_date' => "required|date|date_format:Y-m-d|after:{$date}",
            'end_date' => "required|date|date_format:Y-m-d|after:start_date|before:{$maxDate}",
            'from' => 'required|date_format:H:i:s',
            'to' => 'required|date_format:H:i:s|after:from',
            'description' => 'nullable',
            'repeat' => 'required|boolean',
            'repeat_type' => [
                'required_if:repeat,true',
                new EnumValueRule(ReservationRecurringTypeEnum::class)
            ],
            'days' => 'array|required_if:repeat,true|max:7',
            'days.*'  => 'numeric|distinct|min:0|max:6',
            'week' => "numeric|required_if:repeat_type,{$weeklyType},{$monthlyType}|nullable",
            'month' => "numeric|required_if:repeat_type,{$monthlyType}|nullable",
            'holder' => 'nullable|email:rfc,dns'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void`
     */
    protected function prepareForValidation()
    {
        $daysFormated = array();
        foreach ($this->days as $value) {
            array_push($daysFormated, array(
                'day' => $value,
                'name' => Carbon::create(Carbon::getDays()[$value])->locale('id_ID')->dayName
            ));
        }

        $this->merge([
            'days_formated' => $daysFormated,
        ]);
    }
}
