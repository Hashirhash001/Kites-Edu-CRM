<?php


namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
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


        $query = User::with('branch')
            ->where('id', '!=', auth()->id());


        // Role-based access control
        if ($currentUser->role === 'lead_manager') {
            // Lead managers can ONLY see telecallers from their branch
            $query->where('branch_id', $currentUser->branch_id)
                ->where('role', 'telecallers');
        } elseif ($currentUser->role === 'telecallers') {
            // Telecallers cannot access user management (optional - add abort if needed)
            abort(403, 'Unauthorized access');
        }
        // Super admin sees everyone (default - no additional filter)


        // Apply filters (note: role filter should respect lead_manager restrictions)
        if ($request->filled('role')) {
            // If lead_manager, ignore role filter since they can only see telecallers
            if ($currentUser->role !== 'lead_manager') {
                $query->where('role', $request->role);
            }
        }


        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }


        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
            });
        }


        // Paginate results
        $users = $query->orderBy('created_at', 'desc')->paginate(15);


        $branches = Branch::where('is_active', true)->get();


        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('users.partials.table-rows', compact('users'))->render(),
                'pagination' => $users->links('pagination::bootstrap-5')->render(),
                'total' => $users->total()
            ]);
        }


        return view('users.index', compact('users', 'branches'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|in:super_admin,lead_manager,field_staff,telecallers',
        ]);


        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'User created successfully!'
        ]);
    }


    public function edit(User $user)
    {
        // Prevent editing own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot edit your own account!'
            ], 403);
        }


        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }


    public function update(Request $request, User $user)
    {
        // Prevent updating own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot update your own account!'
            ], 403);
        }


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|in:super_admin,lead_manager,field_staff,telecallers',
        ]);


        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!'
        ]);
    }


    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account!'
            ], 403);
        }


        $user->delete();


        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }


    public function show(User $user)
    {
        $currentUser = auth()->user();


        if ($currentUser->role === 'lead_manager') {
            if ($user->branch_id !== $currentUser->branch_id) {
                abort(403, 'You can only view users from your branch.');
            }
        }


        $user->load([
            'branch',
            'createdLeads.services',
            'assignedLeads.services',
        ]);


        // ==================== ASSIGNED LEADS STATISTICS ====================


        $assignedLeads = $user->assignedLeads;
        $totalAssignedLeads = $assignedLeads->count();
        $assignedConfirmedLeads = $assignedLeads->where('status', 'confirmed')->count();
        $assignedApprovedLeads = $assignedLeads->where('status', 'approved')->count();


        $assignedLeadsValue = $assignedLeads->sum('amount');
        $assignedConfirmedLeadsValue = $assignedLeads->where('status', 'confirmed')->sum('amount');
        $assignedApprovedLeadsValue = $assignedLeads->where('status', 'approved')->sum('amount');


        $assignedConversionRate = $totalAssignedLeads > 0
            ? round(($assignedApprovedLeads / $totalAssignedLeads) * 100, 2)
            : 0;


        $assignedAllLeadsList = $assignedLeads->take(20);
        $assignedConfirmedLeadsList = $assignedLeads->where('status', 'confirmed')->take(20);
        $assignedApprovedLeadsList = $assignedLeads->where('status', 'approved')->take(20);


        // ==================== CREATED LEADS STATISTICS ====================


        $createdLeads = $user->createdLeads;
        $totalCreatedLeads = $createdLeads->count();
        $pendingLeads = $createdLeads->where('status', 'pending')->count();
        $confirmedLeads = $createdLeads->where('status', 'confirmed')->count();
        $approvedLeads = $createdLeads->where('status', 'approved')->count();
        $rejectedLeads = $createdLeads->where('status', 'rejected')->count();


        $totalLeadsValue = $createdLeads->sum('amount');
        $confirmedLeadsValue = $createdLeads->where('status', 'confirmed')->sum('amount');
        $approvedLeadsValue = $createdLeads->where('status', 'approved')->sum('amount');


        $totalAdvancePaid = $createdLeads->sum('advance_paid_amount');


        $conversionRate = $totalCreatedLeads > 0
            ? round(($approvedLeads / $totalCreatedLeads) * 100, 2)
            : 0;


        $pendingLeadsList = $createdLeads->where('status', 'pending')->take(20);
        $allCreatedLeadsList = $createdLeads->take(20);
        $confirmedLeadsList = $createdLeads->where('status', 'confirmed')->take(20);
        $approvedLeadsList = $createdLeads->where('status', 'approved')->take(20);
        $rejectedLeadsList = $createdLeads->where('status', 'rejected')->take(20);


        return view('users.show', compact(
            'user',
            'totalAssignedLeads',
            'assignedConfirmedLeads',
            'assignedApprovedLeads',
            'assignedLeadsValue',
            'assignedConfirmedLeadsValue',
            'assignedApprovedLeadsValue',
            'assignedConversionRate',
            'assignedAllLeadsList',
            'assignedConfirmedLeadsList',
            'assignedApprovedLeadsList',
            'totalCreatedLeads',
            'pendingLeads',
            'confirmedLeads',
            'approvedLeads',
            'rejectedLeads',
            'totalLeadsValue',
            'confirmedLeadsValue',
            'approvedLeadsValue',
            'totalAdvancePaid',
            'conversionRate',
            'pendingLeadsList',
            'allCreatedLeadsList',
            'confirmedLeadsList',
            'approvedLeadsList',
            'rejectedLeadsList'
        ));
    }


    public function getDetails(User $user, $type, Request $request)
    {
        $currentUser = auth()->user();


        if ($currentUser->role === 'lead_manager') {
            if ($user->branch_id !== $currentUser->branch_id) {
                abort(403);
            }
        }


        $perPage = 15;
        $data = null;
        $view = '';


        switch ($type) {
            // ==================== ASSIGNED LEADS ====================


            case 'assigned_all_leads':
                $data = $user->assignedLeads()
                    ->with(['services'])
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            case 'assigned_confirmed_leads':
                $data = $user->assignedLeads()
                    ->with(['services'])
                    ->where('status', 'confirmed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            case 'assigned_approved_leads':
                $data = $user->assignedLeads()
                    ->with(['services'])
                    ->where('status', 'approved')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            // ==================== CREATED LEADS ====================


            case 'all_leads':
                $data = $user->createdLeads()
                    ->with(['services'])
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            case 'pending_leads':
                $data = $user->createdLeads()
                    ->with(['services'])
                    ->where('status', 'pending')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            case 'confirmed_leads':
                $data = $user->createdLeads()
                    ->with(['services'])
                    ->where('status', 'confirmed')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            case 'approved_leads':
                $data = $user->createdLeads()
                    ->with(['services'])
                    ->where('status', 'approved')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            case 'rejected_leads':
                $data = $user->createdLeads()
                    ->with(['services'])
                    ->where('status', 'rejected')
                    ->latest()
                    ->paginate($perPage);
                $view = 'users.partials.leads-panel';
                break;


            default:
                abort(404);
        }


        $html = view($view, ['leads' => $data])->render();


        return response()->json(['html' => $html]);
    }


    public function performance()
    {
        return view('users.performance');
    }


    public function performanceData(Request $request)
    {
        $user = auth()->user();


        // Date range filter
        $period = $request->get('period', 'month');
        $startDate = null;
        $endDate = null;


        switch ($period) {
            case 'day':
                $startDate = Carbon::today();
                $endDate = Carbon::now();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case '6months':
                $startDate = Carbon::now()->subMonths(6);
                $endDate = Carbon::now();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now();
                break;
            case 'last_year':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate = Carbon::now()->subYear()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
                $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
                break;
        }


        // Get all active users (filter by branch for lead_manager)
        $usersQuery = User::where('is_active', true)
            ->where('role', '!=', 'super_admin')
            ->where('role', '!=', 'lead_manager');


        if ($user->role === 'lead_manager') {
            $usersQuery->where('branch_id', $user->branch_id);
        }


        $users = $usersQuery->get();


        // Calculate metrics for each user
        $leaderboard = $users->map(function ($user) use ($startDate, $endDate) {
            // Leads created
            $leadsCreated = $user->createdLeads()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();


            // Leads approved
            $leadsApproved = $user->createdLeads()
                ->where('status', 'approved')
                ->whereBetween('approved_at', [$startDate, $endDate])
                ->count();


            // Conversion rate
            $conversionRate = $leadsCreated > 0 ? round(($leadsApproved / $leadsCreated) * 100, 2) : 0;


            // Total value of APPROVED leads only
            $leadsValue = $user->createdLeads()
                ->where('status', 'approved')
                ->whereBetween('approved_at', [$startDate, $endDate])
                ->sum('amount');


            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'branch' => $user->branch->name ?? 'N/A',
                'leads_created' => $leadsCreated,
                'leads_approved' => $leadsApproved,
                'conversion_rate' => $conversionRate,
                'leads_value' => $leadsValue,
                'total_value' => $leadsValue,
            ];
        });


        // Sort by total value (highest first)
        $leaderboard = $leaderboard->sortByDesc('total_value')->values();


        // Add rank
        $leaderboard = $leaderboard->map(function ($user, $index) {
            $user['rank'] = $index + 1;
            return $user;
        });


        // Summary stats
        $summary = [
            'total_leads_created' => $leaderboard->sum('leads_created'),
            'total_leads_approved' => $leaderboard->sum('leads_approved'),
            'total_value' => $leaderboard->sum('total_value'),
            'avg_conversion_rate' => $leaderboard->avg('conversion_rate'),
        ];


        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
            'summary' => $summary,
            'period' => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);
    }


}
