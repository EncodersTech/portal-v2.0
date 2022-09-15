<?php

namespace App\Console\Commands;

use App\Exceptions\CustomBaseException;
use App\Mail\USAG\USAGReminderMailable;
use App\Models\USAGReservation;
use App\Models\USAGSanction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendUSAGReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usag:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends USAG Sanction and Reservation reminder emails.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $now = now(); /** @var Carbon $now */
            $processed = [];
            $update = [];
            $gyms = [];
            $contacts = [];
            $issues = [];

            #region Sanctions
            $sanctions = USAGSanction::whereIn('status', [USAGSanction::SANCTION_STATUS_PENDING, USAGSanction::SANCTION_STATUS_UNASSIGNED])
                                    ->where('next_notification_on', '<=', $now)
                                    ->with('gym')
                                    ->orderBy('notification_stage');

            $sanctions->chunk(100, function (Collection $collection) use (&$processed, &$update, &$gyms, &$contacts) {
                foreach ($collection as $s) { /** @var USAGSanction $s */
                    $this->info('Processing sanction ' . $s->id);
                    if (!in_array($s->number, $processed)) { // if not already processed
                        $update[$s->number] = [
                            'ids' => [],
                            'stage' => ($s->notification_stage == (count(USAGSanction::SANCTION_NOTIFICATION_STAGES) - 1) ? $s->notification_stage : $s->notification_stage + 1),
                        ];

                        switch ($s->status) {
                            case USAGSanction::SANCTION_STATUS_PENDING:
                                if (!isset($gyms[$s->gym->id])) {
                                    $gyms[$s->gym->id] = [
                                        'contact_name' => $s->contact_name,
                                        'gym' => $s->gym,
                                        'sanctions' => [],
                                        'reservations' => [],
                                    ];
                                }
        
                                $gyms[$s->gym->id]['sanctions'][] = $s->number;
                                break;
                            
                            case USAGSanction::SANCTION_STATUS_UNASSIGNED:
                                if (!isset($contacts[$s->contact_email])) {
                                    $contacts[$s->contact_email] = [
                                        'contact_name' => $s->contact_name,
                                        'sanctions' => [],
                                        'reservations' => [],
                                    ];
                                }
        
                                $contacts[$s->contact_email]['sanctions'][] = $s->number;
                                break;
                        }

                        $processed[] = $s->number;
                    }

                    $update[$s->number]['ids'][] = $s->id;
                }
            });

            foreach ($update as $sanction => $value) {
                $next = $now->copy()->addDays(USAGSanction::SANCTION_NOTIFICATION_STAGES[$value['stage']]);
                $result = USAGSanction::whereIn('id', $value['ids'])
                            ->update([
                                'next_notification_on' => $next,
                                'notification_stage' => $value['stage'],
                            ]);

                if (!$result)
                    $issues[] = "Failed to update sanctions No. $sanction\n";
            }
            #endregion
            
            $processed = [];
            $update = [];

            #region Reservations
            $reservations = USAGReservation::whereIn('status', [USAGReservation::RESERVATION_STATUS_PENDING, USAGReservation::RESERVATION_STATUS_UNASSIGNED])
                                    ->where('next_notification_on', '<=', $now)
                                    ->with('gym', 'usag_sanction')
                                    ->whereHas('usag_sanction', function (Builder $q0) {
                                        $q0->where('status', USAGSanction::SANCTION_STATUS_MERGED);
                                    })->orderBy('notification_stage');

            $reservations->chunk(100, function (Collection $collection) use (&$processed, &$update, &$gyms) {
                foreach ($collection as $r) { /** @var USAGReservation $r */
                    $this->info('Processing reservation ' . $r->id);
                    $id = ($r->parent !== null ? $r->parent->id : $r->id);

                    if (!in_array($id, $processed)) { // if not already processed
                        $update[$id] = [
                            'ids' => [],
                            'sanction' => $r->usag_sanction->id,
                            'gym' => $r->gym->name,
                            'stage' => ($r->notification_stage == (count(USAGReservation::RESERVATION_NOTIFICATION_STAGES) - 1) ? $r->notification_stage : $r->notification_stage + 1),
                        ];

                        switch ($r->status) {
                            case USAGReservation::RESERVATION_STATUS_PENDING:
                                if (!isset($gyms[$r->gym->id])) {
                                    $gyms[$r->gym->id] = [
                                        'contact_name' => $r->contact_name,
                                        'gym' => $r->gym,
                                        'sanctions' => [],
                                        'reservations' => [],
                                    ];
                                }
        
                                $gyms[$r->gym->id]['reservations'][] = $r->usag_sanction->number;
                                break;
                            
                            case USAGReservation::RESERVATION_STATUS_UNASSIGNED:
                                if (!isset($contacts[$r->contact_email])) {
                                    $contacts[$r->contact_email] = [
                                        'contact_name' => $r->contact_name,
                                        'sanctions' => [],
                                        'reservations' => [],
                                    ];
                                }
        
                                $contacts[$r->contact_email]['reservations'][] = $r->usag_sanction->number;
                                break;
                        }

                        $processed[] = $id;
                    }

                    $update[$id]['ids'][] = $r->id;
                }
            });

            foreach ($update as $reservation => $value) {
                $next = $now->copy()->addDays(USAGReservation::RESERVATION_NOTIFICATION_STAGES[$value['stage']]);
                $result = USAGReservation::whereIn('id', $value['ids'])
                            ->update([
                                'next_notification_on' => $next,
                                'notification_stage' => $value['stage'],
                            ]);

                if (!$result)
                    $issues[] = "Failed to update reservations for Gym '" . $value['gym'] . "' and sanction No." . $value['sanction'] . "\n";
            }
            #endregion            

            foreach ($gyms as $id => $gymData)
                Mail::to($gymData['gym']->user->email)->send(new USAGReminderMailable($gymData));

            foreach ($contacts as $email => $contactData)
                Mail::to($email)->send(new USAGReminderMailable($contactData, true));

            if (count($issues) > 0) {
                Log::warning('Some items failed to process', $issues);
                throw new CustomBaseException('Some items failed to process', -1);
            }
            
            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();
            $this->error(self::class . ' failed : ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
