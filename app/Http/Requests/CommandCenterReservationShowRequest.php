<?php

namespace App\Http\Requests;

use App\Rules\GoogelRecaptchaRule;
use App\Rules\RecaptchaRule;
use Illuminate\Foundation\Http\FormRequest;

class CommandCenterReservationShowRequest extends FormRequest
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
            'recaptcha' => [
                new GoogelRecaptchaRule($this->recaptcha)
            ]
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $recaptchaToken = $this->header('recaptcha-token');

        $this->merge([
            'recaptcha' => $recaptchaToken,
        ]);
    }
}
