<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomBaseException;
use App\Exports\GymsExport;
use App\Exports\SanctionLevelsExport;
use App\Models\Conversation;
use App\Models\Gym;
use App\Models\MeetRegistration;
use App\Models\UserBalanceTransaction;
use App\Models\Meet;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use function foo\func;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\StripeService;
use App\Jobs\UserTicketConfirmationJob;


class DashboardController extends AppBaseController
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $managed = $request->_managed_account;
        /** @var User $managed */
        $showSanctionNotifications = $managed->isCurrentUser() || $managed->pivot->can_manage_gyms;
        $popupnotifications = DB::table('popnotificaitons')
                                ->where('validity', '>=', Carbon::now())
                                ->where('status', 1)
                                ->orderBy('id', 'desc')->get();

        $has_popup = false;
        foreach ($popupnotifications as $popupnotification) {
            if($popupnotification->selected_users == null)
            {
                $has_popup = true;
                DB::table('popnotificaitons')->where('id', $popupnotification->id)
                ->update(
                    ['selected_users' => json_encode(array($managed->id => 1))
                ]);
            }
            else
            {
                $selected_users = json_decode($popupnotification->selected_users, true);
                if(!array_key_exists($managed->id, $selected_users) || $selected_users[$managed->id] == 0)
                {
                    $has_popup = true;
                    $selected_users[$managed->id] = 1;
                    DB::table('popnotificaitons')->where('id', $popupnotification->id)
                    ->update(
                        ['selected_users' => json_encode($selected_users)
                    ]);
                }
            }
        }

        return view('dashboard', [
            '_managed' => $managed,
            'current_page' => 'dashboard',
            'showSanctionNotifications' => $showSanctionNotifications,
            'generalNotifications' => $popupnotifications,
            'has_popup' => $has_popup ? 'true' : 'false'
        ]);
    }

    public function browseMeets()
    {
        return view('browse', [
            'current_page' => 'browse-meets'
        ]);
    }
    public function privacyPolicy() {
        // echo "Hello";
        return view('auth.privacy_policy');
    }

    /**
     * @throws CustomBaseException
     */
    public function sanctionLevelExportExcel(Request $request)
    {
//        $this->authenticate($request);
        return Excel::download(new SanctionLevelsExport(), 'sanctionLevels-'.time().'.xlsx');
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function gymExportExcel(Request $request)
    {
        return Excel::download(new GymsExport(), 'gyms-'.time().'.xlsx');
    }
    public function generateTicket(Request $request)
    {
        $meet = Meet::find($request->meetId);
        $meet_admissions = $meet->admissions()->get();
        $meet_admissions = $meet_admissions->sortBy('amount', SORT_REGULAR, true);
        // $gyms = Gym::all()->pluck('name', 'id');
        // registered gyms
        $gyms = $meet->registrations()->get()->pluck('gym_id')->toArray();
        $gyms = Gym::whereIn('id', $gyms)->get()->pluck('name', 'id');
        // dd($gyms);
        return View('ticket', [
            'current_page' => 'ticket',
            'meet' => $meet,
            'meet_admissions' => $meet_admissions,
            'processing_fee' => $meet->cc_fee(),
            'handling_fee' => $meet->handling_fee(),
            'gyms' => $gyms
        ]);
    }
    public function buyTicket(Request $request)
    {
        // dd($request->all());
        $meet = Meet::find($request->meet_id);
        $meet_admissions = $meet->admissions()->get();

        $meet_tickets = json_decode($request->tickets, true);
        $total_from_form = $request->total;
        $stripe_token = $request->token;

        // TODO: insert into DB and send email to user and meet host. Send the money to meet host account
        // charge CC and handling fees.
        $user_name = $request->name;
        $user_email = $request->email;
        $user_phone = $request->phone;
        $user_gym = $request->gym;
        
        $meet_tickets_key_pairs = [];
        foreach ($meet_tickets as $key => $value) {
            $meet_tickets_key_pairs[$value['id']] = $value['count'];
        }
        $calculated_total = 0;
        foreach ($meet_admissions as $key => $value) {
            if(array_key_exists($value->id, $meet_tickets_key_pairs))
            {
                $calculated_total += $value->amount * $meet_tickets_key_pairs[$value->id];
            }
        }
        // Add the calculated total to the HOST allgym balance - fees will be adjusted to the allgym owner.
        $total_amount_before_fees = $calculated_total;

        $fees = ($meet->cc_fee() + $meet->handling_fee() ) / 100;
        $calculated_total += $calculated_total * $fees;
        $calculated_total = round($calculated_total, 2);
        $total_from_form = round($total_from_form, 2);
        if($calculated_total != $total_from_form)
        {
            return redirect(route('ticket.generate', $request->meet_id))->with('error', 'Total amount is not correct '. $calculated_total . ' != ' . $total_from_form);
            exit();
        }
        $meta_data = [
            'meet_id' => $meet->id,
            'meet_name' => $meet->name,
            'meet_tickets' => json_encode($meet_tickets_key_pairs)
        ];
        // 8 digit ticket id
        $ticket_id = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $data = [
            'host_user_id' => $meet->gym->user_id,
            'ticket_id' => $ticket_id,
            'meet_id' => $meet->id,
            'customer_name' => $user_name,
            'customer_email' => $user_email,
            'customer_phone' => $user_phone,
            'customer_gym' => $user_gym,
            'tickets' => json_encode($meet_tickets_key_pairs),
            'total_amount' => $total_amount_before_fees,
            'handling_fee' => round($total_amount_before_fees * ($meet->handling_fee() / 100) ,4),
            'processor_fee' => round($total_amount_before_fees * ($meet->cc_fee() / 100) ,4),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        
        try{
            if($calculated_total > 0.5)
            {
                $card_charge = StripeService::createOneTimeCardCharge($stripe_token, $calculated_total, 'USD', 'Meet Ticket Purchase for '. $meet->name, $meta_data);
            }
            else
            {
                $card_charge = (object) ['id' => 'free'];
            }
            DB::table('host_tickets')->insert($data);
            // get the id of the last inserted ticket
            $ticket_db_id = DB::getPdo()->lastInsertId();
            //TODO:: Add the money to the host account
            
            $user_model = User::find($meet->gym->user_id);
            $user_model->pending_balance += $total_amount_before_fees;
            $user_model->save();
            
            $user_balance_transaction_data = [
                'user_id' => $meet->gym->user_id,
                'related_id' => $ticket_db_id,
                'related_type' => 'host_tickets',
                'processor_id' => $card_charge->id,
                'total' => $total_amount_before_fees,
                'description' => "Meet Ticket Purchase for ". $meet->name,
                'clears_on' =>  Carbon::now()->addDays(1)->toDateTimeString(),
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_TICKET,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            DB::table('user_balance_transactions')->insert($user_balance_transaction_data);


            UserTicketConfirmationJob::dispatch($user_email, $meet, $user_name, $user_phone, $meet_tickets_key_pairs, $ticket_id);
            
            
            return redirect(route('ticket.generate', $request->meet_id))->with('success', 'Payment Successful');
            exit();
        }
        catch(Exception $e)
        {
            return redirect(route('ticket.generate', $request->meet_id))->with('error', $e->getMessage());
            exit();
        }
        // 4242424242424242
    }
}
