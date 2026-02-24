<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\EduLead;
use App\Models\EduLeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // ── SUPER ADMIN ───────────────────────────────────────────────
        if ($user->isSuperAdmin()) {
            return view('dashboard', array_merge(
                $this->globalLeadStats(),
                $this->admissionStats(),
                $this->institutionStats(),
                [
                    'totalUsers'  => User::count(),
                    'activeUsers' => User::active()->count(),
                    'roleStats'   => [
                        'super_admin'    => User::where('role', 'super_admin')->count(),
                        'operation_head' => User::where('role', 'operation_head')->count(),
                        'lead_manager'   => User::where('role', 'lead_manager')->count(),
                    ],
                    'branchStats'  => $this->getBranchStats(detailed: true),
                    'followupData' => $this->getFollowupData($user),
                ]
            ));
        }

        // ── OPERATION HEAD ────────────────────────────────────────────
        if ($user->isOperationHead()) {
            return view('dashboard', array_merge(
                $this->globalLeadStats(),
                $this->admissionStats(),
                $this->institutionStats(),
                [
                    'branchStats'  => $this->getBranchStats(detailed: false),
                    'followupData' => $this->getFollowupData($user),
                ]
            ));
        }

        // ── LEAD MANAGER (branch-scoped) ──────────────────────────────
        if ($user->isLeadManager()) {
            $branchId = $user->branch_id;
            $q        = EduLead::where('branch_id', $branchId);

            return view('dashboard', array_merge(
                $this->branchLeadStats($branchId),
                $this->admissionStats($branchId),
                $this->institutionStats($branchId),
                [
                    'followupData' => $this->getFollowupData($user),
                    'teamStats' => [
                        'total_leads'    => (clone $q)->count(),
                        'pending'        => (clone $q)->where('final_status', 'pending')->count(),
                        'contacted'      => (clone $q)->where('final_status', 'contacted')->count(),
                        'follow_up'      => (clone $q)->where('final_status', 'follow_up')->count(),
                        'admitted'       => (clone $q)->where('final_status', 'admitted')->count(),
                        'not_interested' => (clone $q)->where('final_status', 'not_interested')->count(),
                        'dropped'        => (clone $q)->where('final_status', 'dropped')->count(),
                    ],
                ]
            ));
        }

        // ── TELECALLER (assigned leads only) ──────────────────────────────
        if ($user->isTelecaller()) {
            $q  = EduLead::where('assigned_to', $user->id);
            $fq = EduLeadFollowup::where('assigned_to', $user->id)->where('status', 'pending');

            return view('dashboard', [
                // ── Row 1: My Lead Pipeline ──────────────────────────────
                'myLeads'         => (clone $q)->count(),
                'myPending'       => (clone $q)->where('final_status', 'pending')->count(),
                'myContacted'     => (clone $q)->where('final_status', 'contacted')->count(),
                'myFollowUp'      => (clone $q)->where('final_status', 'follow_up')->count(),
                'myAdmitted'      => (clone $q)->where('final_status', 'admitted')->count(),
                'myDropped'       => (clone $q)->where('final_status', 'dropped')->count(),
                'myNotInterested' => (clone $q)->where('final_status', 'not_interested')->count(),

                // ── Row 2: Interest Level ────────────────────────────────
                'myHot'           => (clone $q)->where('interest_level', 'hot')->count(),
                'myWarm'          => (clone $q)->where('interest_level', 'warm')->count(),
                'myCold'          => (clone $q)->where('interest_level', 'cold')->count(),

                // ── Row 3: My Admissions (time-based) ───────────────────
                'myAdmittedToday'     => (clone $q)->where('final_status', 'admitted')
                                            ->whereDate('admitted_at', today())->count(),
                'myAdmittedThisWeek'  => (clone $q)->where('final_status', 'admitted')
                                            ->whereBetween('admitted_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'myAdmittedThisMonth' => (clone $q)->where('final_status', 'admitted')
                                            ->whereYear('admitted_at', now()->year)
                                            ->whereMonth('admitted_at', now()->month)->count(),

                // ── Row 4: My Follow-up Counts ───────────────────────────
                'myOverdueFollowups'   => (clone $fq)->whereDate('followup_date', '<', today())->count(),
                'myTodayFollowups'     => (clone $fq)->whereDate('followup_date', today())->count(),
                'myWeekFollowups'      => (clone $fq)->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'myMonthFollowups'     => (clone $fq)->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),

                // ── Followup Detail Sections (reuses existing partial) ───
                'followupData'         => $this->getFollowupData($user),
            ]);
        }

        // Fallback
        return view('dashboard');
    }

    // ══════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Global lead stats (all branches) — for super_admin & operation_head.
     */
    private function globalLeadStats(): array
    {
        return [
            'totalLeads'     => EduLead::count(),
            'pendingLeads'   => EduLead::where('final_status', 'pending')->count(),
            'contactedLeads' => EduLead::where('final_status', 'contacted')->count(),
            'followUpLeads'  => EduLead::where('final_status', 'follow_up')->count(),
            'admittedLeads'  => EduLead::where('final_status', 'admitted')->count(),
            'droppedLeads'   => EduLead::where('final_status', 'dropped')->count(),
            'hotLeads'       => EduLead::where('interest_level', 'hot')->count(),
            'warmLeads'      => EduLead::where('interest_level', 'warm')->count(),
            'coldLeads'      => EduLead::where('interest_level', 'cold')->count(),
        ];
    }

    /**
     * Branch-scoped lead stats — for lead_manager.
     */
    private function branchLeadStats(int $branchId): array
    {
        $q = EduLead::where('branch_id', $branchId);

        return [
            'myLeads'     => (clone $q)->count(),
            'myPending'   => (clone $q)->where('final_status', 'pending')->count(),
            'myContacted' => (clone $q)->where('final_status', 'contacted')->count(),
            'myFollowUp'  => (clone $q)->where('final_status', 'follow_up')->count(),
            'myAdmitted'  => (clone $q)->where('final_status', 'admitted')->count(),
            'myDropped'   => (clone $q)->where('final_status', 'dropped')->count(),
            'myHot'       => (clone $q)->where('interest_level', 'hot')->count(),
            'myWarm'      => (clone $q)->where('interest_level', 'warm')->count(),
            'myCold'      => (clone $q)->where('interest_level', 'cold')->count(),
        ];
    }

    /**
     * Institution type & department stats.
     * Pass $branchId to scope to a branch, null for global.
     */
    private function institutionStats(?int $branchId = null): array
    {
        $base = EduLead::query();
        if ($branchId) $base->where('branch_id', $branchId);

        $school  = (clone $base)->where('institution_type', 'school');
        $college = (clone $base)->where('institution_type', 'college');

        // School streams
        $schoolDepts = [];
        foreach (['Science', 'Commerce', 'Arts', 'Vocational', 'Other'] as $stream) {
            $schoolDepts[$stream] = (clone $school)->where('school_department', $stream)->count();
        }

        // College departments
        $collegeDepts = [];
        foreach (['Engineering', 'Medical', 'Arts', 'Commerce', 'Science', 'Law', 'Management', 'Other'] as $dept) {
            $collegeDepts[$dept] = (clone $college)->where('college_department', $dept)->count();
        }

        return [
            'schoolLeads'   => (clone $school)->count(),
            'collegeLeads'  => (clone $college)->count(),
            'otherInstLeads'=> (clone $base)->where('institution_type', 'other')->count(),
            'schoolDepts'   => $schoolDepts,
            'collegeDepts'  => $collegeDepts,
        ];
    }

    /**
     * Admission time-based stats.
     */
    private function admissionStats(?int $branchId = null): array
    {
        $base = EduLead::where('final_status', 'admitted');
        if ($branchId) $base->where('branch_id', $branchId);

        return [
            'admittedToday'     => (clone $base)->whereDate('admitted_at', today())->count(),
            'admittedThisWeek'  => (clone $base)->whereBetween('admitted_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'admittedThisMonth' => (clone $base)->whereYear('admitted_at', now()->year)->whereMonth('admitted_at', now()->month)->count(),
            'admittedThisYear'  => (clone $base)->whereYear('admitted_at', now()->year)->count(),
        ];
    }

    /**
     * Per-branch lead counts.
     */
    private function getBranchStats(bool $detailed = false): \Illuminate\Database\Eloquent\Collection
    {
        $counts = [
            'eduLeads as total_leads',
            'eduLeads as admitted_leads'  => fn($q) => $q->where('final_status', 'admitted'),
            'eduLeads as pending_leads'   => fn($q) => $q->where('final_status', 'pending'),
            'eduLeads as follow_up_leads' => fn($q) => $q->where('final_status', 'follow_up'),
        ];

        if ($detailed) {
            $counts['eduLeads as hot_leads']  = fn($q) => $q->where('interest_level', 'hot');
            $counts['eduLeads as warm_leads'] = fn($q) => $q->where('interest_level', 'warm');
        }

        return Branch::active()
            ->withCount($counts)
            ->orderBy('name')
            ->get();
    }

    /**
     * Follow-up data for the dashboard widget.
     */
    private function getFollowupData(User $user): array
    {
        $query = EduLeadFollowup::with([
            'eduLead:id,name,lead_code,phone,final_status,interest_level',
            'assignedToUser:id,name',
        ])->where('status', 'pending');

        if ($user->isSuperAdmin() || $user->isOperationHead()) {
            // All branches — no scope
        } elseif ($user->isLeadManager()) {
            $branchId = $user->branch_id;
            $query->whereHas('eduLead', fn($q) => $q->where('branch_id', $branchId));
        } else {
            $query->where('assigned_to', $user->id);
        }

        $priorityOrder = "CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END";

        $overdue    = (clone $query)->whereDate('followup_date', '<', today())->count();
        $todayCount = (clone $query)->whereDate('followup_date', today())->count();
        $thisWeek   = (clone $query)->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth  = (clone $query)->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $overdueFollowups = (clone $query)
            ->whereDate('followup_date', '<', today())
            ->orderByRaw($priorityOrder)
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(20)->get();

        $todayFollowups = (clone $query)
            ->whereDate('followup_date', today())
            ->orderByRaw($priorityOrder)
            ->orderBy('followup_time')
            ->limit(20)->get();

        $thisWeekFollowups = (clone $query)
            ->whereDate('followup_date', '>', today())
            ->whereDate('followup_date', '<=', now()->endOfWeek())
            ->orderByRaw($priorityOrder)
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(20)->get();

        $thisMonthFollowups = (clone $query)
            ->whereDate('followup_date', '>', now()->endOfWeek())
            ->whereDate('followup_date', '<=', now()->endOfMonth())
            ->orderByRaw($priorityOrder)
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(30)->get();

        return compact(
            'overdue', 'todayCount', 'thisWeek', 'thisMonth',
            'overdueFollowups', 'todayFollowups', 'thisWeekFollowups', 'thisMonthFollowups'
        );
    }
}
