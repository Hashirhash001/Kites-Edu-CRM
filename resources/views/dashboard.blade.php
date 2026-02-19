@extends('layouts.app')

@section('title', 'Dashboard')

@section('extra-css')
<style>
    :root {
        --primary-blue: #2563eb;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --info-cyan: #06b6d4;
        --purple: #8b5cf6;
        --card-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .stat-card {
        height: 100%;
        min-height: 120px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .stat-card .card-body {
        display: flex;
        align-items: center;
        height: 100%;
        padding: 1.25rem;
    }

    .card {
        margin-bottom: 1.5rem;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
    }

    /* Section Headers */
    .section-header {
        color: white;
        padding: 1.25rem 1.75rem;
        border-radius: 12px;
        margin-bottom: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 15px rgba(102,126,234,0.3);
    }

    .section-header::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: left 0.5s ease;
    }

    .section-header:hover::before { left: 100%; }
    .section-header:hover { transform: translateY(-2px); }

    .section-header.urgent {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        box-shadow: 0 4px 15px rgba(220,38,38,0.3);
    }

    .section-header.week {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        box-shadow: 0 4px 15px rgba(14,165,233,0.3);
    }

    .section-header.month {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 4px 15px rgba(5,150,105,0.3);
    }

    .section-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-header-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .toggle-icon {
        transition: transform 0.3s ease;
        font-size: 1.4rem;
        font-weight: bold;
    }

    .toggle-icon.collapsed { transform: rotate(-90deg); }

    .followup-collapse-container {
        margin-top: 1.5rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Followup Items */
    .followup-item {
        padding: 1.25rem;
        margin-bottom: 0;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        background: #fff;
    }

    .followup-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    .followup-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .followup-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 1.05rem;
    }

    .followup-title a {
        color: #1e293b;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .followup-title a:hover { color: #667eea; }

    .followup-meta {
        font-size: 0.875rem;
        color: #64748b;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .followup-meta i { margin-right: 0.25rem; color: #94a3b8; }

    .followup-notes {
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 8px;
        border-left: 3px solid #64748b;
    }

    /* Priority Badges */
    .priority-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.85rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        border: 2px solid;
    }

    .priority-badge.high {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-color: #fca5a5; color: white;
        box-shadow: 0 2px 6px rgba(220,38,38,0.4);
    }

    .priority-badge.medium {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-color: #fcd34d; color: #fff;
        box-shadow: 0 2px 6px rgba(245,158,11,0.4);
    }

    .priority-badge.low {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #6ee7b7; color: white;
        box-shadow: 0 2px 6px rgba(16,185,129,0.4);
    }

    /* Date Badge */
    .time-preference-badge {
        display: inline-flex;
        align-items: center;
        background: #fefce8;
        color: #713f12;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        border: 1px solid #fde047;
    }

    .time-preference-badge i { font-size: 1rem; margin-right: 0.5rem; color: #ca8a04; }

    /* Overdue badge */
    .overdue-badge {
        background: #fee2e2; color: #991b1b;
        padding: 0.25rem 0.65rem;
        border-radius: 6px; font-weight: 600;
        font-size: 0.75rem; border: 1px solid #fca5a5;
    }

    /* Complete button */
    .btn-complete {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none; color: white; font-weight: 600;
        padding: 0.5rem 1rem; border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(16,185,129,0.3);
    }

    .btn-complete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16,185,129,0.4);
        color: white;
    }

    .count-badge {
        background: rgba(255,255,255,0.25); color: white;
        padding: 0.35rem 0.75rem; border-radius: 20px;
        font-weight: 700; font-size: 0.85rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center; padding: 3rem 1rem; color: #94a3b8;
    }

    .empty-state i { font-size: 3.5rem; opacity: 0.2; margin-bottom: 1rem; display: block; }

    /* Stat card link */
    a.text-decoration-none .stat-card { cursor: pointer; }
    a.text-decoration-none .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    /* Quick search */
    .search-results-dropdown {
        position: absolute; top: 100%; left: 0; right: 0;
        background: white; border: 1px solid #dee2e6;
        border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        max-height: 400px; overflow-y: auto; z-index: 1050; margin-top: 5px;
    }

    .search-result-item {
        padding: 12px 15px; border-bottom: 1px solid #f0f0f0;
        cursor: pointer; transition: background 0.2s;
    }

    .search-result-item:hover { background-color: #f8f9fa; }
    .search-result-item:last-child { border-bottom: none; }

    .search-result-header {
        background: #f8f9fa; padding: 8px 15px;
        font-weight: 600; font-size: 12px;
        text-transform: uppercase; color: #6c757d;
        border-top: 1px solid #dee2e6;
    }

    .search-no-results { padding: 20px; text-align: center; color: #6c757d; }

    /* Interest level */
    .interest-hot  { background: #fee2e2; color: #991b1b; }
    .interest-warm { background: #fef3c7; color: #92400e; }
    .interest-cold { background: #dbeafe; color: #1e3a8a; }

    @media (max-width: 768px) {
        .stat-card { min-height: 100px; }
        .section-header { flex-direction: row; }
        .followup-meta { flex-direction: column; gap: 0.5rem; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Page Title --}}
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <div>
                    <h4 class="page-title mb-1">Dashboard</h4>
                    <span class="badge bg-{{ auth()->user()->role === 'super_admin' ? 'primary' : (auth()->user()->role === 'lead_manager' ? 'success' : 'warning') }} me-2">
                        {{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}
                    </span>
                </div>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">EduCRM</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Quick Search (super_admin, lead_manager, telecallers) --}}
    @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager', 'telecallers']))
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
                                <input type="text"
                                       class="form-control"
                                       id="telecallerQuickSearch"
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

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  SUPER ADMIN DASHBOARD                                          --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if(auth()->user()->role === 'super_admin')

        {{-- Quick Stats --}}
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
                                    <small class="text-warning">Needs action</small>
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
                                    <small class="text-danger">High intent</small>
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
                                <small class="text-muted">{{ $followupData['today'] }} today</small>
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

        {{-- Admitted This Week / Month Cards --}}
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Week</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisWeek }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info text-info rounded">
                                    <i class="las la-calendar-week fs-24"></i>
                                </span>
                            </div>
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
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-purple text-purple rounded">
                                    <i class="las la-graduation-cap fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Week's Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $followupData['thisWeek'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-calendar fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Month's Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $followupData['thisMonth'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-success text-success rounded">
                                    <i class="las la-calendar-alt fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  LEAD MANAGER DASHBOARD                                         --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if(auth()->user()->role === 'lead_manager')

        <div class="row mb-3">
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index') }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">My Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $myLeads }}</h4>
                                    <small class="text-muted">Created by me</small>
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
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $myPending }}</h4>
                                <small class="text-warning">Needs action</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-clock fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $myAdmitted }}</h4>
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
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Hot Leads</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $myHot }}</h4>
                                <small class="text-danger">High intent</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-danger text-danger rounded">
                                    <i class="las la-fire fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted This Week</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $admittedThisWeek }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info text-info rounded">
                                    <i class="las la-calendar-week fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Overdue Followups</p>
                                <h4 class="mt-0 mb-0 fw-semibold text-danger">{{ $followupData['overdue'] }}</h4>
                                <small class="text-muted">{{ $followupData['today'] }} today</small>
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

    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  TELECALLER DASHBOARD                                           --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if(auth()->user()->role === 'telecallers')

        <div class="row mb-3">
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('edu-leads.index') }}" class="text-decoration-none">
                    <div class="card stat-card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Assigned Leads</p>
                                    <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['total'] }}</h4>
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
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Pending</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['pending'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                    <i class="las la-clock fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Contacted</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['contacted'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info text-info rounded">
                                    <i class="las la-phone fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Follow Up</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['follow_up'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-purple text-purple rounded">
                                    <i class="las la-redo fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Admitted</p>
                                <h4 class="mt-0 mb-0 fw-semibold text-success">{{ $telecallerStats['admitted'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-success text-success rounded">
                                    <i class="las la-check-circle fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="card stat-card mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Not Interested</p>
                                <h4 class="mt-0 mb-0 fw-semibold">{{ $telecallerStats['not_interested'] }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-danger text-danger rounded">
                                    <i class="las la-times-circle fs-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  FOLLOWUPS SECTION (ALL ROLES)                                  --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if(isset($followupData))

        {{-- ── IMMEDIATE (Today + Overdue) ──────────────────────────── --}}
        @if($followupData['immediate']->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="section-header urgent" onclick="toggleSection('immediateSection', this)">
                        <h5>
                            <i class="las la-exclamation-circle"></i>
                            Immediate Followups
                        </h5>
                        <div class="section-header-right">
                            <span class="count-badge">{{ $followupData['immediate']->count() }}</span>
                            <span class="toggle-icon">›</span>
                        </div>
                    </div>
                    <div id="immediateSection" class="followup-collapse-container p-3">
                        <div class="d-flex flex-column gap-3">
                            @foreach($followupData['immediate'] as $followup)
                                @include('partials.followup-card', ['followup' => $followup])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── THIS WEEK ─────────────────────────────────────────────── --}}
        @if($followupData['thisWeekFollowups']->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="section-header week" onclick="toggleSection('weekSection', this)">
                        <h5>
                            <i class="las la-calendar-week"></i>
                            This Week's Followups
                        </h5>
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

        {{-- ── THIS MONTH ────────────────────────────────────────────── --}}
        @if($followupData['thisMonthFollowups']->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="section-header month" onclick="toggleSection('monthSection', this)">
                        <h5>
                            <i class="las la-calendar-alt"></i>
                            This Month's Followups
                        </h5>
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

        {{-- Empty state --}}
        @if($followupData['immediate']->count() === 0 && $followupData['thisWeekFollowups']->count() === 0 && $followupData['thisMonthFollowups']->count() === 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="las la-calendar-check"></i>
                            <h5 class="text-muted">No Pending Followups</h5>
                            <p class="text-muted mb-0">You\'re all caught up! No pending followups scheduled.</p>
                        </div>
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
// ── SECTION TOGGLE ─────────────────────────────────────────────────
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

// ── COMPLETE FOLLOWUP ──────────────────────────────────────────────
$(document).on('click', '.btn-complete-followup', function () {
    const btn        = $(this);
    const followupId = btn.data('id');

    Swal.fire({
        title: 'Mark as Complete?',
        text: 'This followup will be marked as completed.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor:  '#6c757d',
        confirmButtonText:  'Yes, Complete It'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url:    '/edu-lead-followups/' + followupId + '/complete',
                method: 'POST',
                data:   { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        btn.closest('.followup-item').fadeOut(300, function () { $(this).remove(); });
                        Swal.fire({ icon: 'success', title: 'Completed!', timer: 1500, showConfirmButton: false });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to complete followup.' });
                }
            });
        }
    });
});

// ── QUICK SEARCH ───────────────────────────────────────────────────
let searchTimeout;

// ✅ Fixed: ID matches the blade input  →  telecallerQuickSearch
$('#telecallerQuickSearch').on('input', function () {
    clearTimeout(searchTimeout);
    const query = $(this).val().trim();

    if (query.length < 2) {
        $('#searchResults').hide();
        return;
    }

    searchTimeout = setTimeout(function () {
        $('#searchResults').show();
        $('.search-loading').show();
        $('.search-content').empty();

        $.ajax({
            // ✅ Fixed: dedicated JSON endpoint, not the resource index
            url:    '{{ route("edu-leads.quick-search") }}',
            method: 'GET',
            data:   { query: query },
            success: function (res) {
                $('.search-loading').hide();

                if (res.leads && res.leads.length > 0) {
                    let html = '<div class="search-result-header">Edu Leads</div>';
                    res.leads.forEach(function (lead) {
                        html += `
                            <div class="search-result-item" onclick="window.location='${lead.url}'">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="search-result-title">${lead.name}</div>
                                        <small class="text-muted">
                                            <i class="las la-phone me-1"></i>${lead.phone ?? '—'}
                                            &nbsp;|&nbsp;
                                            <i class="las la-book me-1"></i>${lead.course}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary">${lead.lead_code}</span><br>
                                        <small class="text-muted">${lead.assigned_to}</small>
                                    </div>
                                </div>
                            </div>`;
                    });
                    $('.search-content').html(html);
                } else {
                    $('.search-content').html(
                        '<div class="search-no-results"><i class="las la-search"></i> No leads found for "<strong>' + query + '</strong>"</div>'
                    );
                }
            },
            error: function () {
                $('.search-loading').hide();
                $('.search-content').html('<div class="search-no-results text-danger">Search failed. Try again.</div>');
            }
        });
    }, 350);
});

// Close dropdown on outside click
$(document).on('click', function (e) {
    if (!$(e.target).closest('#telecallerQuickSearch, #searchResults').length) {
        $('#searchResults').hide();
    }
});
</script>
@endsection
