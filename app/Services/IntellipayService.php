<?php 
namespace App\Services;

use App\Models\ErrorCodeCategory;
use App\Models\MeetTransaction;
use App\Models\User;
use DwollaSwagger\Configuration;
use DwollaSwagger\ApiException;
use DwollaSwagger\ApiClient;
use DwollaSwagger\CustomersApi;
use GuzzleHttp\Client as Guzzle;
use App\Exceptions\CustomDwollaException;
use DwollaSwagger\FundingsourcesApi;
use App\Exceptions\CustomBaseException;
use DwollaSwagger\models\Transfer;
use DwollaSwagger\RootApi;
use DwollaSwagger\TransfersApi;
use DwollaSwagger\WebhooksApi;
use DwollaSwagger\WebhooksubscriptionsApi;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class IntellipayService {

    private $marchant_key;
    private $api_key;
    private $host;
    private $payment_status = ['unprocessed', 'queued', 'pending', 'declined', 'settled', 'completed', 'voided', 'refunded', 'awaiting ack'];
    public const CARD_RULES = [
        "cardname" => ['required', 'string'],
        "cardnumber" => ['required', 'string', 'min:15', 'max:19'],
        "cardexpirydate" => ['required', 'string', 'min:5', 'max:5'],
        "cardcvv" => ['required', 'string', 'min:3', 'max:4']
    ];
    private static $cardBrands = [
        'Amex' => '/img/cards/American Express.png',
        'Disc' => '/img/cards/Discover.png',
        'Jcb' => '/img/cards/JCB.png',
        'Mast' => '/img/cards/MasterCard.png',
        'UnionPay' => '/img/cards/UnionPay.png',
        'Visa' => '/img/cards/Visa.png',
        'Unknown' => '/img/cards/Unknown.png',
    ];
    public function __construct()
    {
        // https=>//test.cpteller.com/api/26/webapi.cfc
        // https=>//secure.cpteller.com/api/26/webapi.cfc
        if(env('APP_ENV') == 'production')
        {
            $this->host = 'https://secure.cpteller.com/api/26/webapi.cfc';
        }
        else
        {
            $this->host = 'https://test.cpteller.com/api/26/webapi.cfc';
        }
        $this->marchant_key = env('INTELLIPAY_MARCHANT_KEY');
        $this->api_key = env('INTELLIPAY_API_KEY');
    }
    public function make_payment($routing,$account_number,$type,$account_name,$amount, $comment)
    {
        try{
            $routing = trim($routing);
            $account_number = trim($account_number);
            $type = trim($type);
            $account_name = trim($account_name);
            $amount = trim($amount);
            $comment = trim($comment);
            
            if(trim($routing) == '' || trim($account_number) == '' || trim($type) == '' || trim($account_name) == '' )
            {
                throw new CustomBaseException("All fields are required for one time ach payment",400);
            }
            $user = auth()->user();
            $data = [
                'method'=>'bank_payment',
                'merchantkey'=> $this->marchant_key,
                'apikey'=> $this->api_key,
                'firstname'=> $user->first_name,
                'lastname'=> $user->last_name,
                'phone'=> $user->office_phone,
                'email'=> $user->email,
                'amount'=> $amount,
                'routingnum'=> $routing,
                'bankacctnum'=> $account_number,
                'bankaccttype'=> strtoupper($type),
                'bankacctname'=> $account_name,
                'comment'=> $comment
            ];
            $client = new Guzzle();
            $response = $client->post($this->host, ['form_params' => $data]);
            if ($response->getStatusCode() === 200) {
                $responseBody = $response->getBody()->getContents();
                return json_decode($responseBody,true);
            } else {
                // Handle non-200 status codes here
                throw new CustomBaseException("Request failed",$response->getStatusCode());
            }
        }
        catch(\Exception $e)
        {
            throw new CustomBaseException($e->getMessage(),$e->getCode());
        }
    }
    public function clear_payment_by_id($paymet_id)
    {
        try{
            $data = [
                'method' => 'payment_read',
                'merchantkey'=> $this->marchant_key,
                'apikey'=> $this->api_key,
                'paymentid' => $paymet_id
            ];
            $client = new Guzzle();
            $response = $client->post($this->host, ['form_params' => $data]);
            if ($response->getStatusCode() === 200) {
                $responseBody = $response->getBody()->getContents();
                return array(
                    'status' => 200,
                    'data' => json_decode($responseBody,true)
                );
            } else {
                return array(
                    'status' => 400,
                    'data' => "Guzzle status return error"
                );
            }
        }
        catch(\Exception $e)
        {
            return array(
                'status' => 400,
                'data' => $e->getMessage()
            );
        }
        
    }
    public function get_ach_payment_list($start_date,$end_date)
    {
        try{
            $data = [
                'method' => 'list_payments',
                'merchantkey'=> $this->marchant_key,
                'apikey'=> $this->api_key,
                'filter' => 'all',
                'datefilter' => 'prc',
                'fromdate' => $start_date,
                'todate' => $end_date
            ];
            $client = new Guzzle();
            $response = $client->post($this->host, ['form_params' => $data]);
            if ($response->getStatusCode() === 200) {
                $responseBody = $response->getBody()->getContents();
                $responseBody = json_decode($responseBody,true);
                $data = [];
                if($responseBody["status"] > 0)
                {
                    if(count($responseBody['items'])>0)
                    {
                        foreach ($responseBody['items'] as $key => $value) {
                            $data[] = [
                                'paymentid' => $value['paymentid'],
                                'paymentdate' => $value['paymentdate'],
                                'settlementdate' => $value['settlementdate'],
                                'name' => $value['firstname'] . ' ' . $value['lastname'],
                                'amount' => $value['amount'],
                                'paymentstatus' => $this->payment_status[$value['paymentstatus']] . ($value['returnreason'] == '' ? '': ' ('.$value['returnreason'].')')
                            ];
                        }
                    }
                }
                // sort data based on payment date desc
                usort($data, function($a, $b) {
                    return $b['paymentdate'] <=> $a['paymentdate'];
                });
                return array(
                    'status' => 200,
                    'data' => $data
                );
            } else {
                return array(
                    'status' => 400,
                    'data' => "Guzzle status return error"
                );
            }
        }
        catch(\Exception $e)
        {
            return array(
                'status' => 400,
                'data' => $e->getMessage()
            );
        }
    }
    public function addCard($attr)
    {
        $user = auth()->user();
        $data = [
            'method'=>'card_payment',
            'merchantkey'=> $this->marchant_key,
            'apikey'=> $this->api_key,
            'amount' => 1,
            'firstname'=> $user->first_name,
            'lastname'=> $user->last_name,
            'phone'=> $user->office_phone,
            'email'=> $user->email,
            'cardname'=> $attr['cardname'],
            'cardnum'=> $attr['cardnumber'],
            'expdate'=> $attr['cardexpirydate'],
            'cvv'=> $attr['cardcvv'],
            'comment'=> 'Card attachment payment, will be refunded'
        ];
        if($user->intellipay_customer_id != null)
        {
            $data['custid'] = $user->intellipay_customer_id;
        }
        $client = new Guzzle();
        $response = $client->post($this->host, ['form_params' => $data]);
        if ($response->getStatusCode() === 200) {
            $responseBody = $response->getBody()->getContents();
            $responseBody = json_decode($responseBody,true);
            if($responseBody['status'] > 0 && $responseBody['response'] == 'A')
            {
                $response_data = [
                    'custid' => $responseBody['custid'],
                    'paymentid' => $responseBody['paymentid'],
                    'response' => $responseBody['response']
                ];

                if($user->intellipay_customer_id == null)
                {
                    $user->intellipay_customer_id = $response_data['custid'];
                    $user->save();
                }

                $refund_data = [
                    'method'=>'payment_refund',
                    'merchantkey'=> $this->marchant_key,
                    'apikey'=> $this->api_key,
                    'paymentid' => $response_data['paymentid'],
                    'amount' => 1
                ];
                $client = new Guzzle();
                $refund_response = $client->post($this->host, ['form_params' => $refund_data]);

                if ($refund_response->getStatusCode() === 200) {
                    $refund_responseBody = $refund_response->getBody()->getContents();
                    $refund_responseBody = json_decode($refund_responseBody,true);
                    if($refund_responseBody['status'] > 0)
                    {
                        return array(
                            'status' => 200,
                            'message' => "Card attachment successful. (Refund of $1 has been initiated)"
                        );
                    }
                    else
                    {
                        return array(
                            'status' => 200,
                            'message' => "Card attachment successful, but charged $1 refund failed. Please contact admin for refund."
                        );
                    }
                } else {
                    // Handle non-200 status codes here
                    throw new CustomBaseException("Request failed",$response->getStatusCode());
                }
            }
            else
            {
                return array(
                    'status' => 400,
                    'message' => "Card attachment failed, please try again or contact admin"
                );
            }
        } else {
            throw new CustomBaseException("Request failed",$response->getStatusCode());
        }
    }
    public function getCards()
    {
        $user = auth()->user();
        // dd($user->intellipay_customer_id);
        if($user->intellipay_customer_id == null)
            return null;
        $data = [
            'method'=>'cust_read',
            'merchantkey'=> $this->marchant_key,
            'apikey'=> $this->api_key,
            'custid' => $user->intellipay_customer_id
        ];
        $client = new Guzzle();
        $response = $client->post($this->host, ['form_params' => $data]);
        if ($response->getStatusCode() === 200) {
            $responseBody = $response->getBody()->getContents();
            $responseBody = json_decode($responseBody,true);
            // dd($user->intellipay_customer_id);
            if($responseBody['status'] > 0)
            {
                $card = [
                    'id' => $user->intellipay_customer_id,
                    'last4' => $responseBody['cardending'],
                    'expires' => [
                        'month' => substr($responseBody['expdate'],0,2),
                        'year' => substr($responseBody['expdate'],2,2),
                    ],
                    'brand' => $responseBody['cardtype']
                ];
                if (key_exists($card['brand'], self::$cardBrands))
                    $card['image'] = self::$cardBrands[$card['brand']];
                else
                    $card['image'] = self::$cardBrands['Unknown'];

                return [$card];
            }
            else
            {
                return null;
            }
        } else {
            throw new CustomBaseException("Request failed",$response->getStatusCode());
        }
    }
    public function createCharge($amount, $meta_data)
    {
        $user = auth()->user();
        $data = [
            'method'=>'card_payment',
            'merchantkey'=> $this->marchant_key,
            'apikey'=> $this->api_key,
            'amount' => $amount,
            'firstname'=> $user->first_name,
            'lastname'=> $user->last_name,
            'custid' => $user->intellipay_customer_id,
            'comment'=> json_encode($meta_data)
        ];
        $client = new Guzzle();
        $response = $client->post($this->host, ['form_params' => $data]);
        if ($response->getStatusCode() === 200) {
            $responseBody = $response->getBody()->getContents();
            $responseBody = json_decode($responseBody,true);
            if($responseBody['status'] > 0 && $responseBody['response'] == 'A')
            {
                $card = self::getCards()[0];
                return array(
                    'paymentid' => $responseBody['paymentid'],
                    'last4' => $card['last4'],
                    'fee' => $responseBody['fee'],
                );
            }
            else
            {
                throw new CustomBaseException("Payment failed, please try again or contact admin. Code ".$responseBody['status'] ,400);
            }
        } else {
            throw new CustomBaseException("Request failed",$response->getStatusCode());
        }
    }
}









?>