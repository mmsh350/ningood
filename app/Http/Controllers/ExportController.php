<?php

namespace App\Http\Controllers;

use App\Exports\BvnSearchExport;
use App\Exports\ModIpeClearanceExport;
use App\Exports\NINServiceExport;
use App\Exports\NINValidationTemplateExport;
use App\Models\NinValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportBvnSearh()
    {
        return Excel::download(new BvnSearchExport, 'bvn-search.xlsx');
    }

    public function exportNINService()
    {
        return Excel::download(new NINServiceExport, 'nin-service.xlsx');
    }

    public function exportModIpe()
    {
        return Excel::download(new ModIpeClearanceExport, 'mod_ipe_clearance.xlsx');
    }

    public function downloadTemplateValidation()
    {
        $records = NinValidation::whereIn('resp_code', ['100', '101'])
            ->whereNull('tag')
            ->select('id', 'nin_number', 'resp_code', 'reason')
            ->get();

        if ($records->isEmpty()) {
            return back()->with('error', 'No pending records to export.');
        }

        $ids = $records->pluck('id')->toArray();

        NinValidation::whereIn('id', $ids)
            ->update(['resp_code' => '101']);

        return Excel::download(
            new NINValidationTemplateExport($records),
            'validation_requests_pending_'.now()->format('Y_m_d_His').'.xlsx'
        );
    }

    public function uploadExcelForNINValidation(Request $request)
    {
        try {
            // Validate uploaded file
            $validator = Validator::make($request->all(), [
                'excel_file' => 'required|file|mimes:xlsx,xls',
            ]);

            if ($validator->fails()) {
                return back()->with('error', 'The file field is required and must be an Excel file.');
            }

            $data = Excel::toArray([], $request->file('excel_file'))[0];

            if (count($data) < 2) {
                return back()->with('error', 'The uploaded file is empty or has no valid data.');
            }

            $header = array_map('strtolower', $data[0]);

            if (! in_array('nin_number', $header) || ! in_array('resp_code', $header) || ! in_array('reason', $header)) {
                return back()->with('error', 'Invalid file format. Required headers: nin_number, resp_code, reason.');
            }

            $successCount = 0;
            $failedRows = [];

            // Process each row
            for ($i = 1; $i < count($data); $i++) {
                $row = array_combine($header, $data[$i]);

                $trackingId = trim($row['nin_number'] ?? '');
                $respCode = trim((string) ($row['resp_code'] ?? ''));
                $reply = trim($row['reason'] ?? '');

                $rowNumber = $i + 1;

                // Validation
                if (! $trackingId || ! $respCode || ! $reply) {
                    $failedRows[] = "Row $rowNumber: Missing nin_number, resp_code or reason.";

                    continue;
                }

                if (! in_array($respCode, ['200', '400'])) {
                    $failedRows[] = "Row $rowNumber: Invalid resp_code '$respCode'. Only 200 and 400 are allowed.";

                    continue;
                }

                $respCode == '200' ? $st = 'Successful' : $st = 'Failed';

                // Perform update
                $updated = NinValidation::where('nin_number', $trackingId)
                    ->whereNull('tag')
                    ->where('resp_code', '101')
                    ->update([
                        'resp_code' => $respCode,
                        'reason' => $reply,
                        'status' => $st,
                        'updated_at' => Carbon::now(),
                    ]);

                if ($updated) {
                    $successCount++;
                } else {
                    $failedRows[] = "Row $rowNumber: NIN Number '$trackingId' not found in the database.";
                }
            }

            // Prepare response message
            $message = "$successCount rows updated successfully.";
            if (count($failedRows)) {
                $message .= ' Some rows failed: <br><ul>';
                foreach ($failedRows as $error) {
                    $message .= "<li>$error</li>";
                }
                $message .= '</ul>';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Excel upload error: '.$e->getMessage());

            return back()->with('error', 'An error occurred while processing the file: '.$e->getMessage());
        }
    }
}
