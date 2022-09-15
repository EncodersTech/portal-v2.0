<?php

namespace App\Repositories;

use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserRepository
 */
class UserRepository
{
    public function generateAdminUserReport(): PdfWrapper
    {
        $userLists = User::where('id', '!=', Auth::id())->where('is_disabled',false)->whereNotNull('email_verified_at')->orderBy('is_disabled', 'ASC')->get();

        $data = [
            'userLists' => $userLists,
        ];

        return PDF::loadView('admin.users_list.PDF.user_lists', $data); /** @var PdfWrapper $pdf */
    }
}
