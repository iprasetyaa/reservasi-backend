<?php

namespace App\Recaptchas;

use GuzzleHttp\Client;

class GoogleRecaptcha
{
    public $response;
    /**
     * Function to validate the google recaptcha
     *
     * @param Request $request
     * @return Boolean
     */
    public function __construct($token)
    {
        $client = new Client();

        $response = $client->request(
            'POST',
            config('recaptcha.base_url'),
            [
                'query' => [
                    'secret' => config('recaptcha.secret_key'),
                    'response' => $token
                ]
            ]
        );

        $this->response = json_decode($response->getBody(), true);
    }
}
