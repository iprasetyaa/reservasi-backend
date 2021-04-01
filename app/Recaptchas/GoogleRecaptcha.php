<?php

namespace App\Recaptchas;

use GuzzleHttp\Client;

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
        $client = new Client();

        $response = $client->request('POST', config('recaptcha.base_url'),
        [
            'query' => [
                'secret' => config('recaptcha.secret_key'),
                'response' => $params['token']
            ]
        ]);

        $this->response = json_decode($response->getBody()->getContents());
    }
}
