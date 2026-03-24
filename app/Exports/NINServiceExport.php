<?php

namespace App\Exports;

use App\Models\NinModification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NINServiceExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $records = NinModification::where('status', 'pending')->get();

        $data = [];
        $sn = 1;

        foreach ($records as $record) {
            $data[] = [
                'sn' => $sn++,
                'refno' => $record->refno,
                'nin_number' => $record->nin_number,
                'first_name' => $record->first_name,
                'middle_name' => $record->middle_name,
                'surname' => $record->surname,
                'dob' => $record->dob,
                'phone_number' => $record->phone_number,
                'address' => $record->address,
                'status' => $record->status,
                'description' => $record->description,
                'reason' => $record->reason,
                'origin_address' => $record->origin_address,
                'full_address' => $record->full_address,
                'state' => $record->state,
                'lga' => $record->lga,
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
            'First Name',
            'Middle Name',
            'Surname',
            'Date of Birth',
            'Phone Number',
            'Address',
            'Status',
            'Description',
            'Reason',
            'Origin Address',
            'Full Address',
            'State',
            'LGA',
        ];
    }
}
