<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Queries\GymBalanceReportDataTable;
use App\Repositories\GymBalanceReportRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class GymBalanceReportController extends AppBaseController
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new GymBalanceReportDataTable())->get())->make(true);
        }

        return view('admin.reports.gym_balance.index');
    }
}
