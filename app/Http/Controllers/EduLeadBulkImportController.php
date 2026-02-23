<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\EduLead;
use App\Models\EduLeadImport;
use App\Models\EduLeadSource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EduLeadBulkImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // INDEX
    // =========================================================================
    public function bulkImport()
    {
        $recentImports = EduLeadImport::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('edu-leads.bulk-import', compact('recentImports'));
    }

    // =========================================================================
    // DOWNLOAD TEMPLATE
    // =========================================================================
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Student Follow-up CRM');

        $headers = [
            'A1' => '✅ Mobile Number',
            'B1' => '✅ School / College Name',
            'C1' => '✅ Department / Stream',
            'D1' => 'Student Name',
            'E1' => 'WhatsApp Number',
            'F1' => 'Course Interested',
            'G1' => 'Country',
            'H1' => 'Source of Lead',
            'I1' => 'Calling Staff Name',
            'J1' => 'Call Date',
            'K1' => 'Call Status (Connected/Not Connected)',
            'L1' => 'Student Interest Level (Hot/Warm/Cold)',
            'M1' => 'Follow-up Date',
            'N1' => 'Follow-up Status',
            'O1' => 'Remarks / Notes',
            'P1' => 'Next Action',
            'Q1' => 'Final Status (Admitted / Not Interested / Pending)',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(42);

        $widths = [
            'A' => 20, 'B' => 30, 'C' => 22, 'D' => 22, 'E' => 18,
            'F' => 22, 'G' => 16, 'H' => 18, 'I' => 20, 'J' => 14,
            'K' => 32, 'L' => 34, 'M' => 14, 'N' => 18, 'O' => 30,
            'P' => 24, 'Q' => 40,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Example row
        $example = [
            'A2' => '+919876543210',
            'B2' => 'St. Mary\'s High School',
            'C2' => 'Science',
            'D2' => 'Rahul Kumar',
            'E2' => '+919876543210',
            'F2' => 'MBBS',
            'G2' => 'Russia',
            'H2' => 'Facebook',
            'I2' => 'Aneesh',
            'J2' => '11/01/2025',
            'K2' => 'Connected',
            'L2' => 'Hot',
            'M2' => '15/01/2025',
            'N2' => 'pending',
            'O2' => 'Interested, needs fee structure',
            'P2' => 'Send fee structure PDF',
            'Q2' => 'Pending',
        ];
        foreach ($example as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A2:Q2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
        ]);
        $sheet->freezePane('A2');

        // Instructions sheet
        $inst = $spreadsheet->createSheet();
        $inst->setTitle('Instructions');
        $inst->fromArray([
            ['Column', 'Header Name', 'Required?', 'Accepted Values'],
            ['A', '✅ Mobile Number',      '✅ YES', '10-15 digits, e.g. +919876543210'],
            ['B', '✅ School/College Name', '✅ YES', 'Name of school or college'],
            ['C', '✅ Department/Stream',   '✅ YES', 'e.g. Science, Commerce, Arts, Engineering, Medical'],
            ['D', 'Student Name',           'No',  'Full name of student'],
            ['E', 'WhatsApp Number',        'No',  'If blank, Mobile Number is used'],
            ['F', 'Course Interested',      'No',  'e.g. MBBS, B.Tech, MBA, Nursing'],
            ['G', 'Country',                'No',  'e.g. Russia, USA, UK, India'],
            ['H', 'Source of Lead',         'No',  'Facebook, Google, Walk-in, Referral'],
            ['I', 'Calling Staff Name',     'No',  'Must match a telecaller in the CRM'],
            ['J', 'Call Date',              'No',  'DD/MM/YYYY'],
            ['K', 'Call Status',            'No',  'Connected  or  Not Connected'],
            ['L', 'Student Interest Level', 'No',  'Hot  /  Warm  /  Cold'],
            ['M', 'Follow-up Date',         'No',  'DD/MM/YYYY'],
            ['N', 'Follow-up Status',       'No',  'pending  /  completed'],
            ['O', 'Remarks / Notes',        'No',  'Free text'],
            ['P', 'Next Action',            'No',  'Free text'],
            ['Q', 'Final Status',           'No',  'pending / contacted / follow_up / admitted / not_interested / dropped'],
            ['', '', '', ''],
            ['CRITICAL REQUIRED FIELDS:', '', '', ''],
            ['1. Mobile Number - Must be unique (10-15 digits)', '', '', ''],
            ['2. School/College Name - Institution name', '', '', ''],
            ['3. Department/Stream - e.g. Science, Commerce, Medical', '', '', ''],
            ['', '', '', ''],
            ['TIPS:', '', '', ''],
            ['Delete the example row (row 2) before uploading', '', '', ''],
            ['Maximum 3000 rows per file', '', '', ''],
            ['Duplicate Mobile Numbers will be skipped automatically', '', '', ''],
        ], null, 'A1');
        $inst->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
        ]);
        $inst->getColumnDimension('A')->setWidth(8);
        $inst->getColumnDimension('B')->setWidth(30);
        $inst->getColumnDimension('C')->setWidth(12);
        $inst->getColumnDimension('D')->setWidth(60);

        $writer   = new Xlsx($spreadsheet);
        $filename = 'edu_leads_import_template_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // =========================================================================
    // MAP COLUMNS
    // =========================================================================
    private function mapColumns(array $headerArray): array
    {
        $patterns = [
            'mobile_number'   => ['mobile number', 'mobile', 'contact number', 'phone', 'contact', '✅ mobile number'],
            'school_college'  => ['school / college name', 'school/college name', 'school college name', 'school', 'college', 'institution', '✅ school / college name', '✅ school/college name'],
            'department'      => ['department / stream', 'department/stream', 'department', 'stream', 'dept', '✅ department / stream', '✅ department/stream'],
            'student_name'    => ['student name', 'name', 'client name', 'customer name'],
            'whatsapp'        => ['whatsapp number', 'whatsapp'],
            'course'          => ['course interested', 'course'],
            'country'         => ['country'],
            'lead_source'     => ['source of lead', 'source', 'lead source'],
            'calling_staff'   => ['calling staff name', 'telecaller name', 'telecaller', 'calling staff', 'staff name'],
            'call_date'       => ['call date', 'date'],
            'call_status'     => ['call status (connected/not connected)', 'call status'],
            'interest_level'  => ['student interest level (hot/warm/cold)', 'interest level', 'interest'],
            'followup_date'   => ['follow-up date', 'followup date', 'follow up date'],
            'followup_status' => ['follow-up status', 'followup status', 'follow up status'],
            'remarks'         => ['remarks / notes', 'remarks', 'notes'],
            'next_action'     => ['next action'],
            'final_status'    => ['final status (admitted / not interested / pending)', 'final status'],
        ];

        $mapping = [];

        // Pass 1: exact match
        foreach ($headerArray as $index => $header) {
            $h = strtolower(trim((string)$header));
            if (empty($h)) continue;
            foreach ($patterns as $field => $alternatives) {
                if (!isset($mapping[$field])) {
                    foreach ($alternatives as $alt) {
                        if ($h === $alt) { $mapping[$field] = $index; break 2; }
                    }
                }
            }
        }

        // Pass 2: contains match
        foreach ($headerArray as $index => $header) {
            $h = strtolower(trim((string)$header));
            if (empty($h)) continue;
            foreach ($patterns as $field => $alternatives) {
                if (isset($mapping[$field])) continue;
                foreach ($alternatives as $alt) {
                    if (stripos($h, $alt) !== false || stripos($alt, $h) !== false) {
                        $mapping[$field] = $index;
                        break 2;
                    }
                }
            }
        }

        return $mapping;
    }

    // =========================================================================
    // PRE-VALIDATE
    // =========================================================================
    public function preValidateImport(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240']);

        try {
            $file   = $request->file('csv_file');
            $ext    = strtolower($file->getClientOriginalExtension());
            $phones = [];
            $whatsapps = [];

            if (in_array($ext, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                for ($si = 0; $si < $spreadsheet->getSheetCount(); $si++) {
                    $ws        = $spreadsheet->getSheet($si);
                    $sheetName = $ws->getTitle();
                    $skipNames = ['dashboard', 'instructions', 'team leader', 'tomorrow'];
                    $skip = false;
                    foreach ($skipNames as $s) {
                        if (stripos($sheetName, $s) !== false) { $skip = true; break; }
                    }
                    if ($skip) continue;

                    $highestRow = $ws->getHighestRow();
                    $highestCol = $ws->getHighestColumn();
                    $headerRow  = null;

                    for ($r = 1; $r <= 5; $r++) {
                        $rd = $ws->rangeToArray('A' . $r . ':' . $highestCol . $r, null, true, false)[0];
                        foreach ($rd as $cell) {
                            if (stripos((string)$cell, 'Mobile') !== false ||
                                stripos((string)$cell, 'Phone') !== false) {
                                $headerRow = $r; break 2;
                            }
                        }
                    }
                    if (!$headerRow) continue;

                    $headerArr = $ws->rangeToArray('A' . $headerRow . ':' . $highestCol . $headerRow, null, true, false)[0];
                    $colMap    = $this->mapColumns($headerArr);
                    if (!isset($colMap['mobile_number'])) continue;

                    for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                        $rowArr = $ws->rangeToArray('A' . $r . ':' . $highestCol . $r, null, true, false)[0];
                        $ph = $this->cleanPhone($rowArr[$colMap['mobile_number']] ?? '');
                        if (!$ph) continue;
                        $phones[] = $ph;

                        $wa = $this->cleanPhone($rowArr[$colMap['whatsapp'] ?? -1] ?? '');
                        if ($wa && $wa !== $ph) $whatsapps[] = $wa;
                    }
                }
            } elseif ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                $bom    = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") rewind($handle);
                $header = fgetcsv($handle);
                $colMap = $this->mapColumns($header);
                while (($line = fgetcsv($handle)) !== false) {
                    $ph = $this->cleanPhone($line[$colMap['mobile_number'] ?? 0] ?? '');
                    if (!$ph) continue;
                    $phones[] = $ph;
                    $wa = $this->cleanPhone($line[$colMap['whatsapp'] ?? -1] ?? '');
                    if ($wa && $wa !== $ph) $whatsapps[] = $wa;
                }
                fclose($handle);
            }

            $total = count($phones);

            $existingPhone    = EduLead::whereIn('phone', $phones)->count();
            $existingWhatsapp = !empty($whatsapps)
                ? EduLead::whereIn('whatsapp_number', $whatsapps)->count()
                : 0;

            $existingCount = $existingPhone + $existingWhatsapp;
            $newCount      = max(0, $total - $existingPhone);

            $messages = [];
            if ($existingPhone    > 0) $messages[] = "{$existingPhone} mobile number(s) already exist";
            if ($existingWhatsapp > 0) $messages[] = "{$existingWhatsapp} WhatsApp number(s) already exist";
            $warningMsg = !empty($messages)
                ? implode(' and ', $messages) . ' — those rows will be skipped.'
                : null;

            return response()->json([
                'success'         => true,
                'needs_warning'   => $existingCount > 0,
                'warning_message' => $warningMsg,
                'analysis'        => [
                    'total_rows'        => $total,
                    'existing_phone'    => $existingPhone,
                    'existing_whatsapp' => $existingWhatsapp,
                    'existing_count'    => $existingCount,
                    'new_count'         => $newCount,
                    'will_create_leads' => $newCount,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PROCESS BULK IMPORT
    // =========================================================================
    public function processBulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        set_time_limit(600);
        ini_set('memory_limit', '512M');

        try {
            DB::beginTransaction();

            $import = EduLeadImport::create([
                'user_id'  => auth()->id(),
                'filename' => $request->file('csv_file')->getClientOriginalName(),
                'status'   => 'processing',
            ]);

            $file    = $request->file('csv_file');
            $ext     = strtolower($file->getClientOriginalExtension());
            $records = [];
            $skipped = [];

            if (in_array($ext, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());

                for ($si = 0; $si < $spreadsheet->getSheetCount(); $si++) {
                    $ws        = $spreadsheet->getSheet($si);
                    $sheetName = $ws->getTitle();

                    $skipSheetNames = ['dashboard', 'instructions', 'team leader', 'tomorrow'];
                    $shouldSkip = false;
                    foreach ($skipSheetNames as $skip) {
                        if (stripos($sheetName, $skip) !== false) { $shouldSkip = true; break; }
                    }
                    if ($shouldSkip) {
                        $skipped[] = ['name' => $sheetName, 'reason' => 'Non-data sheet skipped automatically'];
                        continue;
                    }

                    $highestRow = $ws->getHighestRow();
                    $highestCol = $ws->getHighestColumn();

                    if ($highestRow > 3001) {
                        $skipped[] = ['name' => $sheetName, 'reason' => 'Exceeds 3000 rows limit'];
                        continue;
                    }

                    $headerRow = null;
                    for ($r = 1; $r <= 5; $r++) {
                        $rd = $ws->rangeToArray('A' . $r . ':' . $highestCol . $r, null, true, false)[0];
                        foreach ($rd as $cell) {
                            if (stripos((string)$cell, 'Mobile') !== false ||
                                stripos((string)$cell, 'Phone') !== false) {
                                $headerRow = $r; break 2;
                            }
                        }
                    }

                    if (!$headerRow) {
                        $skipped[] = ['name' => $sheetName, 'reason' => 'Header row not found'];
                        continue;
                    }

                    $headerArr = $ws->rangeToArray('A' . $headerRow . ':' . $highestCol . $headerRow, null, true, false)[0];
                    $colMap    = $this->mapColumns($headerArr);

                    if (!isset($colMap['mobile_number'])) {
                        $skipped[] = ['name' => $sheetName, 'reason' => 'Missing required column: Mobile Number'];
                        continue;
                    }

                    for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                        $rowArr = $ws->rangeToArray('A' . $r . ':' . $highestCol . $r, null, true, false)[0];
                        $ph = $this->cleanPhone($rowArr[$colMap['mobile_number']] ?? '');
                        if (!$ph) continue; // Skip rows without valid phone

                        $records[] = $this->buildRecord($rowArr, $colMap, $sheetName, $r);
                    }
                }
            } elseif ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") rewind($handle);
                $header = fgetcsv($handle);
                $colMap = $this->mapColumns($header);
                $row    = 2;
                while (($line = fgetcsv($handle)) !== false) {
                    $ph = $this->cleanPhone($line[$colMap['mobile_number'] ?? 0] ?? '');
                    if (!$ph) { $row++; continue; }
                    $records[] = $this->buildRecord($line, $colMap, 'CSV', $row++);
                }
                fclose($handle);
            }

            $totalRows = count($records);

            if ($totalRows === 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No valid data rows found.'], 400);
            }

            if ($totalRows > 3000) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Exceeds 3000 row limit ({$totalRows} rows found)."], 400);
            }

            $import->update(['total_rows' => $totalRows]);

            $processed = $successful = $failed = 0;
            $errors = $failedRowsData = [];

            $progressKey = "import_progress_{$import->id}";
            Cache::put($progressKey, [
                'total' => $totalRows, 'processed' => 0, 'successful' => 0,
                'failed' => 0, 'status' => 'processing', 'current_sheet' => null,
            ], now()->addMinutes(30));

            foreach (array_chunk($records, 100) as $chunk) {
                foreach ($chunk as $row) {
                    $rowId = "{$row['sheet_name']} Row {$row['excel_row']}";
                    try {
                        // ═══════════════════════════════════════════════════════════
                        // ✅ REQUIRED FIELDS VALIDATION (ONLY 3)
                        // ═══════════════════════════════════════════════════════════
                        $phone = $this->cleanPhone($row['mobile_number']);
                        if (!$phone) {
                            throw new \Exception('✅ Mobile Number is REQUIRED (10-15 digits)');
                        }

                        $schoolCollege = trim((string)$row['school_college']);
                        if (empty($schoolCollege)) {
                            throw new \Exception('✅ School/College Name is REQUIRED');
                        }

                        $department = trim((string)$row['department']);
                        if (empty($department)) {
                            throw new \Exception('✅ Department/Stream is REQUIRED');
                        }

                        // Duplicate check
                        if (EduLead::where('phone', $phone)->exists()) {
                            throw new \Exception("Mobile number {$phone} already exists");
                        }

                        $cleanWhatsapp = $this->cleanPhone($row['whatsapp']) ?? $phone;
                        if ($cleanWhatsapp !== $phone && EduLead::where('whatsapp_number', $cleanWhatsapp)->exists()) {
                            throw new \Exception("WhatsApp number {$cleanWhatsapp} already exists");
                        }

                        // ═══════════════════════════════════════════════════════════
                        // OPTIONAL FIELDS
                        // ═══════════════════════════════════════════════════════════

                        // Lead Source
                        $leadSource = null;
                        if (!empty($row['lead_source'])) {
                            $leadSource = EduLeadSource::whereRaw('LOWER(name) = ?', [strtolower($row['lead_source'])])
                                ->where('is_active', true)->first()
                                ?? EduLeadSource::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($row['lead_source']) . '%'])
                                    ->where('is_active', true)->first();
                        }
                        if (!$leadSource) {
                            $leadSource = EduLeadSource::where('is_active', true)
                                ->where(fn($q) => $q->whereRaw('LOWER(name) = ?', ['others'])
                                    ->orWhereRaw('LOWER(name) = ?', ['bulk import']))
                                ->first()
                                ?? EduLeadSource::where('is_active', true)->first();
                        }

                        // Telecaller
                        $assignedTo = null;
                        if (!empty($row['calling_staff'])) {
                            $tc = User::where('is_active', true)
                                ->whereIn('role', ['telecaller', 'lead_manager', 'operation_head', 'super_admin'])
                                ->whereRaw('LOWER(name) = ?', [strtolower(trim($row['calling_staff']))])
                                ->first()
                                ?? User::where('is_active', true)
                                    ->whereIn('role', ['telecaller', 'lead_manager'])
                                    ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($row['calling_staff'])) . '%'])
                                    ->first();
                            if ($tc) $assignedTo = $tc->id;
                        }

                        // Interest Level
                        $interestRaw   = strtolower(trim((string)$row['interest_level']));
                        $interestLevel = in_array($interestRaw, ['hot', 'warm', 'cold']) ? $interestRaw : null;

                        // Final Status
                        $statusMap = [
                            'admitted'       => 'admitted',
                            'not interested' => 'not_interested',
                            'not_interested' => 'not_interested',
                            'follow up'      => 'follow_up',
                            'follow_up'      => 'follow_up',
                            'followup'       => 'follow_up',
                            'contacted'      => 'contacted',
                            'dropped'        => 'dropped',
                            'pending'        => 'pending',
                        ];
                        $finalStatus = $statusMap[strtolower(trim((string)$row['final_status']))] ?? 'pending';

                        // Course
                        $courseId = null;
                        if (!empty($row['course'])) {
                            $course   = Course::where('is_active', true)
                                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($row['course']) . '%'])->first();
                            $courseId = $course?->id;
                        }

                        // Determine institution type from school_college name
                        $institutionType = null;
                        $schoolName = null;
                        $collegeName = null;

                        $scLower = strtolower($schoolCollege);
                        if (str_contains($scLower, 'school') ||
                            str_contains($scLower, 'high school') ||
                            str_contains($scLower, 'secondary')) {
                            $institutionType = 'school';
                            $schoolName = $schoolCollege;
                        } elseif (str_contains($scLower, 'college') ||
                                  str_contains($scLower, 'university') ||
                                  str_contains($scLower, 'institute')) {
                            $institutionType = 'college';
                            $collegeName = $schoolCollege;
                        } else {
                            // Default to school if ambiguous
                            $institutionType = 'school';
                            $schoolName = $schoolCollege;
                        }

                        // Branch determination
                        $branchId = auth()->user()->role === 'lead_manager'
                            ? auth()->user()->branch_id
                            : null;

                        EduLead::create([
                            'phone'                => $phone,
                            'school'               => $schoolName,
                            'school_department'    => $institutionType === 'school' ? $department : null,
                            'college'              => $collegeName,
                            'college_department'   => $institutionType === 'college' ? $department : null,
                            'institution_type'     => $institutionType,
                            'name'                 => !empty($row['student_name']) ? trim($row['student_name']) : 'Lead-' . substr($phone, -4),
                            'whatsapp_number'      => $cleanWhatsapp,
                            'course_interested'    => !empty($row['course']) ? $row['course'] : null,
                            'course_id'            => $courseId,
                            'country'              => !empty($row['country']) ? trim($row['country']) : null,
                            'lead_source_id'       => $leadSource?->id,
                            'assigned_to'          => $assignedTo,
                            'interest_level'       => $interestLevel,
                            'final_status'         => $finalStatus,
                            'status'               => 'pending',
                            'remarks'              => !empty($row['remarks']) ? $row['remarks'] : null,
                            'followup_date'        => $this->parseDate($row['followup_date']),
                            'branch_id'            => $branchId,
                            'created_by'           => auth()->id(),
                        ]);

                        $successful++;
                    } catch (\Exception $e) {
                        $errors[]         = [
                            'row'    => $rowId,
                            'sheet'  => $row['sheet_name'],
                            'data'   => ($row['mobile_number'] ?? '?') . ' / ' . ($row['school_college'] ?? '?'),
                            'errors' => [$e->getMessage()],
                        ];
                        $failedRowsData[] = ['data' => $row, 'errors' => $e->getMessage()];
                        $failed++;
                        Log::warning("EduLead import [{$rowId}]: " . $e->getMessage());
                    }
                    $processed++;

                    if ($processed % 5 === 0) {
                        Cache::put($progressKey, [
                            'total'         => $totalRows,
                            'processed'     => $processed,
                            'successful'    => $successful,
                            'failed'        => $failed,
                            'current_sheet' => $row['sheet_name'],
                            'status'        => 'processing',
                        ], now()->addMinutes(30));
                    }
                }
                gc_collect_cycles();
            }

            Cache::put($progressKey, [
                'total' => $totalRows, 'processed' => $processed,
                'successful' => $successful, 'failed' => $failed, 'status' => 'completed',
            ], now()->addMinutes(30));

            $import->update([
                'status'           => $failed === $totalRows ? 'failed' : 'completed',
                'processed_rows'   => $processed,
                'successful_rows'  => $successful,
                'failed_rows'      => $failed,
                'errors'           => $errors,
                'failed_rows_data' => $failedRowsData,
            ]);

            DB::commit();

            return response()->json([
                'success'         => $successful > 0,
                'message'         => "{$successful} leads imported." . ($failed > 0 ? " {$failed} failed." : ''),
                'import_id'       => $import->id,
                'stats'           => [
                    'total' => $totalRows, 'processed' => $processed,
                    'successful' => $successful, 'failed' => $failed,
                ],
                'skipped_sheets'  => $skipped,
                'errors'          => $errors,
                'has_failed_rows' => $failed > 0,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($import)) $import->update(['status' => 'failed', 'errors' => [['message' => $e->getMessage()]]]);
            Log::error('EduLead bulk import failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PROGRESS POLLING
    // =========================================================================
    public function getImportProgress($importId)
    {
        $key      = "import_progress_{$importId}";
        $progress = Cache::get($key);

        if (!$progress) {
            $import = EduLeadImport::find($importId);
            if (!$import) return response()->json(['error' => 'Not found'], 404);
            $pct = $import->total_rows > 0
                ? round(($import->processed_rows / $import->total_rows) * 100, 1)
                : 0;
            return response()->json([
                'total'      => $import->total_rows,
                'processed'  => $import->processed_rows,
                'successful' => $import->successful_rows,
                'failed'     => $import->failed_rows,
                'status'     => $import->status,
                'percentage' => $pct,
            ]);
        }

        $pct = $progress['total'] > 0
            ? round(($progress['processed'] / $progress['total']) * 100, 1)
            : 0;
        return response()->json(array_merge($progress, ['percentage' => $pct]));
    }

    // =========================================================================
    // DOWNLOAD FAILED ROWS
    // =========================================================================
    public function downloadFailedRows($importId)
    {
        $import = EduLeadImport::findOrFail($importId);
        if (empty($import->failed_rows_data)) {
            abort(404, 'No failed rows data available');
        }

        $headers = [
            '✅ Mobile Number', '✅ School/College Name', '✅ Department/Stream',
            'Student Name', 'WhatsApp Number', 'Course Interested', 'Country',
            'Source of Lead', 'Calling Staff Name', 'Call Date',
            'Call Status (Connected/Not Connected)', 'Student Interest Level (Hot/Warm/Cold)',
            'Follow-up Date', 'Follow-up Status', 'Remarks / Notes', 'Next Action',
            'Final Status (Admitted / Not Interested / Pending)',
            'ERROR — Fix before re-importing',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($handle, $headers);

        foreach ($import->failed_rows_data as $fr) {
            $d   = is_array($fr) && isset($fr['data'])   ? $fr['data']   : (is_array($fr) ? $fr : []);
            $err = is_array($fr) && isset($fr['errors']) ? $fr['errors'] : ($fr['error'] ?? 'Unknown');
            if (is_array($err)) $err = implode(' | ', $err);

            fputcsv($handle, [
                $d['mobile_number']   ?? '',
                $d['school_college']  ?? '',
                $d['department']      ?? '',
                $d['student_name']    ?? '',
                $d['whatsapp']        ?? '',
                $d['course']          ?? '',
                $d['country']         ?? '',
                $d['lead_source']     ?? '',
                $d['calling_staff']   ?? '',
                $d['call_date']       ?? '',
                $d['call_status']     ?? '',
                $d['interest_level']  ?? '',
                $d['followup_date']   ?? '',
                $d['followup_status'] ?? '',
                $d['remarks']         ?? '',
                $d['next_action']     ?? '',
                $d['final_status']    ?? '',
                (string)$err,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="edu_failed_rows_' . date('YmdHis') . '.csv"',
            'Cache-Control'       => 'no-cache',
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    private function buildRecord(array $row, array $colMap, string $sheetName, int $excelRow): array
    {
        return [
            'sheet_name'      => $sheetName,
            'excel_row'       => $excelRow,
            'mobile_number'   => $row[$colMap['mobile_number']]                 ?? '',
            'school_college'  => $row[$colMap['school_college']  ?? -1]         ?? '',
            'department'      => $row[$colMap['department']       ?? -1]         ?? '',
            'student_name'    => trim((string)($row[$colMap['student_name'] ?? -1] ?? '')),
            'whatsapp'        => $row[$colMap['whatsapp']         ?? -1]         ?? '',
            'course'          => $row[$colMap['course']           ?? -1]         ?? '',
            'country'         => $row[$colMap['country']          ?? -1]         ?? '',
            'lead_source'     => $row[$colMap['lead_source']      ?? -1]         ?? '',
            'calling_staff'   => $row[$colMap['calling_staff']    ?? -1]         ?? '',
            'call_date'       => $row[$colMap['call_date']        ?? -1]         ?? '',
            'call_status'     => $row[$colMap['call_status']      ?? -1]         ?? '',
            'interest_level'  => $row[$colMap['interest_level']   ?? -1]         ?? '',
            'followup_date'   => $row[$colMap['followup_date']    ?? -1]         ?? '',
            'followup_status' => $row[$colMap['followup_status']  ?? -1]         ?? '',
            'remarks'         => $row[$colMap['remarks']          ?? -1]         ?? '',
            'next_action'     => $row[$colMap['next_action']      ?? -1]         ?? '',
            'final_status'    => $row[$colMap['final_status']     ?? -1]         ?? '',
        ];
    }

    private function cleanPhone($raw): ?string
    {
        if (empty($raw)) return null;
        $raw   = is_numeric($raw) ? number_format((float)$raw, 0, '', '') : (string)$raw;
        $clean = preg_replace('/[^0-9]/', '', $raw);
        return (strlen($clean) >= 10 && strlen($clean) <= 15) ? $clean : null;
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) return null;
        try {
            if (is_string($value) && str_contains($value, '/')) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }
            if (is_numeric($value)) {
                return Carbon::createFromTimestamp(($value - 25569) * 86400)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
