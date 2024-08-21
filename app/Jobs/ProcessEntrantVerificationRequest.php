<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomBaseException;
use App\Mail\Host\VerificationCompletedMailable;
use App\Models\RegistrationAthleteVerification;
use App\Models\RegistrationCoachVerification;
use App\Models\SanctioningBody;
use App\Services\USAGService;
use App\Services\USAIGCService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessEntrantVerificationRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const RATE_LIMIT_KEY = 'agc_verification';

    private $verification;
    private $type;

    /** @var USAGService */
    private $usagService;     

    /** @var USAIGCService */
    private $usaigcService; 
    
    public $tries = 3;
    public $retryAfter = 300;
    public $timeout = 100;
    public $failOnTimeout = false;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, $verification)
    {
        $this->type = $type;
        $this->verification = $verification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Maybe usefull to use rate limiting.
        DB::beginTransaction();
        try {
            $result = [];
            switch ($this->type) {
                case 'athletes':
                    if (!($this->verification instanceof RegistrationAthleteVerification))
                        throw new CustomBaseException("Wrong verification for type " . $this->type, -1);

                    switch ($this->verification->sanctioning_body_id) {
                        case SanctioningBody::USAG:
                            $this->usagService = resolve(USAGService::class);
                            $result = $this->usagService->verifyAthletes($this->verification);
                            break;

                        case SanctioningBody::USAIGC:
                            $this->usaigcService = resolve(USAIGCService::class);
                            $result = $this->usaigcService->verifyAthletes($this->verification);
                            break;
                        
                        default:
                            throw new CustomBaseException("Invalid sanctioning body", -1);
                            break;
                    }
                    break;

                case 'coaches':
                    if (!($this->verification instanceof RegistrationCoachVerification))
                        throw new CustomBaseException("Wrong verification for type " . $this->type, -1);

                    if ($this->verification->sanctioning_body_id != SanctioningBody::USAG)
                        throw new CustomBaseException("Invalid sanctioning body", -1);

                    $this->usagService = resolve(USAGService::class);
                    $result = $this->usagService->verifyCoaches($this->verification);                    
                    break;
                
                default:
                    throw new CustomBaseException("Invalid type.", -1);
                    break;
            }

            $this->verification->results = [
                'status' => 'success',
                'data' => $result
            ];
            $this->verification->status = RegistrationAthleteVerification::VERIFICATION_DONE;
            $this->verification->save();

            Mail::to($this->verification->meet_registration->meet->gym->user->email)
                ->send(new VerificationCompletedMailable($this->verification));

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    public function failed(\Exception $e)
    {
        $this->verification->results = ['status' => 'error'];
        $this->verification->status = RegistrationAthleteVerification::VERIFICATION_DONE;
        $this->verification->save();

        Log::critical(
            self::class . ' job failed',
            [
                'exception' => $e,
                'payload' => $this->verification,
            ]
        );
    }
}
