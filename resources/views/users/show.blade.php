@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('extra-css')
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        min-height: 110px;
    }
    .stat-card.clickable { cursor: pointer; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }

    .stat-card.primary   { border-left-color: #2563eb; }
    .stat-card.success   { border-left-color: #10b981; }
    .stat-card.warning   { border-left-color: #f59e0b; }
    .stat-card.danger    { border-left-color: #ef4444; }
    .stat-card.info      { border-left-color: #06b6d4; }
    .stat-card.secondary { border-left-color: #64748b; }
    .stat-card.purple    { border-left-color: #7c3aed; }
    .stat-card.orange    { border-left-color: #f97316; }

    .stat-card.clickable::after {
        content: 'Click to view';
        position: absolute;
        bottom: 7px; right: 10px;
        font-size: 0.68rem;
        color: #94a3b8;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .stat-card.clickable:hover::after { opacity: 1; }

    .stat-card .card-body { padding: 1rem; }
    .metric-value  { font-size: 1.75rem; font-weight: 700; line-height: 1; margin-bottom: .4rem; }
    .metric-label  { font-size: 0.78rem; color: #64748b; font-weight: 500; }

    .section-title {
        font-size: 1rem; font-weight: 700; color: #1e293b;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: .65rem; margin-bottom: 1.25rem; margin-top: 2rem;
    }
    .section-title:first-of-type { margin-top: 0; }

    .user-avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #fff; font-weight: 700;
        margin: 0 auto 1rem;
    }

    /* Off-canvas */
    .offcanvas { width: 90vw !important; max-width: 1100px !important; }
    .offcanvas-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; padding: 1.25rem 1.5rem;
    }
    .offcanvas-header .btn-close { filter: brightness(0) invert(1); }
    .offcanvas-body { padding: 1.5rem; }
    .offcanvas-body .table { font-size: .875rem; }
    .offcanvas-body .table th { background: #f8f9fa; font-weight: 600; border-bottom: 2px solid #dee2e6; }
    .offcanvas-loading { display: flex; align-items: center; justify-content: center; min-height: 300px; }
    .spinner-border-custom { width: 3rem; height: 3rem; color: #667eea; }

    @media (max-width: 768px) {
        .metric-value { font-size: 1.4rem; }
        .offcanvas { width: 100vw !important; }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">User Details</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">

    {{-- ── Left: User Info Card ──────────────────────────────────── --}}
    <div class="col-lg-3 col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="user-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="mb-2">
                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                </p>

                @if($user->is_active)
                    <span class="badge bg-success mb-3">
                        <i class="las la-check-circle"></i> Active
                    </span>
                @else
                    <span class="badge bg-secondary mb-3">
                        <i class="las la-times-circle"></i> Inactive
                    </span>
                @endif

                <div class="mt-2 text-start">
                    <p class="mb-2">
                        <i class="las la-envelope text-primary"></i>
                        <small class="ms-2">{{ $user->email }}</small>
                    </p>
                    <p class="mb-2">
                        <i class="las la-phone text-success"></i>
                        <small class="ms-2">{{ $user->phone ?? 'N/A' }}</small>
                    </p>
                    <p class="mb-0">
                        <i class="las la-calendar text-warning"></i>
                        <small class="ms-2">Joined {{ $user->created_at->format('d M Y') }}</small>
                    </p>
                </div>

                <hr>

                <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="las la-arrow-left me-1"></i> Back to Users
                </a>
            </div>
        </div>
    </div>

    {{-- ── Right: Stats ──────────────────────────────────────────── --}}
    <div class="col-lg-9 col-md-8">

        {{-- ════════════════════════════════════════════════════════
             TELECALLERS / LEAD MANAGERS — ASSIGNED EDU LEADS
        ════════════════════════════════════════════════════════ --}}
        @if(in_array($user->role, ['telecallers', 'lead_manager']))

        <h5 class="section-title">
            <i class="las la-clipboard-list me-2"></i>Assigned Leads Overview
            <small class="text-muted ms-2 fw-normal" style="font-size:.75rem;">(Click cards to view details)</small>
        </h5>

        {{-- Row 1: Volume --}}
        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card primary clickable"
                     onclick="showDetails('{{ $user->id }}', 'assigned_leads', 'All Assigned Leads', {{ $totalAssignedLeads }})">
                    <div class="card-body">
                        <div class="metric-value text-primary">{{ $totalAssignedLeads }}</div>
                        <div class="metric-label">Total Assigned</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card warning clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_pending', 'Pending Leads', {{ $leadsPending }})">
                    <div class="card-body">
                        <div class="metric-value text-warning">{{ $leadsPending }}</div>
                        <div class="metric-label">Pending</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card info clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_contacted', 'Contacted Leads', {{ $leadsContacted }})">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $leadsContacted }}</div>
                        <div class="metric-label">Contacted</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card orange clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_followup', 'Follow Up Leads', {{ $leadsFollowUp }})">
                    <div class="card-body">
                        <div class="metric-value" style="color:#f97316;">{{ $leadsFollowUp }}</div>
                        <div class="metric-label">Follow Up</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Outcomes --}}
        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card success clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_admitted', 'Admitted Leads', {{ $leadsAdmitted }})">
                    <div class="card-body">
                        <div class="metric-value text-success">{{ $leadsAdmitted }}</div>
                        <div class="metric-label">Admitted ✅</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card danger clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_not_interested', 'Not Interested Leads', {{ $leadsNotInterested }})">
                    <div class="card-body">
                        <div class="metric-value text-danger">{{ $leadsNotInterested }}</div>
                        <div class="metric-label">Not Interested</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card secondary clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_dropped', 'Dropped Leads', {{ $leadsDropped }})">
                    <div class="card-body">
                        <div class="metric-value text-secondary">{{ $leadsDropped }}</div>
                        <div class="metric-label">Dropped</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card purple">
                    <div class="card-body">
                        <div class="metric-value" style="color:#7c3aed;">{{ $conversionRate }}%</div>
                        <div class="metric-label">Admission Rate</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Interest levels --}}
        <h5 class="section-title">
            <i class="las la-fire me-2"></i>Interest Level Breakdown
        </h5>
        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <div class="card stat-card danger clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_hot', 'Hot Leads 🔥', {{ $leadsHot }})">
                    <div class="card-body">
                        <div class="metric-value text-danger">{{ $leadsHot }}</div>
                        <div class="metric-label">🔥 Hot</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card warning clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_warm', 'Warm Leads ☀️', {{ $leadsWarm }})">
                    <div class="card-body">
                        <div class="metric-value text-warning">{{ $leadsWarm }}</div>
                        <div class="metric-label">☀️ Warm</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card info clickable"
                     onclick="showDetails('{{ $user->id }}', 'leads_cold', 'Cold Leads ❄️', {{ $leadsCold }})">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $leadsCold }}</div>
                        <div class="metric-label">❄️ Cold</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Follow-up & Call activity --}}
        <h5 class="section-title">
            <i class="las la-phone me-2"></i>Activity Overview
        </h5>
        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <div class="card stat-card info clickable"
                     onclick="showDetails('{{ $user->id }}', 'followups_pending', 'Pending Follow-ups', {{ $followupsPending }})">
                    <div class="card-body">
                        <div class="metric-value text-info">{{ $followupsPending }}</div>
                        <div class="metric-label">Pending Follow-ups</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card danger clickable"
                     onclick="showDetails('{{ $user->id }}', 'followups_overdue', 'Overdue Follow-ups', {{ $followupsOverdue }})">
                    <div class="card-body">
                        <div class="metric-value text-danger">{{ $followupsOverdue }}</div>
                        <div class="metric-label">Overdue Follow-ups</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="metric-value text-success">{{ $totalCallLogs }}</div>
                        <div class="metric-label">Total Calls Logged</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card primary">
                    <div class="card-body">
                        <div class="metric-value text-primary">{{ $callsToday }}</div>
                        <div class="metric-label">Calls Today</div>
                    </div>
                </div>
            </div>
        </div>

        @endif

        {{-- ════════════════════════════════════════════════════════
             SUPER ADMIN — CREATED LEADS
        ════════════════════════════════════════════════════════ --}}
        @if($user->role === 'super_admin')

        <h5 class="section-title">
            <i class="las la-user-plus me-2"></i>Created Leads Overview
            <small class="text-muted ms-2 fw-normal" style="font-size:.75rem;">(Click cards to view details)</small>
        </h5>

        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <div class="card stat-card primary clickable"
                     onclick="showDetails('{{ $user->id }}', 'created_leads', 'All Created Leads', {{ $totalCreatedLeads }})">
                    <div class="card-body">
                        <div class="metric-value text-primary">{{ $totalCreatedLeads }}</div>
                        <div class="metric-label">Total Created</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card success clickable"
                     onclick="showDetails('{{ $user->id }}', 'created_admitted', 'Admitted (Created by User)', {{ $createdLeadsAdmitted }})">
                    <div class="card-body">
                        <div class="metric-value text-success">{{ $createdLeadsAdmitted }}</div>
                        <div class="metric-label">Admitted</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card danger clickable"
                     onclick="showDetails('{{ $user->id }}', 'created_not_interested', 'Not Interested (Created by User)', {{ $createdLeadsNotInterested }})">
                    <div class="card-body">
                        <div class="metric-value text-danger">{{ $createdLeadsNotInterested }}</div>
                        <div class="metric-label">Not Interested</div>
                    </div>
                </div>
            </div>
        </div>

        @endif

        {{-- ════════════════════════════════════════════════════════
             REPORTING USER / OTHER ROLES — PLACEHOLDER
        ════════════════════════════════════════════════════════ --}}
        @if(in_array($user->role, ['reporting_user']) ||
            (!in_array($user->role, ['telecallers', 'lead_manager', 'super_admin'])))
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="las la-user-shield" style="font-size:5rem; color:#cbd5e0;"></i>
                <h5 class="mt-3">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</h5>
                <p class="text-muted">This user has system access but no lead data to display.</p>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── Off-canvas Panel ───────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="detailsOffcanvas"
     data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <i class="las la-list-alt me-2"></i>
            <span id="offcanvasTitleText">Details</span>
            <span class="badge bg-white text-primary ms-2" id="offcanvasCount">0</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="offcanvasContent">
        <div class="offcanvas-loading">
            <div class="spinner-border spinner-border-custom" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
let currentOffcanvas = null;

function showDetails(userId, type, title, count) {
    $('#offcanvasTitleText').text(title);
    $('#offcanvasCount').text(count);

    $('#offcanvasContent').html(`
        <div class="offcanvas-loading">
            <div class="spinner-border spinner-border-custom" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

    if (!currentOffcanvas) {
        currentOffcanvas = new bootstrap.Offcanvas(document.getElementById('detailsOffcanvas'));
    }
    currentOffcanvas.show();

    loadDetails(userId, type);
}

function loadDetails(userId, type, page = 1) {
    $.ajax({
        url:  `/users/${userId}/details/${type}`,
        type: 'GET',
        data: { page: page },
        success: function (response) {
            $('#offcanvasContent').html(response.html);

            // Re-bind pagination inside offcanvas
            $('#offcanvasContent').find('.pagination a').on('click', function (e) {
                e.preventDefault();
                const page = new URL($(this).attr('href')).searchParams.get('page');
                loadDetails(userId, type, page);
            });
        },
        error: function () {
            $('#offcanvasContent').html(`
                <div class="alert alert-danger">
                    <i class="las la-exclamation-triangle me-2"></i>
                    Failed to load data. Please try again.
                </div>
            `);
        }
    });
}
</script>
@endsection
