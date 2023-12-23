<?php

namespace App\Exports;

use App\Models\AthleteLevel;
use App\Models\Gym;
use App\Models\LevelCategory;
use App\Models\SanctioningBody;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Class SanctionLevelsExport
 */
class SanctionLevelsExport implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
    public function view(): View
    {
        $bodies = SanctioningBody::all();
        $categories = LevelCategory::all();
        $levels = AthleteLevel::orderBy('created_at', 'ASC')->get();

        $bodies = SanctioningBody::SANCTION_BODY;
        $added = [];
        $result = [];
        foreach ($levels as $level) {
            $key = $level->code.'_'.$level->sanctioning_body_id;
            if(!empty($level->code) && !property_exists($key, $added)) {
                $added[$key] = $key;

                $body = isset($bodies[$level->sanctioning_body_id]) ? $bodies[$level->sanctioning_body_id] : null;

                if (empty($body)) {
                    continue;
                }

                $data[] = [
                    'body' => $body,
                    'code' => $level->code,
                    'abr' => $level->abbreviation,
                    'level_name' => $level->name
                ];
            } else {
                $body = isset($bodies[$level->sanctioning_body_id]) ? $bodies[$level->sanctioning_body_id] : null;
                
                $data[] = [
                    'body' => $body,
                    'code' => $level->code,
                    'abr' => $level->abbreviation,
                    'level_name' => $level->name
                ];
            }
        }

        return view('admin.exports.sanction_level', ['levels' => $data]);


        $result = [];
        $data = [];
        $tmp = [];
        foreach ($bodies as $body) {
            $tmp[$body->id] = $body;
        }
        $bodies = $tmp;

        $tmp = [];
        foreach ($categories as $category) {
            $tmp[$category->id] = $category;
        }
        $categories = $tmp;

        foreach ($levels as $level) {
            /** @var AthleteLevel $level */
            $body = $bodies[$level->sanctioning_body_id];
            $category = $categories[$level->level_category_id];

            $result[$body->initialism][$category->name][] = $level;

        }

        foreach($result as $sanction => $categories) {
            foreach ($categories as $category => $levels) {
                foreach ($levels as $level) {
//                    if(empty($level->abbreviation)) {
//                        continue;
//                    }

                    $data[] = [
                        'sanction' => $sanction,
                        'sanction_level_name' => $level->name,
                        'abbreviation' => $level->abbreviation
                    ];
                }
            }
        }

        return view('admin.exports.sanction_level', ['levels' => $data]);
    }

    public function title(): string
    {
        return 'Sanction Levels';
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