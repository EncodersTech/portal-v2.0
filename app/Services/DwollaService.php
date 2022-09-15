<?php

namespace App\Services;

use App\Models\ErrorCodeCategory;
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

class DwollaService {

    public const BANK_TOKEN_RULES = [
        'bank_token' => ['required', 'string']
    ];

    public const BANK_ACCOUNT_RULES = [
        'bank_account' => ['required', 'string']
    ];

    public const MICRO_DEPOSITS_RULES = [
        'amount1' => ['required', 'numeric'],
        'amount2' => ['required', 'numeric']
    ];

    public const STATUS_UNVERIFIED = 'unverified';
    public const STATUS_RETRY = 'retry';
    public const STATUS_DOCUMENT = 'document';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_DEACTIVATED = 'deactivated';

    public const STATUS_STRINGS = [
        self::STATUS_UNVERIFIED => 'Unverified.',
        self::STATUS_RETRY => 'Verification failed',
        self::STATUS_DOCUMENT => 'Additional Document Required',
        self::STATUS_VERIFIED => 'Verified',
        self::STATUS_SUSPENDED => 'Suspended',
        self::STATUS_DEACTIVATED => 'Deactivated',
    ];

    private $credentials;
    private $host;

    public function __construct(string $client_id, string $secret, string $env)
    {
        $this->host = 'https://api' . ($env == 'sandbox' ? '-sandbox' : '') . '.dwolla.com/';
        $this->credentials = [$client_id, $secret];
    }

    private function authenticate() : string {
        try {
            $client = new Guzzle([
                'base_uri' => $this->host,
                'auth' =>$this->credentials,

            ]);

            $responseJSON = (string) $client->request('POST', '/token', [
                'auth' => $this->credentials,
                'form_params' => [
                  'grant_type' => 'client_credentials'
                ]
            ])->getBody();

            $response = json_decode($responseJSON, true);
            if (($response !== null) && isset($response['access_token']))
                return $response['access_token'];

            throw new \Exception("Wrong response\n" . $response);
        } catch (\Throwable $e) {
            logger()->error('DwollaService::authenticate() : ' . $e->getMessage());
            throw new CustomBaseException(
                'Something went wrong with our payment processor.',
                ErrorCodeCategory::getCategoryBase('Dwolla') + 1
            );
        }
    }

    public function createCustomer(string $firstName, string $lastName, string $email)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $customersApi = new CustomersApi(new ApiClient($this->host));
            return $customersApi->getCustomer(
                $customersApi->create([
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $email
                ])
            );
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'customer_create');
        }
    }

    public function retrieveCustomer(string $id)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $customersApi = new CustomersApi(new ApiClient($this->host));
            return $customersApi->getCustomer($id);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'customer_retrieve');
        }
    }

    public function updateCustomer(string $url, array $data)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $customersApi = new CustomersApi(new ApiClient($this->host));
            return $customersApi->updateCustomer($data, $url);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'customer_update');
        }
    }

    public function generateIAVToken(string $customerId)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $customersApi = new CustomersApi(new ApiClient($this->host));
            $iavToken = $customersApi->getCustomerIavToken($customerId);
            return $iavToken->token;
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'iav_generate');
        }
    }

    public function listAccountFundingSources(bool $removed = false)
    {
        try {
            Configuration::$access_token = $this->authenticate();

            $apiClient = new ApiClient($this->host);
            $rootApi = new RootApi($apiClient);
            $root = $rootApi->root();
            $accountUrl = $root->_links["account"]->href;

            $fundingSourcesApi = new FundingsourcesApi($apiClient);
            $fundingSources = $fundingSourcesApi->getAccountFundingSources($accountUrl, $removed);
            return $fundingSources->_embedded->{'funding-sources'};
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'funding_source_list');
        }
    }

    public function listFundingSources(string $customerId, bool $removed = false)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $fundingSourcesApi = new FundingsourcesApi(new ApiClient($this->host));
            $fundingSources = $fundingSourcesApi->getCustomerFundingSources($customerId, $removed);
            return $fundingSources->_embedded->{'funding-sources'};
        } catch (ApiException $e) {
            Log::error($e->getMessage());
            $this->_handleDwollaException($e, 'funding_source_list');
        }
    }

    public function getFundingSource(string $fundingSourceId)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $fundingSourcesApi = new FundingsourcesApi(new ApiClient($this->host));
            return $fundingSourcesApi->id($fundingSourceId);
        } catch (ApiException $e) {
            // return $e->getMessage();
            $this->_handleDwollaException($e, 'funding_source_get');
        }
    }

    public function removeFundingSource(string $fundingSourceId)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $fundingSourcesApi = new FundingsourcesApi(new ApiClient($this->host));
            return $fundingSourcesApi->softDelete(['removed' => true], $fundingSourceId);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'funding_source_delete');
        }
    }

    public function verifyMicroDeposits(string $fundingSourceId, string $v1, string $v2,
                                        string $currency = 'USD') {
        try {
            Configuration::$access_token = $this->authenticate();
            $fundingSourcesApi = new FundingsourcesApi(new ApiClient($this->host));
            $verification = $fundingSourcesApi->microDeposits([
                'amount1' => [
                    'value' => $v1,
                    'currency' => $currency
                ],
                'amount2' => [
                    'value' => $v2,
                    'currency' => $currency
                ]
            ], $fundingSourceId);
            return $verification;
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'micro_deposits_verify');
        }
    }

    public function createWebhook(string $url, string $secret)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $webhookApi = new WebhooksubscriptionsApi(new ApiClient($this->host));
            return $webhookApi->create([
                'url' => $url,
                'secret' => $secret
            ]);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'webhook_create');
        }
    }

    public function listWebhook()
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $webhookApi = new WebhooksubscriptionsApi(new ApiClient($this->host));
            return $webhookApi->_list();
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'webhook_list');
        }
    }

    public function retrieveWebhook(string $id)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $webhookApi = new WebhooksubscriptionsApi(new ApiClient($this->host));
            return $webhookApi->id($id);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'webhook_retrieve');
        }
    }

    public function updateWebhook(string $url, bool $pause)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $webhookApi = new WebhooksubscriptionsApi(new ApiClient($this->host));
            return $webhookApi->updateSubscription(
                [
                    'paused' => $pause
                ],
                $url
            );
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'webhook_update');
        }
    }

    public function deleteWebhook(string $url)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $webhookApi = new WebhooksubscriptionsApi(new ApiClient($this->host));
            return $webhookApi->deleteById($url);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'webhook_delete');
        }
    }

    public function retrieveSubscriptionWebhook(string $url, int $limit = 25, int $offset = 0)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $webhookApi = new WebhooksApi(new ApiClient($this->host));
            return $webhookApi->hooksById($url, $limit, $offset);
        } catch (ApiException $e) {
            throw $e;
            $this->_handleDwollaException($e, 'subscription_retrieve_webhooks');
        }
    }

    public function createMasterFundingSource(string $account, string $routing, string $type, string $name)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $fundingApi = new FundingsourcesApi(new ApiClient($this->host));
            return $fundingApi->createFundingSource([
                "routingNumber" => $routing,
                "accountNumber" => $account,
                "bankAccountType" => $type,
                "name" => $name
            ]);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'master_source_create');
        }
    }

    public function initiateACHTransfer(string $source, string $destination, float $amount, array $meta = null)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $transfersApi = new TransfersApi(new ApiClient($this->host));

            if ($amount > 10000)
                throw new CustomDwollaException("Amound too large. Maximum amount is $10,000 USD per transaction.", 1);

            $transfer = $transfersApi->create([
                '_links' => [
                    'source' => [
                        'href' => $source,
                    ],
                    'destination' => [
                        'href' => $destination
                    ]
                ],
                'amount' => [
                    'currency' => 'USD',
                    'value' => $amount
                ],
                'metadata' => $meta
            ]);
            return $transfer;
        } catch (ApiException $e) {
            Log::info($e->getMessage(), [
                'Exception' => $e,
                'Response' => $e->getResponseBody(),
            ]);
            $this->_handleDwollaException($e, 'initiate_ach_transfer');
        }
    }

    public function getACHTransfer($transfer)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $transfersApi = new TransfersApi(new ApiClient($this->host));
            $transfer = $transfer = $transfersApi->byId($transfer);
            return $transfer;
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'get_ach_transfer');
        }
    }

    public function getACHFailedTransfer($transfer)
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $transfersApi = new TransfersApi(new ApiClient($this->host));
            return $transfersApi->failureById($transfer);
        } catch (ApiException $e) {
            $this->_handleDwollaException($e, 'get_ach_transfer');
        }
    }

    public function cancelACHTransfer($transfer) : bool
    {
        try {
            Configuration::$access_token = $this->authenticate();
            $transfersApi = new TransfersApi(new ApiClient($this->host));
            $transfer = $transfer = $transfersApi->update([
                'status' => 'cancelled',
            ], $transfer);
            return ($transfer->status == 'cancelled');
        } catch (ApiException $e) {
            Log::debug('cancelACHTransfer: ' . $e->getMessage(), [
                'body' => $e->getResponseBody()
            ]);
            return false;
        }
    }

    public function uploadDocument(string $url, UploadedFile $document, string $type)
    {
        try {
            $token = $this->authenticate();
            $fileHandle = fopen($document->getPathname() , 'r');
            if ($fileHandle === false)
                throw new CustomDwollaException("An error occured while opening the file.", -1);

            $client = new Guzzle();

            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/vnd.dwolla.v1.hal+json',
                ],
                'multipart' => [
                    [
                        'name'     => 'documentType',
                        'contents' => $type
                    ],
                    [
                        'name'     => 'file',
                        'contents' => $fileHandle,
                        'filename' => $document->getClientOriginalName(),
                        'mime-type' => $document->getMimeType()
                    ],
                ]
            ]);

            return true;
        } catch (ClientException $e) {
            $body = json_decode((string) $e->getResponse()->getBody());
            if ($body === null)
                throw new CustomDwollaException('Something went wrong while uploading your file.
                Additionally, JSON decoding failed while trying to get the error.', -1);

            $dwollaCode = $body->code;
            $code = ErrorCodeCategory::getCategoryBase('Dwolla');
            $msg = 'Something went wrong while uploading your file.';

            throw new CustomDwollaException($msg, $code, $e);
        } catch (\Throwable $e) {
            throw new CustomDwollaException("A server error occured while uploading your file", -1, $e);
        }
    }

    private function _handleDwollaException(ApiException $e, string $context, bool $throw = true) : array {
        $code = ErrorCodeCategory::getCategoryBase('Dwolla');
        $msg = 'There\'s a problem with your account. Please contact us as soon as possible.';
        $details = null;

        $responseBody = json_decode($e->getResponseBody());
        if ($responseBody === null)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1, $e);

        switch ($context) {
            case 'customer_create':
                switch ($responseBody->code) {
                    case 'BadRequest':
                        $code += 7;
                        break;

                    case 'ValidationError':
                        $code += 6;
                        break;

                    case 'Forbidden':
                        $code += 4;
                        break;

                    case 'InvalidResourceState':
                        $code += 5;
                        break;

                    default:
                }
                break;

            case 'customer_retrieve':
                switch ($responseBody->code) {
                    case 'NotFound':
                        $code += 3;
                        break;

                    case 'Forbidden':
                        $code += 4;
                        break;

                    case 'InvalidResourceState':
                        $code += 5;
                        break;

                    default:
                }
                break;

            case 'customer_update':
                switch ($responseBody->code) {
                    case 'NotFound':
                        $code += 3;
                        break;

                    case 'Forbidden':
                        $code += 4;
                        break;

                    case 'InvalidResourceState':
                        $code += 5;
                        break;

                    case 'ValidationError':
                        $code += 6;
                        break;
                    break;
                }
                break;

            case 'funding_source_list':
                switch ($responseBody->code) {
                    case 'NotFound':
                        $code += 3;
                        break;

                    case 'Forbidden':
                        $code += 4;
                        break;

                    case 'InvalidResourceState':
                        $code += 5;
                        break;

                    default:
                }
                break;

            case 'iav_generate':
                switch ($responseBody->code) {
                    case 'NotFound':
                        $code += 3;
                        break;

                    default:
                }
                break;

            case 'funding_source_delete':
                $code += 8;
                $msg  = 'No such bank account';
                break;

            case 'micro_deposits_verify':
                switch ($responseBody->code) {
                    case 'TryAgainLater':
                        $code += 9;
                        $msg = 'Please wait until the amounts are processed by your bank and try again.';
                        break;

                    case 'ValidationError':
                        $code += 10;
                        $msg = 'Wrong micro-deposit amounts';
                        break;

                    case 'InvalidResourceState':
                        $code += 11;
                        $msg = 'Either this bank account is already verified or you made too many attempts.';
                        break;

                    case 'NotFound':
                        $code += 8;
                        break;

                    case 'Unknown':
                        $code += 999;
                        $msg += 'Something went wrong with our payment processor.';
                        break;

                    default:
                }
                break;

            case 'webhook_create':
                $code += -1;
                $msg = 'The maximum number of Dwolla webhook subscriptions has been reached';
                break;

            case 'webhook_retrieve':
                $code += 8;
                $msg = 'No such webhook subscription';
                break;

            case 'mater_source_create':
                switch ($responseBody->code) {
                    case 'BadRequest':
                        $code += 7;
                        $msg = 'Duplicate funding source or validation error';
                        break;

                    case 'Forbidden':
                        $code += 4;
                        $msg = 'Not authorized to create funding source.';
                        break;
                    break;
                }
                break;

            case 'funding_source_get':
                $code += 8;
                $msg = 'No such bank account';
                break;

            case 'initiate_ach_transfer':
                switch ($responseBody->code) {
                    case 'ValidationError':
                        $code += 6;
                        $msg = 'Invalid amount. If your account is not verified, please verify your account first.';
                        break;

                    case 'BadRequest':
                        $code += 7;
                        $msg = 'Invalid or restricted funding source. Or invalid metadata';
                        break;

                    case 'Forbidden':
                        $code += 4;
                        $msg = 'Not authorized to create a transfer.';
                        break;
                    break;
                }
                break;

            case 'get_ach_transfer':
                $code += 8;
                $msg = 'No such bank account';
                break;

            default:
                $msg += 'Something went wrong with our payment processor.';
        }

        $result = [
            'code' => $code,
            'message' => $msg,
            'details' => $details,
        ];

        if ($throw)
            throw new CustomDwollaException($result['message'], $result['code'], $e);

        return $result;
    }

    public static function verifyWebhookSignature($signature, $secret, $payload) {
        $result = hash_hmac("sha256", $payload, $secret);
        return hash_equals($result, $signature);
    }
}