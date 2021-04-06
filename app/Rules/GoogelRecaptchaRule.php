<?php

namespace App\Rules;

use App\Recaptchas\GoogleRecaptcha;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\App;

class GoogelRecaptchaRule implements Rule
{
    protected $recaptcha;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->recaptcha = App::makeWith(GoogleRecaptcha::class, ['token' => $token]);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->recaptcha->response['success'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.invalid_recaptcha');
    }
}
