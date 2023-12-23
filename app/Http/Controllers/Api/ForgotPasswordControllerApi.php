<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Auth;
use \App\Models\User;

class ForgotPasswordControllerApi extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    public function changedTheName(Request $request) {
        // die(print_r($request->input('email')));
        if($request->input('email')) {
            $user = User::where('email',$request->input('email'))->first();
            if($user)
            {
                $this->sendResetLinkEmail($request);
                return json_encode(array(
                    'error' => false,
                    'status_code' => 200,
                    'response' => 'Reset email sent successfully',
                    'email' => $request->input('email')
                ));
            }
            else
            {
                return json_encode(array(
                    'error' => true,
                    'status_code' => 400,
                    'response' => 'User not found',
                    'email' => $request->input('email')
                ));
            }
        }

        /* Return Success Response */
        
    }
}