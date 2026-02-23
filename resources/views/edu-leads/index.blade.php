@extends('layouts.app')
@section('title', 'Education Leads')

@section('extra-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<style>
    body, .page-content { overflow-x: hidden; }
    .row { margin-left: 0; margin-right: 0; padding-left: 12px; padding-right: 12px; }
    .card { overflow: hidden; }
    .leads-card .card-body  { padding: 0; overflow: hidden; }
    .leads-card .card-footer { padding: 15px 20px; background-color: #f8f9fa; }

    /* ── Quick status tabs ─────────────────────────────────── */
    .status-tabs { display:flex; gap:6px; flex-wrap:wrap; padding:12px 16px; background:#f8f9fa; border-bottom:1px solid #dee2e6; }
    .status-tab {
        padding:6px 14px; border-radius:20px; font-size:.82rem; font-weight:600;
        cursor:pointer; border:2px solid transparent; transition:all .2s;
        background:#fff; color:#6c757d; border-color:#dee2e6;
        display:inline-flex; align-items:center; gap:6px; user-select:none;
    }
    .status-tab:hover { transform:translateY(-1px); box-shadow:0 2px 6px rgba(0,0,0,.1); }
    .status-tab.active { color:#fff; border-color:transparent; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    .status-tab[data-status=""]      { background:#343a40; color:#fff; border-color:#adb5bd; }
    .status-tab[data-status=""].active { background:#343a40; }
    .status-tab[data-status="pending"].active      { background:#ffc107; color:#000; }
    .status-tab[data-status="contacted"].active    { background:#3b82f6; }
    .status-tab[data-status="follow_up"].active    { background:#f97316; }
    .status-tab[data-status="admitted"].active     { background:#10b981; }
    .status-tab[data-status="not_interested"].active { background:#ef4444; }
    .status-tab[data-status="dropped"].active      { background:#6b7280; }
    .status-tab .tab-count {
        background:rgba(0,0,0,.12); border-radius:10px; padding:0 7px; font-size:.75rem; line-height:1.5;
    }
    .status-tab.active .tab-count { background:rgba(255,255,255,.25); }

    /* ── Table ─────────────────────────────────────────────── */
    .table-container { overflow-x:auto; overflow-y:visible; width:100%; }
    .table-container::-webkit-scrollbar { width:10px; height:10px; }
    .table-container::-webkit-scrollbar-track { background:#f1f1f1; border-radius:10px; }
    .table-container::-webkit-scrollbar-thumb { background:#888; border-radius:10px; }
    .table-container::-webkit-scrollbar-thumb:hover { background:#555; }
    .table-container table { margin-bottom:0; min-width:100%; }
    .table-container thead th {
        position:sticky; top:0; background-color:#f8f9fa; z-index:10;
        box-shadow:0 2px 2px -1px rgba(0,0,0,.1); white-space:nowrap;
        padding:12px 15px; vertical-align:middle; font-weight:600;
    }
    .table-container tbody td { white-space:nowrap; padding:12px 15px; vertical-align:middle; }
    .table-container tbody tr:hover td { background-color:#f8f9fa; }

    /* ── Checkboxes ────────────────────────────────────────── */
    .checkbox-col { width:50px; min-width:50px; text-align:center; padding:12px 10px !important; }
    .custom-checkbox { width:12px; height:12px; cursor:pointer; accent-color:#0d6efd; transform:scale(1.2); }
    #selectAll { width:14px; height:14px; cursor:pointer; accent-color:#198754; transform:scale(1.3); }
    .checkbox-wrapper { display:flex; align-items:center; justify-content:center; padding:5px; }
    .checkbox-wrapper input[type="checkbox"] { margin:0; }
    .table-container tbody tr:has(.custom-checkbox:checked) { background-color:#e7f3ff; border-left:3px solid #0d6efd; }

    /* ── Sortable ──────────────────────────────────────────── */
    .sortable { cursor:pointer; user-select:none; position:relative; padding-right:20px !important; }
    .sortable:hover { background-color:#e9ecef; }
    .sortable::after { content:'⇅'; position:absolute; right:8px; opacity:.3; }
    .sortable.asc::after  { content:'▲'; opacity:1; }
    .sortable.desc::after { content:'▼'; opacity:1; }

    /* ── Actions ───────────────────────────────────────────── */
    .action-icons { display:inline-flex; gap:8px; align-items:center; justify-content:flex-end; }
    .action-icons a { display:inline-flex; align-items:center; justify-content:center; }
    .lead-name-link { color:#0d6efd; text-decoration:none; font-weight:600; }
    .lead-name-link:hover { color:#0a58ca; text-decoration:underline; }
    .table-loading { position:relative; opacity:.5; pointer-events:none; }

    /* ── Final status badges ───────────────────────────────── */
    .fs-badge { padding:4px 10px; border-radius:12px; font-size:.78rem; font-weight:600; display:inline-block; white-space:nowrap; }
    .fs-pending        { background:#fff3cd; color:#856404; }
    .fs-contacted      { background:#dbeafe; color:#1d4ed8; }
    .fs-follow_up      { background:#ffedd5; color:#c2410c; }
    .fs-admitted       { background:#d1fae5; color:#065f46; }
    .fs-not_interested { background:#fee2e2; color:#991b1b; }
    .fs-dropped        { background:#f3f4f6; color:#374151; }

    /* ── Filter panel ──────────────────────────────────────── */
    .filter-card-header {
        display:flex; justify-content:space-between; align-items:center;
        padding:.85rem 1.25rem; background:#f8fafc; border-bottom:1px solid #e2e8f0;
        border-radius:8px 8px 0 0; cursor:pointer; user-select:none; transition:background .2s ease;
    }
    .filter-card-header:hover { background:#f1f5f9; }
    .filter-toggle-icon {
        width:28px; height:28px; display:flex; align-items:center; justify-content:center;
        border-radius:50%; background:#e2e8f0; color:#64748b;
        transition:transform .25s ease, background .2s ease; font-size:.8rem;
    }
    .filter-toggle-icon.open { transform:rotate(180deg); background:#dbeafe; color:#3b82f6; }
    .filter-group-label {
        font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:#94a3b8; margin-bottom:.6rem; display:flex; align-items:center; gap:.4rem;
    }
    .filter-divider { border:none; border-top:1px dashed #e2e8f0; margin:.5rem 0 1rem; }
    .filter-label { display:block; font-size:.78rem; font-weight:600; color:#475569; margin-bottom:4px; }
    #schoolDeptWrap, #collegeDeptWrap { transition: opacity .2s ease; }
    #schoolDeptWrap.dimmed, #collegeDeptWrap.dimmed { opacity:.35; pointer-events:none; }
    .bulk-lead-item {
        display:flex; align-items:center; gap:10px; padding:6px 10px;
        border-radius:6px; background:#fff; margin-bottom:4px;
        border:1px solid #e9ecef; font-size:.85rem;
    }

    /* ── Select2 theme ─────────────────────────────────────── */
    .select2-container--bootstrap-5 .select2-selection {
        border:1px solid #ced4da !important; border-radius:4px !important;
        min-height:31px !important; font-size:.875rem; padding:2px 8px !important;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open  .select2-selection {
        border-color:#667eea !important;
        box-shadow:0 0 0 0.15rem rgba(102,126,234,0.2) !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-top:1px; color:#495057; line-height:1.5;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-color:#667eea; border-radius:6px;
        box-shadow:0 4px 16px rgba(102,126,234,0.15);
        font-size:.875rem;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color:#667eea !important;
    }
    .select2-container--bootstrap-5 .select2-search__field {
        border-color:#667eea !important; border-radius:4px !important; font-size:.875rem;
    }
    .select2-container { width:100% !important; }

    @media (max-width: 768px) {
        .table-container table { min-width:1200px; }
        .status-tabs { gap:4px; }
        .status-tab { font-size:.75rem; padding:5px 10px; }
    }

    /* ── Filter collapse hint ─────────────────────────────────── */
    .filter-collapse-hint {
        display: flex;
        align-items: center;
        gap: 5px;
        background: rgba(99,102,241,.07);
        border: 1px solid rgba(99,102,241,.18);
        border-radius: 20px;
        padding: 3px 10px 3px 8px;
        cursor: pointer;
        transition: background .2s ease;
        user-select: none;
    }
    .filter-collapse-hint:hover {
        background: rgba(99,102,241,.14);
    }
    .collapse-hint-text {
        font-size: .72rem;
        font-weight: 600;
        color: #4f46e5;
        white-space: nowrap;
    }
    .filter-toggle-icon {
        width: 20px; height: 20px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: rgba(99,102,241,.15);
        color: #4f46e5;
        font-size: .75rem;
        transition: transform .25s ease;
    }
    .filter-toggle-icon.open {
        transform: rotate(180deg);
        background: #dbeafe;
        color: #3b82f6;
    }

</style>
@endsection

@section('content')
<div class="container-fluid mt-4">

    {{-- ── Page Header ──────────────────────────────────────────── --}}
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

    {{-- ── FILTERS CARD ──────────────────────────────────────── --}}
    <div class="card mb-3" id="filterCard">

        <div class="filter-card-header" onclick="toggleFilters()">
            <div class="d-flex align-items-center gap-2">
                <i class="las la-sliders-h fs-18 text-primary"></i>
                <span class="fw-semibold">Filters</span>
                <span class="badge bg-primary rounded-pill ms-1" id="activeFilterCount" style="display:none;">0</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-primary px-3" id="applyFiltersBtn"
                        onclick="event.stopPropagation()">
                    <i class="las la-check me-1"></i>Apply
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="resetBtn"
                        onclick="event.stopPropagation()">
                    <i class="las la-redo me-1"></i>Reset
                </button>
                <div class="filter-collapse-pill" onclick="toggleFilters(); event.stopPropagation();">
                    {{-- <span id="collapseHintText" class="collapse-pill-text">Hide Filters</span> --}}
                    <span class="filter-toggle-icon" id="filterToggleIcon">
                        <i class="las la-angle-up"></i>
                    </span>
                </div>
            </div>
        </div>

        <div id="filterBody" class="card-body pt-3 pb-2" style="display:none">
            <form id="filterForm">

                {{-- ── GROUP 1: Search & Basics ─────────────────────── --}}
                <div class="filter-group-label"><i class="las la-search"></i> Search &amp; Basics</div>
                <div class="row g-3 mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <label class="filter-label">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Name, phone, email, lead code, app no...">
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-fire text-danger"></i> Interest</label>
                        <select class="form-select form-select-sm" id="filterInterestLevel">
                            <option value="">All Interest</option>
                            <option value="hot">Hot</option>
                            <option value="warm">Warm</option>
                            <option value="cold">Cold</option>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-user-tie text-info"></i> Agent</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="las la-user text-muted"></i></span>
                            <input type="text" class="form-control" id="filterAgentName" placeholder="Agent name...">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-bullhorn text-info"></i> Lead Source</label>
                        <select class="form-select form-select-sm" id="filterSource">
                            <option value="">All Sources</option>
                            @foreach($leadSources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g3 mb-3">
                    <div class="col-xl-5 col-lg-5 col-md-6">
                        <label class="filter-label"><i class="las la-calendar text-success"></i> Created Date Range</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="dateFrom" title="From date">
                            <span class="input-group-text bg-white px-2">→</span>
                            <input type="date" class="form-control" id="dateTo" title="To date">
                        </div>
                    </div>
                </div>

                <hr class="filter-divider">

                {{-- ── GROUP 2: Current Institution, Dept & Location ── --}}
                <div class="filter-group-label"><i class="las la-school"></i> Current Institution &amp; Location</div>
                <div class="row g-3 mb-3">

                    {{-- Institution Type: School (default) / College only — no "All" --}}
                    <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                        <label class="filter-label">Institution Type</label>
                        <select class="form-select form-select-sm" id="filterInstitutionType">
                            <option value="school" selected>🏫 School</option>
                            <option value="college">🎓 College</option>
                        </select>
                    </div>

                    {{-- School Dept --}}
                    <div class="col-xl-3 col-lg-3 col-md-4" id="schoolDeptWrap">
                        <label class="filter-label">School Stream / Dept</label>
                        <select class="form-select form-select-sm" id="filterSchoolDepartment">
                            <option value="">All Streams</option>
                            @foreach(['Biology Science', 'Computer Science','Commerce','Arts & Journalism', 'Humanities','Vocational', 'Other'] as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- College Dept --}}
                    <div class="col-xl-3 col-lg-3 col-md-4" id="collegeDeptWrap">
                        <label class="filter-label">College Department</label>
                        <select class="form-select form-select-sm" id="filterCollegeDepartment">
                            <option value="">All Departments</option>
                            @foreach(['Engineering','Medical','Arts','Commerce','Science','Law','Management','Other'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- State — wider --}}
                    <div class="col-xl-4 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-map text-muted"></i> State</label>
                        <select class="form-select form-select-sm" id="filterState">
                            <option value=""></option>
                            @foreach($states as $stateOption)
                                <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- District — wider, on new row on smaller screens --}}
                    <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-map-pin text-muted"></i> District</label>
                        <select class="form-select form-select-sm" id="filterDistrict">
                            <option value=""></option>
                        </select>
                    </div>

                </div>

                <hr class="filter-divider">

                {{-- ── GROUP 3: Preferred Destination & Course ──────── --}}
                <div class="filter-group-label"><i class="las la-globe"></i> Preferred Destination &amp; Course</div>
                <div class="row g-3 mb-3">
                    <div class="col-xl-5 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-star text-warning"></i> Preferred State</label>
                        <select class="form-select form-select-sm" id="filterPreferredState">
                            <option value=""></option>
                            @foreach($states as $stateOption)
                                <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                        <label class="filter-label">Programme</label>
                        <select class="form-select form-select-sm" id="filterProgramme">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $programme)
                                <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-5">
                        <label class="filter-label">
                            Specific Course
                            <small class="text-muted fw-normal ms-1">(filtered by programme)</small>
                        </label>
                        <select class="form-select form-select-sm" id="filterCourse">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                        data-programme="{{ $course->programme_id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="filter-divider">

                {{-- ── GROUP 4: Assignment ──────────────────────────── --}}
                <div class="filter-group-label"><i class="las la-users-cog"></i> Assignment</div>
                <div class="row g-3">
                    @if(in_array(auth()->user()->role, ['super_admin', 'operation_head']))
                    <div class="col-xl-3 col-lg-3 col-md-4">
                        <label class="filter-label">Branch</label>
                        <select class="form-select form-select-sm" id="filterBranch">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-xl-3 col-lg-3 col-md-4">
                        <label class="filter-label">Assigned To Telecaller</label>
                        <select class="form-select form-select-sm" id="filterAssignedTo">
                            <option value="">All</option>
                            <option value="unassigned">Unassigned</option>
                            @foreach($assignableUsers as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }}{{ $u->branch ? ' — '.$u->branch->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-1 col-lg-2 col-md-2 col-4">
                        <label class="filter-label">Show</label>
                        <select class="form-select form-select-sm" id="perPageSelect">
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- ── Leads Table Card ──────────────────────────────────────── --}}
    <div class="card leads-card">

        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong id="leadCount">{{ $leads->total() }}</strong> leads found
                    <span class="text-muted ms-2" id="pageInfo">
                        @if($leads->total() > 0)
                            {{ $leads->firstItem() ?? 1 }}–{{ $leads->lastItem() ?? $leads->total() }} of {{ $leads->total() }}
                        @else
                            0–0 of 0
                        @endif
                    </span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary py-2 px-3">
                        <i class="las la-school me-1"></i>Schools
                        <span id="ic-school">{{ $institutionCounts['school'] ?? 0 }}</span>
                    </span>
                    <span class="badge bg-success py-2 px-3">
                        <i class="las la-graduation-cap me-1"></i>Colleges
                        <span id="ic-college">{{ $institutionCounts['college'] ?? 0 }}</span>
                    </span>

                    @if(auth()->user()->canAssignLeads())
                    <button type="button" class="btn btn-info d-none" id="bulkAssignBtn">
                        <i class="las la-users me-1"></i>Bulk Assign
                        <span id="bulkcount">0</span>
                    </button>
                    @endif

                    <button type="button" class="btn btn-success" id="exportBtn" onclick="exportWithFilters()">
                        <i class="las la-file-download me-1"></i>Export CSV
                    </button>

                    @if(auth()->user()->canCreateLeads())
                    <a href="{{ route('edu-leads.create') }}" class="btn btn-primary">
                        <i class="las la-plus me-1"></i>Create Lead
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Status Tabs --}}
        <div class="status-tabs" id="statusTabs">
            <span class="status-tab active" data-status="">All <span class="tab-count" id="tc-all">{{ $statusCounts['all'] ?? $leads->total() }}</span></span>
            <span class="status-tab" data-status="pending">Pending <span class="tab-count" id="tc-pending">{{ $statusCounts['pending'] ?? 0 }}</span></span>
            <span class="status-tab" data-status="contacted">Contacted <span class="tab-count" id="tc-contacted">{{ $statusCounts['contacted'] ?? 0 }}</span></span>
            <span class="status-tab" data-status="follow_up">Follow Up <span class="tab-count" id="tc-follow_up">{{ $statusCounts['follow_up'] ?? 0 }}</span></span>
            <span class="status-tab" data-status="admitted">Admitted <span class="tab-count" id="tc-admitted">{{ $statusCounts['admitted'] ?? 0 }}</span></span>
            <span class="status-tab" data-status="not_interested">Not Interested <span class="tab-count" id="tc-not_interested">{{ $statusCounts['not_interested'] ?? 0 }}</span></span>
            <span class="status-tab" data-status="dropped">Dropped <span class="tab-count" id="tc-dropped">{{ $statusCounts['dropped'] ?? 0 }}</span></span>
        </div>

        <div class="card-body p-0">
            <div class="table-container">
                <table class="table table-hover mb-0" id="leadsTable">
                    <thead class="table-light">
                        <tr>
                            @if(auth()->user()->canAssignLeads())
                            <th class="checkbox-col">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="selectAll" title="Select All">
                                </div>
                            </th>
                            @endif
                            <th class="sortable" data-column="lead_code">Lead Code</th>
                            <th class="sortable" data-column="name">Name</th>
                            <th class="sortable" data-column="phone">Phone</th>
                            <th class="sortable" data-column="final_status">Status</th>
                            <th>Followups</th>
                            <th>Agent</th>
                            <th>Institution</th>
                            <th>Department</th>
                            <th>State / District</th>
                            <th>Preferred State</th>
                            <th class="sortable" data-column="course_id">Course</th>
                            <th class="sortable" data-column="interest_level">Interest</th>
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

</div>

{{-- ── SINGLE ASSIGN MODAL ─────────────────────────────────────── --}}
@if(auth()->user()->canAssignLeads())
<div class="modal fade" id="assignLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="las la-user-plus me-2"></i>Assign Lead</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignLeadForm">
                @csrf
                <input type="hidden" id="assignLeadId" name="lead_id">
                <div class="modal-body">
                    <div class="alert alert-light border mb-3 py-2 px-3">
                        <div class="d-flex gap-3">
                            <div>
                                <small class="text-muted d-block">Lead Code</small>
                                <strong id="assignLeadCode" class="text-primary"></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Name</small>
                                <strong id="assignLeadName"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="currentAssignmentBlock" style="display:none">
                        <small class="text-muted">Currently assigned to</small>
                        <span class="badge bg-secondary ms-2" id="currentAssigneeLabel"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-user-tie me-1"></i>Assign To Telecaller
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="assignTelecaller" name="assigned_to" required>
                            <option value="">Choose telecaller...</option>
                            @foreach($assignableUsers as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }}
                                    @if($u->branch) — {{ $u->branch->name }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label"><i class="las la-comment me-1"></i>Notes <small class="text-muted">optional</small></label>
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

{{-- ── BULK ASSIGN MODAL ──────────────────────────────────────── --}}
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="las la-users me-2"></i>Bulk Assign Leads</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAssignForm">
                @csrf
                <div class="modal-body">
                    <div class="alert mb-3 py-3 px-4"
                         style="background:linear-gradient(135deg,#fff3cd,#ffe69c);border-left:4px solid #ffc107;">
                        <div class="d-flex align-items-center gap-3">
                            <i class="las la-check-circle" style="font-size:2rem;color:#997404;"></i>
                            <div>
                                <span style="font-size:1.6rem;font-weight:700;color:#664d03;" id="selectedCount">0</span>
                                <span style="font-size:1rem;color:#664d03;"> leads selected for assignment</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small mb-1">
                            <i class="las la-list me-1"></i>Selected Leads
                        </label>
                        <div id="selectedLeadsList"
                             style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:8px;background:#f8f9fa;padding:8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-user-tie me-1"></i>Assign To Telecaller
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="bulkTelecaller" name="assigned_to" required>
                            <option value="">Choose telecaller...</option>
                            @foreach($assignableUsers as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }}
                                    @if($u->branch) — {{ $u->branch->name }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            <i class="las la-comment me-1"></i>Notes <small class="text-muted">optional</small>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const districtMap = @json($districtMap ?? []);

// Master copy of ALL course options — built once on page load, never mutated
let allCourseOptions = [];

$(document).ready(function () {

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    let currentSort  = { column: 'created_at', direction: 'desc' };
    let activeStatus = '';

    // ── SELECT2 FACTORY ─────────────────────────────────────────────
    function s2(selector, placeholder) {
        $(selector).select2({ theme: 'bootstrap-5', placeholder, allowClear: true, width: '100%' });
    }

    // ── INIT ALL SELECT2 ─────────────────────────────────────────────
    s2('#filterState',         'Search state...');
    s2('#filterDistrict',      'Select district...');
    s2('#filterPreferredState','Search preferred state...');
    s2('#filterProgramme',     'All programmes...');
    s2('#filterCourse',        'All courses...');
    s2('#filterSource',        'All sources...');
    s2('#filterAssignedTo',    'All telecallers...');
    s2('#filterBranch',        'All branches...');
    s2('#assignTelecaller',    'Choose telecaller...');
    s2('#bulkTelecaller',      'Choose telecaller...');

    // ── BUILD MASTER COURSE LIST from initial DOM ────────────────────
    $('#filterCourse option').each(function () {
        if (!$(this).val()) return; // skip "All Courses" placeholder
        allCourseOptions.push({
            val:       $(this).val(),
            text:      $(this).text(),
            programme: $(this).data('programme')
        });
    });

    const districtMap = @json($districtMap);  {{-- NO ?? [] since we now always pass it --}}

    function populateFilterDistricts(state, selectedDistrict) {
        const districts = (state && Array.isArray(districtMap[state])) ? districtMap[state] : [];
        const $d = $('#filterDistrict');

        // Always destroy Select2 cleanly before touching the DOM
        try {
            if ($d.hasClass('select2-hidden-accessible')) $d.select2('destroy');
        } catch(e) {}

        // Rebuild options
        $d.empty().append('<option value=""></option>');
        districts.forEach(function (dist) {
            const isSelected = (dist === selectedDistrict);
            $d.append(new Option(dist, dist, isSelected, isSelected));
        });

        // Re-init Select2 fresh by ID selector string
        $('#filterDistrict').select2({
            theme:       'bootstrap-5',
            placeholder: districts.length > 0 ? 'Search district...' : 'Select a state first...',
            allowClear:  true,
            width:       '100%'
        });
    }

    // State cascade — must use jQuery .on(), not native .onchange
    $('#filterState').on('change', function () {
        populateFilterDistricts($(this).val(), '');
        updateActiveFilterCount();
        loadLeads();
    });

    // ── PROGRAMME → COURSE CASCADE (remove/restore from DOM) ─────────
    function cascadeCourses() {
        const programmeId = $('#filterProgramme').val();
        const currentVal  = $('#filterCourse').val();

        // Destroy Select2 before rebuilding options
        const $fc = $('#filterCourse');
        if ($fc.hasClass('select2-hidden-accessible')) $fc.select2('destroy');

        // Rebuild options from master list
        $fc.empty().append('<option value="">All Courses</option>');
        allCourseOptions.forEach(function (opt) {
            if (!programmeId || String(opt.programme) === String(programmeId)) {
                const o = new Option(opt.text, opt.val, opt.val === currentVal, opt.val === currentVal);
                $(o).attr('data-programme', opt.programme);
                $fc.append(o);
            }
        });

        // Restore previous value only if it still exists in filtered list
        const stillExists = $fc.find(`option[value="${currentVal}"]`).length > 0;
        $fc.val(stillExists ? currentVal : '');

        // Re-init Select2
        s2('#filterCourse', 'All courses...');
    }

    $('#filterProgramme').on('change', function () {
        cascadeCourses();
        updateActiveFilterCount();
        loadLeads();
    });

    // ── FILTER PANEL TOGGLE ──────────────────────────────────────────
    window.toggleFilters = function () {
        const $body = $('#filterBody');
        const $icon = $('#filterToggleIcon');
        const $hint = $('#collapseHintText');
        const isHidden = $body.is(':hidden');
        $body.slideToggle(200);
        $icon.toggleClass('open', isHidden);
        // $hint.text(isHidden ? 'Hide Filters' : 'Show Filters');
    };

    // ── INSTITUTION TYPE — always school or college, no "all" state ──
    function applyInstitutionTypeUi(type) {
        if (type === 'college') {
            $('#collegeDeptWrap').show().removeClass('dimmed');
            $('#schoolDeptWrap').hide().addClass('dimmed');
            $('#filterSchoolDepartment').val('').trigger('change');
        } else {
            // default = school
            $('#schoolDeptWrap').show().removeClass('dimmed');
            $('#collegeDeptWrap').hide().addClass('dimmed');
            $('#filterCollegeDepartment').val('').trigger('change');
        }
    }

    $('#filterInstitutionType').on('change', function () {
        applyInstitutionTypeUi($(this).val());
        updateActiveFilterCount();
        loadLeads();
    });

    // ── ACTIVE FILTER BADGE COUNT ────────────────────────────────────
    function updateActiveFilterCount() {
        const selectIds = [
            'filterInterestLevel', 'filterSource',
            // removed filterInstitutionType — it always has a value
            'filterSchoolDepartment', 'filterCollegeDepartment',
            'filterProgramme', 'filterCourse',
            'filterState', 'filterDistrict',
            'filterPreferredState', 'filterBranch', 'filterAssignedTo'
        ];
        const textIds = ['searchInput', 'filterAgentName', 'dateFrom', 'dateTo'];
        const count = [...selectIds, ...textIds].filter(id => {
            const el = document.getElementById(id);
            return el && el.value && el.value.length > 0;
        }).length;
        const $badge = $('#activeFilterCount');
        count > 0 ? $badge.text(count).show() : $badge.hide();
    }

    // ── COLLECT FILTER PARAMS ────────────────────────────────────────
    function getFilterParams() {
        return {
            search:            $('#searchInput').val(),
            interestlevel:     $('#filterInterestLevel').val(),
            institutiontype:   $('#filterInstitutionType').val(),
            schooldepartment:  $('#filterSchoolDepartment').val(),
            collegedepartment: $('#filterCollegeDepartment').val(),
            programmeid:       $('#filterProgramme').val(),
            courseid:          $('#filterCourse').val(),
            state:             $('#filterState').val(),
            district:          $('#filterDistrict').val(),
            preferredstate:    $('#filterPreferredState').val(),
            agentname:         $('#filterAgentName').val(),
            leadsourceid:      $('#filterSource').val(),
            assignedto:        $('#filterAssignedTo').val(),
            branchid:          $('#filterBranch').val(),
            datefrom:          $('#dateFrom').val(),
            dateto:            $('#dateTo').val(),
            finalstatus:       activeStatus,
            sortcolumn:        currentSort.column,
            sortdirection:     currentSort.direction,
            perpage:           $('#perPageSelect').val(),
        };
    }

    // ── PRE-POPULATE FILTERS FROM URL ────────────────────────────────
    function prePopulateFilters() {
        const p = new URLSearchParams(window.location.search);
        const map = {
            search:            '#searchInput',
            interestlevel:     '#filterInterestLevel',
            institutiontype:   '#filterInstitutionType',
            schooldepartment:  '#filterSchoolDepartment',
            collegedepartment: '#filterCollegeDepartment',
            programmeid:       '#filterProgramme',
            courseid:          '#filterCourse',
            state:             '#filterState',
            district:          '#filterDistrict',
            preferredstate:    '#filterPreferredState',
            agentname:         '#filterAgentName',
            leadsourceid:      '#filterSource',
            assignedto:        '#filterAssignedTo',
            branchid:          '#filterBranch',
            datefrom:          '#dateFrom',
            dateto:            '#dateTo',
            perpage:           '#perPageSelect',
        };
        $.each(map, function (param, selector) {
            const val = p.get(param);
            if (val) $(selector).val(val).trigger('change');
        });

        // Restore district cascade when state is pre-filled from URL
        const preState    = p.get('state');
        const preDistrict = p.get('district');
        if (preState) populateFilterDistricts(preState, preDistrict || '');

        // Restore course cascade when programme pre-filled
        if (p.get('programmeid')) cascadeCourses();

        if (p.get('sortcolumn'))   currentSort.column    = p.get('sortcolumn');
        if (p.get('sortdirection')) currentSort.direction = p.get('sortdirection');
        if (currentSort.column) $(`.sortable[data-column="${currentSort.column}"]`).addClass(currentSort.direction);

        if (p.get('finalstatus')) {
            activeStatus = p.get('finalstatus');
            $('.status-tab').removeClass('active');
            $(`.status-tab[data-status="${activeStatus}"]`).addClass('active');
        }

        applyInstitutionTypeUi(p.get('institutiontype') || 'school');
        // ── AFTER ──
        updateActiveFilterCount();

        // Auto-expand filter panel only if URL has active filters
        const hasUrlFilters = [...p.values()].some(v => v.trim() !== '');
        if (hasUrlFilters) {
            const body = document.getElementById('filterBody');
            const icon = document.getElementById('filterToggleIcon');
            body.style.display = 'block';
            icon.classList.add('open');
        }
    }

    // ── LOAD LEADS AJAX ──────────────────────────────────────────────
    function loadLeads(url) {
        const requestUrl = url || "{{ route('edu-leads.index') }}";
        $('#leadsTable').addClass('table-loading');
        $.ajax({
            url:  requestUrl,
            type: 'GET',
            data: getFilterParams(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                $('#leadsTableBody').html(res.html);
                $('#paginationContainer').html(res.pagination);
                $('#leadCount').text(res.total);
                if (res.from && res.to) {
                    $('#pageInfo').text(`${res.from}–${res.to} of ${res.total}`);
                } else {
                    $('#pageInfo').text(res.total > 0 ? `1–${res.total} of ${res.total}` : '0–0 of 0');
                }
                if (res.statuscounts) {
                    $('#tc-all').text(res.statuscounts.all ?? 0);
                    $.each(res.statuscounts, function (k, v) { if (k !== 'all') $(`#tc-${k}`).text(v); });
                }
                if (res.institutioncounts) {
                    $('#ic-school').text(res.institutioncounts.school ?? 0);
                    $('#ic-college').text(res.institutioncounts.college ?? 0);
                }
                $('#leadsTable').removeClass('table-loading');
                $('#selectAll').prop('checked', false);
                updateBulkBtn();
            },
            error: function () {
                $('#leadsTable').removeClass('table-loading');
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load leads.' });
            }
        });
    }

    // ── EXPORT ───────────────────────────────────────────────────────
    window.exportWithFilters = function () {
        const params = new URLSearchParams();
        $.each(getFilterParams(), function (k, v) { if (v) params.set(k, v); });
        window.location.href = "{{ route('edu-leads.export') }}?" + params.toString();
    };

    // ── FILTER CONTROLS ──────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () { updateActiveFilterCount(); loadLeads(); });

    $('#resetBtn').on('click', function () {
        $('#filterForm')[0].reset();
        $('#filterInstitutionType').val('school').trigger('change');
        // Reset all Select2
        $('#filterState, #filterPreferredState, #filterProgramme, #filterSource, #filterAssignedTo, #filterBranch')
            .val('').trigger('change');
        // Reset & rebuild district
        populateFilterDistricts('', '');
        $('#filterDistrict').val('').trigger('change');
        // Rebuild course list fully
        cascadeCourses();
        $('#filterCourse').val('').trigger('change');
        // Reset institution dept UI
        $('#schoolDeptWrap, #collegeDeptWrap').show().removeClass('dimmed');
        activeStatus = '';
        currentSort  = { column: 'created_at', direction: 'desc' };
        $('.sortable').removeClass('asc desc');
        $('.status-tab').removeClass('active');
        $('.status-tab[data-status=""]').addClass('active');
        $('#activeFilterCount').hide();
        window.location.href = "{{ route('edu-leads.index') }}";
    });

    // Search – debounced
    let searchTimer;
    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { updateActiveFilterCount(); loadLeads(); }, 450);
    });

    // Agent name – debounced
    let agentTimer;
    $('#filterAgentName').on('keyup', function () {
        clearTimeout(agentTimer);
        agentTimer = setTimeout(function () { updateActiveFilterCount(); loadLeads(); }, 450);
    });

    // Instant-change selects
    $('#filterPreferredState, #filterCourse, #filterSource, #filterAssignedTo, #filterBranch, ' +
      '#filterSchoolDepartment, #filterCollegeDepartment, #filterDistrict, #filterInterestLevel')
        .on('change', function () { updateActiveFilterCount(); loadLeads(); });

    $('#dateFrom, #dateTo').on('change', function () { updateActiveFilterCount(); loadLeads(); });
    $('#perPageSelect').on('change', function () { loadLeads(); });

    // ── PAGINATION ───────────────────────────────────────────────────
    $(document).on('click', '#paginationContainer .pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) loadLeads(url);
    });

    // ── SORTING ──────────────────────────────────────────────────────
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

    // ── QUICK STATUS TABS ─────────────────────────────────────────────
    $(document).on('click', '.status-tab', function () {
        $('.status-tab').removeClass('active');
        $(this).addClass('active');
        activeStatus = $(this).data('status');
        loadLeads();
    });

    // ── CHECKBOXES ───────────────────────────────────────────────────
    const $selectAll = $('#selectAll');
    $selectAll.on('change', function () {
        $('.lead-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkBtn();
    });
    $(document).on('change', '.lead-checkbox', function () {
        updateBulkBtn();
        const total   = $('.lead-checkbox').length;
        const checked = $('.lead-checkbox:checked').length;
        $selectAll.prop('checked', total > 0 && total === checked);
    });
    function updateBulkBtn() {
        const n = $('.lead-checkbox:checked').length;
        $('#bulkcount').text(n);
        $('#bulkAssignBtn').toggleClass('d-none', n === 0);
    }
    function getSelectedIds() {
        return $('.lead-checkbox:checked').map(function () { return $(this).val(); }).get();
    }

    // ── SINGLE ASSIGN ─────────────────────────────────────────────────
    @if(auth()->user()->canAssignLeads())
    $(document).on('click', '.assignLeadBtn', function () {
        const btn = $(this);
        $('#assignLeadId').val(btn.data('id'));
        $('#assignLeadCode').text(btn.data('code'));
        $('#assignLeadName').text(btn.data('name'));
        $('#assignNotes').val('');
        $('#assignTelecaller').val('').trigger('change');
        const assignee = btn.data('assignee');
        $('#currentAssigneeLabel').text(assignee);
        $('#currentAssignmentBlock').toggle(!!assignee);
        $('#assignLeadModal').modal('show');
    });

    $('#assignLeadForm').on('submit', function (e) {
        e.preventDefault();
        const telecaller = $('#assignTelecaller').val();
        if (!telecaller) { Swal.fire({ icon: 'warning', title: 'Select Telecaller', text: 'Please choose a telecaller.' }); return; }
        const btn = $(this).find('[type=submit]').prop('disabled', true)
                           .html('<span class="spinner-border spinner-border-sm me-1"></span>Assigning...');
        $.ajax({
            url:  `/edu-leads/${$('#assignLeadId').val()}/assign`,
            type: 'POST',
            data: { assigned_to: telecaller, notes: $('#assignNotes').val() },
            success: function (res) {
                $('#assignLeadModal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Assigned!', html: `Lead assigned to <strong>${res.telecaller_name}</strong>`, timer: 2500, showConfirmButton: false }).then(() => loadLeads());
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="las la-check me-1"></i>Assign Lead');
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Assignment failed.' });
            }
        });
    });

    // ── BULK ASSIGN ───────────────────────────────────────────────────
    $('#bulkAssignBtn').on('click', function () {
        const selected = getSelectedIds();
        $('#selectedCount').text(selected.length);
        $('#bulkTelecaller').val('').trigger('change');
        $('#bulkNotes').val('');
        const list = $('#selectedLeadsList').empty();
        if (!selected.length) {
            list.html('<p class="text-muted text-center py-2 mb-0"><small>No leads selected</small></p>');
        } else {
            selected.forEach(function (id) {
                const row      = $(`input.lead-checkbox[value="${id}"]`).closest('tr');
                const code     = row.find('[data-label="code"]').text().trim() || row.find('td').eq(1).text().trim();
                const name     = row.find('.lead-name-link').text().trim();
                const assignee = row.find('.assigned-to-name').text().trim();
                list.append(`<div class="bulk-lead-item"><span class="badge bg-secondary">${code}</span>
                    <span class="fw-semibold">${name}</span>
                    ${assignee ? `<span class="text-muted small ms-auto">${assignee}</span>` : ''}</div>`);
            });
        }
        $('#bulkAssignModal').modal('show');
    });

    $('#bulkAssignForm').on('submit', function (e) {
        e.preventDefault();
        const selected   = getSelectedIds();
        const telecaller = $('#bulkTelecaller').val();
        if (!selected.length) { Swal.fire({ icon: 'warning', title: 'Nothing Selected', text: 'Select at least one lead.' }); return; }
        if (!telecaller)      { Swal.fire({ icon: 'warning', title: 'Select Telecaller', text: 'Please choose a telecaller.' }); return; }
        $('#bulkAssignModal').modal('hide');
        $.ajax({
            url:  "{{ route('edu-leads.bulk-assign') }}",
            type: 'POST',
            data: { lead_ids: selected, assigned_to: telecaller, notes: $('#bulkNotes').val() },
            success: function (res) {
                Swal.fire({ icon: 'success', title: 'Success!', html: `<strong>${res.count}</strong> leads assigned to <strong>${res.telecaller_name}</strong>`, timer: 3000 })
                    .then(function () { $('.lead-checkbox, #selectAll').prop('checked', false); updateBulkBtn(); loadLeads(); });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Bulk assign failed.' });
            }
        });
    });
    @endif

    // ── DELETE LEAD ───────────────────────────────────────────────────
    $(document).on('click', '.deleteLeadBtn', function () {
        const id   = $(this).attr('data-id');
        const name = $(this).attr('data-name');
        Swal.fire({
            title: 'Delete Lead?',
            html:  `Are you sure you want to delete <strong>${name}</strong>?`,
            icon:  'warning', showCancelButton: true,
            confirmButtonText: 'Yes, Delete', confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url:  `/edu-leads/${id}`,
                type: 'DELETE',
                success: function (res) {
                    if (res.success) Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, timer: 2000, showConfirmButton: false }).then(loadLeads);
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Could not delete lead.' });
                }
            });
        });
    });

    // ── INITIALISE ────────────────────────────────────────────────────
    prePopulateFilters();

});
</script>
@endsection
