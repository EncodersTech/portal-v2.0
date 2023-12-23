<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CsvExport implements FromCollection, WithHeadings
{
    private $data;

    public function __construct($data)
    {
        $this->headings = $data['headings'];
        $this->data = $data['data'];
    }

    public function headings(): array {
        return $this->headings;
    }
    
      /**
      * @return \Illuminate\Support\Collection
      */
    public function collection() {
        return collect($this->data);
    }
}
