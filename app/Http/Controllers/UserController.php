<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\EduLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $currentUser = auth()->user();

        // Telecallers: no access
        if ($currentUser->role === 'telecallers') {
            abort(403, 'Unauthorized access');
        }

        $query = User::where('id', '!=', auth()->id());

        // Lead managers can ONLY see telecallers
        if ($currentUser->role === 'lead_manager') {
            $query->where('role', 'telecallers');
        }

        // Filters
        if ($request->filled('role') && $currentUser->role !== 'lead_manager') {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('users.partials.table-rows', compact('users'))->render(),
                'pagination' => $users->links('pagination::bootstrap-5')->render(),
                'total'      => $users->total(),
            ]);
        }

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|min:8|confirmed',
            'phone'                 => 'nullable|string|max:20',
            'role'                  => 'required|in:super_admin,lead_manager,telecallers',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return response()->json(['success' => true, 'message' => 'User created successfully!']);
    }

    public function edit(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot edit your own account!'], 403);
        }

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot update your own account!'], 403);
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role'  => 'required|in:super_admin,lead_manager,telecallers',
        ]);

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return response()->json(['success' => true, 'message' => 'User updated successfully!']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account!'], 403);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully!']);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        $stats = [];

        // ── Telecallers & Lead Managers ──────────────────────────────────
        if (in_array($user->role, ['telecallers', 'lead_manager'])) {

            $assignedLeads = $user->assignedEduLeads();

            $totalAssignedLeads  = $assignedLeads->count();
            $leadsPending        = $assignedLeads->clone()->where('final_status', 'pending')->count();
            $leadsContacted      = $assignedLeads->clone()->where('final_status', 'contacted')->count();
            $leadsFollowUp       = $assignedLeads->clone()->where('final_status', 'follow_up')->count();
            $leadsAdmitted       = $assignedLeads->clone()->where('final_status', 'admitted')->count();
            $leadsNotInterested  = $assignedLeads->clone()->where('final_status', 'not_interested')->count();
            $leadsDropped        = $assignedLeads->clone()->where('final_status', 'dropped')->count();
            $conversionRate      = $totalAssignedLeads > 0
                                    ? round(($leadsAdmitted / $totalAssignedLeads) * 100, 1)
                                    : 0;

            $leadsHot   = $assignedLeads->clone()->where('interest_level', 'hot')->count();
            $leadsWarm  = $assignedLeads->clone()->where('interest_level', 'warm')->count();
            $leadsCold  = $assignedLeads->clone()->where('interest_level', 'cold')->count();

            $followupsPending = $user->assignedEduFollowups()
                                    ->where('status', 'pending')
                                    ->whereDate('followup_date', '>=', today())
                                    ->count();

            $followupsOverdue = $user->assignedEduFollowups()
                                    ->where('status', 'pending')
                                    ->whereDate('followup_date', '<', today())
                                    ->count();

            $totalCallLogs = $user->eduCallLogs()->count();
            $callsToday    = $user->eduCallLogs()->whereDate('call_datetime', today())->count();

            // pass defaults for super_admin vars so blade doesn't throw
            $totalCreatedLeads         = 0;
            $createdLeadsAdmitted      = 0;
            $createdLeadsNotInterested = 0;

        // ── Super Admin ──────────────────────────────────────────────────
        } elseif ($user->role === 'super_admin') {

            $createdLeads              = $user->createdEduLeads();
            $totalCreatedLeads         = $createdLeads->count();
            $createdLeadsAdmitted      = $createdLeads->clone()->where('final_status', 'admitted')->count();
            $createdLeadsNotInterested = $createdLeads->clone()->where('final_status', 'not_interested')->count();

            // pass defaults for telecaller vars
            $totalAssignedLeads  = $leadsPending = $leadsContacted = $leadsFollowUp = 0;
            $leadsAdmitted       = $leadsNotInterested = $leadsDropped = 0;
            $conversionRate      = $leadsHot = $leadsWarm = $leadsCold = 0;
            $followupsPending    = $followupsOverdue = $totalCallLogs = $callsToday = 0;

        // ── Other roles (reporting_user etc.) ───────────────────────────
        } else {

            $totalAssignedLeads  = $leadsPending = $leadsContacted = $leadsFollowUp = 0;
            $leadsAdmitted       = $leadsNotInterested = $leadsDropped = 0;
            $conversionRate      = $leadsHot = $leadsWarm = $leadsCold = 0;
            $followupsPending    = $followupsOverdue = $totalCallLogs = $callsToday = 0;
            $totalCreatedLeads   = $createdLeadsAdmitted = $createdLeadsNotInterested = 0;

        }

        return view('users.show', compact(
            'user',
            'totalAssignedLeads', 'leadsPending', 'leadsContacted', 'leadsFollowUp',
            'leadsAdmitted', 'leadsNotInterested', 'leadsDropped', 'conversionRate',
            'leadsHot', 'leadsWarm', 'leadsCold',
            'followupsPending', 'followupsOverdue', 'totalCallLogs', 'callsToday',
            'totalCreatedLeads', 'createdLeadsAdmitted', 'createdLeadsNotInterested'
        ));
    }

    public function details($userId, $type, Request $request)
    {
        $user = User::findOrFail($userId);
        $perPage = 15;

        // ── LEAD TYPES ───────────────────────────────────────────────────────
        $leadQuery = match(true) {
            $type === 'assigned_leads'        => $user->assignedEduLeads(),
            $type === 'leads_pending'         => $user->assignedEduLeads()->where('final_status', 'pending'),
            $type === 'leads_contacted'       => $user->assignedEduLeads()->where('final_status', 'contacted'),
            $type === 'leads_followup'        => $user->assignedEduLeads()->where('final_status', 'follow_up'),
            $type === 'leads_admitted'        => $user->assignedEduLeads()->where('final_status', 'admitted'),
            $type === 'leads_not_interested'  => $user->assignedEduLeads()->where('final_status', 'not_interested'),
            $type === 'leads_dropped'         => $user->assignedEduLeads()->where('final_status', 'dropped'),
            $type === 'leads_hot'             => $user->assignedEduLeads()->where('interest_level', 'hot'),
            $type === 'leads_warm'            => $user->assignedEduLeads()->where('interest_level', 'warm'),
            $type === 'leads_cold'            => $user->assignedEduLeads()->where('interest_level', 'cold'),
            $type === 'created_leads'         => $user->createdEduLeads(),
            $type === 'created_admitted'      => $user->createdEduLeads()->where('final_status', 'admitted'),
            $type === 'created_not_interested'=> $user->createdEduLeads()->where('final_status', 'not_interested'),
            default => null,
        };

        if ($leadQuery !== null) {
            $leads = $leadQuery
                ->with(['course', 'assignedTo', 'leadSource'])
                ->latest()
                ->paginate($perPage);

            $html = view('users.partials.leads', compact('leads'))->render();
            return response()->json(['html' => $html]);
        }

        // ── FOLLOW-UP TYPES ──────────────────────────────────────────────────
        if ($type === 'followups_pending') {
            $followups = $user->assignedEduFollowups()
                ->with('eduLead')
                ->where('status', 'pending')
                ->whereDate('followup_date', '>=', today())
                ->orderBy('followup_date')
                ->paginate($perPage);

            $html = view('users.partials.followups', compact('followups'))->render();
            return response()->json(['html' => $html]);
        }

        if ($type === 'followups_overdue') {
            $followups = $user->assignedEduFollowups()
                ->with('eduLead')
                ->where('status', 'pending')
                ->whereDate('followup_date', '<', today())
                ->orderBy('followup_date')
                ->paginate($perPage);

            $html = view('users.partials.followups', compact('followups'))->render();
            return response()->json(['html' => $html]);
        }

        // ── CALL LOGS ────────────────────────────────────────────────────────
        if ($type === 'call_logs') {
            $callLogs = $user->eduCallLogs()
                ->with('eduLead')
                ->latest('call_datetime')
                ->paginate($perPage);

            $html = view('users.partials.call-logs', compact('callLogs'))->render();
            return response()->json(['html' => $html]);
        }

        return response()->json(['html' => '<p class="text-muted text-center py-5">Unknown detail type.</p>']);
    }

    public function performance()
    {
        return view('users.performance');
    }

    public function performanceData(Request $request)
    {
        $authUser = auth()->user();

        // ── Date range ───────────────────────────────────────────────────────
        $period    = $request->get('period', 'month');
        $startDate = null;
        $endDate   = null;

        switch ($period) {
            case 'day':
                $startDate = Carbon::today();
                $endDate   = Carbon::now();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate   = Carbon::now();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case '6months':
                $startDate = Carbon::now()->subMonths(6);
                $endDate   = Carbon::now();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate   = Carbon::now();
                break;
            case 'last_year':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate   = Carbon::now()->subYear()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->filled('start_date')
                    ? Carbon::parse($request->get('start_date'))
                    : Carbon::now()->startOfMonth();
                $endDate = $request->filled('end_date')
                    ? Carbon::parse($request->get('end_date'))
                    : Carbon::now();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now();
        }

        // ── Users query ──────────────────────────────────────────────────────
        // Only roles that handle edu-leads are meaningful on the leaderboard
        $usersQuery = User::where('is_active', true)
            ->where('role', 'telecallers');

        $users = $usersQuery->get();

        // ── Per-user metrics ─────────────────────────────────────────────────
        $leaderboard = $users->map(function ($user) use ($startDate, $endDate) {

            // Total assigned leads (ever, not date-filtered — same as show page)
            $assigned = $user->assignedEduLeads()->count();

            // Leads updated within the period (for activity-based ranking)
            $hot      = $user->assignedEduLeads()
                            ->where('interest_level', 'hot')
                            ->whereBetween('updated_at', [$startDate, $endDate])
                            ->count();

            $contacted = $user->assignedEduLeads()
                            ->where('final_status', 'contacted')
                            ->whereBetween('updated_at', [$startDate, $endDate])
                            ->count();

            $followUp  = $user->assignedEduLeads()
                            ->where('final_status', 'follow_up')
                            ->whereBetween('updated_at', [$startDate, $endDate])
                            ->count();

            // Admitted within the period
            $admitted  = $user->assignedEduLeads()
                            ->where('final_status', 'admitted')
                            ->whereBetween('updated_at', [$startDate, $endDate])
                            ->count();

            // Admission rate against total assigned (not just period)
            $admissionRate = $assigned > 0
                ? round(($user->assignedEduLeads()->where('final_status', 'admitted')->count() / $assigned) * 100, 1)
                : 0;

            // Call logs within period
            $callsLogged = $user->eduCallLogs()
                                ->whereBetween('call_datetime', [$startDate, $endDate])
                                ->count();

            // Pending follow-ups (not date-filtered — current state)
            $followupsPending = $user->assignedEduFollowups()
                                    ->where('status', 'pending')
                                    ->whereDate('followup_date', '>=', today())
                                    ->count();

            // Overdue follow-ups (current state)
            $overdueFollowups = $user->assignedEduFollowups()
                                    ->where('status', 'pending')
                                    ->whereDate('followup_date', '<', today())
                                    ->count();

            // Score: admitted × 3 + calls_logged × 1 + hot × 2 - overdue × 1
            // Adjust weights to your preference
            $score = ($admitted * 3) + ($callsLogged * 1) + ($hot * 2) - ($overdueFollowups * 1);

            return [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'assigned'          => $assigned,
                'hot_leads'         => $hot,
                'contacted'         => $contacted,
                'follow_up'         => $followUp,
                'admitted'          => $admitted,
                'admission_rate'    => $admissionRate,
                'calls_logged'      => $callsLogged,
                'followups_pending' => $followupsPending,
                'overdue_followups' => $overdueFollowups,
                'score'             => $score,
            ];
        });

        // ── Sort by score (highest first) ────────────────────────────────────
        $leaderboard = $leaderboard->sortByDesc('score')->values();

        // ── Add rank ─────────────────────────────────────────────────────────
        $leaderboard = $leaderboard->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        // ── Summary ──────────────────────────────────────────────────────────
        $totalAssigned = $leaderboard->sum('assigned');
        $totalAdmitted = $leaderboard->sum('admitted');

        $summary = [
            'total_assigned'     => $totalAssigned,
            'total_admitted'     => $totalAdmitted,
            'avg_admission_rate' => $totalAssigned > 0
                                        ? round(($totalAdmitted / $totalAssigned) * 100, 1)
                                        : 0,
            'total_hot'          => $leaderboard->sum('hot_leads'),
            'total_calls'        => $leaderboard->sum('calls_logged'),
        ];

        return response()->json([
            'success'    => true,
            'leaderboard' => $leaderboard,
            'summary'    => $summary,
            'period'     => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
        ]);
    }

}
