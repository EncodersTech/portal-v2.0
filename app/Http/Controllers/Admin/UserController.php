<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminUsersExport;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Models\Meet;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use App\Queries\UserDataTable;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use App\Services\StripeService;
use App\Services\DwollaService;
use App\Services\DwollaScheduleWithdrawal;
use App\Mail\Registrant\NotifyMailCheckMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
class UserController extends AppBaseController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function customUsers()
    {
        $transactions = UserBalanceTransaction::selectRaw('user_id, sum(total) as totalsum')->groupBy('user_id')->get();
        echo '<table border="1">';
        foreach($transactions as $transaction)
        {
            $user = User::where('id',$transaction->user_id)->first();
            echo '
                <tr>
                    <td>'.$user->fullName().'</td>
                    <td>'.$transaction->totalsum.'</td>
                    <td>'.$user->cleared_balance.'</td>
                    <td>'.($user->cleared_balance - $transaction->totalsum).'</td>
                </tr>';
            }
        echo '</table>';
    }
    public function customUsersscscs()
    {
        echo 'hi';

        
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new UserDataTable())->get())->make(true);
        }

        return view('admin.users_list.index');
    }

    public function edit(User $user)
    {
        return view('admin.users_list.edit',compact('user'));
    }

    public function update(UpdateAdminUserRequest $request, User $user)
    {
        $error = false;
        $input = $request->all();
        $ur = User::where('id',$input['user_id'])->first();
        if(isset($input['isGatewayOn']) && $input['isGatewayOn'] == 'on')
        {
            if((strpos($ur['stripe_customer_id'], 'fake_stripe_') !== FALSE && strpos($ur['dwolla_customer_id'], 'fake_dwolla_') !== FALSE)
                || ($ur['stripe_customer_id'] == null && $ur['dwolla_customer_id'] == null))
            {
                try{
                    $input['stripe_customer_id'] = StripeService::createCustomer(
                        $user->fullName(),
                        $user->email,
                        config('app.name') . ' | ' . $ur->fullName()
                    )->id;
                    $input['dwolla_customer_id'] = resolve(DwollaService::class)->createCustomer(
                            $user->first_name,
                            $user->last_name,
                            $user->email
                        )->id;
                    User::where('id',$input['user_id'])->update([
                        'stripe_customer_id'=>$input['stripe_customer_id'],
                        'dwolla_customer_id'=>$input['dwolla_customer_id']
                    ]);
                }
                catch(Exception $e)
                {                    
                    $error = true;
                }
                
            }
        }
        
        if(isset($input['email_verified_at']) && !$input['email_verified_at']) // unverify
        {
            $input['email_verified_at'] = null;
        }
        else
        {
            if($ur->email_verified_at == null)
            {
                $input['email_verified_at'] = now();
            }
            else
                unset($input['email_verified_at']);
        }
        // print_r($input); die();
        if ($user->update($input) && $error == false)
            return back()->with('success', 'User profile was updated.');
        else
            return back()->with('error', 'There was an error while updating your profile');
    }

    public function senResetEmail(Request $request)
    {
        $input = $request->all();
        $user = User::find($input['userId']);
        $token = app(PasswordBroker::class)->createToken($user);
        $user->sendPasswordResetNotification($token);

        return $this->sendSuccess('Reset password email has been sent successfully.');
    }

    /**
     * @param  User  $user
     *
     * @return JsonResponse
     */
    public function activeDeactivateUser(User $user)
    {
        if ($user->id == \Auth::id()) {
            return $this->sendError('Login user can\'t De-active itself.', 404);
        }

        /** @var User $user */
        $user = User::findOrFail($user->id);
        if (!$user) {
            return $this->sendError('User not found', 404);
        }

        $user->is_disabled = !$user->is_disabled;
        $user->deactivate_at = (isset($user->deactivate_at) && !empty($user->deactivate_at) ? null : Carbon::now());
        $user->save();

        return $this->sendSuccess('User status updated successfully.');
    }

    public function userExportExcel()
    {
        return Excel::download(new AdminUsersExport(), 'adminUsers-'.time().'.xlsx');
    }

    public function userExportPDF()
    {
        $pdf = $this->userRepository->generateAdminUserReport()->setPaper('a4', 'landscape')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-html', view('PDF.host.meet.reports.header_footer.common_footer')->render());

        return $pdf->stream('adminUsers.'.time().'.pdf');
    }

    public function userWithdrawalFreeze(User $user)
    {
        /** @var User $user */
        $user = User::findOrFail($user->id);
        if (!$user) {
            return $this->sendError('User not found', 404);
        }

        $user->withdrawal_freeze = !$user->withdrawal_freeze;
        $user->save();

        return $this->sendSuccess('User Withdrawal option updated successfully.');
    }

    public function userMailCheckOption(User $user, Request $request)
    {
        /** @var User $user */
        $user = User::findOrFail($user->id);
        if (!$user) {
            return $this->sendError('User not found', 404);
        }

        $input = $request->all();
        if($input['check_box_value']){
            $gymIds = $user->gyms()->pluck('id');
            $meets = Meet::whereIn('gym_id',$gymIds)->update(['accept_mailed_check'=>false]);
        }

        $user->mail_check_disable = !$user->mail_check_disable;
        $user->save();

        return $this->sendSuccess('User Mail-Check option updated successfully.');
    }

    /**
     * @param  User  $user
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function userWithdrawalMoney(User $user, Request $request): JsonResponse
    {
        try {
            if (empty($request->get('total'))) {
                return $this->sendError('The Amount field is required.');
            }
            $user = User::findOrFail($user->id);
            if (!$user) {
                return $this->sendError('User not found', 404);
            }

            $input = $request->all();

            if ($input['total'] > $user->cleared_balance) {
                return $this->sendError('You do not have enough balance to withdraw that amount.');
            }
            if (empty($input['clears_on'])) {
                $input['clears_on'] = now();
            }
            if (empty($input['description'])) {
                $input['description'] = 'Balance withdrawal $' . number_format($input['total'], 2);
            }
            DB::beginTransaction();
            $user->balance_transactions()->create([
                'processor_id' => null,
                'total' => -$input['total'],
                'description' => $input['description'],
                'clears_on' => $input['clears_on'],
                'created_at' => $input['clears_on'],
                'updated_at' => $input['clears_on'],
                'type' => UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_WITHDRAWAL,
                'status' => UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED
            ]);

            $user->cleared_balance -= $input['total'];
            $user->save();
            DB::commit();

            return $this->sendSuccess('Change Withdrawal Money successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }
    }
}