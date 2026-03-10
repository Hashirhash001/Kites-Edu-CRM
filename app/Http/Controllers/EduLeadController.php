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
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

        $baseQuery = EduLead::with([
            'course.programme', 'leadSource', 'createdBy', 'assignedTo', 'branch',
            'followups', 'latestFollowup'
        ]);
        $baseQuery->visibleTo($user);

        // ── Filters ───────────────────────────────────────────────────────────────
        if ($request->filled('interest_level'))
            $baseQuery->where('interest_level', $request->interest_level);

        if ($request->filled('lead_source_id') && $request->lead_source_id != 0)
            $baseQuery->where('lead_source_id', $request->lead_source_id);

        if ($request->filled('course_id'))
            $baseQuery->where('course_id', $request->course_id);

        if ($request->filled('programme_id'))
            $baseQuery->whereHas('course', fn($q) => $q->where('programme_id', $request->programme_id));

        if ($request->filled('institution_type'))
            $baseQuery->where('institution_type', $request->institution_type);

        if ($request->filled('state'))
            $baseQuery->where('state', 'like', '%' . $request->state . '%');

        if ($request->filled('district'))
            $baseQuery->where('district', 'like', '%' . $request->district . '%');

        if ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isOperationHead()))
            $baseQuery->where('branch_id', $request->branch_id);

        if ($request->filled('date_from'))
            $baseQuery->whereDate('created_at', '>=', $request->date_from);

        if ($request->filled('date_to'))
            $baseQuery->whereDate('created_at', '<=', $request->date_to);

        if ($request->filled('school_department'))
            $baseQuery->whereRaw('LOWER(TRIM(school_department)) = ?', [strtolower(trim($request->school_department))]);

        if ($request->filled('college_department'))
            $baseQuery->whereRaw('LOWER(TRIM(college_department)) = ?', [strtolower(trim($request->college_department))]);

        if ($request->filled('assigned_to')) {
            $request->assigned_to === 'unassigned'
                ? $baseQuery->whereNull('assigned_to')
                : $baseQuery->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('agent_name'))
            $baseQuery->where(fn($q) => $q
                ->where('agent_name',    'like', '%' . $request->agent_name . '%')
                ->orWhere('referral_name', 'like', '%' . $request->agent_name . '%')
            );

        if ($request->filled('call_status'))
            $baseQuery->where('call_status', $request->call_status);

        if ($request->filled('counseling_stage'))
            $baseQuery->where('status', $request->counseling_stage);

        if ($request->filled('followup_count')) {
            match($request->followup_count) {
                '0'    => $baseQuery->doesntHave('followups'),
                '1'    => $baseQuery->has('followups', '=', 1),
                '2'    => $baseQuery->has('followups', '=', 2),
                '3'    => $baseQuery->has('followups', '>=', 3),
                default => null,
            };
        }

        if ($request->filled('followup_number')) {
            $baseQuery->has('followups', '>=', (int) $request->followup_number);
        }

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
                ->orWhere('referral_name',    'like', $s)
                ->orWhere('application_number', 'like', $s)
            );
        }

        // ── Status counts ─────────────────────────────────────────────────────────
        $statusCounts = [
            'all'            => (clone $baseQuery)->count(),
            'pending'        => (clone $baseQuery)->where('final_status', 'pending')->count(),
            'contacted'      => (clone $baseQuery)->where('final_status', 'contacted')->count(),
            'follow_up'      => (clone $baseQuery)->where('final_status', 'follow_up')->count(),
            'admitted'       => (clone $baseQuery)->where('final_status', 'admitted')->count(),
            'not_interested' => (clone $baseQuery)->where('final_status', 'not_interested')->count(),
            'dropped'        => (clone $baseQuery)->where('final_status', 'dropped')->count(),
            'not_attended'   => (clone $baseQuery)->where('final_status', 'not_attended')->count(),
        ];

        $institutionCounts = [
            'school'  => (clone $baseQuery)->where('institution_type', 'school')->count(),
            'college' => (clone $baseQuery)->where('institution_type', 'college')->count(),
        ];

        // ── Apply status tab filter ───────────────────────────────────────────────
        $query = clone $baseQuery;
        if ($request->filled('final_status')) {
            $query->where('final_status', $request->final_status);
        }

        // ── Sorting & pagination ──────────────────────────────────────────────────
        $allowedSortColumns = [
            'lead_code', 'name', 'phone', 'final_status', 'course_id',
            'interest_level', 'lead_source_id', 'assigned_to', 'branch_id', 'created_at',
        ];
        $sortColumn    = in_array($request->get('sort_column'), $allowedSortColumns) ? $request->get('sort_column') : 'created_at';
        $sortDirection = in_array($request->get('sort_direction'), ['asc', 'desc']) ? $request->get('sort_direction') : 'desc';
        $perPage       = in_array((int) $request->get('per_page', 15), [15, 30, 50, 100]) ? (int) $request->get('per_page', 15) : 15;
        $page          = (int) $request->get('page', 1);

        // ── Followup number to display in column ─────────────────────────────────
        $followupNumber = $request->filled('followup_number')
            ? (int) $request->get('followup_number')
            : null;

        // ── Eager-load followups for the table column ─────────────────────────────
        // Always load all followups (needed for counts + picking by number).
        // latestFollowup is used as fallback when no followup_number filter is set.
        $leads = $query
            ->with([
                'followups',
                'latestFollowup',
            ])
            ->withCount('followups')
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $page);

        // ── Max followup number for the filter dropdown ───────────────────────
        $maxFollowupNumber = \App\Models\EduLeadFollowup::selectRaw('COUNT(*) as cnt')
            ->whereIn('edu_lead_id', (clone $query)->pluck('id'))
            ->groupBy('edu_lead_id')
            ->orderByDesc('cnt')
            ->value('cnt') ?? 5;
        $maxFollowupNumber = max($maxFollowupNumber, 5); // always show at least 5 options

        // ── Header badge counts ───────────────────────────────────────────────────
        $hotLeadsCount = EduLead::where('interest_level', 'hot')->visibleTo($user)->count();

        $pendingFollowupsCount = EduLeadFollowup::where('status', 'pending')
            ->whereDate('followup_date', '<=', today())
            ->when($user->isTelecaller(),  fn($q) => $q->where('assigned_to', $user->id))
            ->when($user->isLeadManager(), fn($q) => $q->whereHas('eduLead', fn($lq) => $lq->where('branch_id', $user->branch_id)))
            ->count();

        // ── View data ─────────────────────────────────────────────────────────────
        $courses         = Course::active()->with('programme')->orderBy('name')->get();
        $programmes      = Programme::active()->orderBy('name')->get();
        $leadSources     = EduLeadSource::where('is_active', true)->orderBy('name')->get();
        $branches        = Branch::active()->get();
        $assignableUsers = $this->getAssignableUsers($user);
        $states          = IndiaGeoHelper::states();
        $districtMap     = IndiaGeoHelper::districtMap();

        $isJson = $request->boolean('_json') || $request->wantsJson();

        if ($isJson) {
            return response()->json([
                'success'            => true,
                'html'               => view('edu-leads.partials.table-rows', compact('leads', 'followupNumber'))->render(),
                'pagination'         => $leads->links('pagination::bootstrap-5')->render(),
                'total'              => $leads->total(),
                'per_page'           => $leads->perPage(),
                'current_page'       => $leads->currentPage(),
                'last_page'          => $leads->lastPage(),
                'from'               => $leads->firstItem() ?? 0,
                'to'                 => $leads->lastItem()  ?? 0,
                'status_counts'      => $statusCounts,
                'institution_counts' => $institutionCounts,
                'max_followup_number' => $maxFollowupNumber,
            ]);
        }

        return view('edu-leads.index', compact(
            'leads', 'hotLeadsCount', 'pendingFollowupsCount',
            'courses', 'programmes', 'leadSources', 'branches',
            'assignableUsers', 'statusCounts', 'institutionCounts',
            'states', 'districtMap', 'followupNumber', 'maxFollowupNumber'
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

            // If phone belongs to a soft-deleted lead — restore & update instead of creating new
            $trashedLead = EduLead::withTrashed()
                ->where('phone', $validated['phone'])
                ->whereNotNull('deleted_at')
                ->first();

            if ($trashedLead) {
                $trashedLead->restore();

                // Merge new submission data onto the restored lead
                $trashedLead->update(array_merge($validated, [
                    'branch_id'   => ($user->isLeadManager() || $user->isTelecaller())
                                        ? $user->branch_id
                                        : ($validated['branch_id'] ?? $trashedLead->branch_id),
                    'created_by'  => $trashedLead->created_by, // keep original creator
                    'assigned_to' => $user->isTelecaller()
                                        ? $user->id
                                        : ($validated['assigned_to'] ?? $trashedLead->assigned_to),
                    'final_status'   => $validated['final_status']   ?? 'pending',
                    'interest_level' => $validated['interest_level'] ?? null,
                ]));

                Log::info('Soft-deleted lead restored on re-create', [
                    'lead_id'    => $trashedLead->id,
                    'lead_code'  => $trashedLead->lead_code,
                    'created_by' => $user->id,
                ]);

                return response()->json([
                    'success'      => true,
                    'message'      => 'This lead was previously deleted and has been restored with updated information.',
                    'lead_id'      => $trashedLead->id,
                    'lead_code'    => $trashedLead->lead_code,
                    'redirect_url' => route('edu-leads.show', $trashedLead->id),
                ]);
            }

            if (!empty($validated['application_number_suffix'])) {
                $validated['application_number'] = 'AJK-' . trim($validated['application_number_suffix']);
            }
            unset($validated['application_number_suffix']);

            $validated['final_status']   = $validated['final_status']   ?? 'pending';
            $validated['status']         = $validated['status']         ?? null;
            $validated['interest_level'] = $validated['interest_level'] ?? null;

            if (($validated['status'] ?? null) !== 'booking') {
                $validated['booking_payment'] = null;
                $validated['fees_collection'] = null;
            }

            if (($validated['status'] ?? null) !== 'cancelled') {
                $validated['cancellation_reason'] = null;
            }

            $sourceName = strtolower(
                \App\Models\EduLeadSource::find($validated['lead_source_id'])?->name ?? ''
            );
            if (!str_contains($sourceName, 'agent') && !str_contains($sourceName, 'partner')) {
                $validated['agent_name'] = null;
            }
            if (!str_contains($sourceName, 'referral')) {
                $validated['referral_name'] = null;
            }

            $branchId = ($user->isLeadManager() || $user->isTelecaller())
                ? $user->branch_id
                : ($validated['branch_id'] ?? null);

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
            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Lead creation DB error: ' . $e->getMessage());
            if ($e->errorInfo[1] === 1062) {
                $field = str_contains($e->getMessage(), 'phone') ? 'phone' : 'field';
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is already registered.',
                    'errors'  => ['phone' => ['This phone number already exists in the system.']],
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => 'A database error occurred. Please try again.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Lead creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
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

            if (array_key_exists('application_number_suffix', $validated)) {
                $suffix = trim($validated['application_number_suffix'] ?? '');
                $validated['application_number'] = $suffix ? 'AJK-' . $suffix : null;
                unset($validated['application_number_suffix']);
            }

            $validated['final_status'] = $validated['final_status'] ?? $eduLead->final_status;
            $validated['status']       = $validated['status']       ?? $eduLead->status;

            if ($validated['status'] !== 'booking') {
                $validated['booking_payment'] = null;
                $validated['fees_collection'] = null;
            }

            if ($validated['status'] !== 'cancelled') {
                $validated['cancellation_reason'] = null;
            }

            $sourceName = strtolower(
                \App\Models\EduLeadSource::find($validated['lead_source_id'] ?? $eduLead->lead_source_id)?->name ?? ''
            );
            if (!str_contains($sourceName, 'agent') && !str_contains($sourceName, 'partner')) {
                $validated['agent_name'] = null;
            }
            if (!str_contains($sourceName, 'referral')) {
                $validated['referral_name'] = null;
            }

            if ($user->isLeadManager() || $user->isTelecaller()) {
                $validated['branch_id'] = $user->branch_id;
            }

            if ($validated['final_status'] === 'admitted' && $eduLead->final_status !== 'admitted') {
                $validated['admitted_at'] = now();
            }

            if ($validated['status'] === 'cancelled' && $eduLead->status !== 'cancelled') {
                $validated['cancelled_at'] = now();
            } elseif ($validated['final_status'] === 'dropped' && $eduLead->final_status !== 'dropped') {
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
            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Lead update DB error: ' . $e->getMessage());
            if ($e->errorInfo[1] === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is already registered.',
                    'errors'  => ['phone' => ['This phone number already exists in the system.']],
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => 'A database error occurred. Please try again.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Lead update error: ' . $e->getMessage()); // ✅ log it, don't expose it
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
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
                'pending', 'contacted', 'follow_up', 'admitted', 'not_interested', 'dropped', 'not_attended'
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

            if ($assignee->role === 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leads cannot be assigned to Super Admin.',
                ], 422);
            }

            // Telecallers and Lead Managers must be in the same branch as the lead
            if (in_array($assignee->role, ['telecaller', 'lead_manager'])) {
                if ($assignee->branch_id !== $eduLead->branch_id) {
                    return response()->json([
                        'success' => false,
                        'message' => $assignee->name . ' belongs to a different branch. Telecallers and Lead Managers can only be assigned leads from their own branch.',
                    ], 422);
                }
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
                'branch_name'     => $assignee->branch?->name ?? 'No Branch',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lead assign error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error assigning lead.'], 500);
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

            $assignee = User::findOrFail($validated['assigned_to']);

            if ($assignee->role === 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leads cannot be assigned to Super Admin.',
                ], 422);
            }

            $count   = 0;
            $skipped = 0;

            foreach ($validated['lead_ids'] as $leadId) {
                $lead = EduLead::find($leadId);
                if (!$lead) { $skipped++; continue; }

                // LeadManager (assigner) can only assign leads from their own branch
                if ($user->isLeadManager() && $lead->branch_id !== $user->branch_id) {
                    $skipped++; continue;
                }

                // Telecallers and Lead Managers can only receive leads from their own branch
                if (in_array($assignee->role, ['telecaller', 'lead_manager'])) {
                    if ($assignee->branch_id !== $lead->branch_id) {
                        $skipped++; continue;
                    }
                }

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

            $message = "Successfully assigned {$count} lead(s) to {$assignee->name}.";
            if ($skipped > 0) {
                $message .= " {$skipped} lead(s) were skipped (branch mismatch or not found).";
            }

            return response()->json([
                'success'         => true,
                'message'         => $message,
                'count'           => $count,
                'skipped'         => $skipped,
                'telecaller_name' => $assignee->name,
                'branch_name'     => $assignee->branch?->name ?? 'No Branch',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk assign error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error in bulk assignment.'], 500);
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
                'followup_time' => 'nullable|date_format:H:i,H:i:s',
                'notes'         => 'nullable|string|max:1000',
            ]);

            if (!empty($validated['followup_time'])) {
                $validated['followup_time'] = substr($validated['followup_time'], 0, 5);
            }

            EduLeadFollowup::create([
                'edu_lead_id'   => $eduLead->id,
                'followup_date' => $validated['followup_date'],
                'followup_time' => $validated['followup_time'] ?? null,
                'priority'      => 'medium',
                'notes'         => $validated['notes'] ?? null,
                'assigned_to'   => $eduLead->assigned_to ?? auth()->id(),
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
    // COMPLETE FOLLOWUP  — now records outcome & updates lead status
    // =========================================================================
    public function completeFollowup(Request $request, EduLeadFollowup $followup)
    {
        try {
            $user = Auth::user();

            if ($followup->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This followup is already completed.',
                ], 422);
            }

            $validated = $request->validate([
                'outcome_final_status' => ['required', Rule::in([
                    'pending', 'contacted', 'follow_up', 'admitted',
                    'not_interested', 'dropped', 'not_attended',
                ])],
                'call_status' => ['nullable', Rule::in(['contacted', 'not_attended'])],
                'outcome_status'   => ['nullable', Rule::in([
                    'whatsapp_link_submitted', 'application_form_submitted',
                    'booking', 'cancelled',
                ])],
                'outcome_interest' => ['nullable', Rule::in(['hot', 'warm', 'cold'])],
                'outcome_notes'    => ['nullable', 'string', 'max:2000'],
                'next_action'      => ['nullable', 'string', 'max:500'],
            ]);

            $lead = $followup->eduLead;

            // ── Snapshot old values for history ──────────────────────────
            $oldFinalStatus = $lead->final_status;
            $oldStatus      = $lead->status;
            $oldInterest    = $lead->interest_level;

            // ── Mark followup complete with outcome ───────────────────────
            $followup->update(array_merge($validated, [
                'status'       => 'completed',
                'completed_at' => now(),
            ]));

            // ── Propagate outcome onto the lead ───────────────────────────
            $leadUpdates = [
                'final_status' => $validated['outcome_final_status'],
            ];

            if (!empty($validated['outcome_status'])) {
                $leadUpdates['status'] = $validated['outcome_status'];
            }

            if (!empty($validated['call_status'])) {
                $leadUpdates['call_status'] = $validated['call_status'];
            }

            if (!empty($validated['outcome_interest'])) {
                $leadUpdates['interest_level'] = $validated['outcome_interest'];
            }

            if ($validated['outcome_final_status'] === 'admitted' && $lead->final_status !== 'admitted') {
                $leadUpdates['admitted_at'] = now();
            }

            $lead->update($leadUpdates);

            // ── Write status history row (linked to this followup) ────────
            $changes = [];

            if ($oldFinalStatus !== $validated['outcome_final_status']) {
                $changes['old_status'] = $oldFinalStatus;
                $changes['new_status'] = $validated['outcome_final_status'];
            }

            if (!empty($validated['outcome_interest']) && $oldInterest !== $validated['outcome_interest']) {
                $changes['old_interest_level'] = $oldInterest;
                $changes['new_interest_level'] = $validated['outcome_interest'];
            }

            if (!empty($changes)) {
                $lead->statusHistory()->create(array_merge($changes, [
                    'user_id'     => $user->id,
                    'followup_id' => $followup->id,
                ]));
            }

            Log::info('Followup completed with outcome', [
                'followup_id'    => $followup->id,
                'followup_number'=> $followup->followup_number,
                'lead_id'        => $lead->id,
                'old_status'     => $oldFinalStatus,
                'new_status'     => $validated['outcome_final_status'],
                'completed_by'   => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Followup completed successfully.",
                'lead'    => [
                    'final_status'   => $lead->final_status,
                    'status'         => $lead->status,
                    'interest_level' => $lead->interest_level,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Complete followup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error completing followup.',
            ], 500);
        }
    }

    // =========================================================================
    // UPDATE FOLLOWUP
    // =========================================================================
    public function updateFollowup(Request $request, EduLeadFollowup $followup)
    {
        try {
            $user = Auth::user();

            $canEdit = $user->isSuperAdmin()
                || $user->isOperationHead()
                || ($user->isLeadManager() && $followup->eduLead->branch_id === $user->branch_id)
                || ($user->isTelecaller()  && $followup->assigned_to === $user->id);

            if (!$canEdit) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'followup_date'        => 'required|date',
                'followup_time'        => 'nullable|date_format:H:i,H:i:s',
                'notes'                => 'nullable|string|max:1000',
                'priority'             => 'nullable|in:low,medium,high',
                'outcome_final_status' => ['nullable', Rule::in([
                    'pending', 'contacted', 'follow_up', 'admitted',
                    'not_interested', 'dropped', 'not_attended',
                ])],
                'call_status' => ['nullable', Rule::in(['contacted', 'not_attended', ''])],
                'outcome_status'       => ['nullable', Rule::in([
                    'whatsapp_link_submitted', 'application_form_submitted',
                    'booking', 'cancelled', '',
                ])],
                'outcome_interest'     => ['nullable', Rule::in(['hot', 'warm', 'cold', ''])],
                'outcome_notes'        => 'nullable|string|max:2000',
                'next_action'          => 'nullable|string|max:500',
            ]);

            // Strip seconds from time if present (e.g. "14:30:00" → "14:30")
            if (!empty($validated['followup_time'])) {
                $validated['followup_time'] = substr($validated['followup_time'], 0, 5);
            }

            // Treat empty strings as null for nullable fields
            foreach (['outcome_status', 'outcome_interest', 'call_status'] as $field) {
                if (isset($validated[$field]) && $validated[$field] === '') {
                    $validated[$field] = null;
                }
            }

            $followup->update($validated);

            // If outcome_final_status was edited, propagate to the lead
            $lead = $followup->eduLead;
            $leadUpdates = [];

            if (!empty($validated['outcome_final_status'])) {
                $leadUpdates['final_status'] = $validated['outcome_final_status'];
            }
            if (!empty($validated['call_status'])) {
                $leadUpdates['call_status'] = $validated['call_status'];
            }

            if (!empty($validated['outcome_status'])) {
                $leadUpdates['status'] = $validated['outcome_status'];
            }
            if (!empty($validated['outcome_interest'])) {
                $leadUpdates['interest_level'] = $validated['outcome_interest'];
            }

            if (!empty($leadUpdates)) {
                $lead->update($leadUpdates);
            }

            return response()->json([
                'success' => true,
                'message' => "Followup updated successfully.",
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update followup error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating followup.'], 500);
        }
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
        try {
            $user = Auth::user();

            $canDelete = $user->isSuperAdmin()
                || $user->isOperationHead()
                || ($user->isLeadManager() && $followup->eduLead->branch_id === $user->branch_id)
                || ($user->isTelecaller()  && $followup->assigned_to === $user->id);

            if (!$canDelete) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // if ($followup->status === 'completed') {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Completed followups cannot be deleted.',
            //     ], 422);
            // }

            $followup->delete();

            return response()->json([
                'success' => true,
                'message' => "Followup deleted.",
            ]);

        } catch (\Exception $e) {
            Log::error('Delete followup error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting followup.'], 500);
        }
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
    // EXPORT XLSX
    // =========================================================================
    public function export(Request $request)
    {
        try {
            $user           = Auth::user();
            $followupNumber = $request->filled('followup_number') ? (int) $request->followup_number : null;

            $query = EduLead::with([
                'course.programme', 'leadSource', 'createdBy', 'assignedTo', 'branch',
                'followups' => fn($q) => $q->orderBy('followup_number'),
            ]);

            $query->visibleTo($user);

            // ── Filters ───────────────────────────────────────────────────────
            if ($request->filled('interest_level'))     $query->where('interest_level',   $request->interest_level);
            if ($request->filled('final_status'))       $query->where('final_status',     $request->final_status);
            if ($request->filled('lead_source_id'))     $query->where('lead_source_id',   $request->lead_source_id);
            if ($request->filled('course_id'))          $query->where('course_id',        $request->course_id);
            if ($request->filled('programme_id'))       $query->whereHas('course', fn($q) => $q->where('programme_id', $request->programme_id));
            if ($request->filled('institution_type'))   $query->where('institution_type', $request->institution_type);
            if ($request->filled('state'))              $query->where('state',            'like', '%' . $request->state    . '%');
            if ($request->filled('district'))           $query->where('district',         'like', '%' . $request->district . '%');
            if ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isOperationHead()))
                                                        $query->where('branch_id',        $request->branch_id);
            if ($request->filled('date_from'))          $query->whereDate('created_at',  '>=', $request->date_from);
            if ($request->filled('date_to'))            $query->whereDate('created_at',  '<=', $request->date_to);
            if ($request->filled('school_department'))  $query->whereRaw('LOWER(TRIM(school_department)) = ?',  [strtolower(trim($request->school_department))]);
            if ($request->filled('college_department')) $query->whereRaw('LOWER(TRIM(college_department)) = ?', [strtolower(trim($request->college_department))]);
            if ($request->filled('assigned_to')) {
                $request->assigned_to === 'unassigned'
                    ? $query->whereNull('assigned_to')
                    : $query->where('assigned_to', $request->assigned_to);
            }
            if ($request->filled('agent_name'))       $query->where('agent_name',  'like', '%' . $request->agent_name . '%');
            if ($request->filled('call_status'))      $query->where('call_status', $request->call_status);
            if ($request->filled('counseling_stage')) $query->where('status',      $request->counseling_stage);
            if ($request->filled('followup_count')) {
                match($request->followup_count) {
                    '0'     => $query->doesntHave('followups'),
                    '1'     => $query->has('followups', '=', 1),
                    '2'     => $query->has('followups', '=', 2),
                    '3'     => $query->has('followups', '>=', 3),
                    default => null,
                };
            }
            if ($request->filled('followup_number')) {
                $query->has('followups', '>=', (int) $request->followup_number);
            }
            if ($request->filled('search')) {
                $s = '%' . $request->search . '%';
                $query->where(fn($q) => $q
                    ->where('name',                'like', $s)
                    ->orWhere('email',             'like', $s)
                    ->orWhere('phone',             'like', $s)
                    ->orWhere('whatsapp_number',   'like', $s)
                    ->orWhere('lead_code',         'like', $s)
                    ->orWhere('school',            'like', $s)
                    ->orWhere('college',           'like', $s)
                    ->orWhere('agent_name',        'like', $s)
                    ->orWhere('application_number','like', $s)
                );
            }

            $leads    = $query->orderBy('created_at', 'desc')->get();
            $filename = 'education_leads_' . date('Y-m-d_His') . '.xlsx';

            // ── Ordinal helper ────────────────────────────────────────────────
            $ordinal = function(int $n): string {
                $suffix = match(true) {
                    $n % 100 >= 11 && $n % 100 <= 13 => 'th',
                    $n % 10 === 1 => 'st',
                    $n % 10 === 2 => 'nd',
                    $n % 10 === 3 => 'rd',
                    default       => 'th',
                };
                return $n . $suffix;
            };

            // ── Followup outcome_status label helper ──────────────────────────
            $fuCounselingStage = function(?EduLeadFollowup $fu): string {
                if (!$fu || empty($fu->outcome_status)) return '';
                return EduLead::COUNSELING_STAGES[$fu->outcome_status]
                    ?? ucfirst(str_replace('_', ' ', $fu->outcome_status));
            };

            // ── Build spreadsheet ─────────────────────────────────────────────
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Education Leads');

            // ── Determine max followup count for dynamic columns ──────────────
            $maxFuCount = $followupNumber
                ? 1
                : $leads->map(fn($l) => $l->followups->count())->max();
            $maxFuCount = max((int) $maxFuCount, 1);

            // ── Static headers ────────────────────────────────────────────────
            $staticHeaders = [
                ['header' => 'Lead Code',           'width' => 16],
                ['header' => 'Name',                'width' => 24],
                ['header' => 'Email',               'width' => 28],
                ['header' => 'Phone',               'width' => 16],
                ['header' => 'WhatsApp Number',     'width' => 18],
                ['header' => 'State',               'width' => 18],
                ['header' => 'District',            'width' => 18],
                ['header' => 'Branch',              'width' => 20],
                ['header' => 'Institution Type',    'width' => 18],
                ['header' => 'School',              'width' => 28],
                ['header' => 'School Department',   'width' => 22],
                ['header' => 'College',             'width' => 28],
                ['header' => 'College Department',  'width' => 22],
                ['header' => 'Programme',           'width' => 22],
                ['header' => 'Course',              'width' => 26],
                ['header' => 'Addon Course',        'width' => 22],
                ['header' => 'Lead Source',         'width' => 18],
                ['header' => 'Reference Name',      'width' => 22],
                ['header' => 'Application Number',  'width' => 20],
                ['header' => 'Booking Payment (₹)', 'width' => 20],
                ['header' => 'Fees Collected (₹)',  'width' => 20],
                ['header' => 'Cancellation Reason', 'width' => 28],
                ['header' => 'Interest Level',      'width' => 16],
                ['header' => 'Candidate Status',    'width' => 20],
                ['header' => 'Call Status',         'width' => 18],
                ['header' => 'Counseling Stage',    'width' => 26],
                ['header' => 'Assigned To',         'width' => 20],
                ['header' => 'Created By',          'width' => 20],
                ['header' => 'Total Followups',     'width' => 16],
                ['header' => 'Created At',          'width' => 20],
            ];

            // ── Dynamic followup headers ──────────────────────────────────────
            $followupHeaders = [];
            if ($followupNumber) {
                $label = $ordinal($followupNumber) . ' Followup';
                $followupHeaders[] = ['header' => $label . ' Date',             'width' => 20];
                $followupHeaders[] = ['header' => $label . ' Counseling Stage', 'width' => 26];
                $followupHeaders[] = ['header' => $label . ' Notes',            'width' => 40];
            } else {
                for ($i = 1; $i <= $maxFuCount; $i++) {
                    $label = $ordinal($i) . ' Followup';
                    $followupHeaders[] = ['header' => $label . ' Date',             'width' => 20];
                    $followupHeaders[] = ['header' => $label . ' Counseling Stage', 'width' => 26];
                    $followupHeaders[] = ['header' => $label . ' Notes',            'width' => 40];
                }
            }

            $allHeaders = array_merge($staticHeaders, $followupHeaders);

            // ── Write headers ─────────────────────────────────────────────────
            $colIndex = 1;
            $colMap   = [];
            foreach ($allHeaders as $meta) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $colMap[]  = $colLetter;
                $sheet->setCellValue("{$colLetter}1", $meta['header']);
                $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
                $colIndex++;
            }

            $lastCol     = end($colMap);
            $headerRange = "A1:{$lastCol}1";

            // ── Header styling ────────────────────────────────────────────────
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF667EEA'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF5A67D8'],
                    ],
                ],
            ]);

            $sheet->freezePane('A2');
            $sheet->getRowDimension(1)->setRowHeight(22);

            // ── Data rows ─────────────────────────────────────────────────────
            $row   = 2;
            $today = \Carbon\Carbon::today();

            foreach ($leads as $lead) {
                $orderedFollowups = $lead->followups->sortBy('followup_number')->values();

                $totalFu    = $orderedFollowups->count();
                $overdueFu  = $orderedFollowups->filter(fn($f) =>
                    $f->status === 'pending' &&
                    \Carbon\Carbon::parse($f->followup_date)->startOfDay()->lt($today)
                )->count();
                $todayFu    = $orderedFollowups->filter(fn($f) =>
                    $f->status === 'pending' &&
                    \Carbon\Carbon::parse($f->followup_date)->isToday()
                )->count();
                $pendingFu  = $orderedFollowups->where('status', 'pending')->count();
                $upcomingFu = max(0, $pendingFu - $overdueFu - $todayFu);
                $doneFu     = $orderedFollowups->where('status', 'completed')->count();

                $referenceName = $lead->agent_name ?? $lead->referral_name ?? '';

                // ── Static row data ───────────────────────────────────────────
                $rowData = [
                    $lead->lead_code,
                    $lead->name,
                    $lead->email              ?? '',
                    $lead->phone,
                    $lead->whatsapp_number    ?? '',
                    $lead->state              ?? '',
                    $lead->district           ?? '',
                    $lead->branch?->name      ?? '',
                    ucfirst($lead->institution_type ?? ''),
                    $lead->school             ?? '',
                    $lead->school_department  ?? '',
                    $lead->college            ?? '',
                    $lead->college_department ?? '',
                    $lead->course?->programme?->name ?? '',
                    $lead->course?->name      ?? '',
                    $lead->addon_course       ?? '',
                    $lead->leadSource?->name  ?? '',
                    $referenceName,
                    $lead->application_number  ?? '',
                    $lead->booking_payment     ?? '',
                    $lead->fees_collection     ?? '',
                    $lead->cancellation_reason ?? '',
                    ucfirst($lead->interest_level ?? ''),
                    EduLead::FINAL_STATUSES[$lead->final_status]   ?? ucfirst(str_replace('_', ' ', $lead->final_status ?? '')),
                    EduLead::CALL_STATUSES[$lead->call_status]     ?? '',
                    EduLead::COUNSELING_STAGES[$lead->status]      ?? '',
                    $lead->assignedTo?->name ?? 'Unassigned',
                    $lead->createdBy?->name  ?? '',
                    $totalFu,
                    $lead->created_at->format('d-m-Y H:i'),
                ];

                // ── Dynamic followup columns ──────────────────────────────────
                if ($followupNumber) {
                    $fu      = $orderedFollowups->get($followupNumber - 1);
                    $fuNote  = $fu
                        ? ($fu->status === 'completed' && $fu->outcome_notes
                            ? $fu->outcome_notes
                            : $fu->notes ?? '')
                        : '';
                    $rowData[] = $fu ? \Carbon\Carbon::parse($fu->followup_date)->format('d-m-Y') : '';
                    $rowData[] = $fuCounselingStage($fu);
                    $rowData[] = $fuNote;
                } else {
                    for ($i = 0; $i < $maxFuCount; $i++) {
                        $fu     = $orderedFollowups->get($i);
                        $fuNote = $fu
                            ? ($fu->status === 'completed' && $fu->outcome_notes
                                ? $fu->outcome_notes
                                : $fu->notes ?? '')
                            : '';
                        $rowData[] = $fu ? \Carbon\Carbon::parse($fu->followup_date)->format('d-m-Y') : '';
                        $rowData[] = $fuCounselingStage($fu);
                        $rowData[] = $fuNote;
                    }
                }

                $sheet->fromArray($rowData, null, 'A' . $row);

                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8FAFC');
                }

                $row++;
            }

            // ── Auto-filter ───────────────────────────────────────────────────
            $sheet->setAutoFilter("A1:{$lastCol}1");

            // ── Stream ────────────────────────────────────────────────────────
            $writer = new Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0',
            ]);

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'Export failed. Please try again.');
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
    private function getAssignableUsers(User $user)
    {
        if ($user->isSuperAdmin() || $user->isOperationHead()) {
            return User::where('role', '!=', 'super_admin')
                ->where('is_active', true)
                ->with('branch:id,name')
                ->select('id', 'name', 'branch_id', 'role')
                ->orderBy('branch_id')
                ->orderBy('name')
                ->get();
        }

        if ($user->isLeadManager()) {
            return User::where('role', '!=', 'super_admin')
                ->where('is_active', true)
                ->where(function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id)
                    ->orWhere('role', 'operation_head');
                })
                ->with('branch:id,name')
                ->select('id', 'name', 'branch_id', 'role')
                ->orderBy('branch_id')
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    // =========================================================================
    // VALIDATION RULES
    // =========================================================================
    protected function leadValidationRules(?int $ignoreId = null): array
    {
        return [
            'name'             => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => [
                'required', 'string', 'max:20',
                Rule::unique('edu_leads', 'phone')
                    ->ignore($ignoreId)
                    ->whereNull('deleted_at'),
            ],
            'whatsapp_number'  => [
                'nullable', 'string', 'max:20',
                Rule::unique('edu_leads', 'whatsapp_number')
                    ->ignore($ignoreId)
                    ->whereNull('deleted_at'),
            ],
            'state'                     => 'nullable|string|max:100',
            'district'                  => 'nullable|string|max:100',
            'preferred_state'           => 'nullable|string|max:100',
            'institution_type'          => 'nullable|in:school,college,other',
            'school'                    => 'nullable|string|max:255',
            'school_department'         => 'nullable|string|max:255',
            'college'                   => 'nullable|string|max:255',
            'college_department'        => 'nullable|string|max:255',
            'course_id'                 => 'nullable|exists:courses,id',
            'addon_course'              => 'nullable|string|max:255',
            'lead_source_id'            => 'required|exists:edu_lead_sources,id',
            'agent_name'                => 'nullable|string|max:255',
            'referral_name'             => 'nullable|string|max:255',
            'interest_level'            => 'nullable|in:hot,warm,cold',
            'branch_id'                 => 'nullable|exists:branches,id',
            'assigned_to'               => 'nullable|exists:users,id',
            'application_number_suffix' => 'nullable|string|max:20',
            'description'               => 'nullable|string',
            'remarks'                   => 'nullable|string',
            'status'                    => 'nullable|in:whatsapp_link_submitted,application_form_submitted,booking,cancelled',
            'booking_payment'           => 'nullable|numeric|min:0',
            'fees_collection'           => 'nullable|numeric|min:0',
            'cancellation_reason'       => 'nullable|string|max:1000',
            'call_status'              => 'nullable|in:contacted,not_attended',
            'final_status'              => 'nullable|in:pending,contacted,follow_up,admitted,not_interested,not_attended,dropped',
        ];
    }

    /**
     * Update a single tracking field inline (PATCH via JS)
     * Fields: final_status, status, booking_payment, fees_collection,
     *         application_number, cancellation_reason
     */
    public function updateTracking(Request $request, EduLead $eduLead)
    {
        $user = Auth::user();

        $canChange = $user->isSuperAdmin()
            || $user->isOperationHead()
            || ($user->isLeadManager() && $eduLead->branch_id === $user->branch_id)
            || $eduLead->assigned_to === $user->id;

        if (!$canChange) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $field = $request->input('field');
        $value = $request->input('value');

        // Whitelist allowed fields + their validation rules
        $allowedFields = [
            'final_status'        => ['nullable', Rule::in(['pending','contacted','follow_up','admitted', 'not_interested','dropped','not_attended'])],
            'status'              => ['nullable', Rule::in(['whatsapp_link_submitted','application_form_submitted','booking','cancelled'])],
            'call_status'         => ['nullable', Rule::in(['contacted','not_attended'])],
            'booking_payment'     => ['nullable', 'numeric', 'min:0'],
            'fees_collection'     => ['nullable', 'numeric', 'min:0'],
            'application_number'  => ['nullable', 'string', 'max:100'],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ];

        if (!array_key_exists($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid field.'], 422);
        }

        try {
            $request->validate([$field => $allowedFields[$field]]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }

        // Build the update payload
        $payload = [$field => $value ?: null];

        // Special handling for application_number — prefix with AJK-
        if ($field === 'application_number') {
            $suffix = trim($value ?? '');
            $payload['application_number'] = $suffix ? 'AJK-' . $suffix : null;
        }

        // Clear booking fields when status changes away from 'booking'
        if ($field === 'status' && $value !== 'booking') {
            $payload['booking_payment'] = null;
            $payload['fees_collection'] = null;
        }

        // Clear cancellation reason when status changes away from 'cancelled'
        if ($field === 'status' && $value !== 'cancelled') {
            $payload['cancellation_reason'] = null;
        }

        // Track cancelled_at when status set to 'cancelled'
        if ($field === 'status' && $value === 'cancelled' && $eduLead->status !== 'cancelled') {
            $payload['cancelled_at'] = now();
        }

        // Track admitted_at / cancelled_at on final_status changes
        if ($field === 'final_status') {
            if ($value === 'admitted' && $eduLead->final_status !== 'admitted') {
                $payload['admitted_at'] = now();
            }
            if ($value === 'dropped' && $eduLead->final_status !== 'dropped') {
                $payload['cancelled_at'] = now();
            }
        }

        $eduLead->update($payload);

        Log::info('Tracking field updated', [
            'lead_id' => $eduLead->id,
            'field'   => $field,
            'value'   => $value,
            'by'      => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully.',
            'field'   => $field,
            'value'   => $value,
        ]);
    }

    // =========================================================================
    // BULK DELETE
    // =========================================================================
    public function bulkDelete(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->canDelete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin can delete leads.',
                ], 403);
            }

            $validated = $request->validate([
                'lead_ids'   => 'required|array|min:1',
                'lead_ids.*' => 'exists:edu_leads,id',
            ]);

            $leads   = EduLead::whereIn('id', $validated['lead_ids'])->get();
            $count   = 0;
            $skipped = 0;

            foreach ($leads as $lead) {
                $lead->delete();
                Log::info('Bulk delete: lead deleted', [
                    'lead_id'    => $lead->id,
                    'lead_code'  => $lead->lead_code,
                    'deleted_by' => $user->id,
                ]);
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} lead(s) deleted successfully.",
                'count'   => $count,
                'skipped' => $skipped,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting leads.',
            ], 500);
        }
    }


}
