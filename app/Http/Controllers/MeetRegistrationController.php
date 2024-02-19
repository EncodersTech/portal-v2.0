<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomBaseException;
use App\Exceptions\CustomStripeException;
use App\Helper;
use App\Models\CategoryMeet;
use App\Models\MeetCredit;
use App\Models\ErrorCodeCategory;
use App\Models\Meet;
use App\Models\Gym;
use App\Models\LevelCategory;
use App\Models\User;
use App\Models\MeetRegistration;
use App\Models\MeetTransaction;
use App\Models\SanctioningBody;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PDF;
use Illuminate\Support\Str;
use App\Models\AuditEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\Host\RegistrationUpdateMailable;

class MeetRegistrationController extends Controller
{

    public function index(Request $request, string $meet)
    {
        try {
            $today = now()->startOfDay(); /** @var Carbon $today */
            $meet = Meet::retrieveMeet($meet, true); /** @var Meet $meet */
            $host = $meet->gym; /** @var Gym $gym */
            $owner = $host->user; /** @var User $owner */
            $is_own = ($request->_managed_account->id == $owner->id);
            $registrationStatus = $meet->registrationStatus();

            if (!$meet->is_published)
                throw new CustomBaseException("No such meet", -1);

            if ($registrationStatus == Meet::REGISTRATION_STATUS_OPENING_SOON)
                throw new CustomBaseException("This meet is not open for registrations yet", -1);

            if (!(
                ($registrationStatus == Meet::REGISTRATION_STATUS_OPEN) ||
                ($meet->allow_late_registration && ($registrationStatus == Meet::REGISTRATION_STATUS_LATE)) ||
                (
                    ($registrationStatus == Meet::REGISTRATION_STATUS_CLOSED) &&
                    ($today < $meet->start_date)
                )
            ))
                throw new CustomBaseException("This meet is not open for registrations", -1);

            if ($request->_managed_account->gyms()->count() < 1)
                throw new CustomBaseException("Please add at least one gym to your account to be able to register.", -1);

            $registrations = $meet->getUserRegistrations($request->_managed_account);
            /** @var MeetRegistration[] $registrations */
            $registeredGyms = [];
            foreach ($registrations as $registration)
                $registeredGyms[] = $registration->gym->id;

            if (count($registeredGyms) == $request->_managed_account->gyms()->count())
                throw new CustomBaseException("All of your gyms have already registered for this meet.", -1);

            $levels = Helper::getStructuredLevelList($meet->activeLevels, $meet);
            $required_sanctions = [];
            foreach (SanctioningBody::all() as $body) /** @var SanctioningBody $body */
                $required_sanctions[$body->id] = LevelCategory::requiresSanction($body->id);

            $required_sanctions = json_encode($required_sanctions);
            if ($required_sanctions === false)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

            if ($meet->schedule != null) {
                $meet->schedule = json_decode($meet->schedule);
                if ($meet->schedule === null)
                    throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            }

        } catch (CustomBaseException $e) {
            return redirect(route('meets.browse'))->with('error', $e->getMessage());
        }

        return view('registration.register', [
            'current_page' => 'browse-meets',
            'meet' => $meet,
            'host' => $host,
            'is_own' => $is_own,
            'registrations' => $registrations,
            'registeredGyms' => $registeredGyms,
            'bodies' => $levels,
            'required_sanctions' => $required_sanctions
        ]);
    }

    public function show(Request $request, string $gym, string $registration) {
        $gym = $request->_managed_account->retrieveGym($gym, true); /** @var Gym $gym */

        $registration = $gym->registrations()
                            ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                            ->find($registration); /** @var MeetRegistration $registration */
        if ($registration == null)
            throw new CustomBaseException("No such registration", -1);

        $meet = $registration->meet; /** @var Meet $meet */
        $disable_edit = 0;
        foreach ($registration->transactions as $key => $value) {
            if($value['is_deposit'] == true && $value['is_deposit_sattle'] == false && $value['status'] == 1)
            {
                $disable_edit = 1;
                break;
            }
        }
        return view('registration.details', [
            'current_page' => 'gym-' . $gym->id,
            'meet' => $meet,
            'gym' => $gym,
            'registration' => $registration,
            'disable_edit' => $disable_edit
        ]);
    }

    public function pay(Request $request, string $gym, string $registration,
        string $transaction) {
        try {
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

            $registration = $gym->registrations()
                                ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $transaction = $registration->transactions()
                                        ->whereIn('status', [
                                            MeetTransaction::STATUS_WAITLIST_CONFIRMED,
                                            MeetTransaction::STATUS_FAILED,
                                            MeetTransaction::STATUS_CANCELED,
                                        ])->find($transaction); /** @var MeetTransaction $transaction */
            if ($transaction == null)
                throw new CustomBaseException("No such transaction", -1);

            $waitlist = ($transaction->status == MeetTransaction::STATUS_WAITLIST_CONFIRMED);

            $subtotal = 0;
            if (!$waitlist) {
                if ($transaction->was_replaced)
                    throw new CustomBaseException("Invalid transaction status.", -1);
            }
            if($waitlist)
            {
                $snapshot = $transaction->chargeWaitlistedTransaction(true);
                $subtotal = $transaction->calculateWaitlistTotal($snapshot)['subtotal'];
            }
            else{
                $snapshot = $transaction->reapplyFees(true);
                $subtotal = $transaction->calculatedTotal($snapshot)['subtotal'];
            }

        } catch (CustomBaseException $e) {
            return redirect(route('meets.browse'))->with('error', $e->getMessage());
        }

        return view('registration.repay', [
            'current_page' => 'gym-' . $gym->id,
            'gym' => $gym,
            'meet' => $registration->meet,
            'registration' => $registration,
            'transaction' => $transaction,
            'waitlist' => $waitlist,
            'subtotal' => $subtotal,
        ]);
    }

    public function edit(Request $request, string $gym, string $registration) {
        $gym = $request->_managed_account->retrieveGym($gym, true); /** @var Gym $gym */

        $registration = $gym->registrations()
                            ->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                            ->find($registration); /** @var MeetRegistration $registration */
        if ($registration == null)
            throw new CustomBaseException("No such registration", -1);

        if (!$registration->canBeEdited())
            throw new CustomBaseException("You cannot edit this registration", -1);

        $required_sanctions = [];
        foreach (SanctioningBody::all() as $body) /** @var SanctioningBody $body */
            $required_sanctions[$body->id] = LevelCategory::requiresSanction($body->id);

        $required_sanctions = json_encode($required_sanctions);
        if ($required_sanctions === false)
            throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);

        $previous_deposit_remaining_total = 0;
        foreach ($registration->transactions as $key => $value) {
            if($value['is_deposit'] == true && $value['is_deposit_sattle'] == false && $value['status'] == MeetTransaction::STATUS_COMPLETED)
            {
                $previous_deposit_remaining = $value['breakdown'];
                $previous_deposit_remaining_total = $previous_deposit_remaining['gym']['subtotal'] - $previous_deposit_remaining['gym']['deposit_subtotal'];
                break;
            }
        }
        $previous_registration_credit_amount = 0;
        $previous_registration_credit = MeetCredit::where('meet_registration_id',$registration->id)
                                                    ->where('gym_id', $gym->id)
                                                    ->where('meet_id', $registration->meet->id)->first();
                
        if($previous_registration_credit != null && $previous_registration_credit->count() > 0)
        {
            $previous_registration_credit_amount = $previous_registration_credit->credit_amount - $previous_registration_credit->used_credit_amount;
        }
        // $registration->test = "111";
        return view('registration.edit', [
            'current_page' => 'gym-' . $gym->id,
            'meet' => $registration->meet,
            'registration' => $registration,
            'gym' => $gym,
            'required_sanctions' => $required_sanctions,
            'previous_remaining' => $previous_deposit_remaining_total,
            'previous_registration_credit_amount' => $previous_registration_credit_amount
        ]);
    }
    public function test()
    {
        $audit = AuditEvent::get()->last();
        // dd($audit->event_meta);
        $mr = resolve(MeetRegistration::class);
        // $mr->process_audit_event((object)$audit->event_meta);

        $meet = Meet::find(171);
        $gym = Gym::find(213);

        Mail::to($meet->gym->user->email)->send(new RegistrationUpdateMailable(
            $meet,
            $gym,
            $mr->process_audit_event((object)$audit->event_meta)
        ));

    }
    public function reportCreate(Request $request, string $gym, string $registration, string $reportType) {
        try {
//            throw new CustomBaseException('WiP');
            $gym = $request->_managed_account->retrieveGym($gym); /** @var Gym $gym */

            $registration = $gym->registrations()
                                ->where('status', MeetRegistration::STATUS_REGISTERED)
                                ->find($registration); /** @var MeetRegistration $registration */
            if ($registration == null)
                throw new CustomBaseException("No such registration", -1);

            $meet = $registration->meet;  /** @var Meet $meet */

            $pdf = null;
            $name = Str::slug($meet->name, '_') . '_' . $reportType . '.pdf';
            switch ($reportType) {
                case Meet::REPORT_TYPE_SUMMARY:
                    $pdf = $meet->generateSummaryReport($gym)->setPaper('a4', 'landscape')
                        ->setOption('margin-top', '38mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('header-html', view('PDF.host.meet.reports.header_footer.meet_summery_header',['meet' => $meet])->render())
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_ENTRY_NOT_ATHLETE:
                    $notAthlete = true;
                    $pdf = $meet->generateEntryReport($gym)->setPaper('a4', 'landscape')
                        ->setOption('margin-top', '40mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('header-html', view('PDF.host.meet.reports.header_footer.team_summary_header',['meet' => $meet])->render())
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());
                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_REGISTRATION_DETAIL:
                    $pdf = $meet->generateRegistrationDetailReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_COACHES:
                    $pdf = $meet->generateGymRegistrationReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;
                case Meet::REPORT_TYPE_USAIGC_COACHES_SIGN_IN:
                    $pdf = $meet->generateUSAIGCCoachSignInReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_SPECIALISTS:
                    $pdf = $meet->generateEventSpecialistReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_SCRATCH:
                    $pdf = $meet->generateScratchReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_REFUNDS:
                    $pdf = $meet->generateRefundReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                case Meet::REPORT_TYPE_MEETENTRY:
                    $pdf = $meet->generateMeetEntryReport($gym)->setPaper('a4')
                        ->setOption('margin-top', '10mm')
                        ->setOption('margin-bottom', '10mm')
                        ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

                    return $pdf->stream($name);
                    break;

                default:
                    throw new CustomBaseException("Invalid report type.", 1);
            }

            $repsonse = $pdf->download($name); /** @var Response $response */

            return $repsonse;

        } catch(CustomBaseException $e) {
            throw $e;
        } catch(\Throwable $e) {
            Log::warning(self::class . '@reportCreate : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
            throw new CustomBaseException('Something went wrong while generating your report.', Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }
    }
}