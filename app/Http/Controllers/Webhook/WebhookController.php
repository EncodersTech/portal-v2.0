<?php

namespace App\Http\Controllers\Webhook;

use App\Exceptions\CustomBaseException;
use App\Helper;
use App\Jobs\ProcessDwollaTransferWebhook;
use App\Jobs\ProcessStripeTransferWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessDwollaWebhook;
use App\Models\USAGSanction;
use App\Services\USAGService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WebhookController extends \App\Http\Controllers\Webhook\BaseWebhookController
{
    public const WEBHOOK_ERROR_RESSOURCE_NOT_FOUND = 404;
    public const WEBHOOK_ERROR_INVALID_VALUE = 400;

    public function test(){
		return array(
            "test" => config('services.usag.log_payloads', false)
        );
	}
    public function dwolla()
    {
        try {
            ProcessDwollaWebhook::dispatch(request()->getContent(), request()->headers);
            return $this->success(['message' => 'Event handled successfully.']);
        } catch (\Throwable $e) {
            Log::critical(self::class . '@dwolla : ' . $e->getTraceAsString(), $e);
            return $this->error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    public function usag(Request $request, string $version)
    {
        $payload = null;
        $result = [
            'TxnType' => null,
            'ResponseStatus' => [
                'ErrorCode' => 0,
                'Message' => 'OK'
            ]
        ];
        
        try {
            $logPayloads = config('services.usag.log_payloads', false);
            $payloadLogFile = __DIR__ . '/../../../../storage/logs/usag.log';
            $now = now()->toDateTimeString();
            $payloadLogSeparator = str_repeat('=', 64);

            if (!Helper::isInteger($version))
                throw new CustomBaseException('Invalid USAG API version.', Response::HTTP_BAD_REQUEST);
            $version = (int) $version;
            switch ($version) {
                case 3:
                    #region V3
                    $usagService = resolve(USAGService::class); /** @var USAGService $usagService */

                    // $usagService->authorize($request);

                    if ($logPayloads) {
                        $data = $payloadLogSeparator . "\n";
                        $data .= $now . "\n";
                        $data .= request()->getContent() . "\n";
                        $data .= $payloadLogSeparator . "\n\n";
                        file_put_contents($payloadLogFile, $data, FILE_APPEND);
                    }

                    $payload = json_decode(request()->getContent());
                    if ($payload === null)
                        throw new CustomBaseException('JSON decode failed', Response::HTTP_BAD_REQUEST);

                    if (!isset($payload->Action))
                        throw new CustomBaseException('Missing payload action', Response::HTTP_BAD_REQUEST);

                    if (!isset($payload->TimeStamp))
                        throw new CustomBaseException('Missing payload timestamp', Response::HTTP_BAD_REQUEST);

                    $timestamp = new Carbon($payload->TimeStamp);

                    if (!isset($payload->Action))
                        throw new CustomBaseException('Missing payload action', Response::HTTP_BAD_REQUEST);

                    if (isset($payload->Sanction)) {
                        if (!isset($payload->Sanction->SanctionID))
                            throw new CustomBaseException('Missing sanction number', Response::HTTP_BAD_REQUEST);

                        $result['TxnType'] = USAGService::WEBHOOK_TYPE_SANCTION;
                        $result['SanctionID'] = $payload->Sanction->SanctionID;
                        $result['SanctionURL'] = null;

                        switch ($payload->Action) {
                            case USAGService::WEBHOOK_ACTION_ADD:
                                $usagService->AddSanction($payload, $timestamp);
                                break;

                            case USAGService::WEBHOOK_ACTION_UPDATE:
                                $usagService->UpdateSanction($payload, $timestamp);
                                break;

                            case USAGService::WEBHOOK_ACTION_DELETE:
                                $usagService->UpdateSanction($payload, $timestamp, USAGSanction::SANCTION_ACTION_DELETE);
                                break;

                            case USAGService::WEBHOOK_ACTION_CHANGE_VENDOR:
                                break;

                            default:
                                throw new CustomBaseException('Invalid payload type / action combination', Response::HTTP_BAD_REQUEST);
                                break;
                        }
                    } elseif (isset($payload->Reservation)) {
                        if (!isset($payload->Reservation->SanctionID))
                            throw new CustomBaseException('Missing sanction number', Response::HTTP_BAD_REQUEST);

                        $result['TxnType'] = USAGService::WEBHOOK_TYPE_RESERVATION;
                        $result['ReservationID'] = [];

                        if (isset($payload->Reservation->Details)) {
                            foreach (['Gymnasts', 'Coaches'] as $field) {
                                if (isset($payload->Reservation->Details->$field)) {
                                    foreach ([USAGService::WEBHOOK_ACTION_ADD, USAGService::WEBHOOK_ACTION_UPDATE, USAGService::WEBHOOK_ACTION_DELETE] as $action) {
                                        if (isset($payload->Reservation->Details->$field->$action) && is_array($payload->Reservation->Details->$field->$action)) {
                                            $result['ReservationID'] = array_merge($result['ReservationID'], array_column($payload->Reservation->Details->$field->$action, 'ReservationID'));
                                        }
                                    }
                                }
                            }

                            switch ($payload->Action) {
                                case USAGService::WEBHOOK_ACTION_ADD:
                                    $usagService->AddReservation($payload, $timestamp);
                                    break;

                                case USAGService::WEBHOOK_ACTION_UPDATE:
                                    $usagService->UpdateReservation($payload, $timestamp);
                                    break;

                                default:
                                    throw new CustomBaseException('Invalid payload type / action combination', Response::HTTP_BAD_REQUEST);
                                    break;
                            }
                        }
                    } else {
                        throw new CustomBaseException('Invalid payload type', Response::HTTP_BAD_REQUEST);
                    }
                    #endregion
                    break;

                default:
                    throw new CustomBaseException('Invalid USAG API version.', Response::HTTP_BAD_REQUEST);
                    break;
            }

            if ($logPayloads) {
                $data = $payloadLogSeparator . "\n";
                $data .= $now . "\n";
                $data .= json_encode($result) . "\n";
                $data .= $payloadLogSeparator . "\n\n";
                file_put_contents($payloadLogFile, $data, FILE_APPEND);
            }

            return $this->success($result);
        } catch (\Throwable $e) {
            Log::critical(self::class . '@usag : ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $payload
            ]);

            $returnCode = Response::HTTP_INTERNAL_SERVER_ERROR;

            if ($e instanceof CustomBaseException) {
                $result['ResponseStatus']['ErrorCode'] = $e->getCode();
                $result['ResponseStatus']['Message'] = $e->getMessage();
                $returnCode = Response::HTTP_BAD_REQUEST;
            } else {
                $result['ResponseStatus']['ErrorCode'] = Response::HTTP_INTERNAL_SERVER_ERROR;
                $result['ResponseStatus']['Message'] = 'Something went wrong on Allgymnastics servers.';//$e->getMessage();
            }

            return $this->error($result, $returnCode);
        }
    }

    public function dwollaTransfer(Request  $request): JsonResponse
    {
        Log::info('webhook', $request->all());
        try {
            ProcessDwollaTransferWebhook::dispatch(request()->getContent(), request()->headers);
            return $this->success(['message' => 'Event handled successfully.']);
        } catch (\Throwable $e) {
            return $this->error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    public function stripeAchTransfer(Request  $request)
    {
        // Log::info('webhook', $request->all());
        try {
            ProcessStripeTransferWebhook::dispatch(request()->getContent(), request()->headers);
            return $this->success(['message' => 'Event handled successfully.']);
        } catch (\Throwable $e) {
            return $this->error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    public function stripeConnect(Request  $request)
    {
        
        try {
            // Log::info('webhook', request()->all());
            StripeService::updateConnectAccountWebhook(request()->getContent(), request()->headers);
            return $this->success(['message' => 'Event handled successfully.']);
        } catch (\Throwable $e) {
            return $this->error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
}