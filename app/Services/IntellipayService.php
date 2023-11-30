<?php 
namespace App\Services;

use App\Models\ErrorCodeCategory;
use App\Models\MeetTransaction;
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


class IntellipayService {

    private $marchant_key;
    private $api_key;
    private $host;
    private $payment_status = ['unprocessed', 'queued', 'pending', 'declined', 'settled', 'completed', 'voided', 'refunded', 'awaiting ack'];
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
        $this->host = 'https://test.cpteller.com/api/26/webapi.cfc';
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
                                'paymentstatus' => $this->payment_status[$value['paymentstatus']]
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

}









?>