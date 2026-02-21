<?php

namespace App\Http\Controllers;

use App\Helpers\IndiaGeoHelper;
use App\Models\Branch;
use App\Models\Course;
use App\Models\EduCallLog;
use App\Models\EduLead;
use App\Models\EduLeadFollowup;
use App\Models\EduLeadNote;
use App\Models\EduLeadSource;
use App\Models\EduLeadStatusHistory;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EduLeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateLeads()) {
            abort(403, 'Unauthorized');
        }

        $baseQuery = EduLead::with(['course.programme', 'leadSource', 'createdBy', 'assignedTo', 'branch']);
        $baseQuery->visibleTo($user);

        if ($request->filled('interest_level'))    $baseQuery->where('interest_level',  $request->interest_level);
        if ($request->filled('lead_source_id') && $request->lead_source_id != 0)
                                                   $baseQuery->where('lead_source_id',  $request->lead_source_id);
        if ($request->filled('course_id'))         $baseQuery->where('course_id',       $request->course_id);
        if ($request->filled('programme_id'))      $baseQuery->whereHas('course', fn($q) => $q->where('programme_id', $request->programme_id));
        if ($request->filled('institution_type'))  $baseQuery->where('institution_type', $request->institution_type);
        if ($request->filled('state'))             $baseQuery->where('state',    'like', '%' . $request->state    . '%');
        if ($request->filled('district'))          $baseQuery->where('district', 'like', '%' . $request->district . '%');
        if ($request->filled('preferred_state'))   $baseQuery->where('preferred_state', $request->preferred_state);
        if ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isOperationHead()))
                                                   $baseQuery->where('branch_id', $request->branch_id);
        if ($request->filled('date_from'))         $baseQuery->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))           $baseQuery->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('school_department')) $baseQuery->whereRaw('LOWER(TRIM(school_department)) = ?', [strtolower(trim($request->school_department))]);
        if ($request->filled('college_department'))$baseQuery->whereRaw('LOWER(TRIM(college_department)) = ?', [strtolower(trim($request->college_department))]);
        if ($request->filled('assigned_to')) {
            $request->assigned_to === 'unassigned'
                ? $baseQuery->whereNull('assigned_to')
                : $baseQuery->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('agent_name'))        $baseQuery->where('agent_name', 'like', '%' . $request->agent_name . '%');
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $baseQuery->where(fn($q) => $q
                ->where('name',               'like', $s)
                ->orWhere('email',            'like', $s)
                ->orWhere('phone',            'like', $s)
                ->orWhere('whatsapp_number',  'like', $s)
                ->orWhere('lead_code',        'like', $s)
                ->orWhere('school',           'like', $s)
                ->orWhere('college',          'like', $s)
                ->orWhere('agent_name',       'like', $s)
                ->orWhere('application_number','like', $s)
            );
        }

        $statusCounts = [
            'all'            => (clone $baseQuery)->count(),
            'pending'        => (clone $baseQuery)->where('final_status', 'pending')->count(),
            'contacted'      => (clone $baseQuery)->where('final_status', 'contacted')->count(),
            'follow_up'      => (clone $baseQuery)->where('final_status', 'follow_up')->count(),
            'admitted'       => (clone $baseQuery)->where('final_status', 'admitted')->count(),
            'not_interested' => (clone $baseQuery)->where('final_status', 'not_interested')->count(),
            'dropped'        => (clone $baseQuery)->where('final_status', 'dropped')->count(),
        ];

        $institutionCounts = [
            'school'  => (clone $baseQuery)->where('institution_type', 'school')->count(),
            'college' => (clone $baseQuery)->where('institution_type', 'college')->count(),
        ];

        $query = clone $baseQuery;
        if ($request->filled('final_status')) {
            $query->where('final_status', $request->final_status);
        }

        $sortColumn    = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $perPage = in_array($request->get('per_page', 15), [15, 30, 50, 100])
            ? $request->get('per_page', 15) : 15;

        $leads = $query->orderBy($sortColumn, $sortDirection)->paginate($perPage);

        $hotLeadsCount = EduLead::where('interest_level', 'hot')
            ->where('final_status', 'pending')
            ->visibleTo($user)
            ->count();

        $pendingFollowupsCount = EduLeadFollowup::where('status', 'pending')
            ->whereDate('followup_date', '<=', today())
            ->when($user->isTelecaller(),  fn($q) => $q->where('assigned_to', $user->id))
            ->when($user->isLeadManager(), fn($q) => $q->whereHas('eduLead', fn($lq) => $lq->where('branch_id', $user->branch_id)))
            ->count();

        $courses         = Course::active()->with('programme')->orderBy('name')->get();
        $programmes      = Programme::active()->orderBy('name')->get();
        $leadSources     = EduLeadSource::where('is_active', true)->orderBy('name')->get();
        $branches        = Branch::active()->get();
        $assignableUsers = $this->getAssignableUsers($user);
        $states          = IndiaGeoHelper::states();

        if ($request->ajax()) {
            return response()->json([
                'success'            => true,
                'html'               => view('edu-leads.partials.table-rows', compact('leads'))->render(),
                'pagination'         => $leads->links('pagination::bootstrap-5')->render(),
                'total'              => $leads->total(),
                'per_page'           => $leads->perPage(),
                'current_page'       => $leads->currentPage(),
                'from'               => $leads->firstItem() ?? 0,
                'to'                 => $leads->lastItem()  ?? 0,
                'status_counts'      => $statusCounts,
                'institution_counts' => $institutionCounts,
            ]);
        }

        return view('edu-leads.index', compact(
            'leads', 'hotLeadsCount', 'pendingFollowupsCount',
            'courses', 'programmes', 'leadSources', 'branches',
            'assignableUsers', 'statusCounts', 'institutionCounts', 'states'
        ));
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    public function create()
    {
        $user = Auth::user();

        if (!$user->canCreateLeads()) {
            abort(403, 'Unauthorized to create leads');
        }

        $courses            = Course::active()->with('programme')->orderBy('name')->get();
        $programmes         = Programme::active()->orderBy('name')->get();
        $leadSources        = EduLeadSource::where('is_active', true)->orderBy('name')->get();
        $branches           = Branch::active()->get();
        $userBranchId       = $user->branch_id;
        $coursesByProgramme = $courses->groupBy('programme_id');
        $states             = IndiaGeoHelper::states();
        $districtMap        = IndiaGeoHelper::districtMap();

        return view('edu-leads.create', compact(
            'courses', 'programmes', 'leadSources', 'branches',
            'userBranchId', 'coursesByProgramme',
            'states', 'districtMap'
        ));
    }

    // =========================================================================
    // STORE
    // =========================================================================
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->canCreateLeads()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate($this->leadValidationRules());

            // Build full application number from AJK- prefix + suffix
            if (!empty($validated['application_number_suffix'])) {
                $validated['application_number'] = 'AJK-' . trim($validated['application_number_suffix']);
            }
            unset($validated['application_number_suffix']);

            $validated['final_status']   = $validated['final_status']   ?? 'pending';
            $validated['status']         = $validated['status']         ?? 'pending';
            $validated['interest_level'] = $validated['interest_level'] ?? null;

            // Force branch for lead_manager and telecaller
            $branchId = ($user->isLeadManager() || $user->isTelecaller())
                ? $user->branch_id
                : ($validated['branch_id'] ?? null);

            // Telecallers are auto-set as agent
            if ($user->isTelecaller() && empty($validated['agent_name'])) {
                $validated['agent_name'] = $user->name;
            }

            $lead = EduLead::create(array_merge($validated, [
                'branch_id'   => $branchId,
                'created_by'  => $user->id,
                'assigned_to' => $user->isTelecaller()
                    ? $user->id
                    : ($validated['assigned_to'] ?? null),
            ]));

            Log::info('Education lead created', ['lead_id' => $lead->id, 'created_by' => $user->id]);

            return response()->json([
                'success'      => true,
                'message'      => 'Lead created successfully!',
                'lead_id'      => $lead->id,
                'lead_code'    => $lead->lead_code,
                'redirect_url' => route('edu-leads.show', $lead->id),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Please fix the errors below', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Lead creation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error creating lead: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(EduLead $eduLead)
    {
        $user = Auth::user();

        if ($user->isTelecaller() && $eduLead->assigned_to !== $user->id) {
            abort(403, 'You can only view your own assigned leads.');
        }

        if ($user->isLeadManager() && $eduLead->branch_id !== $user->branch_id) {
            abort(403, 'You can only view leads from your branch.');
        }

        $eduLead->load([
            'course.programme', 'leadSource', 'createdBy', 'assignedTo.branch', 'branch',
            'callLogs.user',
            'notes.createdBy',
            'followups' => fn($q) => $q->with('assignedToUser', 'createdBy')
                                       ->orderBy('followup_date')->orderBy('followup_time'),
            'statusHistory.user',
        ]);

        return view('edu-leads.show', compact('eduLead'));
    }

    // =========================================================================
    // EDIT
    // =========================================================================
    public function edit(EduLead $eduLead)
    {
        $user = Auth::user();

        if (!$user->canCreateLeads()) {
            abort(403, 'Unauthorized');
        }

        if ($user->isTelecaller() && $eduLead->assigned_to !== $user->id) {
            abort(403, 'You can only edit your own assigned leads.');
        }

        if ($user->isLeadManager() && $eduLead->branch_id !== $user->branch_id) {
            abort(403, 'You can only edit leads from your branch.');
        }

        $eduLead->load('course.programme', 'leadSource', 'assignedTo.branch', 'branch');

        $courses            = Course::active()->with('programme')->orderBy('name')->get();
        $programmes         = Programme::active()->orderBy('name')->get();
        $leadSources        = EduLeadSource::where('is_active', true)->orderBy('name')->get();
        $branches           = Branch::active()->get();
        $coursesByProgramme = $courses->groupBy('programme_id');
        $assignableUsers    = $this->getAssignableUsers($user);
        $states             = IndiaGeoHelper::states();
        $districtMap        = IndiaGeoHelper::districtMap();

        return view('edu-leads.edit', compact(
            'eduLead', 'courses', 'programmes', 'leadSources', 'branches',
            'coursesByProgramme', 'assignableUsers',
            'states', 'districtMap'
        ));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================
    public function update(Request $request, EduLead $eduLead)
    {
        try {
            $user = Auth::user();

            if (!$user->canCreateLeads()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($user->isTelecaller() && $eduLead->assigned_to !== $user->id) {
                return response()->json(['success' => false, 'message' => 'You can only edit your own assigned leads.'], 403);
            }

            if ($user->isLeadManager() && $eduLead->branch_id !== $user->branch_id) {
                return response()->json(['success' => false, 'message' => 'You can only edit leads from your branch.'], 403);
            }

            $validated = $request->validate($this->leadValidationRules($eduLead->id));

            // Build full application number from AJK- prefix + suffix
            if (array_key_exists('application_number_suffix', $validated)) {
                $suffix = trim($validated['application_number_suffix'] ?? '');
                $validated['application_number'] = $suffix ? 'AJK-' . $suffix : null;
                unset($validated['application_number_suffix']);
            }

            $validated['final_status'] = $validated['final_status'] ?? $eduLead->final_status;
            $validated['status']       = $validated['status']       ?? $eduLead->status;

            // Preserve branch for lead_manager and telecaller
            if ($user->isLeadManager() || $user->isTelecaller()) {
                $validated['branch_id'] = $user->branch_id;
            }

            // Track admitted_at timestamp
            if (($validated['final_status'] ?? null) === 'admitted' && $eduLead->final_status !== 'admitted') {
                $validated['admitted_at'] = now();
            }

            // Track cancellation timestamp
            if (($validated['final_status'] ?? null) === 'dropped' && $eduLead->final_status !== 'dropped') {
                $validated['cancelled_at'] = now();
            }

            $eduLead->update($validated);

            Log::info('Education lead updated', ['lead_id' => $eduLead->id, 'updated_by' => $user->id]);

            return response()->json([
                'success'      => true,
                'message'      => 'Lead updated successfully!',
                'lead_id'      => $eduLead->id,
                'lead_code'    => $eduLead->lead_code,
                'redirect_url' => route('edu-leads.show', $eduLead->id),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Please fix the errors below', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Lead update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating lead: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // UPDATE STATUS
    // =========================================================================
    public function updateStatus(Request $request, EduLead $eduLead)
    {
        $user = Auth::user();

        $canChange = $user->isSuperAdmin()
            || $user->isOperationHead()
            || ($user->isLeadManager() && $eduLead->branch_id === $user->branch_id)
            || $eduLead->assigned_to === $user->id;

        if (!$canChange) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'final_status' => ['required', \Illuminate\Validation\Rule::in([
                'pending', 'contacted', 'follow_up', 'admitted', 'not_interested', 'dropped',
            ])],
        ]);

        $eduLead->update($validated);

        return response()->json([
            'success'      => true,
            'message'      => 'Status updated successfully.',
            'status_class' => 'status-' . $validated['final_status'],
        ]);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================
    public function destroy(EduLead $eduLead)
    {
        try {
            $user = Auth::user();

            if (!$user->canDelete()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized — only Super Admin can delete leads.'], 403);
            }

            $leadName = $eduLead->name;
            $eduLead->delete();

            Log::info('Education lead deleted', ['lead_name' => $leadName, 'deleted_by' => $user->id]);

            return response()->json(['success' => true, 'message' => 'Lead deleted successfully!']);

        } catch (\Exception $e) {
            Log::error('Lead delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting lead'], 500);
        }
    }

    // =========================================================================
    // ASSIGN LEAD
    // =========================================================================
    public function assignLead(Request $request, EduLead $eduLead)
    {
        try {
            $user = Auth::user();

            if (!$user->canAssignLeads()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($user->isLeadManager() && $eduLead->branch_id !== $user->branch_id) {
                return response()->json(['success' => false, 'message' => 'You can only assign leads from your branch.'], 403);
            }

            $validated = $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'notes'       => 'nullable|string',
            ]);

            $assignee = User::findOrFail($validated['assigned_to']);

            if ($user->isLeadManager() && $assignee->branch_id !== $user->branch_id) {
                return response()->json(['success' => false, 'message' => 'You can only assign to telecallers in your branch.'], 403);
            }

            $eduLead->update(['assigned_to' => $validated['assigned_to']]);

            if ($request->filled('notes')) {
                EduLeadNote::create([
                    'edu_lead_id' => $eduLead->id,
                    'created_by'  => auth()->id(),
                    'note'        => 'Assignment Note: ' . $validated['notes'],
                ]);
            }

            return response()->json([
                'success'         => true,
                'message'         => 'Lead assigned to ' . $assignee->name . ' successfully!',
                'telecaller_name' => $assignee->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Lead assign error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error assigning lead'], 500);
        }
    }

    // =========================================================================
    // BULK ASSIGN
    // =========================================================================
    public function bulkAssign(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->canAssignLeads()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'lead_ids'    => 'required|array',
                'lead_ids.*'  => 'exists:edu_leads,id',
                'assigned_to' => 'required|exists:users,id',
                'notes'       => 'nullable|string',
            ]);

            $assignee = User::find($validated['assigned_to']);
            $count    = 0;

            foreach ($validated['lead_ids'] as $leadId) {
                $lead = EduLead::find($leadId);
                if (!$lead) continue;
                if ($user->isLeadManager() && $lead->branch_id !== $user->branch_id) continue;

                $lead->update(['assigned_to' => $validated['assigned_to']]);

                if ($request->filled('notes')) {
                    EduLeadNote::create([
                        'edu_lead_id' => $lead->id,
                        'created_by'  => $user->id,
                        'note'        => 'Bulk Assignment Note: ' . $validated['notes'],
                    ]);
                }
                $count++;
            }

            return response()->json([
                'success'         => true,
                'message'         => 'Bulk assignment completed!',
                'count'           => $count,
                'telecaller_name' => $assignee->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk assign error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error in bulk assignment'], 500);
        }
    }

    // =========================================================================
    // ADD CALL LOG
    // =========================================================================
    public function addCall(Request $request, EduLead $eduLead)
    {
        try {
            $validated = $request->validate([
                'call_datetime' => 'required|date',
                'call_status'   => 'required|in:connected,not_connected',
                'duration'      => 'nullable|string|max:50',
                'remarks'       => 'nullable|string',
                'next_action'   => 'nullable|string',
                'followup_date' => 'nullable|date|after_or_equal:today',
            ]);

            // ── Create the call log ────────────────────────────────────────
            $callLog = $eduLead->callLogs()->create([
                'call_datetime' => $validated['call_datetime'],
                'call_status'   => $validated['call_status'],
                'duration'      => $validated['duration']    ?? null,
                'remarks'       => $validated['remarks']     ?? null,
                'next_action'   => $validated['next_action'] ?? null,
                'user_id'       => auth()->id(),
            ]);

            // ── Update lead's call status on the lead itself ───────────────
            $eduLead->update(['status' => $validated['call_status']]);

            // ── Auto-create followup if date is provided ───────────────────
            if (!empty($validated['followup_date'])) {

                $notePrefix = $validated['call_status'] === 'connected'
                    ? 'Follow-up from connected call on '
                    : 'Retry call — not connected on ';

                $eduLead->followups()->create([
                    'followup_date' => $validated['followup_date'],
                    'priority'      => 'medium',
                    'notes'         => $notePrefix . now()->format('d M Y'),
                    'assigned_to'   => $eduLead->assigned_to ?? auth()->id(),
                    'created_by'    => auth()->id(),
                    'status'        => 'pending',
                ]);

                $eduLead->update([
                    'followup_date'   => $validated['followup_date'],
                    'followup_status' => 'pending',
                ]);
            }

            // ── Build inline HTML for JS prepend (no full reload) ─────────
            $callLog->load('user');

            $callStatusBadge = $callLog->call_status === 'connected'
                ? '<span class="badge bg-success"><i class="las la-phone me-1"></i>Connected</span>'
                : '<span class="badge bg-danger"><i class="las la-phone-slash me-1"></i>Not Connected</span>';

            $remarksHtml = $callLog->remarks
                ? '<div class="mb-1 p-2 bg-light rounded small">
                    <i class="las la-comment-alt me-1 text-muted"></i>'
                    . e($callLog->remarks) .
                '</div>'
                : '';

            $durationHtml = $callLog->duration
                ? '<p class="mb-1 small text-muted">
                    <i class="las la-stopwatch me-1"></i>Duration: ' . e($callLog->duration) .
                '</p>'
                : '';

            $nextActionHtml = $callLog->next_action
                ? '<p class="mb-0 small text-muted">
                    <i class="las la-arrow-right me-1"></i>
                    <strong>Next:</strong> ' . e($callLog->next_action) .
                '</p>'
                : '';

            $deleteBtn = '<button class="btn btn-sm btn-outline-danger deleteCall ms-2 flex-shrink-0"
                                data-id="' . $callLog->id . '" title="Delete Call">
                            <i class="las la-trash"></i>
                        </button>';

            $html = '
                <div class="call-log-item" id="call-' . $callLog->id . '">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                <strong>' . e($callLog->user->name ?? '—') . '</strong>
                                ' . $callStatusBadge . '
                                <small class="text-muted ms-auto">
                                    <i class="las la-clock me-1"></i>
                                    ' . $callLog->call_datetime->format('d M Y, h:i A') . '
                                </small>
                            </div>
                            ' . $durationHtml . '
                            ' . $remarksHtml . '
                            ' . $nextActionHtml . '
                        </div>
                        ' . $deleteBtn . '
                    </div>
                </div>
            ';

            return response()->json([
                'success' => true,
                'message' => 'Call logged successfully!',
                'html'    => $html,
                'data'    => $callLog,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->validator->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error logging call: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error logging call: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // ADD FOLLOWUP
    // =========================================================================
    public function addFollowup(Request $request, EduLead $eduLead)
    {
        $user = Auth::user();

        if ($user->isLeadManager() && $eduLead->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }

        try {
            $validated = $request->validate([
                'followup_date' => 'required|date|after_or_equal:today',
                'followup_time' => 'nullable|date_format:H:i',
                'priority'      => 'required|in:low,medium,high',
                'notes'         => 'nullable|string|max:1000',
            ]);

            EduLeadFollowup::create([
                'edu_lead_id'   => $eduLead->id,
                'followup_date' => $validated['followup_date'],
                'followup_time' => $validated['followup_time'] ?? null,
                'priority'      => $validated['priority'],
                'notes'         => $validated['notes']         ?? null,
                'assigned_to'   => $eduLead->assigned_to       ?? auth()->id(),
                'created_by'    => auth()->id(),
                'status'        => 'pending',
            ]);

            $eduLead->update([
                'followup_date'   => $validated['followup_date'],
                'followup_status' => 'pending',
            ]);

            return response()->json(['success' => true, 'message' => 'Followup scheduled successfully!']);

        } catch (\Exception $e) {
            Log::error('Add followup error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error scheduling followup'], 500);
        }
    }

    // =========================================================================
    // COMPLETE FOLLOWUP
    // =========================================================================
    public function completeFollowup(EduLeadFollowup $followup)
    {
        if ($followup->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Already completed']);
        }

        $followup->update(['status' => 'completed', 'completed_at' => now()]);
        $followup->eduLead->update(['followup_status' => 'completed']);

        return response()->json(['success' => true, 'message' => 'Follow-up marked as completed!']);
    }

    // =========================================================================
    // ADD NOTE
    // =========================================================================
    public function addNote(Request $request, EduLead $eduLead)
    {
        $user = Auth::user();

        if ($user->isLeadManager() && $eduLead->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }

        try {
            $validated = $request->validate(['note' => 'required|string']);

            $note = EduLeadNote::create([
                'edu_lead_id' => $eduLead->id,
                'created_by'  => auth()->id(),
                'note'        => $validated['note'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!',
                'note'    => $note->load('createdBy'),
            ]);

        } catch (\Exception $e) {
            Log::error('Add note error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error adding note'], 500);
        }
    }

    // =========================================================================
    // DELETE FOLLOWUP
    // =========================================================================
    public function deleteFollowup(EduLeadFollowup $followup)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() &&
            $followup->created_by  !== $user->id &&
            $followup->assigned_to !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($followup->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Cannot delete completed followups'], 400);
        }

        $followup->delete();
        return response()->json(['success' => true, 'message' => 'Followup deleted successfully!']);
    }

    // =========================================================================
    // DELETE CALL LOG
    // =========================================================================
    public function deleteCall(EduCallLog $call)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOperationHead() &&
            !$user->isLeadManager() && $call->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $call->delete();
        return response()->json(['success' => true, 'message' => 'Call log deleted successfully!']);
    }

    // =========================================================================
    // DELETE NOTE
    // =========================================================================
    public function deleteNote(EduLeadNote $note)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOperationHead() &&
            !$user->isLeadManager() && $note->created_by !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $note->delete();
        return response()->json(['success' => true, 'message' => 'Note deleted successfully!']);
    }

    // =========================================================================
    // EXPORT CSV
    // =========================================================================
    public function export(Request $request)
    {
        try {
            $user  = Auth::user();
            $query = EduLead::with(['course.programme', 'leadSource', 'createdBy', 'assignedTo', 'branch']);

            if ($user->isLeadManager()) $query->where('branch_id', $user->branch_id);

            if ($request->filled('interest_level'))    $query->where('interest_level',  $request->interest_level);
            if ($request->filled('final_status'))      $query->where('final_status',    $request->final_status);
            if ($request->filled('lead_source_id'))    $query->where('lead_source_id',  $request->lead_source_id);
            if ($request->filled('course_id'))         $query->where('course_id',       $request->course_id);
            if ($request->filled('programme_id'))      $query->whereHas('course', fn($q) => $q->where('programme_id', $request->programme_id));
            if ($request->filled('institution_type'))  $query->where('institution_type', $request->institution_type);
            if ($request->filled('state'))             $query->where('state',            $request->state);
            if ($request->filled('district'))          $query->where('district',         $request->district);
            if ($request->filled('preferred_state'))   $query->where('preferred_state',  $request->preferred_state);
            if ($request->filled('branch_id'))         $query->where('branch_id',        $request->branch_id);
            if ($request->filled('date_from'))         $query->whereDate('created_at',  '>=', $request->date_from);
            if ($request->filled('date_to'))           $query->whereDate('created_at',  '<=', $request->date_to);
            if ($request->filled('school_department')) $query->where('school_department', $request->school_department);
            if ($request->filled('college_department'))$query->where('college_department',$request->college_department);

            $leads    = $query->get();
            $filename = 'education_leads_' . date('Y-m-d_His') . '.csv';

            $callback = function () use ($leads) {
                $file = fopen('php://output', 'w');
                fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM

                fputcsv($file, [
                    'Lead Code', 'Name', 'Email', 'Phone', 'WhatsApp',
                    'State', 'District', 'Preferred State',
                    'Branch', 'Institution Type',
                    'School', 'School Department',
                    'College', 'College Department',
                    'Programme', 'Course', 'Addon Course',
                    'Application No.', 'Lead Source', 'Agent Name',
                    'Interest Level', 'Final Status', 'Call Status',
                    'WhatsApp Link', 'Application Form URL',
                    'Booking Payment', 'Fees Collection', 'Cancellation Reason',
                    'Assigned To', 'Created By', 'Created At',
                    'Followup Date', 'Remarks',
                ]);

                foreach ($leads as $lead) {
                    fputcsv($file, [
                        $lead->lead_code,
                        $lead->name,
                        $lead->email                           ?? '',
                        $lead->phone,
                        $lead->whatsapp_number                 ?? '',
                        $lead->state                           ?? '',
                        $lead->district                        ?? '',
                        $lead->preferred_state                 ?? '',
                        $lead->branch?->name                   ?? '',
                        ucfirst($lead->institution_type        ?? ''),
                        $lead->school                          ?? '',
                        $lead->school_department               ?? '',
                        $lead->college                         ?? '',
                        $lead->college_department              ?? '',
                        $lead->course?->programme?->name       ?? '',
                        $lead->course?->name                   ?? '',
                        $lead->addon_course                    ?? '',
                        $lead->application_number              ?? '',
                        $lead->leadSource?->name               ?? '',
                        $lead->agent_name                      ?? '',
                        ucfirst($lead->interest_level          ?? ''),
                        ucfirst(str_replace('_', ' ', $lead->final_status)),
                        ucfirst(str_replace('_', ' ', $lead->status        ?? '')),
                        $lead->whatsapp_link                   ?? '',
                        $lead->application_form_url            ?? '',
                        $lead->booking_payment                 ?? '',
                        $lead->fees_collection                 ?? '',
                        $lead->cancellation_reason             ?? '',
                        $lead->assignedTo?->name               ?? 'Unassigned',
                        $lead->createdBy?->name                ?? '',
                        $lead->created_at->format('d-m-Y H:i'),
                        $lead->followup_date?->format('d-m-Y') ?? '',
                        $lead->remarks                         ?? '',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'Error exporting leads');
        }
    }

    // =========================================================================
    // GET COURSES BY PROGRAMME (AJAX)
    // =========================================================================
    public function getCoursesByProgramme(Request $request)
    {
        $courses = Course::active()
            ->when($request->input('programme_id'), fn($q) => $q->where('programme_id', $request->programme_id))
            ->orderBy('name')
            ->get(['id', 'name', 'duration', 'programme_id']);

        return response()->json(['success' => true, 'courses' => $courses]);
    }

    // =========================================================================
    // GET DISTRICTS BY STATE (AJAX)
    // =========================================================================
    public function getDistrictsByState(Request $request)
    {
        $state     = $request->input('state', '');
        $districts = IndiaGeoHelper::districts($state);

        return response()->json(['success' => true, 'districts' => $districts]);
    }

    // =========================================================================
    // TODAY'S FOLLOWUPS (dashboard widget)
    // =========================================================================
    public function getTodayFollowups()
    {
        $user  = Auth::user();

        $query = EduLeadFollowup::with(['eduLead', 'assignedToUser'])
            ->where('status', 'pending')
            ->whereDate('followup_date', today())
            ->orderBy('followup_time');

        if ($user->isLeadManager()) {
            $query->whereHas('eduLead', fn($q) => $q->where('branch_id', $user->branch_id));
        }

        return response()->json(['success' => true, 'followups' => $query->get()]);
    }

    // =========================================================================
    // QUICK SEARCH
    // =========================================================================
    public function quickSearch(Request $request)
    {
        $user  = Auth::user();
        $query = $request->input('query', '');

        if (strlen($query) < 2) return response()->json(['leads' => []]);

        $leadsQuery = EduLead::with(['course.programme', 'assignedTo', 'leadSource', 'branch'])
            ->where(fn($q) => $q
                ->where('name',               'like', "%{$query}%")
                ->orWhere('phone',            'like', "%{$query}%")
                ->orWhere('email',            'like', "%{$query}%")
                ->orWhere('lead_code',        'like', "%{$query}%")
                ->orWhere('application_number','like', "%{$query}%")
                ->orWhere('school',           'like', "%{$query}%")
                ->orWhere('college',          'like', "%{$query}%")
            );

        if ($user->isLeadManager()) {
            $leadsQuery->where('branch_id', $user->branch_id);
        }

        $leads = $leadsQuery->limit(8)->get()->map(fn($lead) => [
            'id'               => $lead->id,
            'lead_code'        => $lead->lead_code,
            'name'             => $lead->name,
            'phone'            => $lead->phone,
            'email'            => $lead->email,
            'status'           => $lead->final_status,
            'branch'           => $lead->branch?->name             ?? 'N/A',
            'institution_type' => ucfirst($lead->institution_type  ?? ''),
            'institution'      => $lead->institution_summary,
            'programme'        => $lead->course?->programme?->name ?? 'N/A',
            'course'           => $lead->course?->name             ?? 'N/A',
            'assigned_to'      => $lead->assignedTo?->name         ?? 'Unassigned',
            'url'              => route('edu-leads.show', $lead->id),
        ]);

        return response()->json(['leads' => $leads]);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================
    private function getAssignableUsers(User $user): \Illuminate\Support\Collection
    {
        if ($user->isSuperAdmin() || $user->isOperationHead()) {
            return User::telecallers()->active()
                ->with('branch:id,name')
                ->select('id', 'name', 'branch_id')
                ->orderBy('name')
                ->get();
        }

        if ($user->isLeadManager()) {
            return User::telecallers()->active()
                ->where('branch_id', $user->branch_id)
                ->select('id', 'name', 'branch_id')
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    private function leadValidationRules(?int $leadId = null): array
    {
        return [
            // Basic
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'required|string|max:20|unique:edu_leads,phone' . ($leadId ? ',' . $leadId : ''),
            'whatsapp_number' => 'nullable|string|max:20',
            'description'     => 'nullable|string',

            // Location
            'state'           => 'nullable|string|max:100',
            'district'        => 'nullable|string|max:100',
            'preferred_state' => 'nullable|string|max:100',  // ← new

            // Agent
            'agent_name'      => 'nullable|string|max:255',

            // Institution
            'institution_type'   => 'nullable|in:school,college,other',
            'school'             => 'nullable|string|max:255',
            'school_department'  => 'nullable|string|max:255',
            'college'            => 'nullable|string|max:255',
            'college_department' => 'nullable|string|max:255',

            // Programme & Course
            'course_id'          => 'nullable|exists:courses,id',
            'addon_course'       => 'nullable|string|max:255',

            // Application & Payment
            'application_number_suffix' => 'nullable|string|max:50',  // ← suffix only; full AJK- built in controller
            'whatsapp_link'             => 'nullable|url|max:500',
            'application_form_url'      => 'nullable|url|max:500',
            'booking_payment'           => 'nullable|numeric|min:0',
            'fees_collection'           => 'nullable|numeric|min:0',
            'cancellation_reason'       => 'nullable|string',

            // CRM
            'lead_source_id'  => 'required|exists:edu_lead_sources,id',
            'assigned_to'     => 'nullable|exists:users,id',
            'branch_id'       => 'nullable|exists:branches,id',
            'interest_level'  => 'nullable|in:hot,warm,cold',
            'final_status'    => 'nullable|in:pending,contacted,not_interested,follow_up,admitted,dropped',
            'status'          => 'nullable|in:pending,connected,not_connected,interested,not_interested,follow_up_scheduled,admitted,closed',
            'remarks'         => 'nullable|string',
            'next_action'     => 'nullable|string',
            'followup_date'   => 'nullable|date',
        ];
    }
}
