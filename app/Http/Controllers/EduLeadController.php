<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\EduCallLog;
use App\Models\EduLead;
use App\Models\EduLeadFollowup;
use App\Models\EduLeadNote;
use App\Models\EduLeadSource;
use App\Models\EduLeadStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EduLeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of education leads
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base query with relationships (NO BRANCH)
        $query = EduLead::with([
            'course',
            'leadSource',
            'createdBy',
            'assignedTo'
        ]);

        // ========== ROLE-BASED ACCESS CONTROL (NO BRANCH) ==========
        if ($user->role === 'super_admin') {
            // Super admin sees all leads
        } elseif ($user->role === 'lead_manager') {
            // Lead manager sees only their created leads
            $query->where('created_by', $user->id);
        } elseif ($user->role === 'telecallers') {
            // Telecallers see their assigned leads
            $query->where('assigned_to', $user->id);
        } else {
            abort(403, 'Unauthorized');
        }

        // ========== FILTERS ==========

        // Interest Level Filter
        if ($request->filled('interest_level')) {
            $query->where('interest_level', $request->interest_level);
        }

        // Final Status Filter
        if ($request->filled('final_status')) {
            $query->where('final_status', $request->final_status);
        }

        // Lead Source Filter
        if ($request->filled('lead_source_id') && $request->lead_source_id != 0) {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        // Course Filter
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Country Filter
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Date From Filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Date To Filter
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Assigned To Filter
        if ($request->filled('assigned_to')) {
            if ($user->role === 'telecallers') {
                // Telecaller can only see 'me' or 'unassigned'
                if ($request->assigned_to === 'me') {
                    $query->where('assigned_to', $user->id);
                } elseif ($request->assigned_to === 'unassigned') {
                    $query->whereNull('assigned_to');
                }
            } else {
                // Admin/manager can see all
                if ($request->assigned_to === 'unassigned') {
                    $query->whereNull('assigned_to');
                } else {
                    $query->where('assigned_to', $request->assigned_to);
                }
            }
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone', 'like', $search)
                  ->orWhere('whatsapp_number', 'like', $search)
                  ->orWhere('lead_code', 'like', $search);
            });
        }

        // ========== SORTING ==========
        $sortColumn = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // ========== PAGINATION ==========
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 30, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $leads = $query->paginate($perPage);

        // ========== COUNTS ==========
        $hotLeadsCount = EduLead::where('interest_level', 'hot')
            ->where('final_status', 'pending')
            ->when($user->role === 'lead_manager', fn($q) => $q->where('created_by', $user->id))
            ->when($user->role === 'telecallers', fn($q) => $q->where('assigned_to', $user->id))
            ->count();

        $pendingFollowupsCount = EduLeadFollowup::where('status', 'pending')
            ->whereDate('followup_date', '<=', today())
            ->when($user->role !== 'super_admin', fn($q) => $q->where('assigned_to', $user->id))
            ->count();

        // Get filter dropdown data (NO BRANCHES)
        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $leadSources = EduLeadSource::where('is_active', true)->orderBy('name')->get();
        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get unique countries from courses
        $countries = Course::where('is_active', true)
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values();

        // ========== AJAX RESPONSE ==========
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('edu-leads.partials.table-rows', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-5')->render(),
                'total' => $leads->total(),
                'per_page' => $leads->perPage(),
                'current_page' => $leads->currentPage(),
                'from' => $leads->firstItem() ?? 0,
                'to' => $leads->lastItem() ?? 0,
                'current_sort' => [
                    'column' => $sortColumn,
                    'direction' => $sortDirection
                ]
            ]);
        }

        return view('edu-leads.index', compact(
            'leads',
            'hotLeadsCount',
            'pendingFollowupsCount',
            'courses',
            'leadSources',
            'telecallers',
            'countries'
        ));
    }

    /**
     * Show the form for creating a new lead
     */
    public function create()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized to create leads');
        }

        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $leadSources = EduLeadSource::where('is_active', true)->orderBy('name')->get();
        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $countries = Course::where('is_active', true)
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values();

        return view('edu-leads.create', compact('courses', 'leadSources', 'telecallers', 'countries'));
    }

    /**
     * Store a newly created lead
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Authorization check
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'errors' => ['authorization' => ['You are not authorized to create leads']]
                ], 403);
            }

            // ✅ FIXED VALIDATION - status and final_status are now NULLABLE
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|max:20|unique:edu_leads,phone',
                'whatsapp_number' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'course_interested' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:100',
                'college' => 'nullable|string|max:255',
                'course_id' => 'nullable|exists:courses,id',
                'lead_source_id' => 'required|exists:edu_lead_sources,id',
                'assigned_to' => 'nullable|exists:users,id',
                'interest_level' => 'nullable|in:hot,warm,cold',
                'final_status' => 'nullable|in:pending,contacted,not_interested,follow_up,admitted,dropped',
                'status' => 'nullable|in:pending,connected,not_connected,interested,not_interested,follow_up_scheduled,admitted,closed',
                'remarks' => 'nullable|string',
            ]);

            // ✅ SET DEFAULTS for status fields
            $validated['final_status'] = $validated['final_status'] ?? 'pending';
            $validated['status'] = $validated['status'] ?? 'pending';
            $validated['interest_level'] = $validated['interest_level'] ?? null;

            // Determine assigned_to
            $assignedTo = $validated['assigned_to'] ?? null;
            if ($user->role === 'telecallers' && !$assignedTo) {
                $assignedTo = $user->id;
            }

            // Create lead
            $lead = EduLead::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? $validated['phone'],
                'description' => $validated['description'] ?? null,
                'course_interested' => $validated['course_interested'] ?? null,
                'country' => $validated['country'] ?? null,
                'college' => $validated['college'] ?? null,
                'course_id' => $validated['course_id'] ?? null,
                'lead_source_id' => $validated['lead_source_id'],
                'assigned_to' => $assignedTo,
                'interest_level' => $validated['interest_level'],
                'final_status' => $validated['final_status'],
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => $user->id,
            ]);

            Log::info('Education lead created successfully', [
                'lead_id' => $lead->id,
                'lead_code' => $lead->lead_code,
                'created_by' => $user->id
            ]);

            // ✅ ALWAYS return JSON for AJAX
            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully!',
                'lead_id' => $lead->id,
                'lead_code' => $lead->lead_code,
                'name' => $lead->name,
                'redirect_url' => route('edu-leads.show', $lead->id)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Lead validation error', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Lead creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating lead: ' . $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Display the specified lead
     */
    public function show(EduLead $eduLead)
    {
        $user = Auth::user();

        // Authorization check (NO BRANCH)
        if ($user->role === 'telecallers' && $eduLead->assigned_to != $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'lead_manager' && $eduLead->created_by != $user->id) {
            abort(403, 'You can only view your own leads.');
        }

        // Load relationships
        $eduLead->load([
            'course',
            'leadSource',
            'createdBy',
            'assignedTo',
            'callLogs.user',
            'notes.createdBy',
            'followups' => function($query) {
                $query->with(['assignedToUser', 'createdBy'])
                      ->orderBy('followup_date', 'asc')
                      ->orderBy('followup_time', 'asc');
            }
        ]);

        return view('edu-leads.show', compact('eduLead'));
    }

    /**
     * Show the form for editing the specified lead
     */
    public function edit(EduLead $eduLead)
    {
        $user = Auth::user();

        // Authorization (NO BRANCH)
        if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'lead_manager' && $eduLead->created_by != $user->id) {
            abort(403, 'You can only edit your own leads');
        }

        if ($user->role === 'telecallers' && $eduLead->assigned_to != $user->id) {
            abort(403, 'Unauthorized');
        }

        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $leadSources = EduLeadSource::where('is_active', true)->orderBy('name')->get();
        $telecallers = User::where('role', 'telecallers')
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $countries = Course::where('is_active', true)
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values();

        return view('edu-leads.edit', compact('eduLead', 'courses', 'leadSources', 'telecallers', 'countries'));
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, EduLead $eduLead)
    {
        try {
            $user = Auth::user();

            // Authorization (NO BRANCH)
            if (!in_array($user->role, ['super_admin', 'lead_manager', 'telecallers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'errors' => ['authorization' => ['You are not authorized to update this lead']]
                ], 403);
            }

            if ($user->role === 'telecallers' && $eduLead->assigned_to != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'errors' => ['authorization' => ['You can only edit your assigned leads']]
                ], 403);
            }

            if ($user->role === 'lead_manager' && $eduLead->created_by != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'errors' => ['authorization' => ['You can only edit your own leads']]
                ], 403);
            }

            // ✅ FIXED VALIDATION - status fields are nullable
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|max:20|unique:edu_leads,phone,' . $eduLead->id,
                'whatsapp_number' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'course_interested' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:100',
                'college' => 'nullable|string|max:255',
                'course_id' => 'nullable|exists:courses,id',
                'lead_source_id' => 'required|exists:edu_lead_sources,id',
                'assigned_to' => 'nullable|exists:users,id',
                'interest_level' => 'nullable|in:hot,warm,cold',
                'final_status' => 'nullable|in:pending,contacted,not_interested,follow_up,admitted,dropped',
                'status' => 'nullable|in:pending,connected,not_connected,interested,not_interested,follow_up_scheduled,admitted,closed',
                'remarks' => 'nullable|string',
                'next_action' => 'nullable|string',
            ]);

            // Keep existing values if not provided
            $validated['final_status'] = $validated['final_status'] ?? $eduLead->final_status;
            $validated['status'] = $validated['status'] ?? $eduLead->status;

            if ($user->role === 'telecallers') {
                $validated['assigned_to'] = $user->id;
            }

            // Track status changes for history
            $statusChanged = $eduLead->status !== $validated['status'];
            $interestChanged = $eduLead->interest_level !== ($validated['interest_level'] ?? $eduLead->interest_level);
            $oldStatus = $eduLead->status;
            $oldInterest = $eduLead->interest_level;

            // Check if admitted
            if ($validated['final_status'] === 'admitted' && $eduLead->final_status !== 'admitted') {
                $validated['admitted_at'] = now();
            }

            // Update lead
            $eduLead->update($validated);

            // Log status change to history
            if ($statusChanged || $interestChanged) {
                EduLeadStatusHistory::create([
                    'edu_lead_id' => $eduLead->id,
                    'user_id' => $user->id,
                    'old_status' => $statusChanged ? $oldStatus : null,
                    'new_status' => $statusChanged ? $validated['status'] : $eduLead->status,
                    'old_interest_level' => $interestChanged ? $oldInterest : null,
                    'new_interest_level' => $interestChanged ? ($validated['interest_level'] ?? $eduLead->interest_level) : null,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                Log::info('Education lead status changed', [
                    'lead_id' => $eduLead->id,
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status'],
                    'updated_by' => $user->id
                ]);
            }

            Log::info('Education lead updated successfully', [
                'lead_id' => $eduLead->id,
                'updated_by' => $user->id
            ]);

            // ✅ ALWAYS return JSON
            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully!',
                'lead_id' => $eduLead->id,
                'lead_code' => $eduLead->lead_code,
                'redirect_url' => route('edu-leads.show', $eduLead->id)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Lead update validation error', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'lead_id' => $eduLead->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Lead update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'lead_id' => $eduLead->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating lead: ' . $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Remove the specified lead
     */
    public function destroy(EduLead $eduLead)
    {
        try {
            $user = Auth::user();

            // Authorization
            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($user->role === 'lead_manager' && $eduLead->created_by != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own leads'
                ], 403);
            }

            $leadName = $eduLead->name;
            $eduLead->delete();

            Log::info('Education lead deleted', [
                'lead_name' => $leadName,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lead delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting lead'
            ], 500);
        }
    }

    /**
     * Assign lead to telecaller (NO BRANCH VALIDATION)
     */
    public function assignLead(Request $request, EduLead $eduLead)
    {
        try {
            $user = Auth::user();

            // Only super admin and lead manager can assign
            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            $telecaller = User::findOrFail($validated['assigned_to']);

            $eduLead->update([
                'assigned_to' => $validated['assigned_to']
            ]);

            // Add note if provided
            if ($request->filled('notes')) {
                EduLeadNote::create([
                    'edu_lead_id' => $eduLead->id,
                    'created_by' => auth()->id(),
                    'note' => 'Assignment Note: ' . $validated['notes']
                ]);
            }

            Log::info('Education lead assigned', [
                'lead_id' => $eduLead->id,
                'assigned_to' => $telecaller->name,
                'assigned_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead assigned to ' . $telecaller->name . ' successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lead assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning lead'
            ], 500);
        }
    }

    /**
     * Bulk assign leads to telecaller (NO BRANCH VALIDATION)
     */
    public function bulkAssign(Request $request)
    {
        try {
            $user = Auth::user();

            // Authorization check
            if (!in_array($user->role, ['super_admin', 'lead_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Validate
            $validated = $request->validate([
                'lead_ids' => 'required|array',
                'lead_ids.*' => 'exists:edu_leads,id',
                'assigned_to' => 'required|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            $telecaller = User::find($validated['assigned_to']);
            $count = 0;

            foreach ($validated['lead_ids'] as $leadId) {
                $lead = EduLead::find($leadId);
                if ($lead) {
                    $lead->update(['assigned_to' => $validated['assigned_to']]);

                    // Add note if provided
                    if ($request->filled('notes')) {
                        EduLeadNote::create([
                            'edu_lead_id' => $lead->id,
                            'created_by' => $user->id,
                            'note' => 'Bulk Assignment Note: ' . $validated['notes']
                        ]);
                    }

                    $count++;
                }
            }

            Log::info('Bulk education lead assignment', [
                'count' => $count,
                'assigned_to' => $telecaller->name,
                'assigned_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bulk assignment completed!',
                'count' => $count,
                'telecaller_name' => $telecaller->name
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk assign error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk assignment'
            ], 500);
        }
    }

    /**
     * Add call log
     */
    public function addCall(Request $request, EduLead $eduLead)
    {
        try {
            // Validate
            $validated = $request->validate([
                'call_datetime' => 'required|date',
                'interest_level' => 'nullable|in:hot,warm,cold',
                'remarks' => 'nullable|string',
                'next_action' => 'nullable|string',
                'followup_date' => 'nullable|date|after:today',
                // 'call_status' => 'nullable|string', // ← MADE OPTIONAL (or remove completely)
            ]);

            // Create call log
            $callLog = $eduLead->callLogs()->create([
                'call_datetime' => $validated['call_datetime'],
                'interest_level' => $validated['interest_level'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'next_action' => $validated['next_action'] ?? null,
                'user_id' => auth()->id(),
                // 'call_status' => $validated['call_status'] ?? null, // ← MADE OPTIONAL
            ]);

            // If followup_date provided, create followup
            if (!empty($validated['followup_date'])) {
                $eduLead->followups()->create([
                    'followup_date' => $validated['followup_date'],
                    'priority' => 'medium',
                    'notes' => 'Follow-up from call on ' . now()->format('d M Y'),
                    'assigned_to' => $eduLead->assigned_to ?? auth()->id(),
                    'created_by' => auth()->id(),
                    'status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Call logged successfully!',
                'data' => $callLog->load('user')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error logging call: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error logging call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add followup
     */
    public function addFollowup(Request $request, EduLead $eduLead)
    {
        $user = Auth::user();

        // Authorization: telecallers can only add to their assigned leads
        if ($user->role === 'telecallers' && $eduLead->assigned_to != $user->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $validated = $request->validate([
                'followup_date' => 'required|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'priority' => 'required|in:low,medium,high',
                'notes' => 'nullable|string|max:1000',
            ]);

            EduLeadFollowup::create([
                'edu_lead_id' => $eduLead->id,
                'followup_date' => $validated['followup_date'],
                'followup_time' => $validated['followup_time'] ?? null,
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] ?? null,
                'assigned_to' => $eduLead->assigned_to ?? auth()->id(),
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]);

            // Update lead followup fields
            $eduLead->update([
                'followup_date' => $validated['followup_date'],
                'followup_status' => 'pending'
            ]);

            Log::info('Followup scheduled for education lead', [
                'lead_id' => $eduLead->id,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup scheduled successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Add followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling followup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete followup
     */
    public function completeFollowup(EduLeadFollowup $followup)
    {
        if ($followup->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Already completed'
            ]);
        }

        $followup->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Update lead followup status
        $followup->eduLead->update([
            'followup_status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up marked as completed!'
        ]);
    }

    /**
     * Add note
     */
    public function addNote(Request $request, EduLead $eduLead)
    {
        $user = Auth::user();

        // Authorization
        if ($user->role === 'telecallers' && $eduLead->assigned_to != $user->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $validated = $request->validate([
                'note' => 'required|string',
            ]);

            $note = EduLeadNote::create([
                'edu_lead_id' => $eduLead->id,
                'created_by' => auth()->id(),
                'note' => $validated['note'],
            ]);

            Log::info('Note added to education lead', [
                'lead_id' => $eduLead->id,
                'note_id' => $note->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!',
                'note' => $note->load('createdBy')
            ]);

        } catch (\Exception $e) {
            Log::error('Add note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding note'
            ], 500);
        }
    }

    /**
     * Delete a followup
     */
    public function deleteFollowup(EduLeadFollowup $followup)
    {
        try {
            $user = Auth::user();

            // Authorization
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $followup->created_by !== $user->id &&
                $followup->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this followup'
                ], 403);
            }

            // Don't allow deletion of completed followups (optional)
            if ($followup->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete completed followups'
                ], 400);
            }

            $followup->delete();

            Log::info('Followup deleted', [
                'followup_id' => $followup->id,
                'edu_lead_id' => $followup->edu_lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Followup deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting followup'
            ], 500);
        }
    }

    /**
     * Delete a call log
     */
    public function deleteCall(EduCallLog $call)
    {
        try {
            $user = Auth::user();

            // Authorization: super admin, lead manager, or creator
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $call->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this call log'
                ], 403);
            }

            $call->delete();

            Log::info('Call log deleted', [
                'call_id' => $call->id,
                'edu_lead_id' => $call->edu_lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call log deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete call error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting call log'
            ], 500);
        }
    }

    /**
     * Delete a note
     */
    public function deleteNote(EduLeadNote $note)
    {
        try {
            $user = Auth::user();

            // Authorization: super admin, lead manager, or creator
            if ($user->role !== 'super_admin' &&
                $user->role !== 'lead_manager' &&
                $note->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this note'
                ], 403);
            }

            $note->delete();

            Log::info('Note deleted', [
                'note_id' => $note->id,
                'edu_lead_id' => $note->edu_lead_id,
                'deleted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete note error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting note'
            ], 500);
        }
    }

    /**
     * Export leads to CSV (NO BRANCH)
     */
    public function export(Request $request)
    {
        try {
            $user = Auth::user();

            // Base query (NO BRANCH)
            $query = EduLead::with(['course', 'leadSource', 'createdBy', 'assignedTo']);

            // Role-based access
            if ($user->role === 'lead_manager') {
                $query->where('created_by', $user->id);
            } elseif ($user->role === 'telecallers') {
                $query->where('assigned_to', $user->id);
            }

            // Apply all filters from request
            if ($request->filled('interest_level')) {
                $query->where('interest_level', $request->interest_level);
            }

            if ($request->filled('final_status')) {
                $query->where('final_status', $request->final_status);
            }

            if ($request->filled('lead_source_id')) {
                $query->where('lead_source_id', $request->lead_source_id);
            }

            if ($request->filled('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            if ($request->filled('country')) {
                $query->where('country', $request->country);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', $search)
                      ->orWhere('email', 'like', $search)
                      ->orWhere('phone', 'like', $search)
                      ->orWhere('whatsapp_number', 'like', $search)
                      ->orWhere('lead_code', 'like', $search);
                });
            }

            $leads = $query->get();

            // Create CSV
            $filename = 'education_leads_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($leads) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, [
                    'Lead Code',
                    'Name',
                    'Email',
                    'Phone',
                    'WhatsApp',
                    'Course Interested',
                    'Country',
                    'College',
                    'Course',
                    'Lead Source',
                    'Interest Level',
                    'Final Status',
                    'Status',
                    'Assigned To',
                    'Created By',
                    'Created At',
                    'Call Date',
                    'Call Status',
                    'Followup Date',
                    'Remarks'
                ]);

                // CSV Data
                foreach ($leads as $lead) {
                    fputcsv($file, [
                        $lead->lead_code,
                        $lead->name,
                        $lead->email ?? '',
                        $lead->phone,
                        $lead->whatsapp_number ?? '',
                        $lead->course_interested ?? '',
                        $lead->country ?? '',
                        $lead->college ?? '',
                        $lead->course ? $lead->course->name : '',
                        $lead->leadSource ? $lead->leadSource->name : '',
                        ucfirst($lead->interest_level ?? ''),
                        ucfirst(str_replace('_', ' ', $lead->final_status)),
                        ucfirst(str_replace('_', ' ', $lead->status)),
                        $lead->assignedTo ? $lead->assignedTo->name : 'Unassigned',
                        $lead->createdBy ? $lead->createdBy->name : '',
                        $lead->created_at->format('d-m-Y H:i'),
                        $lead->call_date ? $lead->call_date->format('d-m-Y') : '',
                        $lead->call_status ? ucfirst(str_replace('_', ' ', $lead->call_status)) : '',
                        $lead->followup_date ? $lead->followup_date->format('d-m-Y') : '',
                        $lead->remarks ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'Error exporting leads');
        }
    }

    /**
     * Get today's followups for dashboard
     */
    public function getTodayFollowups()
    {
        $user = Auth::user();

        $query = EduLeadFollowup::with(['eduLead', 'assignedToUser'])
            ->where('status', 'pending')
            ->whereDate('followup_date', today())
            ->orderBy('followup_time', 'asc');

        if ($user->role !== 'super_admin') {
            $query->where('assigned_to', $user->id);
        }

        $followups = $query->get();

        return response()->json([
            'success' => true,
            'followups' => $followups
        ]);
    }
}
