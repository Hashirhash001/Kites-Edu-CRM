<?php

namespace App\Console\Commands;

use App\Mail\DailyLeadsExport;
use App\Models\EduLead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SendDailyLeadsExport extends Command
{
    protected $signature   = 'leads:send-daily-export';
    protected $description = 'Generate and email today\'s new education leads at 8 PM IST';

    public function handle(): void
    {
        $this->info('Fetching today\'s leads...');

        $leads = EduLead::with([
            'course.programme', 'leadSource', 'createdBy', 'assignedTo', 'branch',
            'followups', 'latestFollowup',
        ])
        ->whereDate('created_at', today())
        ->orderBy('created_at', 'desc')
        ->get();

        if ($leads->isEmpty()) {
            Mail::to('leads@ajkadm.com')->send(new DailyLeadsExport(null, null, [
                'total'    => 0,
                'hot'      => 0,
                'admitted' => 0,
                'pending'  => 0,
            ]));
            $this->info('No leads today — notification email sent.');
            return;
        }

        $this->info("Found {$leads->count()} lead(s). Building spreadsheet...");

        // ── Build spreadsheet ─────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Education Leads');

        $headers = [
            'A'  => ['header' => 'Lead Code',              'width' => 16],
            'B'  => ['header' => 'Name',                   'width' => 24],
            'C'  => ['header' => 'Email',                  'width' => 28],
            'D'  => ['header' => 'Phone',                  'width' => 16],
            'E'  => ['header' => 'WhatsApp Number',        'width' => 18],
            'F'  => ['header' => 'State',                  'width' => 18],
            'G'  => ['header' => 'District',               'width' => 18],
            'H'  => ['header' => 'Branch',                 'width' => 20],
            'I'  => ['header' => 'Institution Type',       'width' => 18],
            'J'  => ['header' => 'School',                 'width' => 28],
            'K'  => ['header' => 'School Stream / Dept',   'width' => 22],
            'L'  => ['header' => 'College',                'width' => 28],
            'M'  => ['header' => 'College Department',     'width' => 22],
            'N'  => ['header' => 'Programme',              'width' => 22],
            'O'  => ['header' => 'Course',                 'width' => 26],
            'P'  => ['header' => 'Addon Course',           'width' => 22],
            'Q'  => ['header' => 'Lead Source',            'width' => 18],
            'R'  => ['header' => 'Agent Name',             'width' => 20],
            'S'  => ['header' => 'Application Number',     'width' => 20],
            'T'  => ['header' => 'Booking Payment (₹)',    'width' => 20],
            'U'  => ['header' => 'Fees Collected (₹)',     'width' => 20],
            'V'  => ['header' => 'Cancellation Reason',    'width' => 28],
            'W'  => ['header' => 'Interest Level',         'width' => 16],
            'X'  => ['header' => 'Candidate Status',       'width' => 20],
            'Y'  => ['header' => 'Call Status',            'width' => 18],
            'Z'  => ['header' => 'Counseling Stage',       'width' => 26],
            'AA' => ['header' => 'Assigned To',            'width' => 20],
            'AB' => ['header' => 'Created By',             'width' => 20],
            'AC' => ['header' => 'Total Followups',        'width' => 16],
            'AD' => ['header' => 'Overdue Followups',      'width' => 16],
            'AE' => ['header' => 'Today Followups',        'width' => 16],
            'AF' => ['header' => 'Upcoming Followups',     'width' => 16],
            'AG' => ['header' => 'Completed Followups',    'width' => 18],
            'AH' => ['header' => 'Latest Followup Date',   'width' => 20],
            'AI' => ['header' => 'Latest Followup Time',   'width' => 18],
            'AJ' => ['header' => 'Latest Followup Status', 'width' => 18],
            'AK' => ['header' => 'Latest Followup Notes',  'width' => 36],
            'AL' => ['header' => 'Created At',             'width' => 20],
        ];

        foreach ($headers as $col => $meta) {
            $sheet->setCellValue("{$col}1", $meta['header']);
            $sheet->getColumnDimension($col)->setWidth($meta['width']);
        }

        $lastCol = array_key_last($headers); // 'AL'

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF667EEA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF5A67D8']]],
        ]);

        $sheet->freezePane('A2');
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->setAutoFilter("A1:{$lastCol}1");

        // ── Data rows ─────────────────────────────────────────────────────
        $row   = 2;
        $today = \Carbon\Carbon::today();

        foreach ($leads as $lead) {
            $totalFu   = $lead->followups->count();
            $doneFu    = $lead->followups->where('status', 'completed')->count();
            $overdueFu = $lead->followups->filter(fn($f) =>
                $f->status === 'pending' &&
                \Carbon\Carbon::parse($f->followup_date)->startOfDay()->lt($today)
            )->count();
            $todayFu   = $lead->followups->filter(fn($f) =>
                $f->status === 'pending' &&
                \Carbon\Carbon::parse($f->followup_date)->isToday()
            )->count();
            $upcomingFu = max(0, $lead->followups->where('status', 'pending')->count() - $overdueFu - $todayFu);

            $lfu = $lead->latestFollowup;

            $sheet->fromArray([
                $lead->lead_code,
                $lead->name,
                $lead->email               ?? '',
                $lead->phone,
                $lead->whatsapp_number     ?? '',
                $lead->state               ?? '',
                $lead->district            ?? '',
                $lead->branch?->name       ?? '',
                ucfirst($lead->institution_type ?? ''),
                $lead->school              ?? '',
                $lead->school_department   ?? '',
                $lead->college             ?? '',
                $lead->college_department  ?? '',
                $lead->course?->programme?->name ?? '',
                $lead->course?->name       ?? '',
                $lead->addon_course        ?? '',
                $lead->leadSource?->name   ?? '',
                $lead->agent_name          ?? '',
                $lead->application_number  ?? '',
                $lead->booking_payment     ?? '',
                $lead->fees_collection     ?? '',
                $lead->cancellation_reason ?? '',
                ucfirst($lead->interest_level ?? ''),
                EduLead::FINAL_STATUSES[$lead->final_status] ?? ucfirst(str_replace('_', ' ', $lead->final_status ?? '')),
                EduLead::CALL_STATUSES[$lead->call_status]   ?? '',
                EduLead::COUNSELING_STAGES[$lead->status]    ?? '',
                $lead->assignedTo?->name   ?? 'Unassigned',
                $lead->createdBy?->name    ?? '',
                $totalFu, $overdueFu, $todayFu, $upcomingFu, $doneFu,
                $lfu ? \Carbon\Carbon::parse($lfu->followup_date)->format('d-m-Y') : '',
                $lfu && $lfu->followup_time ? \Carbon\Carbon::parse($lfu->followup_time)->format('h:i A') : '',
                $lfu ? ucfirst($lfu->status ?? '') : '',
                $lfu ? ($lfu->notes ?? '') : '',
                $lead->created_at->format('d-m-Y H:i'),
            ], null, 'A' . $row);

            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFF8FAFC');
            }

            $row++;
        }

        // ── Save temp file ────────────────────────────────────────────────
        $fileName = 'leads_' . now()->setTimezone('Asia/Kolkata')->format('Y-m-d') . '.xlsx';
        $tempPath = storage_path('app/exports/' . $fileName);

        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($tempPath);

        // ── Stats for email body ──────────────────────────────────────────
        $stats = [
            'total'    => $leads->count(),
            'hot'      => $leads->where('interest_level', 'hot')->count(),
            'admitted' => $leads->where('final_status', 'admitted')->count(),
            'pending'  => $leads->where('final_status', 'pending')->count(),
        ];

        // ── Send email ────────────────────────────────────────────────────
        Mail::to('leads@ajkadm.com')->send(new DailyLeadsExport($tempPath, $fileName, $stats));

        // ── Cleanup ───────────────────────────────────────────────────────
        @unlink($tempPath);

        $this->info("✅ Daily export sent to leads@ajkadm.com ({$leads->count()} leads).");
    }
}
