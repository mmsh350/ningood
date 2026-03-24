<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IpeTemplateExport implements FromCollection, WithHeadings
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
                'trackingId' => $item->trackingId,
                'resp_code' => $item->resp_code,
                'reply' => $item->reply,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'tracking_id',
            'resp_code',
            'reply',
        ];
    }
}
