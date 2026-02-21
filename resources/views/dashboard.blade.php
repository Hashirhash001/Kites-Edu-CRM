@extends('layouts.app')

@section('title', 'Dashboard')

@section('extra-css')
<style>
    /* ── Design tokens ─────────────────────────────────────────────── */
    :root {
        --primary      : #2563eb;
        --success      : #10b981;
        --warning      : #f59e0b;
        --danger       : #ef4444;
        --info         : #06b6d4;
        --purple       : #8b5cf6;
        --orange       : #f97316;
        --text-primary : #1e293b;
        --text-muted   : #64748b;
        --border       : #e5e7eb;
        --bg-subtle    : #f8fafc;
        --radius-sm    : 8px;
        --radius-md    : 12px;
        --radius-lg    : 16px;
        --shadow-sm    : 0 1px 3px rgba(0,0,0,0.08);
        --shadow-md    : 0 4px 12px rgba(0,0,0,0.10);
        --shadow-lg    : 0 8px 24px rgba(0,0,0,0.14);
        --transition   : all 0.25s ease;
    }

    /* ── Base card ─────────────────────────────────────────────────── */
    .card { border-radius: var(--radius-md); border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 0; }

    /* ── Stat cards ────────────────────────────────────────────────── */
    .stat-card { height: 100%; min-height: 110px; transition: var(--transition); }
    .stat-card .card-body { display: flex; align-items: center; height: 100%; padding: 1.1rem 1.25rem; gap: 0.75rem; }
    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
    a.text-decoration-none .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .stat-card .avatar-sm { width: 48px; height: 48px; flex-shrink: 0; }
    .stat-card .avatar-title { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); font-size: 1.5rem; }

    /* Soft bg helpers */
    .bg-soft-primary  { background-color: #dbeafe !important; }
    .bg-soft-success  { background-color: #d1fae5 !important; }
    .bg-soft-warning  { background-color: #fef3c7 !important; }
    .bg-soft-danger   { background-color: #fee2e2 !important; }
    .bg-soft-info     { background-color: #cffafe !important; }
    .bg-soft-purple   { background-color: #ede9fe !important; }
    .bg-soft-orange   { background-color: #ffedd5 !important; }
    .text-purple      { color: var(--purple) !important; }
    .text-orange      { color: var(--orange) !important; }

    /* ── Section headers ────────────────────────────────────────────── */
    .section-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1.1rem 1.5rem; border-radius: var(--radius-md) var(--radius-md) 0 0;
        cursor: pointer; user-select: none; color: #fff; position: relative;
        overflow: hidden; transition: var(--transition);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 15px rgba(102,126,234,0.25);
    }
    .section-header::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.12) 50%, transparent 100%);
        transform: translateX(-100%); transition: transform 0.5s ease;
    }
    .section-header:hover::before { transform: translateX(100%); }
    .section-header:hover { transform: translateY(-1px); }
    .section-header.overdue { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); box-shadow: 0 4px 15px rgba(220,38,38,0.25); }
    .section-header.today   { background: linear-gradient(135deg, #f97316 0%, #c2410c 100%); box-shadow: 0 4px 15px rgba(249,115,22,0.25); }
    .section-header.week    { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); box-shadow: 0 4px 15px rgba(14,165,233,0.25); }
    .section-header.month   { background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 4px 15px rgba(5,150,105,0.25); }
    .section-header h5 { margin: 0; font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .section-header-right { display: flex; align-items: center; gap: 0.75rem; }
    .count-badge { background: rgba(255,255,255,0.22); color: #fff; padding: 0.3rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.82rem; border: 1px solid rgba(255,255,255,0.3); }
    .toggle-icon { font-size: 1.3rem; font-weight: 700; transition: transform 0.3s ease; line-height: 1; }
    .toggle-icon.collapsed { transform: rotate(-90deg); }

    /* ── Followup collapse ──────────────────────────────────────────── */
    .followup-collapse-container { animation: slideDown 0.25s ease; }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Followup items ─────────────────────────────────────────────── */
    .followup-item { background: #fff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.1rem 1.25rem; transition: var(--transition); }
    .followup-item:hover { box-shadow: var(--shadow-md); border-color: #c7d2fe; }
    .followup-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; gap: 0.75rem; }
    .followup-title { font-weight: 600; color: var(--text-primary); font-size: 1rem; margin-bottom: 0.25rem; }
    .followup-title a { color: var(--text-primary); text-decoration: none; transition: color 0.2s ease; }
    .followup-title a:hover { color: var(--primary); }
    .followup-meta { font-size: 0.85rem; color: var(--text-muted); display: flex; flex-wrap: wrap; gap: 0.75rem 1.25rem; align-items: center; }
    .followup-meta span { display: inline-flex; align-items: center; gap: 0.3rem; }
    .followup-meta i { color: #94a3b8; }
    .followup-notes { margin-top: 0.85rem; padding: 0.65rem 1rem; background: var(--bg-subtle); border-radius: var(--radius-sm); border-left: 3px solid #cbd5e1; font-size: 0.875rem; color: var(--text-muted); }

    /* ── Priority badges ────────────────────────────────────────────── */
    .priority-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.78rem; white-space: nowrap; }
    .priority-badge.high   { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; box-shadow: 0 2px 6px rgba(220,38,38,0.35); }
    .priority-badge.medium { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 2px 6px rgba(245,158,11,0.35); }
    .priority-badge.low    { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 2px 6px rgba(16,185,129,0.35); }

    /* ── Followup date badge ────────────────────────────────────────── */
    .time-preference-badge { display: inline-flex; align-items: center; gap: 0.4rem; background: #fefce8; color: #713f12; padding: 0.4rem 0.85rem; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.82rem; border: 1px solid #fde047; white-space: nowrap; }
    .time-preference-badge i { color: #ca8a04; font-size: 0.95rem; }

    /* ── Overdue badge ──────────────────────────────────────────────── */
    .overdue-badge { display: inline-flex; align-items: center; gap: 0.3rem; background: #fee2e2; color: #991b1b; padding: 0.25rem 0.65rem; border-radius: 6px; font-weight: 600; font-size: 0.75rem; border: 1px solid #fca5a5; }

    /* ── Complete button ────────────────────────────────────────────── */
    .btn-complete-followup { background: linear-gradient(135deg, #10b981, #059669); border: none; color: #fff; font-weight: 600; font-size: 0.82rem; padding: 0.45rem 1rem; border-radius: var(--radius-sm); transition: var(--transition); box-shadow: 0 2px 6px rgba(16,185,129,0.3); white-space: nowrap; cursor: pointer; }
    .btn-complete-followup:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.4); color: #fff; }

    /* ── Empty state ────────────────────────────────────────────────── */
    .empty-state { text-align: center; padding: 3.5rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 3.5rem; opacity: 0.18; display: block; margin-bottom: 1rem; }

    /* ── Quick search dropdown ──────────────────────────────────────── */
    .search-results-dropdown { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); max-height: 420px; overflow-y: auto; z-index: 1060; }
    .search-result-header { position: sticky; top: 0; background: var(--bg-subtle); padding: 7px 15px; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-muted); border-bottom: 1px solid var(--border); }
    .search-result-item { padding: 11px 15px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.15s ease; }
    .search-result-item:hover { background: #f8faff; }
    .search-result-item:last-child { border-bottom: none; }
    .search-result-title { font-weight: 600; font-size: 0.9rem; color: var(--text-primary); margin-bottom: 2px; }
    .search-no-results { padding: 24px; text-align: center; color: var(--text-muted); font-size: 0.9rem; }

    /* ── Interest chips ─────────────────────────────────────────────── */
    .interest-hot  { background: #fee2e2; color: #991b1b; }
    .interest-warm { background: #fef3c7; color: #92400e; }
    .interest-cold { background: #dbeafe; color: #1e3a8a; }

    /* ── Table helpers ──────────────────────────────────────────────── */
    .card .table th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); font-weight: 600; background: var(--bg-subtle); }
    .card .table td { vertical-align: middle; }

    /* ── Pipeline mini-cards ────────────────────────────────────────── */
    .pipeline-stat { border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem 1.25rem; transition: var(--transition); background: #fff; }
    .pipeline-stat:hover { box-shadow: var(--shadow-md); border-color: #c7d2fe; }

    /* ══════════════════════════════════════════════════════════════════
       ── Institution Overview card ──────────────────────────────────
    ══════════════════════════════════════════════════════════════════ */

    /* The outer card contains two halves side-by-side */
    .inst-overview-card .card-body { padding: 0; }

    .inst-half {
        padding: 1.25rem 1.5rem;
        flex: 1 1 0;
        min-width: 0;
    }

    /* Divider between the two halves */
    .inst-half + .inst-half {
        border-left: 1px solid var(--border);
    }

    /* Top section: icon + big count */
    .inst-half-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid var(--border);
    }

    .inst-half-icon {
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        font-size: 1.6rem;
    }

    .inst-half-meta { flex: 1; min-width: 0; }
    .inst-half-meta .inst-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-muted); margin-bottom: 0.1rem; }
    .inst-half-meta .inst-count { font-size: 2rem; font-weight: 800; line-height: 1.1; color: var(--text-primary); }
    .inst-half-meta .inst-sub   { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.1rem; }

    /* Dept list rows */
    .dept-list { display: flex; flex-direction: column; gap: 0.35rem; }

    .dept-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.4rem 0.7rem;
        border-radius: 6px;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        transition: var(--transition);
    }
    .dept-row:hover { background: #f0f4ff; border-color: #c7d2fe; }
    .dept-row-label { font-size: 0.82rem; font-weight: 600; color: var(--text-primary); }
    .dept-row-count { font-size: 0.82rem; font-weight: 700; padding: 0.15rem 0.55rem; border-radius: 20px; }

    /* School = blue chip, College = purple chip */
    .dept-count-school  { background: #dbeafe; color: #1d4ed8; }
    .dept-count-college { background: #ede9fe; color: #6d28d9; }

    /* On mobile: stack halves vertically */
    @media (max-width: 767px) {
        .inst-half + .inst-half { border-left: none; border-top: 1px solid var(--border); }
        .inst-half-meta .inst-count { font-size: 1.6rem; }
    }

    /* ── Role rows ──────────────────────────────────────────────────── */
    .role-stat-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: #fff; margin-bottom: 0.6rem; transition: var(--transition); }
    .role-stat-row:last-child { margin-bottom: 0; }
    .role-stat-row:hover { box-shadow: var(--shadow-sm); border-color: #c7d2fe; background: #f8faff; }
    .role-stat-label { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; }
    .role-stat-count { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); min-width: 2rem; text-align: right; }
    .role-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .role-dot.super_admin    { background: #2563eb; }
    .role-dot.operation_head { background: #f59e0b; }
    .role-dot.lead_manager   { background: #10b981; }

    /* ── Branch stats table ─────────────────────────────────────────── */
    .branch-stats-table { width: 100%; margin: 0; }
    .branch-stats-table thead tr th { padding: 0.65rem 1rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); background: var(--bg-subtle); border-bottom: 2px solid var(--border); white-space: nowrap; }
    .branch-stats-table tbody tr td { padding: 0.7rem 1rem; font-size: 0.875rem; color: var(--text-primary); vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    .branch-stats-table tbody tr:last-child td { border-bottom: none; }
    .branch-stats-table tbody tr:hover td { background: #f8faff; }
    .branch-stats-table .branch-name { font-weight: 600; color: var(--text-primary); white-space: nowrap; }
    .branch-stats-table .badge { font-size: 0.78rem; padding: 0.3em 0.65em; font-weight: 600; min-width: 2rem; text-align: center; }

    /* ── Responsive ─────────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .stat-card { min-height: 95px; }
        .stat-card .card-body { padding: 0.9rem 1rem; }
        .section-header { padding: 0.9rem 1.1rem; }
        .section-header h5 { font-size: 0.9rem; }
        .followup-meta { flex-direction: column; gap: 0.4rem; }
        .followup-header { flex-direction: column; }
        .time-preference-badge { width: 100%; justify-content: center; }
    }
    @media (max-width: 576px) {
        .stat-card .avatar-sm,
        .stat-card .avatar-title { width: 40px; height: 40px; font-size: 1.2rem; }
        .count-badge { font-size: 0.75rem; padding: 0.25rem 0.6rem; }
    }
</style>
@endsection

@section('content')
@php
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();
@endphp

{{-- ══════════════════════════════════════════════════════════════════
     REUSABLE: Institution Overview partial (used by all 3 roles)
     Variables expected: $schoolLeads, $otherInstLeads, $collegeLeads,
                         $schoolDepts (assoc), $collegeDepts (assoc)
══════════════════════════════════════════════════════════════════ --}}
@php
    // Inline closure so we don't repeat HTML three times
    $renderInstOverview = true; // just a flag — we'll use an @include pattern below
@endphp

<div class="container-fluid">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <div>
                    <h4 class="page-title mb-1">Dashboard</h4>
                    <span class="badge me-2
                        @if($authUser->isSuperAdmin())        bg-primary
                        @elseif($authUser->isOperationHead()) bg-warning text-dark
                        @elseif($authUser->isLeadManager())   bg-success
                        @else bg-secondary
                        @endif">
                        {{ $authUser->role_label }}
                    </span>
                    @if($authUser->branch)
                        <span class="badge bg-light text-dark border">
                            <i class="las la-building me-1"></i>{{ $authUser->branch->name }}
                        </span>
                    @endif
                </div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">EduCRM</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- ── Quick Search ─────────────────────────────────────────────── --}}
    @if($authUser->isSuperAdmin() || $authUser->isOperationHead() || $authUser->isLeadManager())
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="row align-items-end g-2">
                        <div class="col-lg-8 col-md-7">
                            <label class="form-label fw-semibold mb-2">
                                <i class="las la-search me-1"></i>Quick Search Leads
                            </label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="quickSearch"
                                       placeholder="Search by lead code, name, phone, email..."
                                       autocomplete="off">
                                <i class="las la-search position-absolute"
                                   style="right:12px;top:50%;transform:translateY(-50%);font-size:18px;color:#6c757d;pointer-events:none;"></i>
                                <div id="searchResults" class="search-results-dropdown" style="display:none;">
                                    <div class="search-loading text-center py-3" style="display:none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <span class="ms-2 text-muted">Searching...</span>
                                    </div>
                                    <div class="search-content"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <a href="{{ route('edu-leads.create') }}" class="btn btn-primary w-100">
                                <i class="las la-plus me-1"></i> New Lead
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════════
         SUPER ADMIN DASHBOARD
    ════════════════════════════════════════════════════════════════ --}}
    @if($authUser->isSuperAdmin())

        {{-- Row 1: Core Stats --}}
        <div class="row mb-3">
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('users.index') }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Total Users</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $totalUsers }}</h4>
                                    <small class="text-success">{{ $activeUsers }} Active</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-success text-success rounded">
                                        <i class="las la-users fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index') }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Total Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $totalLeads }}</h4>
                                    <small class="text-muted">All time</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-primary text-primary rounded">
                                        <i class="las la-user-graduate fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $pendingLeads }}</h4>
                                    <small class="text-muted">{{ $contactedLeads }} contacted</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-warning text-warning rounded">
                                        <i class="las la-clock fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'admitted']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedLeads }}</h4>
                                    <small class="text-success">{{ $admittedThisMonth }} this month</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-success text-success rounded">
                                        <i class="las la-check-circle fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['interest_level' => 'hot']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Hot Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $hotLeads }}</h4>
                                    <small class="text-warning">{{ $warmLeads }} warm</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                        <i class="las la-fire fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Overdue Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold text-danger">{{ $followupData['overdue'] }}</h4>
                                <small class="text-muted">{{ $followupData['todayCount'] }} today</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-danger text-danger rounded">
                                    <i class="las la-exclamation-triangle fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Admission Time Breakdown --}}
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted Today</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedToday }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-success text-success rounded avatar-sm">
                                <i class="las la-calendar-day fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Week</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisWeek }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-info text-info rounded avatar-sm">
                                <i class="las la-calendar-week fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Month</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisMonth }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-purple text-purple rounded avatar-sm">
                                <i class="las la-graduation-cap fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Year</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisYear }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-primary text-primary rounded avatar-sm">
                                <i class="las la-trophy fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Institution Overview (School + College unified card) --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card inst-overview-card mb-0">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="las la-university text-primary fs-18"></i>
                        <h5 class="card-title mb-0 fw-semibold">Institution Overview</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex flex-wrap">

                            {{-- ── School Half ── --}}
                            <div class="inst-half">
                                <div class="inst-half-header">
                                    <div class="inst-half-icon bg-soft-primary">
                                        <i class="las la-school text-primary"></i>
                                    </div>
                                    <div class="inst-half-meta">
                                        <div class="inst-label">School Leads</div>
                                        <div class="inst-count text-primary">{{ $schoolLeads }}</div>
                                        <div class="inst-sub">{{ $otherInstLeads }} from other institutions</div>
                                    </div>
                                    <a href="{{ route('edu-leads.index', ['institution_type' => 'school']) }}"
                                       class="btn btn-sm btn-outline-primary ms-auto align-self-start">
                                        View
                                    </a>
                                </div>
                                @if(!empty($schoolDepts))
                                    <div class="dept-list">
                                        @foreach($schoolDepts as $stream => $count)
                                        <div class="dept-row">
                                            <span class="dept-row-label">
                                                <i class="las la-angle-right me-1 text-muted" style="font-size:.7rem;"></i>
                                                {{ $stream }}
                                            </span>
                                            <span class="dept-row-count dept-count-school">{{ $count }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0 fs-13">No stream data available.</p>
                                @endif
                            </div>

                            {{-- ── College Half ── --}}
                            <div class="inst-half">
                                <div class="inst-half-header">
                                    <div class="inst-half-icon bg-soft-purple">
                                        <i class="las la-graduation-cap text-purple"></i>
                                    </div>
                                    <div class="inst-half-meta">
                                        <div class="inst-label">College Leads</div>
                                        <div class="inst-count text-purple">{{ $collegeLeads }}</div>
                                        <div class="inst-sub">Across all departments</div>
                                    </div>
                                    <a href="{{ route('edu-leads.index', ['institution_type' => 'college']) }}"
                                       class="btn btn-sm btn-outline-primary ms-auto align-self-start">
                                        View
                                    </a>
                                </div>
                                @if(!empty($collegeDepts))
                                    <div class="dept-list">
                                        @foreach($collegeDepts as $dept => $count)
                                        <div class="dept-row">
                                            <span class="dept-row-label">
                                                <i class="las la-angle-right me-1 text-muted" style="font-size:.7rem;"></i>
                                                {{ $dept }}
                                            </span>
                                            <span class="dept-row-count dept-count-college">{{ $count }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0 fs-13">No department data available.</p>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Users by Role + Leads by Branch --}}
        <div class="row mb-3">
            <div class="col-xl-4 col-lg-5 mb-3">
                <div class="card h-100 mb-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="las la-id-badge me-2 text-primary"></i>Users by Role
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach([
                            'super_admin'    => 'Super Admin',
                            'operation_head' => 'Operation Head',
                            'lead_manager'   => 'Lead Manager',
                        ] as $role => $label)
                        <div class="role-stat-row">
                            <div class="role-stat-label">
                                <span class="role-dot {{ $role }}"></span>{{ $label }}
                            </div>
                            <span class="role-stat-count">{{ $roleStats[$role] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7 mb-3">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="las la-building me-2 text-primary"></i>Leads by Branch
                        </h5>
                        <a href="{{ route('edu-leads.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table branch-stats-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Branch</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Pending</th>
                                        <th class="text-center">Follow Up</th>
                                        <th class="text-center">Admitted</th>
                                        <th class="text-center">🔥 Hot</th>
                                        <th class="text-center">☀️ Warm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($branchStats as $branch)
                                    <tr>
                                        <td class="branch-name"><i class="las la-map-marker text-muted me-1"></i>{{ $branch->name }}</td>
                                        <td class="text-center"><span class="badge bg-secondary">{{ $branch->total_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning text-dark">{{ $branch->pending_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-info text-dark">{{ $branch->follow_up_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-success">{{ $branch->admitted_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $branch->hot_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning text-dark">{{ $branch->warm_leads }}</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="las la-building" style="font-size:2rem;opacity:.2;display:block;"></i>
                                            No active branches found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif


    {{-- ════════════════════════════════════════════════════════════════
         OPERATION HEAD DASHBOARD
    ════════════════════════════════════════════════════════════════ --}}
    @if($authUser->isOperationHead())

        {{-- Row 1: Core Stats --}}
        <div class="row mb-3">
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index') }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Total Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $totalLeads }}</h4>
                                    <small class="text-muted">All branches</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-primary text-primary rounded">
                                        <i class="las la-user-graduate fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $pendingLeads }}</h4>
                                    <small class="text-muted">{{ $contactedLeads }} contacted</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-warning text-warning rounded">
                                        <i class="las la-clock fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'follow_up']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Follow Up</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $followUpLeads }}</h4>
                                    <small class="text-info">Scheduled</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded">
                                        <i class="las la-redo fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'admitted']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedLeads }}</h4>
                                    <small class="text-success">{{ $admittedThisMonth }} this month</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-success text-success rounded">
                                        <i class="las la-check-circle fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['interest_level' => 'hot']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Hot Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $hotLeads }}</h4>
                                    <small class="text-warning">{{ $warmLeads }} warm</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                        <i class="las la-fire fs-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Overdue Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold text-danger">{{ $followupData['overdue'] }}</h4>
                                <small class="text-muted">{{ $followupData['todayCount'] }} today</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-danger text-danger rounded">
                                    <i class="las la-exclamation-triangle fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Admission Breakdown --}}
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted Today</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedToday }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-success text-success rounded avatar-sm"><i class="las la-calendar-day fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Week</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisWeek }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-info text-info rounded avatar-sm"><i class="las la-calendar-week fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Month</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisMonth }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-purple text-purple rounded avatar-sm"><i class="las la-graduation-cap fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Week's Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $followupData['thisWeek'] }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-warning text-warning rounded avatar-sm"><i class="las la-calendar fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Institution Overview --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card inst-overview-card mb-0">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="las la-university text-primary fs-18"></i>
                        <h5 class="card-title mb-0 fw-semibold">Institution Overview</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex flex-wrap">
                            <div class="inst-half">
                                <div class="inst-half-header">
                                    <div class="inst-half-icon bg-soft-primary"><i class="las la-school text-primary"></i></div>
                                    <div class="inst-half-meta">
                                        <div class="inst-label">School Leads</div>
                                        <div class="inst-count text-primary">{{ $schoolLeads }}</div>
                                        <div class="inst-sub">{{ $otherInstLeads }} from other institutions</div>
                                    </div>
                                    <a href="{{ route('edu-leads.index', ['institution_type' => 'school']) }}" class="btn btn-sm btn-outline-primary ms-auto align-self-start">View</a>
                                </div>
                                @if(!empty($schoolDepts))
                                <div class="dept-list">
                                    @foreach($schoolDepts as $stream => $count)
                                    <div class="dept-row">
                                        <span class="dept-row-label"><i class="las la-angle-right me-1 text-muted" style="font-size:.7rem;"></i>{{ $stream }}</span>
                                        <span class="dept-row-count dept-count-school">{{ $count }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <div class="inst-half">
                                <div class="inst-half-header">
                                    <div class="inst-half-icon bg-soft-purple"><i class="las la-graduation-cap text-purple"></i></div>
                                    <div class="inst-half-meta">
                                        <div class="inst-label">College Leads</div>
                                        <div class="inst-count text-purple">{{ $collegeLeads }}</div>
                                        <div class="inst-sub">Across all departments</div>
                                    </div>
                                    <a href="{{ route('edu-leads.index', ['institution_type' => 'college']) }}" class="btn btn-sm btn-outline-primary ms-auto align-self-start">View</a>
                                </div>
                                @if(!empty($collegeDepts))
                                <div class="dept-list">
                                    @foreach($collegeDepts as $dept => $count)
                                    <div class="dept-row">
                                        <span class="dept-row-label"><i class="las la-angle-right me-1 text-muted" style="font-size:.7rem;"></i>{{ $dept }}</span>
                                        <span class="dept-row-count dept-count-college">{{ $count }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Branch Table --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="las la-building me-2 text-primary"></i>Leads by Branch</h5>
                        <a href="{{ route('edu-leads.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table branch-stats-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Branch</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Pending</th>
                                        <th class="text-center">Follow Up</th>
                                        <th class="text-center">Admitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($branchStats as $branch)
                                    <tr>
                                        <td class="branch-name">{{ $branch->name }}</td>
                                        <td class="text-center"><span class="badge bg-secondary">{{ $branch->total_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning text-dark">{{ $branch->pending_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-info text-dark">{{ $branch->follow_up_leads }}</span></td>
                                        <td class="text-center"><span class="badge bg-success">{{ $branch->admitted_leads }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No branches found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif


    {{-- ════════════════════════════════════════════════════════════════
         LEAD MANAGER DASHBOARD
    ════════════════════════════════════════════════════════════════ --}}
    @if($authUser->isLeadManager())

        {{-- Row 1: Branch Lead Stats --}}
        <div class="row mb-3">
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index') }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Branch Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $myLeads }}</h4>
                                    <small class="text-muted">All time</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-primary text-primary rounded"><i class="las la-user-graduate fs-24"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $myPending }}</h4>
                                    <small class="text-warning">Needs action</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-warning text-warning rounded"><i class="las la-clock fs-24"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'follow_up']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Follow Up</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $myFollowUp }}</h4>
                                    <small class="text-info">Scheduled</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded"><i class="las la-redo fs-24"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['final_status' => 'admitted']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $myAdmitted }}</h4>
                                    <small class="text-success">{{ $admittedThisMonth }} this month</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-success text-success rounded"><i class="las la-check-circle fs-24"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index', ['interest_level' => 'hot']) }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Hot Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $myHot }}</h4>
                                    <small class="text-warning">{{ $myWarm }} warm</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-danger text-danger rounded"><i class="las la-fire fs-24"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Overdue Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold text-danger">{{ $followupData['overdue'] }}</h4>
                                <small class="text-muted">{{ $followupData['todayCount'] }} today</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-danger text-danger rounded"><i class="las la-exclamation-triangle fs-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Admission Time Stats --}}
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted Today</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedToday }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-success text-success rounded avatar-sm"><i class="las la-calendar-day fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Week</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisWeek }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-info text-info rounded avatar-sm"><i class="las la-calendar-week fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Month</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisMonth }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-purple text-purple rounded avatar-sm"><i class="las la-graduation-cap fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Month's Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $followupData['thisMonth'] }}</h4>
                            </div>
                            <span class="avatar-title bg-soft-warning text-warning rounded avatar-sm"><i class="las la-calendar-alt fs-24"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Institution Overview --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card inst-overview-card mb-0">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="las la-university text-primary fs-18"></i>
                        <h5 class="card-title mb-0 fw-semibold">Institution Overview</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex flex-wrap">
                            <div class="inst-half">
                                <div class="inst-half-header">
                                    <div class="inst-half-icon bg-soft-primary"><i class="las la-school text-primary"></i></div>
                                    <div class="inst-half-meta">
                                        <div class="inst-label">School Leads</div>
                                        <div class="inst-count text-primary">{{ $schoolLeads }}</div>
                                        <div class="inst-sub">{{ $otherInstLeads }} from other institutions</div>
                                    </div>
                                    <a href="{{ route('edu-leads.index', ['institution_type' => 'school']) }}" class="btn btn-sm btn-outline-primary ms-auto align-self-start">View</a>
                                </div>
                                @if(!empty($schoolDepts))
                                <div class="dept-list">
                                    @foreach($schoolDepts as $stream => $count)
                                    <div class="dept-row">
                                        <span class="dept-row-label"><i class="las la-angle-right me-1 text-muted" style="font-size:.7rem;"></i>{{ $stream }}</span>
                                        <span class="dept-row-count dept-count-school">{{ $count }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <div class="inst-half">
                                <div class="inst-half-header">
                                    <div class="inst-half-icon bg-soft-purple"><i class="las la-graduation-cap text-purple"></i></div>
                                    <div class="inst-half-meta">
                                        <div class="inst-label">College Leads</div>
                                        <div class="inst-count text-purple">{{ $collegeLeads }}</div>
                                        <div class="inst-sub">Across all departments</div>
                                    </div>
                                    <a href="{{ route('edu-leads.index', ['institution_type' => 'college']) }}" class="btn btn-sm btn-outline-primary ms-auto align-self-start">View</a>
                                </div>
                                @if(!empty($collegeDepts))
                                <div class="dept-list">
                                    @foreach($collegeDepts as $dept => $count)
                                    <div class="dept-row">
                                        <span class="dept-row-label"><i class="las la-angle-right me-1 text-muted" style="font-size:.7rem;"></i>{{ $dept }}</span>
                                        <span class="dept-row-count dept-count-college">{{ $count }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Pipeline Breakdown --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="las la-chart-bar me-2 text-primary"></i>Branch Pipeline Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach([
                                ['label' => 'Contacted',      'value' => $myContacted,                'color' => 'info'],
                                ['label' => 'Not Interested', 'value' => $teamStats['not_interested'], 'color' => 'danger'],
                                ['label' => 'Dropped',        'value' => $myDropped,                  'color' => 'secondary'],
                                ['label' => 'Cold Leads',     'value' => $myCold,                     'color' => 'primary'],
                            ] as $stat)
                            <div class="col-xl-3 col-md-6">
                                <div class="d-flex justify-content-between align-items-center border rounded p-3">
                                    <div>
                                        <p class="text-muted mb-1 fs-12 fw-medium text-uppercase">{{ $stat['label'] }}</p>
                                        <h5 class="mb-0 fw-semibold">{{ $stat['value'] }}</h5>
                                    </div>
                                    <span class="badge bg-{{ $stat['color'] }} rounded-circle p-2 fs-14">
                                        {{ round($myLeads > 0 ? ($stat['value'] / $myLeads * 100) : 0, 1) }}%
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif


    {{-- ════════════════════════════════════════════════════════════════
         FOLLOWUPS SECTION — 4 buckets: Overdue | Today | This Week | This Month
    ════════════════════════════════════════════════════════════════ --}}
    @if(isset($followupData))

        {{-- ── Overdue ──────────────────────────────────────────────── --}}
        @if(isset($followupData['overdueFollowups']) && $followupData['overdueFollowups']->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="section-header overdue" onclick="toggleSection('overdueSection', this)">
                        <h5><i class="las la-exclamation-circle"></i> Overdue Followups</h5>
                        <div class="section-header-right">
                            <span class="count-badge">{{ $followupData['overdueFollowups']->count() }}</span>
                            <span class="toggle-icon">›</span>
                        </div>
                    </div>
                    <div id="overdueSection" class="followup-collapse-container p-3">
                        <div class="d-flex flex-column gap-3">
                            @foreach($followupData['overdueFollowups'] as $followup)
                                @include('partials.followup-card', ['followup' => $followup])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Today's Followups ────────────────────────────────────── --}}
        @if(isset($followupData['todayFollowups']) && $followupData['todayFollowups']->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="section-header today" onclick="toggleSection('todaySection', this)">
                        <h5><i class="las la-calendar-day"></i> Today's Followups</h5>
                        <div class="section-header-right">
                            <span class="count-badge">{{ $followupData['todayFollowups']->count() }}</span>
                            <span class="toggle-icon">›</span>
                        </div>
                    </div>
                    <div id="todaySection" class="followup-collapse-container p-3">
                        <div class="d-flex flex-column gap-3">
                            @foreach($followupData['todayFollowups'] as $followup)
                                @include('partials.followup-card', ['followup' => $followup])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── This Week ────────────────────────────────────────────── --}}
        @if(isset($followupData['thisWeekFollowups']) && $followupData['thisWeekFollowups']->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="section-header week" onclick="toggleSection('weekSection', this)">
                        <h5><i class="las la-calendar-week"></i> This Week's Followups</h5>
                        <div class="section-header-right">
                            <span class="count-badge">{{ $followupData['thisWeekFollowups']->count() }}</span>
                            <span class="toggle-icon collapsed">›</span>
                        </div>
                    </div>
                    <div id="weekSection" class="followup-collapse-container p-3" style="display:none;">
                        <div class="d-flex flex-column gap-3">
                            @foreach($followupData['thisWeekFollowups'] as $followup)
                                @include('partials.followup-card', ['followup' => $followup])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── This Month ───────────────────────────────────────────── --}}
        @if(isset($followupData['thisMonthFollowups']) && $followupData['thisMonthFollowups']->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="section-header month" onclick="toggleSection('monthSection', this)">
                        <h5><i class="las la-calendar-alt"></i> This Month's Followups</h5>
                        <div class="section-header-right">
                            <span class="count-badge">{{ $followupData['thisMonthFollowups']->count() }}</span>
                            <span class="toggle-icon collapsed">›</span>
                        </div>
                    </div>
                    <div id="monthSection" class="followup-collapse-container p-3" style="display:none;">
                        <div class="d-flex flex-column gap-3">
                            @foreach($followupData['thisMonthFollowups'] as $followup)
                                @include('partials.followup-card', ['followup' => $followup])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── All-clear empty state ────────────────────────────────── --}}
        @php
            $hasAnyFollowup =
                (isset($followupData['overdueFollowups'])    && $followupData['overdueFollowups']->count() > 0)    ||
                (isset($followupData['todayFollowups'])      && $followupData['todayFollowups']->count() > 0)      ||
                (isset($followupData['thisWeekFollowups'])   && $followupData['thisWeekFollowups']->count() > 0)   ||
                (isset($followupData['thisMonthFollowups'])  && $followupData['thisMonthFollowups']->count() > 0);
        @endphp
        @if(!$hasAnyFollowup)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="las la-calendar-check" style="font-size:3rem;opacity:.2;display:block;margin-bottom:8px;"></i>
                        <h5 class="text-muted">No Pending Followups</h5>
                        <p class="text-muted mb-0">You're all caught up! No pending followups scheduled.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @endif

</div>
@endsection

@section('extra-scripts')
<script>
// ── SECTION TOGGLE ──────────────────────────────────────────────────
function toggleSection(sectionId, header) {
    const section = document.getElementById(sectionId);
    const icon    = header.querySelector('.toggle-icon');
    if (!section) return;
    if (section.style.display === 'none') {
        section.style.display = 'block';
        icon.classList.remove('collapsed');
    } else {
        section.style.display = 'none';
        icon.classList.add('collapsed');
    }
}

// ── COMPLETE FOLLOWUP ───────────────────────────────────────────────
$(document).on('click', '.btn-complete-followup', function () {
    const btn        = $(this);
    const followupId = btn.data('id');
    Swal.fire({
        title: 'Mark as Complete?', text: 'This followup will be marked as completed.',
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#10b981', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Complete It',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: '/edu-lead-followups/' + followupId + '/complete',
            method: 'POST', data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    btn.closest('.followup-item').fadeOut(300, function () { $(this).remove(); });
                    Swal.fire({ icon: 'success', title: 'Completed!', timer: 1500, showConfirmButton: false });
                }
            },
            error: function () { Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to complete followup.' }); }
        });
    });
});

// ── QUICK SEARCH ────────────────────────────────────────────────────
let searchTimeout;
$('#quickSearch').on('input', function () {
    clearTimeout(searchTimeout);
    const query = $(this).val().trim();
    if (query.length < 2) { $('#searchResults').hide(); return; }
    searchTimeout = setTimeout(function () {
        $('#searchResults').show();
        $('.search-loading').show();
        $('.search-content').empty();
        $.ajax({
            url: '{{ route("edu-leads.quick-search") }}', method: 'GET', data: { query },
            success: function (res) {
                $('.search-loading').hide();
                if (res.leads && res.leads.length > 0) {
                    let html = '<div class="search-result-header">Edu Leads</div>';
                    res.leads.forEach(lead => {
                        html += `<div class="search-result-item" onclick="window.location='${lead.url}'">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="search-result-title">${lead.name}</div>
                                    <small class="text-muted"><i class="las la-phone me-1"></i>${lead.phone ?? '—'} &nbsp;|&nbsp; <i class="las la-book me-1"></i>${lead.course ?? '—'}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-secondary">${lead.lead_code}</span><br>
                                    <small class="text-muted">${lead.assigned_to ?? 'Unassigned'}</small>
                                </div>
                            </div>
                        </div>`;
                    });
                    $('.search-content').html(html);
                } else {
                    $('.search-content').html(`<div class="search-no-results"><i class="las la-search"></i> No leads found for "<strong>${query}</strong>"</div>`);
                }
            },
            error: function () {
                $('.search-loading').hide();
                $('.search-content').html('<div class="search-no-results text-danger">Search failed. Try again.</div>');
            }
        });
    }, 350);
});
$(document).on('click', function (e) {
    if (!$(e.target).closest('#quickSearch, #searchResults').length) $('#searchResults').hide();
});
</script>
@endsection
