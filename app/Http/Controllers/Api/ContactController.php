<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactForm;
use App\Mail\ContactReply;
use Illuminate\Support\Facades\Auth;

class ContactController extends BaseApiController
{
    public function index()
    {
        $apiGuard = Auth::guard('api');
        $rules = [
            'message' => ['required', 'string']        
        ];

        if (!$apiGuard->check())
            $rules['email'] = ['required', 'string', 'email', 'max:750'];

        $attr = request()->validate($rules);

        if ($apiGuard->check())
        {
            $attr['email'] = $apiGuard->user()->email;
            $attr['name'] = $apiGuard->user()->fullName();
        }
        Mail::to('support@allgymnastics.com')->send(new ContactForm($attr['email'], $attr['message']));
        Mail::to($attr['email'])->send(new ContactReply($attr['name']));
    }
}
