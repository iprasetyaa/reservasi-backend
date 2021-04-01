<?php

namespace App\Recaptchas;

use Illuminate\Support\Facades\Http;

class GoogleRecaptcha
{
    /**
     * Function to validate the google recaptcha
     *
     * @param [String] $token
     * @return Boolean
     */
    public function __construct($params)
    {
        $response = Http::post(config('recaptcha.base_url') . $params['token']);

        $this->response = json_decode($response);
    }
}
