<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NINValidationTemplateExport implements FromCollection, WithHeadings
{
    protected $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records->map(function ($item) {
            return collect([
                'nin_number' => $item->nin_number,
                'resp_code' => $item->resp_code,
                'reason' => $item->reason,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'nin_number',
            'resp_code',
            'reason',
        ];
    }
}
