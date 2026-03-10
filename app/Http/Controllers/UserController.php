<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\EduLead;
use App\Models\EduLeadFollowup;
use App\Models\EduCallLog;
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

        if ($request->filled('role'))      $query->where('role', $request->role);
        if ($request->filled('status'))    $query->where('is_active', $request->status);
        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);
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
            'email'     => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password'  => ['required', 'min:8', 'confirmed'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['required', Rule::in(array_keys(User::ROLES))],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        if (in_array($validated['role'], ['lead_manager', 'telecaller']) && empty($validated['branch_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Managers and Telecallers must be assigned to a branch.',
                'errors'  => ['branch_id' => ['A branch is required for this role.']],
            ], 422);
        }

        $branchId = in_array($validated['role'], User::BRANCH_FREE_ROLES)
            ? null
            : ($validated['branch_id'] ?? null);

        // ── Check for soft-deleted user with same email ───────────────────
        $deletedUser = User::withTrashed()
            ->where('email', $validated['email'])
            ->whereNotNull('deleted_at')
            ->first();

        if ($deletedUser) {
            // Restore and update with new details
            $deletedUser->restore();
            $deletedUser->update([
                'name'      => $validated['name'],
                'password'  => Hash::make($validated['password']),
                'phone'     => $validated['phone'] ?? null,
                'role'      => $validated['role'],
                'branch_id' => $branchId,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => "User {$deletedUser->name} restored and updated successfully!",
            ]);
        }

        // ── Create fresh user ─────────────────────────────────────────────
        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'branch_id' => $branchId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} created successfully!",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════════════════════════
    public function edit(User $user): JsonResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$authUser->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($user->id === $authUser->id) {
            return response()->json(['success' => false, 'message' => 'You cannot edit your own account here.'], 403);
        }

        return response()->json([
            'success' => true,
            'user'    => $user->only(['id', 'name', 'email', 'phone', 'role', 'branch_id', 'is_active']),
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
            return response()->json(['success' => false, 'message' => 'You cannot update your own account here.'], 403);
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['required', Rule::in(array_keys(User::ROLES))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'password'  => ['nullable', 'min:8', 'confirmed'],
        ]);

        if (in_array($validated['role'], ['lead_manager', 'telecaller']) && empty($validated['branch_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Managers and Telecallers must be assigned to a branch.',
                'errors'  => ['branch_id' => ['A branch is required for this role.']],
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
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account!'], 403);
        }

        EduLead::where('assigned_to', $user->id)->update(['assigned_to' => null]);

        $name = $user->name;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "{$name} has been deleted successfully.",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // SHOW
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

        // ── Assigned leads base query ─────────────────────────────────
        $assigned = fn() => EduLead::where('assigned_to', $user->id)
            ->with(['course', 'assignedTo', 'leadSource']);

        // ── Created leads base query ──────────────────────────────────
        $created = fn() => EduLead::where('created_by', $user->id)
            ->with(['course', 'assignedTo', 'leadSource']);

        // ── Lead query map ────────────────────────────────────────────
        $leadQueryMap = [
            'assigned_leads'         => fn() => $assigned(),
            'leads_pending'          => fn() => $assigned()->where('final_status', 'pending'),
            'leads_contacted'        => fn() => $assigned()->where('final_status', 'contacted'),
            'leads_followup'         => fn() => $assigned()->where('final_status', 'follow_up'),
            'leads_admitted'         => fn() => $assigned()->where('final_status', 'admitted'),
            'leads_not_interested'   => fn() => $assigned()->where('final_status', 'not_interested'),
            'leads_dropped'          => fn() => $assigned()->where('final_status', 'dropped'),
            'leads_hot'              => fn() => $assigned()->where('interest_level', 'hot'),
            'leads_warm'             => fn() => $assigned()->where('interest_level', 'warm'),
            'leads_cold'             => fn() => $assigned()->where('interest_level', 'cold'),
            'created_leads'          => fn() => $created(),
            'created_admitted'       => fn() => $created()->where('final_status', 'admitted'),
            'created_not_interested' => fn() => $created()->where('final_status', 'not_interested'),
        ];

        if (isset($leadQueryMap[$type])) {
            $leads = $leadQueryMap[$type]()->latest()->paginate($perPage);
            return response()->json([
                'html' => view('users.partials.leads', compact('leads'))->render(),
            ]);
        }

        // ── Follow-up queries ─────────────────────────────────────────
        if ($type === 'followups_pending') {
            $followups = EduLeadFollowup::where('assigned_to', $user->id)
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
            $followups = EduLeadFollowup::where('assigned_to', $user->id)
                ->with('eduLead')
                ->where('status', 'pending')
                ->whereDate('followup_date', '<', today())
                ->orderBy('followup_date')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.followups', compact('followups'))->render(),
            ]);
        }

        // ── Call log queries ──────────────────────────────────────────
        if ($type === 'call_logs') {
            $callLogs = EduCallLog::where('user_id', $user->id)
                ->with('eduLead')
                ->latest('call_datetime')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.call-logs', compact('callLogs'))->render(),
            ]);
        }

        if ($type === 'calls_connected') {
            $callLogs = EduCallLog::where('user_id', $user->id)
                ->with('eduLead')
                ->where('call_status', 'connected')
                ->latest('call_datetime')
                ->paginate($perPage);

            return response()->json([
                'html' => view('users.partials.call-logs', compact('callLogs'))->render(),
            ]);
        }

        if ($type === 'calls_not_connected') {
            $callLogs = EduCallLog::where('user_id', $user->id)
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

        $usersQuery = User::where('is_active', true)
            ->whereIn('role', ['lead_manager', 'telecaller']);

        if ($request->filled('role') && in_array($request->role, ['lead_manager', 'telecaller'])) {
            $usersQuery->where('role', $request->role);
        }

        if ($request->filled('branch_id')) {
            $usersQuery->where('branch_id', $request->branch_id);
        }

        $users = $usersQuery->with('branch:id,name')->get();

        $leaderboard = $users->map(function (User $user) use ($startDate, $endDate) {

            $assignedInPeriod = EduLead::where('assigned_to', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $assignedTotal = EduLead::where('assigned_to', $user->id)->count();

            $admitted = EduLead::where('assigned_to', $user->id)
                ->where('final_status', 'admitted')
                ->whereBetween('admitted_at', [$startDate, $endDate])
                ->count();

            $hot = EduLead::where('assigned_to', $user->id)
                ->where('interest_level', 'hot')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $contacted = EduLead::where('assigned_to', $user->id)
                ->where('final_status', 'contacted')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $followUp = EduLead::where('assigned_to', $user->id)
                ->where('final_status', 'follow_up')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $callsTotal = EduCallLog::where('user_id', $user->id)
                ->whereBetween('call_datetime', [$startDate, $endDate])
                ->count();

            $callsConnected = EduCallLog::where('user_id', $user->id)
                ->where('call_status', 'connected')
                ->whereBetween('call_datetime', [$startDate, $endDate])
                ->count();

            $callsNotConnected = EduCallLog::where('user_id', $user->id)
                ->where('call_status', 'not_connected')
                ->whereBetween('call_datetime', [$startDate, $endDate])
                ->count();

            $connectionRate = $callsTotal > 0
                ? round(($callsConnected / $callsTotal) * 100, 1) : 0;

            $followupsCompleted = EduLeadFollowup::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->count();

            $overdueFollowups = EduLeadFollowup::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->whereDate('followup_date', '<', today())
                ->count();

            $followupsPending = EduLeadFollowup::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->whereDate('followup_date', '>=', today())
                ->count();

            $admissionRate = $assignedInPeriod > 0
                ? round(($admitted / $assignedInPeriod) * 100, 1)
                : ($assignedTotal > 0 ? round(($admitted / $assignedTotal) * 100, 1) : 0);

            $totalFollowupsInPeriod = $followupsCompleted + $overdueFollowups + $followupsPending;
            $followupCompletionRate = $totalFollowupsInPeriod > 0
                ? round(($followupsCompleted / $totalFollowupsInPeriod) * 100, 1) : 0;

            $score = 0;
            $hasMeaningfulActivity = ($admitted > 0 || $hot > 0 || $contacted > 0
                || $followUp > 0 || $callsConnected > 0 || $assignedInPeriod > 0);

            if ($hasMeaningfulActivity) {
                $score += $admitted           * 10;
                $score += $hot                * 4;
                $score += $followupsCompleted * 2;
                $score += $callsConnected     * 1;
                $score += $contacted          * 0.5;
                $score += $followUp           * 0.5;

                if ($admissionRate >= 50 && $admitted > 0) $score += $admitted * 5;
                if ($connectionRate >= 60 && $callsTotal >= 5) $score += 10;

                $score -= $overdueFollowups * 3;
                $score = max(0, round($score, 1));
            }

            return [
                'id'                       => $user->id,
                'name'                     => $user->name,
                'email'                    => $user->email,
                'role'                     => $user->role,
                'branch'                   => $user->branch?->name ?? '—',
                'assigned'                 => $assignedInPeriod,
                'assigned_total'           => $assignedTotal,
                'hot_leads'                => $hot,
                'contacted'                => $contacted,
                'follow_up'                => $followUp,
                'admitted'                 => $admitted,
                'admission_rate'           => $admissionRate,
                'calls_total'              => $callsTotal,
                'calls_connected'          => $callsConnected,
                'calls_not_connected'      => $callsNotConnected,
                'connection_rate'          => $connectionRate,
                'followups_pending'        => $followupsPending,
                'followups_completed'      => $followupsCompleted,
                'followup_completion_rate' => $followupCompletionRate,
                'overdue_followups'        => $overdueFollowups,
                'score'                    => $score,
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

    private function buildUserStats(User $user): array
    {
        $stats = [
            'totalAssignedLeads'        => 0,
            'leadsPending'              => 0,
            'leadsContacted'            => 0,
            'leadsFollowUp'             => 0,
            'leadsAdmitted'             => 0,
            'leadsNotInterested'        => 0,
            'leadsDropped'              => 0,
            'leadsHot'                  => 0,
            'leadsWarm'                 => 0,
            'leadsCold'                 => 0,
            'conversionRate'            => 0,
            'followupsPending'          => 0,
            'followupsOverdue'          => 0,
            'totalCallLogs'             => 0,
            'callsToday'                => 0,
            'totalCreatedLeads'         => 0,
            'createdLeadsAdmitted'      => 0,
            'createdLeadsNotInterested' => 0,
        ];

        if (in_array($user->role, ['telecaller', 'lead_manager'])) {
            $stats['totalAssignedLeads'] = EduLead::where('assigned_to', $user->id)->count();
            $stats['leadsPending']       = EduLead::where('assigned_to', $user->id)->where('final_status', 'pending')->count();
            $stats['leadsContacted']     = EduLead::where('assigned_to', $user->id)->where('final_status', 'contacted')->count();
            $stats['leadsFollowUp']      = EduLead::where('assigned_to', $user->id)->where('final_status', 'follow_up')->count();
            $stats['leadsAdmitted']      = EduLead::where('assigned_to', $user->id)->where('final_status', 'admitted')->count();
            $stats['leadsNotInterested'] = EduLead::where('assigned_to', $user->id)->where('final_status', 'not_interested')->count();
            $stats['leadsDropped']       = EduLead::where('assigned_to', $user->id)->where('final_status', 'dropped')->count();
            $stats['leadsHot']           = EduLead::where('assigned_to', $user->id)->where('interest_level', 'hot')->count();
            $stats['leadsWarm']          = EduLead::where('assigned_to', $user->id)->where('interest_level', 'warm')->count();
            $stats['leadsCold']          = EduLead::where('assigned_to', $user->id)->where('interest_level', 'cold')->count();

            $stats['conversionRate'] = $stats['totalAssignedLeads'] > 0
                ? round(($stats['leadsAdmitted'] / $stats['totalAssignedLeads']) * 100, 1)
                : 0;

            $stats['followupsPending'] = EduLeadFollowup::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->whereDate('followup_date', '>=', today())
                ->count();

            $stats['followupsOverdue'] = EduLeadFollowup::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->whereDate('followup_date', '<', today())
                ->count();

            $stats['totalCallLogs'] = EduCallLog::where('user_id', $user->id)->count();
            $stats['callsToday']    = EduCallLog::where('user_id', $user->id)
                ->whereDate('call_datetime', today())
                ->count();
        }

        if (in_array($user->role, ['super_admin', 'operation_head'])) {
            $stats['totalCreatedLeads']         = EduLead::where('created_by', $user->id)->count();
            $stats['createdLeadsAdmitted']      = EduLead::where('created_by', $user->id)->where('final_status', 'admitted')->count();
            $stats['createdLeadsNotInterested'] = EduLead::where('created_by', $user->id)->where('final_status', 'not_interested')->count();
        }

        return $stats;
    }

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
                $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth(),
                $request->filled('end_date')   ? Carbon::parse($request->end_date)   : Carbon::now(),
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()],
        };
    }
}
