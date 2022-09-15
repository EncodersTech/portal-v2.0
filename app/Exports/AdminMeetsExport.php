<?php

namespace App\Exports;

use App\Models\Meet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class AdminMeetsExport implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
    public function view(): View
    {
        $meetLists = Meet::with(['gym.user','levels','meetCategories','venue_state'])->get();

        return view('admin.exports.admin_meets', ['meetLists' => $meetLists]);
    }

    public function title(): string
    {
        return 'Meets';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:W1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }
}
