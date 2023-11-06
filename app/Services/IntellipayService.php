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

}









?>