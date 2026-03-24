<?php

namespace App\Http\Repositories;

use App\Models\Verification;
use Illuminate\Support\Facades\Log;
use TCPDF;
use TCPDF_FONTS;

class NIN_PDF_Repository
{
    public function regularPDF($nin_no)
    {
        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)
                ->latest()
                ->first();

            $mNameCleaned = str_replace('*', '', $verifiedRecord->middle_name);

            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $mNameCleaned,
                'tId' => $verifiedRecord->trackingId,
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo),
            ];

            $names = $verifiedRecord->first_name . ' ' . $verifiedRecord->last_name;

            // Initialize TCPDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);

            // Set document information
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle(html_entity_decode($names));
            $pdf->SetSubject('Regular');
            $pdf->SetKeywords('Regular, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Register the custom OCR font
            $fontPath = public_path('fonts/ocrb10pitchbt_regular.ttf');
            $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);

            if (! $fontname) {
                // Fallback to helvetica if font registration fails
                $fontname = 'helvetica';
            }

            // Add a new page
            $pdf->AddPage();

            // Load the background image
            $pdf->Image('assets/card_and_Slip/regular.png', 15, 50, 178, 80, '', '', '', false, 300, '', false, false, 0);

            // Decode and add the photo
            $photo = $ninData['photo'];
            $imgdata = base64_decode($photo);
            $pdf->Image('@' . $imgdata, 166.8, 69.3, 25, 31, '', '', '', false, 300, '', false, false, 0);

            // Add text fields using custom OCR font
            $pdf->SetFont($fontname, '', 9);
            $pdf->Text(85, 71, html_entity_decode($ninData['sName']));
            $pdf->Text(85, 79.7, html_entity_decode($ninData['fName']));
            $pdf->Text(85, 86.8, html_entity_decode($ninData['mName']));

            $pdf->SetFont($fontname, '', 8);
            $pdf->Text(85, 96, $ninData['gender']);

            $pdf->SetFont($fontname, '', 7);
            $pdf->Text(32, 71.8, $ninData['tId']);

            $pdf->SetFont($fontname, '', 8);
            $pdf->Text(25, 79.5, $ninData['nin']);

            $pdf->SetFont($fontname, '', 9);
            $pdf->MultiCell(50, 20, html_entity_decode($ninData['address']), 0, 'L', false, 1, 116, 74, true);

            $pdf->SetFont($fontname, '', 8);
            $pdf->Text(116, 93, $ninData['lga']);
            $pdf->Text(116, 97, $ninData['state']);

            // Output the PDF
            $filename = 'Regular NIN Slip - ' . $nin_no . '.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename=' . $filename)
                ->header('Content-Length', strlen($pdfContent));
        } else {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found !'],
            ], 422);
        }
    }

    public function standardPDF($nin_no)
    {
        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)
                ->latest()
                ->first();

            $mNameCleaned = str_replace('*', '', $verifiedRecord->middle_name);

            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $mNameCleaned,
                'tId' => $verifiedRecord->trackingId,
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo),
            ];

            $names = $verifiedRecord->first_name . ' ' . $verifiedRecord->last_name;

            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle(html_entity_decode($names));
            $pdf->SetSubject('Standard');
            $pdf->SetKeywords('Standard, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $fontPath = public_path('fonts/ocrb10pitchbt_regular.ttf');
            $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);
            if (! $fontname) {
                $fontname = 'helvetica';
            }

            $pdf->AddPage();

            // Center A4 width
            $pageWidth = 210;
            $scaledWidth = 104; // 80 * 1.3
            $centerX = ($pageWidth - $scaledWidth) / 2;

            // Instruction text
            $pdf->SetFont($fontname, '', 15.6); // 12 * 1.3
            $instruction = "Please find below your new High Resolution NIN Slip. You may cut it out of the paper, fold and laminate as desired. Please DO NOT allow others to make copies of your NIN Slip.\n";
            $pdf->MultiCell(195, 26, $instruction, 0, 'C', false, 1, 7.5, 26);

            // Card images
            $pdf->Image('assets/card_and_Slip/standard.jpg', $centerX, 65, 104, 65);
            $pdf->Image('assets/card_and_Slip/back.jpg', $centerX, 131.3, 104, 65);

            // QR Code
            $style = [
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => [255, 255, 255],
            ];
            $datas = '{NIN: ' . $ninData['nin'] . ', NAME:' . html_entity_decode($ninData['fName']) . ' ' . html_entity_decode($ninData['mName']) . ' ' . html_entity_decode($ninData['sName']) . ', DOB: ' . $ninData['dob'] . ', Status:Verified}';
            $pdf->write2DBarcode($datas, 'QRCODE,H', $centerX + 80.2, 84.11, 18.46, 17.55);
            $pdf->Image('assets/card_and_Slip/pin.jpg', $centerX + 66, 90.35, 5.85, 5.85);

            // Photo
            $photo = base64_decode($ninData['photo']);
            $pdf->Image('@' . $photo, $centerX + 2.6, 80.6, 23.4, 29.9);

            // Text fields
            $pdf->SetFont($fontname, '', 10.4); // 8 * 1.3
            $pdf->Text($centerX + 28, 84.5, html_entity_decode($ninData['sName']));
            $pdf->Text($centerX + 28, 93.6, html_entity_decode($ninData['fName']) . ', ' . html_entity_decode($ninData['mName']));

            $formattedDob = date('d M Y', strtotime($ninData['dob']));
            $pdf->Text($centerX + 28, 102.3, $formattedDob);

            $issueDate = date('d M Y');
            $pdf->Text($centerX + 76, 104, $issueDate);

            // NIN number
            $formattedNin = substr($ninData['nin'], 0, 4) . ' ' . substr($ninData['nin'], 4, 3) . ' ' . substr($ninData['nin'], 7);
            $pdf->SetFont($fontname, '', 27.3); // 21 * 1.3
            $pdf->Text($centerX + 14.3, 115.7, $formattedNin);

            // Watermark
            $pdf->StartTransform();
            $pdf->Rotate(50, $centerX + 23.4, 123.5);
            $pdf->setTextColor(220, 220, 220);
            $pdf->SetFont($fontname, '', 9.1); // 7 * 1.3
            $pdf->Text($centerX + 14, 104, $ninData['nin']);
            $pdf->StopTransform();

            // Output
            $filename = 'Standard NIN Slip - ' . $nin_no . '.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename=' . $filename);
        } else {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found!'],
            ], 422);
        }
    }

    // public function premiumPDF($nin_no)
    // {
    //     // Check if record exists and retrieve the latest record
    //     if (Verification::where('idno', $nin_no)->exists()) {
    //         $verifiedRecord = Verification::where('idno', $nin_no)
    //             ->latest()
    //             ->first();

    //         // Prepare data for the PDF
    //         $ninData = [
    //             "nin" => $verifiedRecord->idno,
    //             "fName" => $verifiedRecord->first_name,
    //             "sName" => $verifiedRecord->last_name,
    //             "mName" => $verifiedRecord->middle_name,
    //             "tId" => $verifiedRecord->trackingId,
    //             "address" => $verifiedRecord->address,
    //             "lga" => $verifiedRecord->lga,
    //             "state" => $verifiedRecord->state,
    //             "gender" => ($verifiedRecord->gender === 'Male') ? "M" : "F",
    //             "dob" => $verifiedRecord->dob,
    //             "photo" => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo)
    //         ];

    //         $names = html_entity_decode($verifiedRecord->first_name) . ' ' . html_entity_decode($verifiedRecord->last_name);

    //         // Initialize TCPDF
    //         $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    //         $pdf->setPrintHeader(false);
    //         $pdf->SetCreator('Abu');
    //         $pdf->SetAuthor('Zulaiha');
    //         $pdf->SetTitle($names);
    //         $pdf->SetSubject('Premium');
    //         $pdf->SetKeywords('premium, TCPDF, PHP');
    //         $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    //         // Register the custom OCR font
    //         $fontPath = public_path('fonts/ocrb10pitchbt_regular.ttf');
    //         $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);

    //         if (!$fontname) {
    //             // Fallback to helvetica if font registration fails
    //             $fontname = 'helvetica';
    //         }

    //         $pdf->AddPage();

    //         // Use custom font for the main text
    //         $pdf->SetFont($fontname, '', 12);
    //         $txt = "Please find below your new High Resolution NIN Slip...";
    //         $pdf->MultiCell(150, 20, $txt, 0, 'C', false, 1, 35, 20, true, 0, false, true, 0, 'T', false);

    //         // Use JPG images
    //         $pdf->Image('assets/card_and_Slip/premium.jpg', 70, 50, 80, 50, 'JPG', '', '', false, 300, '', false, false, 0);
    //         $pdf->Image('assets/card_and_Slip/back.jpg', 70, 101, 80, 50, 'JPG', '', '', false, 300, '', false, false, 0);

    //         // Add barcode
    //         $style = [
    //             'border' => false,
    //             'padding' => 0,
    //             'fgcolor' => [0, 0, 0],
    //             'bgcolor' => [255, 255, 255]
    //         ];
    //         $datas = '{NIN: ' . $ninData['nin'] . ', NAME: ' . html_entity_decode($ninData['fName']) . ' ' . html_entity_decode($ninData['mName']) . ' ' . html_entity_decode($ninData['sName']) . ', DOB: ' . $ninData['dob'] . ', Status:Verified}';
    //         $pdf->write2DBarcode($datas, 'QRCODE,H', 128, 53, 20, 20, $style, 'H');

    //         // Add image from base64
    //         $photo = $ninData['photo'];
    //         $imgdata = base64_decode($photo);
    //         $pdf->Image('@' . $imgdata, 71.5, 62, 20, 25, 'JPG', '', '', false, 300, '', false, false, 0);

    //         // Add text with custom font
    //         $sur = html_entity_decode($ninData['sName']);
    //         $pdf->SetFont($fontname, '', 9);
    //         $pdf->Text(93.3, 66.5, $sur);

    //         $othername = html_entity_decode($ninData['fName']) . ', ' . html_entity_decode($ninData['mName']);
    //         $pdf->SetFont($fontname, '', 9);
    //         $pdf->Text(93.3, 73.5, $othername);

    //         $dob = $ninData['dob'];
    //         $newD = strtotime($dob);
    //         $cdate = date("d M Y", $newD);
    //         $pdf->SetFont($fontname, '', 8);
    //         $pdf->Text(93.3, 80.5, $cdate);

    //         $gender = $ninData['gender'];
    //         $pdf->SetFont($fontname, '', 9);
    //         $pdf->Text(114, 80.5, $gender);

    //         $issueD = date("d M Y");
    //         $pdf->SetFont($fontname, '', 8);
    //         $pdf->Text(128, 81.8, $issueD);

    //         // Format NIN with custom font
    //         $nin = $ninData['nin'];
    //         $pdf->setTextColor(0, 0, 0);
    //         $newNin = substr($nin, 0, 4) . " " . substr($nin, 4, 3) . " " . substr($nin, 7);
    //         $pdf->SetFont($fontname, '', 21);
    //         $pdf->Text(81, 91, $newNin);

    //         // Watermark with custom font
    //         $pdf->StartTransform();
    //         $pdf->Rotate(50, 88, 95);
    //         $pdf->setTextColor(165, 162, 156);
    //         $pdf->SetFont($fontname, '', 7);
    //         $pdf->Text(80, 80, $nin);
    //         $pdf->StopTransform();

    //         $pdf->StartTransform();
    //         $pdf->Rotate(50, 90, 95);
    //         $pdf->setTextColor(165, 162, 156);
    //         $pdf->SetFont($fontname, '', 7);
    //         $pdf->Text(77, 86, $nin);
    //         $pdf->StopTransform();

    //         $pdf->StartTransform();
    //         $pdf->Rotate(127, 118, 74);
    //         $pdf->setTextColor(165, 162, 156);
    //         $pdf->SetFont($fontname, '', 7);
    //         $pdf->Text(80, 80, $nin);
    //         $pdf->StopTransform();

    //         $pdf->setTextColor(165, 162, 156);
    //         $pdf->SetFont($fontname, '', 7);
    //         $pdf->Text(129, 73, $nin);

    //         // Save and download PDF
    //         $filename = 'Premium NIN Slip - ' . $nin_no . '.pdf';
    //         $pdfContent = $pdf->Output($filename, 'S');

    //         return response($pdfContent, 200)
    //             ->header('Content-Type', 'application/pdf')
    //             ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
    //             ->header('Content-Length', strlen($pdfContent));
    //     } else {
    //         return response()->json([
    //             "message" => "Error",
    //             "errors" => ["Not Found" => "Verification record not found!"]
    //         ], 422);
    //     }
    // }

    public function premiumPDF($nin_no)
    {
        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)->latest()->first();

            $mNameCleaned = str_replace('*', '', $verifiedRecord->middle_name);

            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $mNameCleaned,
                'tId' => $verifiedRecord->trackingId,
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo),
            ];

            $names = html_entity_decode($verifiedRecord->first_name) . ' ' . html_entity_decode($verifiedRecord->last_name);

            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle($names);
            $pdf->SetSubject('Premium');
            $pdf->SetKeywords('premium, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $fontPath = public_path('fonts/ocrb10pitchbt_regular.ttf');
            $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);
            if (! $fontname) {
                $fontname = 'helvetica';
            }

            $pdf->AddPage();

            // === Centering Logic ===
            $scale = 1.3;
            $pageWidth = $pdf->getPageWidth();
            $marginLeft = ($pageWidth - ($scale * 210)) / 2;
            if ($marginLeft < 0) {
                $marginLeft = 0;
            }

            $pdf->SetFont($fontname, '', 12 * $scale);
            $txt = 'Please find below your new High Resolution NIN Slip...';
            $pdf->MultiCell(150 * $scale, 20 * $scale, $txt, 0, 'C', false, 1, $marginLeft + 5, 20 * $scale, true, 0, false, true, 0, 'T', false);

            $pdf->Image('assets/card_and_Slip/premium.jpg', $marginLeft + 40 * $scale, 50 * $scale, 80 * $scale, 50 * $scale, 'JPG', '', '', false, 300);
            $pdf->Image('assets/card_and_Slip/back.jpg', $marginLeft + 40 * $scale, 101 * $scale, 80 * $scale, 50 * $scale, 'JPG', '', '', false, 300);

            $style = [
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => [255, 255, 255],
            ];
            $datas = '{NIN: ' . $ninData['nin'] . ', NAME: ' . html_entity_decode($ninData['fName']) . ' ' . html_entity_decode($ninData['mName']) . ' ' . html_entity_decode($ninData['sName']) . ', DOB: ' . $ninData['dob'] . ', Status:Verified}';
            $pdf->write2DBarcode($datas, 'QRCODE,H', $marginLeft + 98 * $scale, 53 * $scale, 20 * $scale, 20 * $scale, $style, 'H');

            $imgdata = base64_decode($ninData['photo']);
            $pdf->Image('@' . $imgdata, $marginLeft + 41.2 * $scale, 62 * $scale, 20 * $scale, 25 * $scale, 'JPG', '', '', false, 300);

            $pdf->SetFont($fontname, '', 9 * $scale);
            $pdf->Text($marginLeft + 63.8 * $scale, 66.5 * $scale, html_entity_decode($ninData['sName']));

            $othername = html_entity_decode($ninData['fName']) . ', ' . html_entity_decode($ninData['mName']);
            $pdf->Text($marginLeft + 63.8 * $scale, 73.5 * $scale, $othername);

            $cdate = date('d M Y', strtotime($ninData['dob']));
            $pdf->SetFont($fontname, '', 8 * $scale);
            $pdf->Text($marginLeft + 63.8 * $scale, 80.5 * $scale, $cdate);

            $pdf->SetFont($fontname, '', 9 * $scale);
            $pdf->Text($marginLeft + 84.2 * $scale, 80.5 * $scale, $ninData['gender']);

            $pdf->SetFont($fontname, '', 8 * $scale);
            $pdf->Text($marginLeft + 99 * $scale, 81.8 * $scale, date('d M Y'));

            $newNin = substr($ninData['nin'], 0, 4) . ' ' . substr($ninData['nin'], 4, 3) . ' ' . substr($ninData['nin'], 7);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont($fontname, '', 21 * $scale);
            $pdf->Text($marginLeft + 51 * $scale, 91 * $scale, $newNin);

            // Watermarks
            $pdf->SetTextColor(165, 162, 156);
            $pdf->SetFont($fontname, '', 7 * $scale);

            $pdf->StartTransform();
            $pdf->Rotate(50, $marginLeft + 88 * $scale, 95 * $scale);
            $pdf->Text($marginLeft + 60 * $scale, 59 * $scale, $ninData['nin']);
            $pdf->StopTransform();

            $pdf->StartTransform();
            $pdf->Rotate(50, $marginLeft + 90 * $scale, 95 * $scale);
            $pdf->Text($marginLeft + 58 * $scale, 63 * $scale, $ninData['nin']);
            $pdf->StopTransform();

            $pdf->StartTransform();
            $pdf->Rotate(127, $marginLeft + 118 * $scale, 74 * $scale);
            $pdf->Text($marginLeft + 97 * $scale, 58 * $scale, $ninData['nin']);
            $pdf->StopTransform();

            $pdf->Text($marginLeft + 99 * $scale, 73 * $scale, $ninData['nin']);

            // Output
            $filename = 'Premium NIN Slip - ' . $nin_no . '.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));
        } else {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found!'],
            ], 422);
        }
    }

    public function extractBase64AndType($dataUrl)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
            $type = strtoupper($matches[1]); // e.g., JPG, PNG
            $data = substr($dataUrl, strpos($dataUrl, ',') + 1);

            return [$type, $data];
        }

        return [null, $dataUrl]; // fallback
    }

    public function basicPDF($nin_no)
    {
        // Check if record exists and retrieve the latest record
        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)
                ->latest()
                ->first();

            [$photoType, $photoBase64] = $this->extractBase64AndType($verifiedRecord->photo);
            [$signatureType, $signatureBase64] = $this->extractBase64AndType($verifiedRecord->signature);

            $mNameCleaned = str_replace('*', '', $verifiedRecord->middle_name);
            // Prepare data for the PDF
            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $mNameCleaned,
                'tId' => $verifiedRecord->trackingId,
                'phoneno' => str_replace('+234', '0', $verifiedRecord->phoneno),
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'town' => $verifiedRecord->town,
                'residence_lga' => $verifiedRecord->residence_lga,
                'residence_state' => $verifiedRecord->residence_state,
                'residence_town' => $verifiedRecord->residence_town,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => $photoBase64,
                'photo_type' => $photoType,
                'signature' => $signatureBase64,
                'signature_type' => $signatureType,
            ];

            $names = html_entity_decode($verifiedRecord->first_name) . ' ' . html_entity_decode($verifiedRecord->last_name);

            // Initialize TCPDF
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle($names);
            $pdf->SetSubject('Basic');
            $pdf->SetKeywords('basic, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Register the custom OCR font
            $fontPath = public_path('fonts/ocrb10pitchbt_regular.ttf');
            $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);

            if (! $fontname) {
                // Fallback to helvetica if font registration fails
                $fontname = 'helvetica';
            }

            $pdf->AddPage();

            // Use JPG image for background
            $pdf->Image('assets/card_and_Slip/basic.jpg', 20, 25, 250, 163, 'JPG', '', '', false, 300, '', false, false, 0);

            // Add photo from base64
            $photo = $ninData['photo'];
            $imgdata = base64_decode($photo);
            $pdf->Image('@' . $imgdata, 92.1, 67.8, 46.5, 49, $ninData['photo_type'], '', '', false, 300, '', false, false, 0);

            // Add signature from base64
            $signature = base64_decode($ninData['signature']);
            $pdf->Image('@' . $signature, 109, 117.5, 30, 8, $ninData['signature_type'], '', '', false, 300, '', false, false, 0);

            // Format NIN with custom font
            $nin = $ninData['nin'];
            $pdf->setTextColor(90, 90, 90);
            $newNin = substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7);
            $pdf->SetFont($fontname, 'B', 18);
            $pdf->Text(74, 125.7, $newNin);

            // Add text fields with custom font
            $first_name = html_entity_decode($ninData['fName']);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 70, $first_name);

            $midle_name = html_entity_decode($ninData['mName']);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 78.5, $midle_name);

            $pdf->setTextColor(90, 90, 90);
            $sur = html_entity_decode($ninData['sName']);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 92.3, $sur);

            $dob = $ninData['dob'];
            $newD = strtotime($dob);
            $cdate = date('d M Y', $newD);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 102, $cdate);

            $gender = $ninData['gender'];
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 116, $gender);

            $tId = $ninData['tId'];
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 138.5, $tId);

            $phoneno = $ninData['phoneno'];
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(117, 138.5, $phoneno);

            $state = $ninData['state'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 161, $state);

            $lga = $ninData['lga'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(117, 160.9, $lga);

            $residence_state = $ninData['residence_state'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(51, 149.8, $residence_state);

            $residence_lga = $ninData['residence_lga'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(117, 149.8, $residence_lga);

            $town = $ninData['town'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(130, 149.8, $town);

            $address = $ninData['address'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont($fontname, 'B', 10);
            $pdf->Text(47, 171, $address);

            // Save and download PDF
            $filename = 'Basic NIN Slip - ' . $nin_no . '.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));
        } else {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found!'],
            ], 422);
        }
    }

    public function individualSlip($reference)
    {

        Log::info('Generating Individual TIN Slip PDF');
        $verifiedRecord = Verification::where('idno', $reference)->latest()->first();

        $modificationData = $verifiedRecord;

        $tinData = [
            'nin' => $modificationData['nin'] ?? '',
            'fName' => html_entity_decode($modificationData['first_name'] ?? ''),
            'sName' => html_entity_decode($modificationData['last_name'] ?? ''),
            'mName' => html_entity_decode($modificationData['middle_name'] ?? ''),
            'dob' => $modificationData['dob'] ?? '',
            'tax_id' => $modificationData['idno'] ?? '',
            'tax_residency' => $modificationData['state'] ?? '',
        ];

        $names = html_entity_decode($tinData['fName']) . ' ' . html_entity_decode($tinData['sName']);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->setPrintHeader(false);
        $pdf->SetCreator('Abu');
        $pdf->SetAuthor('Zulaiha');
        $pdf->SetTitle($names);
        $pdf->SetSubject('Individual TIN Slip');
        $pdf->SetKeywords('individual tin slip, TCPDF, PHP');
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->SetFont('dejavuserifcondensedbi', '', 12);

        $txt = "Please find below your new Individual TIN Slip...";
        $pdf->MultiCell(150, 20, $txt, 0, 'C', false, 1, 35, 20, true, 0, false, true, 0, 'T', false);

        $pdf->Image('assets/images/nrs_bg_front.png.png', 60, 30, 100, 100, 'PNG', '', '', false, 300, '', false, false, 0);
        $pdf->Image('assets/images/nrs_bg_back.png', 61.5, 87, 97, 97, 'PNG', '', '', false, 300, '', false, false, 0);

        $style = [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255]
        ];

        $datas = '{TIN: ' . $tinData['tax_id'] . ', NAME: ' . html_entity_decode($tinData['fName']) . ' ' . html_entity_decode($tinData['mName']) . ' ' . html_entity_decode($tinData['sName']) . ', dob: ' . $tinData['dob'] . ', Status:Verified}';
        $pdf->write2DBarcode($datas, 'QRCODE,H', 123.5, 67, 23, 18, $style, 'H');


        // Register the custom OCR font
        $fontPath = public_path('fonts/ocrb10pitchbt_regular.ttf');
        $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);

        if (! $fontname) {
            // Fallback to helvetica if font registration fails
            $fontname = 'helvetica';
        }

        $sur = html_entity_decode($tinData['sName']);
        $pdf->SetFont($fontname, '', 9);
        $pdf->Text(76.5, 73.5, $sur);

        $othername = html_entity_decode($tinData['fName']);
        $pdf->SetFont($fontname, '', 9);
        $pdf->Text(76.6, 80, $othername);

        $dob = $tinData['dob'];
        $newD = strtotime($dob);
        $cdate = date("d M Y", $newD);
        $pdf->SetFont($fontname, '', 8);
        $pdf->Text(76.6, 87, $cdate);

        $tin = $tinData['tax_id'];
        $pdf->setTextColor(0, 0, 0);
        $newTin = substr($tin, 0, 4) . " " . substr($tin, 4, 3) . " " . substr($tin, 7);
        $pdf->SetFont($fontname, '', 18);
        $pdf->Text(85, 93, $newTin);

        $filename = 'Individual TIN Slip - ' . $reference . '.pdf';
        $pdfContent = $pdf->Output($filename, 'S');

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent));
    }

    public function coperateSlip($reference)
    {
        Log::info('Generating Corporate TIN Slip PDF');

        $verifiedRecord = Verification::where('idno', $reference)->latest()->first();

        if (!$verifiedRecord) {
            abort(404, 'Record not found');
        }

        $tinData = [
            'rc_number' => $verifiedRecord->nin ?? '',
            'fName'     => html_entity_decode($verifiedRecord->first_name ?? ''),
            'tax_id'    => $verifiedRecord->idno ?? '',
        ];

        $names = $tinData['fName'];

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

        // 🔴 CRITICAL FIXES
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        $pdf->SetCreator('Abu');
        $pdf->SetAuthor('Zulaiha');
        $pdf->SetTitle($names);
        $pdf->SetSubject('Corporate TIN Slip');

        // ✅ ADD ONLY ONE PAGE
        $pdf->AddPage('L');

        $pageWidth = $pdf->getPageWidth();

        // Background Image
        $pdf->Image(
            public_path('assets/images/Corporate.png'),
            0,
            0,
            $pageWidth,
            210,
            'PNG'
        );

        // QR Code
        $style = [
            'border'  => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ];

        $datas = '{TIN: ' . $tinData['tax_id'] .
            ', NAME: ' . $tinData['fName'] .
            ', Status: Verified}';

        $pdf->write2DBarcode(
            $datas,
            'QRCODE,H',
            230.5,
            108,
            35,
            35,
            $style,
            'H'
        );

        // Name
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(204, 51, 51);
        $pdf->Text(94, 80, $tinData['fName']);

        // TIN
        $tin = $tinData['tax_id'];
        $formattedTin = substr($tin, 0, 4) . ' ' .
            substr($tin, 4, 3) . ' ' .
            substr($tin, 7);

        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(204, 51, 51);
        $pdf->Text(48, 102.3, $formattedTin);

        $issueDate = date('d M Y');

        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(204, 51, 51);
        $pdf->Text(65, 147, $issueDate);

        $filename = 'Corporate TIN Slip - ' . $reference . '.pdf';
        $pdfContent = $pdf->Output($filename, 'S');

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'attachment; filename="' . $filename . '"'
            );
    }
}
