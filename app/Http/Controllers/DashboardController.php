<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\EduLead;
use App\Models\EduLeadFollowup;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // ── SUPER ADMIN ──────────────────────────────────────────────
        if ($user->role === 'super_admin') {

            // Quick Stats
            $totalUsers      = User::count();
            $activeUsers     = User::where('is_active', true)->count();
            $totalLeads      = EduLead::count();
            $pendingLeads    = EduLead::where('final_status', 'pending')->count();
            $admittedLeads   = EduLead::where('final_status', 'admitted')->count();
            $hotLeads        = EduLead::where('interest_level', 'hot')->count();

            // This week / month admitted counts
            $admittedThisWeek = EduLead::where('final_status', 'admitted')
                ->whereBetween('admitted_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            $admittedThisMonth = EduLead::where('final_status', 'admitted')
                ->whereYear('admitted_at', now()->year)
                ->whereMonth('admitted_at', now()->month)
                ->count();

            // Followup counts
            $followupData = $this->getFollowupData($user);

            // User role breakdown
            $roleStats = [
                'super_admin'   => User::where('role', 'super_admin')->count(),
                'lead_manager'  => User::where('role', 'lead_manager')->count(),
                'telecallers'   => User::where('role', 'telecallers')->count(),
            ];

            return view('dashboard', compact(
                'totalUsers',
                'activeUsers',
                'totalLeads',
                'pendingLeads',
                'admittedLeads',
                'hotLeads',
                'admittedThisWeek',
                'admittedThisMonth',
                'followupData',
                'roleStats'
            ));
        }

        // ── LEAD MANAGER ─────────────────────────────────────────────
        if ($user->role === 'lead_manager') {

            $myLeads         = EduLead::where('created_by', $user->id)->count();
            $myPending       = EduLead::where('created_by', $user->id)->where('final_status', 'pending')->count();
            $myAdmitted      = EduLead::where('created_by', $user->id)->where('final_status', 'admitted')->count();
            $myHot           = EduLead::where('created_by', $user->id)->where('interest_level', 'hot')->count();

            $admittedThisWeek = EduLead::where('created_by', $user->id)
                ->where('final_status', 'admitted')
                ->whereBetween('admitted_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            $admittedThisMonth = EduLead::where('created_by', $user->id)
                ->where('final_status', 'admitted')
                ->whereYear('admitted_at', now()->year)
                ->whereMonth('admitted_at', now()->month)
                ->count();

            $followupData = $this->getFollowupData($user);

            return view('dashboard', compact(
                'myLeads',
                'myPending',
                'myAdmitted',
                'myHot',
                'admittedThisWeek',
                'admittedThisMonth',
                'followupData'
            ));
        }

        // ── TELECALLERS ───────────────────────────────────────────────
        if ($user->role === 'telecallers') {

            $telecallerStats = [
                'total'          => EduLead::where('assigned_to', $user->id)->count(),
                'pending'        => EduLead::where('assigned_to', $user->id)->where('final_status', 'pending')->count(),
                'contacted'      => EduLead::where('assigned_to', $user->id)->where('final_status', 'contacted')->count(),
                'follow_up'      => EduLead::where('assigned_to', $user->id)->where('final_status', 'follow_up')->count(),
                'not_interested' => EduLead::where('assigned_to', $user->id)->where('final_status', 'not_interested')->count(),
                'admitted'       => EduLead::where('assigned_to', $user->id)->where('final_status', 'admitted')->count(),
            ];

            $followupData = $this->getFollowupData($user);

            return view('dashboard', compact('followupData', 'telecallerStats'));
        }

        return view('dashboard');
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE: Build followup data array
    // ─────────────────────────────────────────────────────────────────
    private function getFollowupData($user)
    {
        $query = EduLeadFollowup::with(['eduLead', 'assignedToUser'])
            ->where('status', 'pending');

        // Role-based scoping
        if ($user->role === 'super_admin') {
            // See all pending followups
        } elseif ($user->role === 'lead_manager') {
            // Own leads\'s followups + followups assigned to them
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhereHas('eduLead', function ($lq) use ($user) {
                      $lq->where('created_by', $user->id);
                  });
            });
        } else {
            // Telecallers: only assigned to them
            $query->where('assigned_to', $user->id);
        }

        // ── COUNTS ───────────────────────────────────────────────────
        $overdue   = (clone $query)->whereDate('followup_date', '<', today())->count();
        $today     = (clone $query)->whereDate('followup_date', today())->count();
        $thisWeek  = (clone $query)->whereBetween('followup_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = (clone $query)->whereBetween('followup_date', [now()->startOfMonth(), now()->endOfMonth()])->count();

        // ── IMMEDIATE (Today + Overdue) ───────────────────────────────
        $immediate = (clone $query)
            ->whereDate('followup_date', '<=', today())
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(20)
            ->get();

        // ── THIS WEEK (future days only) ─────────────────────────────
        $thisWeekFollowups = (clone $query)
            ->whereDate('followup_date', '>', today())
            ->whereDate('followup_date', '<=', now()->endOfWeek())
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(20)
            ->get();

        // ── THIS MONTH (after this week) ─────────────────────────────
        $thisMonthFollowups = (clone $query)
            ->whereDate('followup_date', '>', now()->endOfWeek())
            ->whereDate('followup_date', '<=', now()->endOfMonth())
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('followup_date')
            ->orderBy('followup_time')
            ->limit(30)
            ->get();

        return compact(
            'overdue',
            'today',
            'thisWeek',
            'thisMonth',
            'immediate',
            'thisWeekFollowups',
            'thisMonthFollowups'
        );
    }
}
