@extends('layouts.app')

@section('title', 'Education Leads')

@section('extra-css')
<style>
    .badge-pending      { background-color: #ffc107; color: #000; }
    .badge-approved     { background-color: #28a745; color: #fff; }
    .badge-rejected     { background-color: #dc3545; color: #fff; }
    body, .page-content { overflow-x: hidden; }
    .row { margin-left: 0; margin-right: 0; }
    .row > * { padding-left: 12px; padding-right: 12px; }
    .card { overflow: hidden; }
    .leads-card .card-body { padding: 0; overflow: hidden; }
    .leads-card .card-footer { padding: 15px 20px; background-color: #f8f9fa; }

    /* ── Quick Status Tabs ─────────────────────────────────── */
    .status-tabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        padding: 12px 16px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .status-tab {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        background: #fff;
        color: #6c757d;
        border-color: #dee2e6;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        user-select: none;
    }
    .status-tab:hover  { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,.1); }
    .status-tab.active { color: #fff; border-color: transparent; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
    .status-tab[data-status=""]             { background:#343a40; color:#fff; border-color:#adb5bd; }
    .status-tab[data-status=""].active      { background:#343a40; }
    .status-tab[data-status="pending"].active       { background:#ffc107; color:#000; }
    .status-tab[data-status="contacted"].active     { background:#3b82f6; }
    .status-tab[data-status="follow_up"].active     { background:#f97316; }
    .status-tab[data-status="admitted"].active      { background:#10b981; }
    .status-tab[data-status="not_interested"].active{ background:#ef4444; }
    .status-tab[data-status="dropped"].active       { background:#6b7280; }
    .status-tab .tab-count {
        background: rgba(0,0,0,.12);
        border-radius: 10px;
        padding: 0 7px;
        font-size: 0.75rem;
        line-height: 1.5;
    }
    .status-tab.active .tab-count { background: rgba(255,255,255,.25); }

    /* ── Table ─────────────────────────────────────────────── */
    .table-container {
        overflow-x: auto;
        overflow-y: visible;
        width: 100%;
    }
    .table-container::-webkit-scrollbar        { width: 10px; height: 10px; }
    .table-container::-webkit-scrollbar-track  { background: #f1f1f1; border-radius: 10px; }
    .table-container::-webkit-scrollbar-thumb  { background: #888; border-radius: 10px; }
    .table-container::-webkit-scrollbar-thumb:hover { background: #555; }
    .table-container table   { margin-bottom: 0; min-width: 100%; }
    .table-container thead th {
        position: sticky; top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0,0,0,.1);
        white-space: nowrap;
        padding: 12px 15px;
        vertical-align: middle;
        font-weight: 600;
    }
    .table-container tbody td {
        white-space: nowrap;
        padding: 12px 15px;
        vertical-align: middle;
    }
    .table-container tbody tr:hover td { background-color: #f8f9fa; }

    /* ── Checkboxes ────────────────────────────────────────── */
    .checkbox-col { width: 50px; min-width: 50px; text-align: center; padding: 12px 10px !important; }
    .custom-checkbox { width: 12px; height: 12px; cursor: pointer; accent-color: #0d6efd; transform: scale(1.2); }
    .custom-checkbox:hover { transform: scale(1.3); }
    #selectAll { width: 14px; height: 14px; cursor: pointer; accent-color: #198754; transform: scale(1.3); }
    .checkbox-wrapper { display: flex; align-items: center; justify-content: center; padding: 5px; }
    .checkbox-wrapper input[type="checkbox"] { margin: 0; }
    .table-container tbody tr:has(.custom-checkbox:checked) {
        background-color: #e7f3ff;
        border-left: 3px solid #0d6efd;
    }

    /* ── Sortable ──────────────────────────────────────────── */
    .sortable { cursor: pointer; user-select: none; position: relative; padding-right: 20px !important; }
    .sortable:hover       { background-color: #e9ecef; }
    .sortable::after      { content: '⇅'; position: absolute; right: 8px; opacity: .3; }
    .sortable.asc::after  { content: '↑'; opacity: 1; }
    .sortable.desc::after { content: '↓'; opacity: 1; }

    .action-icons { display: inline-flex; gap: 8px; align-items: center; justify-content: flex-end; }
    .action-icons a { display: inline-flex; align-items: center; justify-content: center; }
    .lead-name-link { color: #0d6efd; text-decoration: none; font-weight: 600; }
    .lead-name-link:hover { color: #0a58ca; text-decoration: underline; }
    .table-loading { position: relative; opacity: .5; pointer-events: none; }

    /* ── Final Status Inline Badge ─────────────────────────── */
    .fs-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }
    .fs-pending        { background:#fff3cd; color:#856404; }
    .fs-contacted      { background:#dbeafe; color:#1d4ed8; }
    .fs-follow_up      { background:#ffedd5; color:#c2410c; }
    .fs-admitted       { background:#d1fae5; color:#065f46; }
    .fs-not_interested { background:#fee2e2; color:#991b1b; }
    .fs-dropped        { background:#f3f4f6; color:#374151; }

    @media (max-width: 768px) {
        .table-container table { min-width: 1200px; }
        .status-tabs { gap: 4px; }
        .status-tab  { font-size: 0.75rem; padding: 5px 10px; }
    }

    .bulk-lead-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 10px;
        border-radius: 6px;
        background: #fff;
        margin-bottom: 4px;
        border: 1px solid #e9ecef;
        font-size: 0.85rem;
    }
    .bulk-lead-item:last-child { margin-bottom: 0; }
    .bulk-lead-code {
        font-weight: 700;
        color: #0d6efd;
        min-width: 90px;
        font-size: 0.78rem;
    }
    .bulk-lead-name { color: #1e293b; font-weight: 500; }
    .bulk-lead-assignee {
        margin-left: auto;
        font-size: 0.75rem;
        color: #6c757d;
        background: #f1f3f5;
        padding: 2px 8px;
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid mt-4">

    {{-- ── Page Header ───────────────────────────────────────── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">Education Leads</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Education Leads</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-danger fs-6">
                        <i class="las la-fire"></i>
                        <span id="hotLeadsCountBadge">{{ $hotLeadsCount }}</span> Hot
                    </span>
                    <span class="badge bg-warning text-dark fs-6">
                        <i class="las la-clock"></i>
                        <span id="pendingFollowupsBadge">{{ $pendingFollowupsCount }}</span> Followups
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filters ────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-2 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1"><i class="las la-search"></i> Search</label>
                        <input type="text" class="form-control" id="searchInput"
                               placeholder="Name, Phone, Email, Code...">
                    </div>

                    {{-- Interest Level --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-fire"></i> Interest</label>
                        <select class="form-select" id="filterInterestLevel">
                            <option value="">All Levels</option>
                            <option value="hot">🔥 Hot</option>
                            <option value="warm">☀️ Warm</option>
                            <option value="cold">❄️ Cold</option>
                        </select>
                    </div>

                    {{-- Course --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-book"></i> Course</label>
                        <select class="form-select" id="filterCourse">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Country --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-globe"></i> Country</label>
                        <select class="form-select" id="filterCountry">
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Source --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-bullhorn"></i> Source</label>
                        <select class="form-select" id="filterSource">
                            <option value="">All Sources</option>
                            @foreach($leadSources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Assigned To --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-user-tie"></i> Assigned To</label>
                        <select class="form-select" id="filterAssignedTo">
                            <option value="">All</option>
                            <option value="unassigned">Unassigned</option>
                            @if(auth()->user()->role === 'telecallers')
                                <option value="me">My Leads</option>
                            @else
                                @foreach($telecallers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-calendar"></i> From</label>
                        <input type="date" class="form-control" id="dateFrom">
                    </div>

                    {{-- Date To --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-calendar"></i> To</label>
                        <input type="date" class="form-control" id="dateTo">
                    </div>

                    {{-- Per Page --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1"><i class="las la-list"></i> Per Page</label>
                        <select class="form-select" id="perPageSelect">
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-md-4">
                        <label class="form-label mb-1">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                                <i class="las la-filter"></i> Apply
                            </button>
                            <button type="button" class="btn btn-secondary" id="resetBtn">
                                <i class="las la-redo"></i> Reset
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ── Leads Table Card ───────────────────────────────────── --}}
    <div class="card">

        {{-- Card Header --}}
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong id="leadCount">{{ $leads->total() }}</strong> leads found
                    <span class="text-muted ms-2" id="pageInfo">
                        @if($leads->total() > 0)
                            {{ $leads->firstItem() ?? 1 }} – {{ $leads->lastItem() ?? $leads->total() }} of {{ $leads->total() }}
                        @else
                            0–0 of 0
                        @endif
                    </span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                    <button type="button" class="btn btn-info d-none" id="bulkAssignBtn">
                        <i class="las la-users me-1"></i> Bulk Assign (<span id="bulkcount">0</span>)
                    </button>
                    @endif
                    <button type="button" class="btn btn-success" id="exportBtn"
                            onclick="exportWithFilters()">
                        <i class="las la-file-download me-1"></i> Export CSV
                    </button>
                    <a href="{{ route('edu-leads.create') }}" class="btn btn-primary">
                        <i class="las la-plus me-1"></i> Create Lead
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Quick Status Tabs ──────────────────────────────── --}}
        <div class="status-tabs" id="statusTabs">
            <span class="status-tab active" data-status="">
                All <span class="tab-count" id="tc-all">{{ $statusCounts['all'] ?? $leads->total() }}</span>
            </span>
            <span class="status-tab" data-status="pending">
                ⏳ Pending <span class="tab-count" id="tc-pending">{{ $statusCounts['pending'] ?? 0 }}</span>
            </span>
            <span class="status-tab" data-status="contacted">
                📞 Contacted <span class="tab-count" id="tc-contacted">{{ $statusCounts['contacted'] ?? 0 }}</span>
            </span>
            <span class="status-tab" data-status="follow_up">
                🔔 Follow Up <span class="tab-count" id="tc-follow_up">{{ $statusCounts['follow_up'] ?? 0 }}</span>
            </span>
            <span class="status-tab" data-status="admitted">
                ✅ Admitted <span class="tab-count" id="tc-admitted">{{ $statusCounts['admitted'] ?? 0 }}</span>
            </span>
            <span class="status-tab" data-status="not_interested">
                ❌ Not Interested <span class="tab-count" id="tc-not_interested">{{ $statusCounts['not_interested'] ?? 0 }}</span>
            </span>
            <span class="status-tab" data-status="dropped">
                🚫 Dropped <span class="tab-count" id="tc-dropped">{{ $statusCounts['dropped'] ?? 0 }}</span>
            </span>
        </div>

        {{-- Table --}}
        <div class="card-body p-0">
            <div class="table-container">
                <table class="table table-hover mb-0" id="leadsTable">
                    <thead class="table-light">
                        <tr>
                            @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                            <th class="checkbox-col">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="selectAll" title="Select All">
                                </div>
                            </th>
                            @endif
                            <th class="sortable" data-column="lead_code">Lead Code</th>
                            <th class="sortable" data-column="name">Name</th>
                            <th class="sortable" data-column="phone">Phone</th>
                            <th class="sortable" data-column="country">Country</th>
                            <th class="sortable" data-column="course_id">Course</th>
                            <th class="sortable" data-column="interest_level">Interest</th>
                            <th class="sortable" data-column="final_status">Status</th>
                            <th class="sortable" data-column="lead_source_id">Source</th>
                            <th class="sortable" data-column="assigned_to">Assigned To</th>
                            <th class="sortable" data-column="created_at">Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="leadsTableBody">
                        @include('edu-leads.partials.table-rows')
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <div id="paginationContainer">
                {!! $leads->links('pagination::bootstrap-5') !!}
            </div>
        </div>
    </div>

</div><!-- /container-fluid -->


{{-- ══════════════════════════════════════════════════════════
     SINGLE ASSIGN MODAL
═══════════════════════════════════════════════════════════ --}}
@if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
<div class="modal fade" id="assignLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="las la-user-plus me-2"></i>Assign Lead
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignLeadForm">
                @csrf
                <input type="hidden" id="assignLeadId" name="lead_id">

                <div class="modal-body">

                    {{-- Lead info chip --}}
                    <div class="alert alert-light border mb-3 py-2 px-3">
                        <div class="d-flex gap-3">
                            <div>
                                <small class="text-muted d-block">Lead Code</small>
                                <strong id="assignLeadCode" class="text-primary">—</strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Name</small>
                                <strong id="assignLeadName">—</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Current assignment --}}
                    <div class="mb-3" id="currentAssignmentBlock" style="display:none">
                        <small class="text-muted">Currently assigned to:</small>
                        <span class="badge bg-secondary ms-2" id="currentAssigneeLabel"></span>
                    </div>

                    {{-- Telecaller select --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-user-tie me-1"></i>Select Telecaller
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="assignTelecaller" name="assigned_to" required>
                            <option value="">Choose telecaller...</option>
                            @foreach($telecallers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-1">
                        <label class="form-label">
                            <i class="las la-comment me-1"></i>Notes <small class="text-muted">(optional)</small>
                        </label>
                        <textarea class="form-control" id="assignNotes" name="notes" rows="2"
                                  placeholder="Any notes about this assignment..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-check me-1"></i>Assign Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════
     BULK ASSIGN MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="las la-users me-2"></i>Bulk Assign Leads
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAssignForm">
                @csrf
                <div class="modal-body">

                    {{-- Selected count banner --}}
                    <div class="alert mb-3 py-3 px-4"
                         style="background:linear-gradient(135deg,#fff3cd,#ffe69c); border-left:4px solid #ffc107;">
                        <div class="d-flex align-items-center gap-3">
                            <i class="las la-check-circle" style="font-size:2rem; color:#997404;"></i>
                            <div>
                                <span style="font-size:1.6rem; font-weight:700; color:#664d03;"
                                      id="selectedCount">0</span>
                                <span style="font-size:1rem; color:#664d03;"> leads selected for assignment</span>
                            </div>
                        </div>
                    </div>

                    {{-- Selected leads list --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small mb-1">
                            <i class="las la-list me-1"></i>Selected Leads
                        </label>
                        <div id="selectedLeadsList"
                             style="max-height:200px; overflow-y:auto; border:1px solid #dee2e6;
                                    border-radius:8px; background:#f8f9fa; padding:8px;">
                            {{-- filled dynamically --}}
                        </div>
                    </div>

                    {{-- Telecaller selection --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-user-tie me-1"></i>Assign To Telecaller
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="bulkTelecaller" name="assigned_to" required>
                            <option value="">Choose telecaller...</option>
                            @foreach($telecallers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            <i class="las la-comment me-1"></i>Notes <small class="text-muted">(optional)</small>
                        </label>
                        <textarea class="form-control" id="bulkNotes" name="notes" rows="2"
                                  placeholder="Notes about this bulk assignment..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="las la-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="las la-check-double me-1"></i>Assign All Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection


@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    let currentSort   = { column: 'created_at', direction: 'desc' };
    let activeStatus  = '';   // tracks the quick-filter tab

    // ── PRE-POPULATE FILTERS FROM URL (dashboard redirects) ───────────
    (function prePopulateFilters() {
        const p = new URLSearchParams(window.location.search);

        if (p.get('search'))         $('#searchInput').val(p.get('search'));
        if (p.get('interest_level')) $('#filterInterestLevel').val(p.get('interest_level'));
        if (p.get('course_id'))      $('#filterCourse').val(p.get('course_id'));
        if (p.get('country'))        $('#filterCountry').val(p.get('country'));
        if (p.get('lead_source_id')) $('#filterSource').val(p.get('lead_source_id'));
        if (p.get('assigned_to'))    $('#filterAssignedTo').val(p.get('assigned_to'));
        if (p.get('date_from'))      $('#dateFrom').val(p.get('date_from'));
        if (p.get('date_to'))        $('#dateTo').val(p.get('date_to'));
        if (p.get('per_page'))       $('#perPageSelect').val(p.get('per_page'));

        // Sort state
        if (p.get('sort_column'))    currentSort.column    = p.get('sort_column');
        if (p.get('sort_direction')) currentSort.direction = p.get('sort_direction');
        if (currentSort.direction)
            $('.sortable[data-column="' + currentSort.column + '"]').addClass(currentSort.direction);

        // Quick-status tab
        if (p.get('final_status')) {
            activeStatus = p.get('final_status');
            $('.status-tab').removeClass('active');
            $('.status-tab[data-status="' + activeStatus + '"]').addClass('active');
        }
    })();

    function exportWithFilters() {
        const params = new URLSearchParams({
            search:         $('#searchInput').val(),
            interest_level: $('#filterInterestLevel').val(),
            final_status:   activeStatus,
            course_id:      $('#filterCourse').val(),
            country:        $('#filterCountry').val(),
            lead_source_id: $('#filterSource').val(),
            assigned_to:    $('#filterAssignedTo').val(),
            date_from:      $('#dateFrom').val(),
            date_to:        $('#dateTo').val(),
            sort_column:    currentSort.column,
            sort_direction: currentSort.direction,
        });
        // Remove empty params for cleaner URL
        for (const [k, v] of [...params.entries()]) {
            if (!v) params.delete(k);
        }
        window.location.href = '{{ route("edu-leads.export") }}?' + params.toString();
    }

    window.exportWithFilters = exportWithFilters;

    // ── LOAD LEADS ──────────────────────────────────────────
    function loadLeads(url) {
        const requestUrl = url || '{{ route("edu-leads.index") }}';

        const params = {
            search:         $('#searchInput').val(),
            interest_level: $('#filterInterestLevel').val(),
            final_status:   activeStatus,
            course_id:      $('#filterCourse').val(),
            country:        $('#filterCountry').val(),
            lead_source_id: $('#filterSource').val(),
            assigned_to:    $('#filterAssignedTo').val(),
            date_from:      $('#dateFrom').val(),
            date_to:        $('#dateTo').val(),
            sort_column:    currentSort.column,
            sort_direction: currentSort.direction,
            per_page:       $('#perPageSelect').val(),
        };

        $('#leadsTable').addClass('table-loading');

        $.ajax({
            url: requestUrl,
            type: 'GET',
            data: params,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                $('#leadsTableBody').html(res.html);
                $('#paginationContainer').html(res.pagination);
                $('#leadCount').text(res.total);

                if (res.from && res.to) {
                    $('#pageInfo').text(res.from + ' – ' + res.to + ' of ' + res.total);
                } else {
                    $('#pageInfo').text(res.total > 0 ? '1–' + res.total + ' of ' + res.total : '0–0 of 0');
                }

                // Update tab counts if provided
                if (res.status_counts) {
                    $('#tc-all').text(res.status_counts.all ?? 0);
                    $.each(res.status_counts, function (k, v) {
                        if (k !== 'all') $('#tc-' + k).text(v);
                    });
                }

                $('#leadsTable').removeClass('table-loading');

                // Reset checkboxes
                $('#selectAll').prop('checked', false);
                updateBulkBtn();
            },
            error: function () {
                $('#leadsTable').removeClass('table-loading');
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load leads.' });
            }
        });
    }

    // ── FILTERS ─────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () { loadLeads(); });

    $('#resetBtn').on('click', function () {
        $('#filterForm')[0].reset();
        activeStatus = '';
        currentSort  = { column: 'created_at', direction: 'desc' };
        $('.sortable').removeClass('asc desc');
        $('.status-tab').removeClass('active');
        $('.status-tab[data-status=""]').addClass('active');
        window.location.href = '{{ route("edu-leads.index") }}';
    });

    let searchTimer;
    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadLeads, 450);
    });

    $('#perPageSelect').on('change', function () { loadLeads(); });

    // Pagination
    $(document).on('click', '#paginationContainer .pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) loadLeads(url);
    });

    // Sorting
    $(document).on('click', '.sortable', function () {
        const col = $(this).data('column');
        if (currentSort.column === col) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column    = col;
            currentSort.direction = 'asc';
        }
        $('.sortable').removeClass('asc desc');
        $(this).addClass(currentSort.direction);
        loadLeads();
    });

    // ── QUICK STATUS TABS ────────────────────────────────────
    $(document).on('click', '.status-tab', function () {
        $('.status-tab').removeClass('active');
        $(this).addClass('active');
        activeStatus = $(this).data('status');
        loadLeads();
    });

    // ── CHECKBOXES ───────────────────────────────────────────
    $('#selectAll').on('change', function () {
        $('.lead-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkBtn();
    });

    $(document).on('change', '.lead-checkbox', function () {
        updateBulkBtn();
        const total   = $('.lead-checkbox').length;
        const checked = $('.lead-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked && total > 0);
    });

    function updateBulkBtn() {
        const n = $('.lead-checkbox:checked').length;
        $('#bulkcount').text(n);
        n > 0 ? $('#bulkAssignBtn').removeClass('d-none') : $('#bulkAssignBtn').addClass('d-none');
    }

    @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))

    // ── SINGLE ASSIGN ────────────────────────────────────────────────────
    $(document).on('click', '.assignLeadBtn', function () {
        const btn = $(this);
        $('#assignLeadId').val(btn.data('id'));
        $('#assignLeadCode').text(btn.data('code'));
        $('#assignLeadName').text(btn.data('name'));
        $('#assignNotes').val('');

        const assignee = btn.data('assignee');
        if (assignee) {
            $('#currentAssigneeLabel').text(assignee);
            $('#currentAssignmentBlock').show();
        } else {
            $('#currentAssignmentBlock').hide();
        }

        $('#assignTelecaller').val('');
        $('#assignLeadModal').modal('show');
    });

    $('#assignLeadForm').on('submit', function (e) {
        e.preventDefault();

        const leadId     = $('#assignLeadId').val();
        const telecaller = $('#assignTelecaller').val();
        const notes      = $('#assignNotes').val();

        if (!telecaller) {
            Swal.fire({ icon: 'warning', title: 'Select Telecaller', text: 'Please choose a telecaller.' });
            return;
        }

        const $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Assigning...');

        $.ajax({
            url:  '/edu-leads/' + leadId + '/assign',
            type: 'POST',
            data: { assigned_to: telecaller, notes: notes },
            success: function (res) {
                $('#assignLeadModal').modal('hide');
                // res.telecaller_name is returned by the fixed controller
                Swal.fire({
                    icon: 'success',
                    title: 'Assigned!',
                    html: 'Lead assigned to <strong>' + (res.telecaller_name || 'telecaller') + '</strong>',
                    timer: 2500,
                    showConfirmButton: false
                }).then(() => loadLeads());
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="las la-check me-1"></i>Assign Lead');
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Assignment failed.' });
            }
        });
    });

    // ── BULK ASSIGN — open modal with lead list ──────────────────────────
    $('#bulkAssignBtn').on('click', function () {
        const selected = getSelectedIds();
        $('#selectedCount').text(selected.length);
        $('#bulkTelecaller').val('');
        $('#bulkNotes').val('');

        // Build selected leads list
        const listEl = $('#selectedLeadsList');
        listEl.empty();

        if (selected.length === 0) {
            listEl.html('<p class="text-muted text-center py-2 mb-0 small">No leads selected</p>');
        } else {
            selected.forEach(function (id) {
                const row = $('input.lead-checkbox[value="' + id + '"]').closest('tr');
                const code     = row.find('td:nth-child(2)').text().trim();
                const name     = row.find('.lead-name-link').text().trim();
                const assignee = row.find('.badge.bg-secondary').text().trim() || '';

                const item = $('<div class="bulk-lead-item">' +
                    '<span class="bulk-lead-code">' + code + '</span>' +
                    '<span class="bulk-lead-name">' + name + '</span>' +
                    (assignee ? '<span class="bulk-lead-assignee">→ ' + assignee + '</span>' : '') +
                    '</div>');
                listEl.append(item);
            });
        }

        $('#bulkAssignModal').modal('show');
    });

    $('#bulkAssignForm').on('submit', function (e) {
        e.preventDefault();

        const selected   = getSelectedIds();
        const telecaller = $('#bulkTelecaller').val();
        const notes      = $('#bulkNotes').val();

        if (!selected.length) {
            Swal.fire({ icon: 'warning', title: 'Nothing Selected', text: 'Select at least one lead.' });
            return;
        }
        if (!telecaller) {
            Swal.fire({ icon: 'warning', title: 'Select Telecaller', text: 'Please choose a telecaller.' });
            return;
        }

        $('#bulkAssignModal').modal('hide');

        $.ajax({
            url:  '{{ route("edu-leads.bulk-assign") }}',
            type: 'POST',
            data: { lead_ids: selected, assigned_to: telecaller, notes: notes },
            success: function (res) {
                Swal.fire({
                    icon:  'success',
                    title: 'Success!',
                    html:  '<strong>' + res.count + '</strong> leads assigned to <strong>' + res.telecaller_name + '</strong>',
                    timer: 3000
                }).then(() => {
                    $('.lead-checkbox').prop('checked', false);
                    $('#selectAll').prop('checked', false);
                    updateBulkBtn();
                    loadLeads();
                });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Bulk assign failed.' });
            }
        });
    });

    @endif

    function getSelectedIds() {
        const ids = [];
        $('.lead-checkbox:checked').each(function () { ids.push($(this).val()); });
        return ids;
    }

    // ── DELETE LEAD ──────────────────────────────────────────
    $(document).on('click', '.deleteLeadBtn', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Delete Lead?',
            html: 'Are you sure you want to delete <strong>' + name + '</strong>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/edu-leads/' + id,
                    type: 'DELETE',
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, timer: 2000, showConfirmButton: false })
                                .then(() => loadLeads());
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Could not delete lead.' });
                    }
                });
            }
        });
    });

});
</script>
@endsection
