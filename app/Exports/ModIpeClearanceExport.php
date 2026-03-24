<?php

namespace App\Exports;

use App\Models\ModIpeClearance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ModIpeClearanceExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $records = ModIpeClearance::select(
            'refno',
            'nin_number',
            'tracking_id',
        )->where('status', 'pending')->get();

        $data = [];
        $sn = 1;

        foreach ($records as $record) {
            $data[] = [
                'sn' => $sn++,
                'refno' => $record->refno,
                'nin_number' => $record->nin_number,
                'tracking_id' => $record->tracking_id,
            ];
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Reference Number',
            'NIN Number',
            'Tracking ID',
        ];
    }
}
