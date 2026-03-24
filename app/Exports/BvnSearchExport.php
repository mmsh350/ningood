<?php

namespace App\Exports;

use App\Models\BvnPhoneSearch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BvnSearchExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $records = BvnPhoneSearch::select('refno', 'phone_number', 'name')
            ->where('status', 'pending')->get();

        $data = [];
        $sn = 1;

        foreach ($records as $record) {
            $data[] = [
                'sn' => $sn++,
                'refno' => $record->refno,
                'phone_number' => $record->phone_number,
                'name' => $record->name,
            ];
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        return ['S/N', 'Reference Number', 'Phone Number', 'Full Name'];
    }
}
