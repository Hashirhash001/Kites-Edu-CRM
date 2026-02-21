<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\EduLead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ══════════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->isLeadManager()) {
            abort(403, 'Unauthorized access');
        }

        $query = User::with(['branch'])
            ->where('id', '!=', $currentUser->id);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users    = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $branches = Branch::active()->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('users.partials.table-rows', compact('users'))->render(),
                'pagination' => $users->links('pagination::bootstrap-5')->render(),
                'total'      => $users->total(),
            ]);
        }

        return view('users.index', compact('users', 'branches'));
    }

    // ══════════════════════════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════════════════════════
    public function store(Request $request): JsonResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$authUser->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'min:8', 'confirmed'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['required', Rule::in(array_keys(User::ROLES))],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        if ($validated['role'] === 'lead_manager' && empty($validated['branch_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Manager must be assigned to a branch.',
            ], 422);
        }

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'branch_id' => in_array($validated['role'], User::BRANCH_FREE_ROLES)
                            ? null
                            : ($validated['branch_id'] ?? null),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} created successfully!",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // EDIT (returns JSON for modal)
    // ══════════════════════════════════════════════════════════════════
    public function edit(User $user): JsonResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$authUser->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($user->id === $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot edit your own account here.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user'    => $user->only([
                'id', 'name', 'email', 'phone',
                'role', 'branch_id', 'is_active',
            ]),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════════════════════════
    public function update(Request $request, User $user): JsonResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$authUser->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($user->id === $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot update your own account here.',
            ], 403);
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['required', Rule::in(array_keys(User::ROLES))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'password'  => ['nullable', 'min:8', 'confirmed'],
        ]);

        if ($validated['role'] === 'lead_manager' && empty($validated['branch_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Manager must be assigned to a branch.',
            ], 422);
        }

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'branch_id' => in_array($validated['role'], User::BRANCH_FREE_ROLES)
                            ? null
                            : ($validated['branch_id'] ?? null),
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} updated successfully!",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════════════════════════
    public function destroy(User $user): JsonResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$authUser->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($user->id === $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account!',
            ], 403);
        }

        // Unassign leads so no orphaned foreign keys remain
        EduLead::where('assigned_to', $user->id)->update(['assigned_to' => null]);

        $name = $user->name;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "{$name} has been deleted successfully.",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // SHOW (profile / stats page)
    // ══════════════════════════════════════════════════════════════════
    public function show(User $user)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if ($authUser->isLeadManager() && $authUser->id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $stats = $this->buildUserStats($user);

        return view('users.show', array_merge(compact('user'), $stats));
    }

    // ══════════════════════════════════════════════════════════════════
    // DETAILS (AJAX drilldown)
    // ══════════════════════════════════════════════════════════════════
    public function details(User $user, string $type, Request $request): JsonResponse
    {
        $perPage = 15;

        // ── Lead query builders ───────────────────────────────────────
        $assigned = fn() => $user->assignedEduLeads()
            ->with(['course', 'assignedTo', 'leadSource']);

        $created = fn() => $user->createdEduLeads()
            ->with(['course', 'assignedTo', 'leadSource']);

        $leadQueryMap = [
            'assigned_leads'         => $assigned(),
            'leads_pending'          => $assigned()->where('final_status', 'pending'),
            'leads_contacted'        => $assigned()->where('final_status', 'contacted'),
            'leads_followup'         => $assigned()->where('final_status', 'follow_up'),
            'leads_admitted'         => $assigned()->where('final_status', 'admitted'),
            'leads_not_interested'   => $assigned()->where('final_status', 'not_interested'),
            'leads_dropped'          => $assigned()->where('final_status', 'dropped'),
            'leads_hot'              => $assigned()->where('interest_level', 'hot'),
            'leads_warm'             => $assigned()->where('interest_level', 'warm'),
            'leads_cold'             => $assigned()->where('interest_level', 'cold'),
            'created_leads'          => $created(),
            'created_admitted'       => $created()->where('final_status', 'admitted'),
            'created_not_interested' => $created()->where('final_status', 'not_interested'),
        ];

        if (isset($leadQueryMap[$type])) {
            $leads = $leadQueryMap[$type]->latest()->paginate($perPage);
            return response()->json([
                'html' => view('users.partials.leads', compact('leads'))->render(),
            ]);
        }

        // ── Follow-up queries ─────────────────────────────────────────
        if ($type === 'followups_pending') {
            $followups = $user->assignedEduFollowups()
                ->with('eduLead')
                ->where('status', 'pending')
                ->whereDate('followup_date', '>=', today())
                ->orderBy('followup_date')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.followups', compact('followups'))->render(),
            ]);
        }

        if ($type === 'followups_overdue') {
            $followups = $user->assignedEduFollowups()
                ->with('eduLead')
                ->where('status', 'pending')
                ->whereDate('followup_date', '<', today())
                ->orderBy('followup_date')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.followups', compact('followups'))->render(),
            ]);
        }

        // ── Call logs — now includes call_status breakdown ────────────
        if ($type === 'call_logs') {
            $callLogs = $user->eduCallLogs()
                ->with('eduLead')
                ->latest('call_datetime')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.call-logs', compact('callLogs'))->render(),
            ]);
        }

        if ($type === 'calls_connected') {
            $callLogs = $user->eduCallLogs()
                ->with('eduLead')
                ->where('call_status', 'connected')
                ->latest('call_datetime')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.call-logs', compact('callLogs'))->render(),
            ]);
        }

        if ($type === 'calls_not_connected') {
            $callLogs = $user->eduCallLogs()
                ->with('eduLead')
                ->where('call_status', 'not_connected')
                ->latest('call_datetime')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.call-logs', compact('callLogs'))->render(),
            ]);
        }

        return response()->json([
            'html' => '<p class="text-muted text-center py-5">Unknown detail type.</p>',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // PERFORMANCE PAGE
    // ══════════════════════════════════════════════════════════════════
    public function performance()
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if ($authUser->isLeadManager()) {
            abort(403);
        }

        $branches = Branch::active()->orderBy('name')->get();

        return view('users.performance', compact('branches'));
    }

    // ══════════════════════════════════════════════════════════════════
    // PERFORMANCE DATA (AJAX)
    // ══════════════════════════════════════════════════════════════════
    public function performanceData(Request $request): JsonResponse
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        // Both lead_manager and telecaller appear in the leaderboard
        $usersQuery = User::where('is_active', true)
            ->whereIn('role', ['lead_manager', 'telecaller']);

        // Optional role filter from the new dropdown
        if ($request->filled('role') && in_array($request->role, ['lead_manager', 'telecaller'])) {
            $usersQuery->where('role', $request->role);
        }

        if ($request->filled('branch_id')) {
            $usersQuery->where('branch_id', $request->branch_id);
        }

        $users = $usersQuery->with('branch:id,name')->get();

        $leaderboard = $users->map(function (User $user) use ($startDate, $endDate) {

            $assigned = $user->assignedEduLeads()->count();

            $admitted = $user->assignedEduLeads()
                ->where('final_status', 'admitted')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $hot = $user->assignedEduLeads()
                ->where('interest_level', 'hot')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $contacted = $user->assignedEduLeads()
                ->where('final_status', 'contacted')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $followUp = $user->assignedEduLeads()
                ->where('final_status', 'follow_up')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            // ── Call stats split by status ────────────────────────────────
            $callsTotal = $user->eduCallLogs()
                ->whereBetween('call_datetime', [$startDate, $endDate])
                ->count();

            $callsConnected = $user->eduCallLogs()
                ->where('call_status', 'connected')
                ->whereBetween('call_datetime', [$startDate, $endDate])
                ->count();

            $callsNotConnected = $user->eduCallLogs()
                ->where('call_status', 'not_connected')
                ->whereBetween('call_datetime', [$startDate, $endDate])
                ->count();

            $connectionRate = $callsTotal > 0
                ? round(($callsConnected / $callsTotal) * 100, 1)
                : 0;

            $followupsPending = $user->assignedEduFollowups()
                ->where('status', 'pending')
                ->whereDate('followup_date', '>=', today())
                ->count();

            $overdueFollowups = $user->assignedEduFollowups()
                ->where('status', 'pending')
                ->whereDate('followup_date', '<', today())
                ->count();

            $totalAdmitted = $user->assignedEduLeads()
                ->where('final_status', 'admitted')
                ->count();

            $admissionRate = $assigned > 0
                ? round(($totalAdmitted / $assigned) * 100, 1)
                : 0;

            // Score: admitted ×3, connected calls ×1, hot ×2, overdue −1
            $score = ($admitted * 3) + $callsConnected + ($hot * 2) - $overdueFollowups;

            return [
                'id'                  => $user->id,
                'name'                => $user->name,
                'email'               => $user->email,
                'role'                => $user->role,
                'branch'              => $user->branch?->name ?? '—',
                'assigned'            => $assigned,
                'hot_leads'           => $hot,
                'contacted'           => $contacted,
                'follow_up'           => $followUp,
                'admitted'            => $admitted,
                'admission_rate'      => $admissionRate,
                'calls_total'         => $callsTotal,
                'calls_connected'     => $callsConnected,
                'calls_not_connected' => $callsNotConnected,
                'connection_rate'     => $connectionRate,
                'followups_pending'   => $followupsPending,
                'overdue_followups'   => $overdueFollowups,
                'score'               => $score,
            ];
        })
        ->sortByDesc('score')
        ->values()
        ->map(fn ($item, $i) => array_merge($item, ['rank' => $i + 1]));

        $totalAssigned  = $leaderboard->sum('assigned');
        $totalAdmitted  = $leaderboard->sum('admitted');
        $totalCalls     = $leaderboard->sum('calls_total');
        $totalConnected = $leaderboard->sum('calls_connected');

        return response()->json([
            'success'     => true,
            'leaderboard' => $leaderboard,
            'summary'     => [
                'total_assigned'      => $totalAssigned,
                'total_admitted'      => $totalAdmitted,
                'avg_admission_rate'  => $totalAssigned > 0
                    ? round(($totalAdmitted / $totalAssigned) * 100, 1) : 0,
                'total_hot'           => $leaderboard->sum('hot_leads'),
                'total_calls'         => $totalCalls,
                'total_connected'     => $totalConnected,
                'total_not_connected' => $leaderboard->sum('calls_not_connected'),
                'avg_connection_rate' => $totalCalls > 0
                    ? round(($totalConnected / $totalCalls) * 100, 1) : 0,
            ],
            'period'     => $request->get('period', 'month'),
            'start_date' => $startDate->format('d M Y'),
            'end_date'   => $endDate->format('d M Y'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Build stats array for the show() view.
     * Always returns all keys so Blade never gets an undefined variable.
     */
    private function buildUserStats(User $user): array
    {
        $base = [
            'totalAssignedLeads'        => 0,
            'leadsPending'              => 0,
            'leadsContacted'            => 0,
            'leadsFollowUp'             => 0,
            'leadsAdmitted'             => 0,
            'leadsNotInterested'        => 0,
            'leadsDropped'              => 0,
            'conversionRate'            => 0,
            'leadsHot'                  => 0,
            'leadsWarm'                 => 0,
            'leadsCold'                 => 0,
            'followupsPending'          => 0,
            'followupsOverdue'          => 0,
            'totalCallLogs'             => 0,
            'callsConnected'            => 0,
            'callsNotConnected'         => 0,
            'connectionRate'            => 0,
            'callsToday'                => 0,
            'callsConnectedToday'       => 0,
            'totalCreatedLeads'         => 0,
            'createdLeadsAdmitted'      => 0,
            'createdLeadsNotInterested' => 0,
        ];

        // ── Lead Manager ──────────────────────────────────────────────
        if ($user->isLeadManager()) {
            $q       = $user->assignedEduLeads();
            $total   = $q->count();
            $admitted = (clone $q)->where('final_status', 'admitted')->count();

            $callsTotal     = $user->eduCallLogs()->count();
            $callsConnected = $user->eduCallLogs()
                ->where('call_status', 'connected')
                ->count();
            $callsNotConn   = $user->eduCallLogs()
                ->where('call_status', 'not_connected')
                ->count();

            $callsTodayTotal     = $user->eduCallLogs()
                ->whereDate('call_datetime', today())
                ->count();
            $callsTodayConnected = $user->eduCallLogs()
                ->where('call_status', 'connected')
                ->whereDate('call_datetime', today())
                ->count();

            return array_merge($base, [
                'totalAssignedLeads'   => $total,
                'leadsPending'         => (clone $q)->where('final_status', 'pending')->count(),
                'leadsContacted'       => (clone $q)->where('final_status', 'contacted')->count(),
                'leadsFollowUp'        => (clone $q)->where('final_status', 'follow_up')->count(),
                'leadsAdmitted'        => $admitted,
                'leadsNotInterested'   => (clone $q)->where('final_status', 'not_interested')->count(),
                'leadsDropped'         => (clone $q)->where('final_status', 'dropped')->count(),
                'conversionRate'       => $total > 0
                    ? round(($admitted / $total) * 100, 1)
                    : 0,
                'leadsHot'             => (clone $q)->where('interest_level', 'hot')->count(),
                'leadsWarm'            => (clone $q)->where('interest_level', 'warm')->count(),
                'leadsCold'            => (clone $q)->where('interest_level', 'cold')->count(),
                'followupsPending'     => $user->assignedEduFollowups()
                                              ->where('status', 'pending')
                                              ->whereDate('followup_date', '>=', today())
                                              ->count(),
                'followupsOverdue'     => $user->assignedEduFollowups()
                                              ->where('status', 'pending')
                                              ->whereDate('followup_date', '<', today())
                                              ->count(),
                'totalCallLogs'        => $callsTotal,
                'callsConnected'       => $callsConnected,
                'callsNotConnected'    => $callsNotConn,
                'connectionRate'       => $callsTotal > 0
                    ? round(($callsConnected / $callsTotal) * 100, 1)
                    : 0,
                'callsToday'           => $callsTodayTotal,
                'callsConnectedToday'  => $callsTodayConnected,
            ]);
        }

        // ── Super Admin / Operation Head ──────────────────────────────
        if ($user->isSuperAdmin() || $user->isOperationHead()) {
            $cq    = $user->createdEduLeads();
            $total = $cq->count();

            return array_merge($base, [
                'totalCreatedLeads'         => $total,
                'createdLeadsAdmitted'      => (clone $cq)
                    ->where('final_status', 'admitted')->count(),
                'createdLeadsNotInterested' => (clone $cq)
                    ->where('final_status', 'not_interested')->count(),
            ]);
        }

        return $base;
    }

    /**
     * Resolve Carbon start/end dates from the request period param.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(Request $request): array
    {
        $period = $request->get('period', 'month');

        return match ($period) {
            'day'        => [Carbon::today(),                           Carbon::now()],
            'week'       => [Carbon::now()->startOfWeek(),              Carbon::now()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            '6months'    => [Carbon::now()->subMonths(6),               Carbon::now()],
            'year'       => [Carbon::now()->startOfYear(),              Carbon::now()],
            'last_year'  => [Carbon::now()->subYear()->startOfYear(),   Carbon::now()->subYear()->endOfYear()],
            'custom'     => [
                $request->filled('start_date')
                    ? Carbon::parse($request->start_date)
                    : Carbon::now()->startOfMonth(),
                $request->filled('end_date')
                    ? Carbon::parse($request->end_date)
                    : Carbon::now(),
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()],
        };
    }
}
