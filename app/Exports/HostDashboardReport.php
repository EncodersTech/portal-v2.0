<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;

class HostDashboardReport implements FromView, WithTitle, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $user = resolve(User::class);
        $data = $user->hostDashboardData();
        return view('host_dashboard.exports.summary', $data);
    }
    public function title(): string
    {
        return 'Host Dashboard Report';
    }
}
