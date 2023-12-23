<?php

namespace App\Services;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use App\Models\ErrorCodeCategory;
use App\Exceptions\CustomBaseException;

class PayPalService {

    private $apiContext;

    private $redirectUrls;

    public function __construct(string $client_id, string $secret, bool $live) {
        $this->apiContext = new ApiContext(new OAuthTokenCredential($client_id, $secret));

        $this->apiContext->setConfig([
            'mode' => ($live ? 'live' : 'sandbox'),
            'log.LogEnabled' => config(),
            'log.FileName' => __DIR__ . '/../../storage/logs/PayPal.log',
            'log.LogLevel' => ($live ? 'INFO' : 'DEBUG'), // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => true,
            //'cache.FileName' => '/PaypalCache' // for determining paypal cache directory
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
            //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
        ]);

        $baseUrl = config('app.url') . '/paypal';
        $this->redirectUrls = [
            'return' => $baseUrl . '/process',
            'cancel' => $baseUrl. '/cancel',
        ];
    }
    
    public static function _handlePayPalException(PayPalConnectionException $e, bool $throw = true) : array
    {
        $result = [
            'code' => ErrorCodeCategory::getCategoryBase('PayPal'),
            'message' => 'Something went wrong with our payment processor.',
            'details' => null,
        ];

        //$e->getCode();

        if ($throw)
            throw new CustomBaseException($result['message'], $result['code'], $e);

        return $result;
    }
}