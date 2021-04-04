<?php

namespace App\Recaptchas;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class GoogleRecaptcha
{
    protected $response;
    /**
     * Function to validate the google recaptcha
     *
     * @param Collection $request
     * @return Boolean
     */
    public function __construct($request)
    {
        $client = new Client();

        $token = $request->header('recaptcha-token');

        $response = $client->request('POST', config('recaptcha.base_url'),
        [
            'query' => [
                'secret' => config('recaptcha.secret_key'),
                'response' => $token
            ]
        ]);

        $response = json_decode($response->getBody(), true);

        abort_if(! $response['success'], Response::HTTP_FORBIDDEN, $response['error-codes'][0]);
    }
}
