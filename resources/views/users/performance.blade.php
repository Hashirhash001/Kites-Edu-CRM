@extends('layouts.app')

@section('title', 'Performance Leaderboard')

@section('extra-css')
<style>
    body { background: #f7f9fc; }

    body::before {
        content: '';
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background:
            radial-gradient(circle at 20% 50%, rgba(99,102,241,.08), transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(236,72,153,.08), transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(139,92,246,.08), transparent 50%);
        pointer-events: none; z-index: 0;
    }

    .content-wrapper { position: relative; z-index: 1; }

    /* ── Glass card ───────────────────────────────────── */
    .glass-card {
        background: rgba(255,255,255,.98);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(99,102,241,.1);
        box-shadow: 0 8px 32px rgba(99,102,241,.08);
        border-radius: 20px;
        transition: all .4s cubic-bezier(.4,0,.2,1);
        color: #1e293b;
    }
    .glass-card:hover { transform: translateY(-8px); box-shadow: 0 20px 60px rgba(0,0,0,.15); }

    /* ── Stat card ────────────────────────────────────── */
    .stat-card {
        background: white; border-radius: 16px;
        box-shadow: 0 4px 20px rgba(99,102,241,.08);
        border: 1px solid rgba(99,102,241,.05);
        transition: all .3s ease; color: #1e293b;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,.12); }
    .stat-card:hover .stat-icon { transform: scale(1.15) rotate(5deg); }
    .stat-icon { transition: transform .3s ease; }

    /* ── Rank badges ──────────────────────────────────── */
    .rank-badge {
        width: 65px; height: 65px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 1.5rem;
        position: relative;
        box-shadow: 0 8px 20px rgba(0,0,0,.2);
        transition: all .3s ease;
    }
    .rank-badge:hover { transform: scale(1.1) rotate(5deg); }

    .rank-1 {
        background: linear-gradient(135deg,#FFD700,#FFA500,#FFD700);
        color: #fff; box-shadow: 0 10px 30px rgba(255,215,0,.4);
    }
    .rank-1::after {
        content: '👑'; position: absolute; top: -35px;
        font-size: 2.5rem;
        animation: crownFloat 2s ease-in-out infinite;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,.3));
    }
    .rank-2 {
        background: linear-gradient(135deg,#E8E8E8,#C0C0C0,#E8E8E8);
        color: #555; box-shadow: 0 10px 30px rgba(192,192,192,.4);
    }
    .rank-3 {
        background: linear-gradient(135deg,#E9967A,#CD7F32,#E9967A);
        color: #fff; box-shadow: 0 10px 30px rgba(205,127,50,.4);
    }
    .rank-other {
        background: linear-gradient(135deg,#a8edea,#fed6e3);
        color: #555; box-shadow: 0 5px 15px rgba(0,0,0,.15);
    }

    @keyframes crownFloat {
        0%,100% { transform: translateY(0) rotate(-5deg); }
        50%      { transform: translateY(-8px) rotate(5deg); }
    }

    /* ── Winner card animation ────────────────────────── */
    .winner-card { animation: slideInScale .6s cubic-bezier(.34,1.56,.64,1) both; }
    .winner-card:nth-child(1) { animation-delay: .1s; }
    .winner-card:nth-child(2) { animation-delay: .2s; }
    .winner-card:nth-child(3) { animation-delay: .3s; }
    @keyframes slideInScale {
        from { opacity: 0; transform: translateY(30px) scale(.9); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ── Progress ─────────────────────────────────────── */
    .progress-thin { height: 8px; background: rgba(0,0,0,.05); border-radius: 10px; overflow: hidden; }
    .progress-bar-gradient {
        background: linear-gradient(90deg,#667eea,#764ba2);
        transition: width 1s ease-in-out;
    }
    .progress-bar-green {
        background: linear-gradient(90deg,#10b981,#059669);
        transition: width 1s ease-in-out;
    }

    /* ── Table ────────────────────────────────────────── */
    .leaderboard-table { border-collapse: separate; border-spacing: 0; width: 100%; }
    .leaderboard-table thead th {
        background: rgba(99,102,241,.08); color: #1e293b;
        font-weight: 700; text-transform: uppercase;
        font-size: .72rem; letter-spacing: .5px;
        padding: 14px 12px; border: none;
        white-space: nowrap;
    }
    .leaderboard-table tbody tr {
        background: white; transition: all .3s ease;
        box-shadow: 0 2px 8px rgba(99,102,241,.04);
        color: #1e293b;
    }
    .leaderboard-table tbody tr:hover {
        background: linear-gradient(90deg,rgba(102,126,234,.05),rgba(255,255,255,1));
        box-shadow: 0 8px 20px rgba(102,126,234,.2);
        transform: translateY(-2px);
    }
    .leaderboard-table tbody td {
        padding: 12px; vertical-align: middle;
        border: none; border-top: 6px solid transparent;
    }
    .leaderboard-table tbody tr:first-child td { border-top: none; }

    /* ── Role chips ───────────────────────────────────── */
    .role-chip {
        display: inline-block; font-size: .72rem; font-weight: 700;
        padding: .25em .65em; border-radius: 20px;
    }
    .role-lead_manager   { background: #dcfce7; color: #15803d; }
    .role-telecaller     { background: #dbeafe; color: #1d4ed8; }
    .role-super_admin    { background: #ede9fe; color: #6d28d9; }
    .role-operation_head { background: #cffafe; color: #0e7490; }

    /* ── Call stat chips ──────────────────────────────── */
    .call-chip {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .72rem; font-weight: 700;
        padding: .2em .55em; border-radius: 20px; white-space: nowrap;
    }
    .call-connected     { background: #dcfce7; color: #15803d; }
    .call-not-connected { background: #fee2e2; color: #b91c1c; }
    .call-rate          { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* ── Misc ─────────────────────────────────────────── */
    .badge { font-weight: 600; padding: .4em .8em; transition: all .2s ease; }
    .badge:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }

    .confetti-piece {
        position: fixed; width: 8px; height: 12px; top: -10px;
        z-index: 9999; pointer-events: none;
        animation: confetti-fall linear forwards;
    }
    @keyframes confetti-fall {
        0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }

    .page-title-box {
        background: rgba(255,255,255,.98); border-radius: 16px;
        padding: 24px !important;
        box-shadow: 0 4px 20px rgba(99,102,241,.08);
        backdrop-filter: blur(10px);
    }
    .page-title {
        background: linear-gradient(135deg,#4f46e5,#7c3aed);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; font-weight: 800; margin: 0;
    }

    .filter-card {
        background: rgba(255,255,255,.98); border-radius: 16px;
        box-shadow: 0 4px 20px rgba(99,102,241,.08); border: none; color: #1e293b;
    }

    .spinner-professional {
        width: 50px; height: 50px;
        border: 4px solid rgba(102,126,234,.2);
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .medal-emoji {
        font-size: 3rem; display: inline-block;
        animation: medalPulse 2s ease-in-out infinite;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,.2));
    }
    @keyframes medalPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.1); } }

    .value-highlight {
        background: linear-gradient(135deg,rgba(16,185,129,.08),rgba(6,182,212,.08));
        border-radius: 12px; padding: 14px;
        border: 2px solid rgba(16,185,129,.2);
    }

    .fade-in-up { animation: fadeInUp .6s ease-out; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    h1,h2,h3,h4,h5,h6 { color: #1e293b; }
    .text-muted { color: #64748b !important; }

    #applyFilter {
        background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
        border: none;
    }

    @media (max-width: 768px) {
        .rank-badge { width: 50px; height: 50px; font-size: 1.2rem; }
        .rank-1::after { font-size: 2rem; top: -28px; }
    }
</style>
@endsection


@section('content')
<div class="content-wrapper">
<div id="confettiContainer"></div>

{{-- ── Page Title ──────────────────────────────────────────────── --}}
<div class="row mb-4 mt-4">
    <div class="col-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">
                <i class="las la-trophy me-2" style="font-size:2rem;"></i>
                Performance Leaderboard
            </h4>
            <ol class="breadcrumb mb-0" style="background:transparent;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Performance</li>
            </ol>
        </div>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card filter-card border-0">
            <div class="card-body">
                <div class="row align-items-end g-3">

                    {{-- Period --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">⏱️ Time Period</label>
                        <select class="form-select shadow-sm" id="periodSelect" style="border-radius:10px;">
                            <option value="day">Today</option>
                            <option value="week">This Week</option>
                            <option value="month" selected>This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="6months">Last 6 Months</option>
                            <option value="year">This Year</option>
                            <option value="last_year">Last Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    {{-- Custom dates --}}
                    <div class="col-md-2" id="startDateDiv" style="display:none;">
                        <label class="form-label fw-bold">📅 Start Date</label>
                        <input type="date" class="form-control shadow-sm" id="startDate"
                               style="border-radius:10px;">
                    </div>
                    <div class="col-md-2" id="endDateDiv" style="display:none;">
                        <label class="form-label fw-bold">📅 End Date</label>
                        <input type="date" class="form-control shadow-sm" id="endDate"
                               style="border-radius:10px;">
                    </div>

                    {{-- Role filter — NEW --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold">👤 Role</label>
                        <select class="form-select shadow-sm" id="roleFilter" style="border-radius:10px;">
                            <option value="">All Staff</option>
                            <option value="lead_manager">Lead Managers</option>
                            <option value="telecaller">Telecallers</option>
                        </select>
                    </div>

                    {{-- Branch --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold">🏢 Branch</label>
                        <select class="form-select shadow-sm" id="branchFilter" style="border-radius:10px;">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Apply --}}
                    <div class="col-md-1">
                        <button type="button" class="btn btn-primary w-100 shadow" id="applyFilter"
                                style="border-radius:10px; font-weight:bold;">
                            <i class="las la-filter me-1"></i>Apply
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Summary Cards ───────────────────────────────────────────── --}}
<div class="row mb-4" id="summaryCards">
    {{-- Populated via AJAX --}}
</div>

{{-- ── Top 3 Podium ────────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="text-center mb-3">
            <h3 class="fw-bold">
                <i class="las la-medal me-2" style="font-size:2rem; vertical-align:middle;"></i>
                Top Performers
            </h3>
            <p class="text-muted">Celebrating excellence in lead management</p>
        </div>
    </div>
    <div class="col-12">
        <div class="row justify-content-center" id="topPerformersContainer">
            <div class="col-12 text-center py-5">
                <div class="spinner-professional mx-auto"></div>
                <p class="text-muted mt-3">Loading champions...</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Full Leaderboard Table ───────────────────────────────────── --}}
<div class="row">
    <div class="col-12">
        <div class="card glass-card border-0 shadow-lg">
            <div class="card-header border-0"
                 style="background:rgba(255,255,255,.3); border-radius:16px 16px 0 0;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="las la-list me-2"></i>Full Rankings
                        <span class="badge bg-primary ms-2" id="userCount">0</span>
                    </h5>
                    <small class="text-muted" id="periodLabel"></small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 leaderboard-table">
                        <thead>
                            <tr>
                                <th width="60">Rank</th>
                                <th>User</th>
                                <th>Role</th>
                                <th class="text-center">Assigned</th>
                                <th class="text-center">🔥 Hot</th>
                                <th class="text-center">📞 Contacted</th>
                                <th class="text-center">🔔 Follow Up</th>
                                <th class="text-center">✅ Admitted</th>
                                <th class="text-center">Admission %</th>
                                <th class="text-center">Calls</th>
                                <th class="text-center">Connected</th>
                                <th class="text-center">Not Connected</th>
                                <th class="text-center">Connect %</th>
                                <th class="text-center">Overdue F/ups</th>
                                <th class="text-center">Score</th>
                            </tr>
                        </thead>
                        <tbody id="leaderboardTableBody">
                            <tr>
                                <td colspan="15" class="text-center py-5">
                                    <div class="spinner-professional mx-auto"></div>
                                    <p class="text-muted mt-2">Loading rankings...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /content-wrapper --}}
@endsection


@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── Confetti ───────────────────────────────────────────────────
    function launchConfetti() {
        const colors = ['#667eea','#764ba2','#10b981','#f59e0b','#ef4444','#06b6d4','#f97316'];
        const container = $('#confettiContainer');
        for (let i = 0; i < 80; i++) {
            const el = $('<div class="confetti-piece"></div>');
            const dur = 2 + Math.random() * 2;
            el.css({
                left               : Math.random() * 100 + '%',
                background         : colors[Math.floor(Math.random() * colors.length)],
                animationDuration  : dur + 's',
                animationDelay     : Math.random() * .5 + 's',
                borderRadius       : Math.random() > .5 ? '50%' : '2px',
            });
            container.append(el);
            setTimeout(() => el.remove(), dur * 1200);
        }
    }

    // ── Period toggle ──────────────────────────────────────────────
    $('#periodSelect').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#startDateDiv, #endDateDiv').slideDown(300);
        } else {
            $('#startDateDiv, #endDateDiv').slideUp(300);
        }
    });

    // ── Load performance data ──────────────────────────────────────
    function loadPerformanceData() {
        const data = {
            period     : $('#periodSelect').val(),
            role       : $('#roleFilter').val(),          // NEW
            branch_id  : $('#branchFilter').val(),        // NEW
            start_date : $('#startDate').val(),
            end_date   : $('#endDate').val(),
        };

        const spinnerRow = (cols) => `
            <tr><td colspan="${cols}" class="text-center py-5">
                <div class="spinner-professional mx-auto"></div>
                <p class="text-muted mt-2">Loading...</p>
            </td></tr>`;

        $('#leaderboardTableBody').html(spinnerRow(15));
        $('#topPerformersContainer').html(`
            <div class="col-12 text-center py-4">
                <div class="spinner-professional mx-auto"></div>
                <p class="text-muted mt-3">Loading champions...</p>
            </div>`);

        $.ajax({
            url    : '{{ route("users.performance.data") }}',
            type   : 'GET',
            data   : data,
            success: function (res) {
                if (res.success) {
                    renderSummaryCards(res.summary);
                    renderTopPerformers(res.leaderboard.slice(0, 3));
                    renderTable(res.leaderboard);
                    updatePeriodLabel(res.start_date, res.end_date);
                    if (res.leaderboard.length > 0) launchConfetti();
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error', title: 'Oops…',
                    text: 'Failed to load performance data.',
                    confirmButtonColor: '#667eea',
                });
            }
        });
    }

    // ── Summary cards — now 6 cards incl. connected/not-connected ──
    function renderSummaryCards(s) {
        const connRate = s.avg_connection_rate ?? 0;
        const connRateColor = connRate >= 60 ? 'success' : (connRate >= 40 ? 'warning' : 'danger');

        $('#summaryCards').html(`
            <div class="col-xl-2 col-md-4 col-6 fade-in-up">
                <div class="card stat-card border-0">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold small">Total Assigned</p>
                                <h3 class="mb-0 fw-bold">${s.total_assigned}</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle stat-icon">
                                <i class="las la-clipboard-list text-primary" style="font-size:1.6rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 fade-in-up" style="animation-delay:.05s;">
                <div class="card stat-card border-0">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold small">Total Admitted</p>
                                <h3 class="mb-0 fw-bold text-success">${s.total_admitted}</h3>
                                <small class="text-success fw-bold">${s.avg_admission_rate}% avg</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-2 rounded-circle stat-icon">
                                <i class="las la-check-circle text-success" style="font-size:1.6rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 fade-in-up" style="animation-delay:.1s;">
                <div class="card stat-card border-0">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold small">🔥 Hot Leads</p>
                                <h3 class="mb-0 fw-bold text-danger">${s.total_hot}</h3>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-2 rounded-circle stat-icon">
                                <i class="las la-fire text-danger" style="font-size:1.6rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 fade-in-up" style="animation-delay:.15s;">
                <div class="card stat-card border-0">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold small">Total Calls</p>
                                <h3 class="mb-0 fw-bold text-info">${s.total_calls}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-2 rounded-circle stat-icon">
                                <i class="las la-phone text-info" style="font-size:1.6rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 fade-in-up" style="animation-delay:.2s;">
                <div class="card stat-card border-0">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold small">📞 Connected</p>
                                <h3 class="mb-0 fw-bold text-success">${s.total_connected}</h3>
                                <small class="text-muted">${s.total_not_connected} not connected</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-2 rounded-circle stat-icon">
                                <i class="las la-phone-volume text-success" style="font-size:1.6rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 fade-in-up" style="animation-delay:.25s;">
                <div class="card stat-card border-0">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold small">Connect Rate</p>
                                <h3 class="mb-0 fw-bold text-${connRateColor}">${connRate}%</h3>
                                <div class="progress progress-thin mt-1" style="width:80px;">
                                    <div class="progress-bar-green" style="width:${Math.min(connRate,100)}%;height:100%;border-radius:10px;"></div>
                                </div>
                            </div>
                            <div class="bg-${connRateColor} bg-opacity-10 p-2 rounded-circle stat-icon">
                                <i class="las la-percentage text-${connRateColor}" style="font-size:1.6rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // ── Top 3 podium ───────────────────────────────────────────────
    function renderTopPerformers(top) {
        if (!top.length) {
            $('#topPerformersContainer').html(`
                <div class="col-12 text-center py-5">
                    <div class="glass-card p-5 mx-auto" style="max-width:400px;">
                        <i class="las la-trophy" style="font-size:4rem;color:#ccc;"></i>
                        <p class="text-muted mt-3 mb-0">No performance data yet for this period</p>
                    </div>
                </div>`);
            return;
        }

        const medals = ['🥇','🥈','🥉'];
        let html = '';
        top.forEach(u => {
            const admRate  = u.admission_rate   ?? 0;
            const connRate = u.connection_rate  ?? 0;
            html += `
            <div class="col-lg-4 col-md-6 mb-3 winner-card">
                <div class="card glass-card border-0 h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <span class="medal-emoji">${medals[u.rank - 1]}</span>
                        </div>
                        <div class="rank-badge rank-${u.rank} mx-auto mb-3">
                            <span style="font-weight:900;">#${u.rank}</span>
                        </div>

                        <h5 class="fw-bold mb-1">${u.name}</h5>
                        <p class="text-muted small mb-1">
                            <span class="role-chip role-${u.role}">${formatRole(u.role)}</span>
                        </p>
                        <p class="text-muted small mb-3">${u.branch}</p>

                        {{-- Lead Stats --}}
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(13,110,253,.08);">
                                    <h6 class="mb-0 fw-bold text-primary">${u.assigned}</h6>
                                    <small class="text-muted" style="font-size:.66rem;">Assigned</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(239,68,68,.08);">
                                    <h6 class="mb-0 fw-bold text-danger">${u.hot_leads}</h6>
                                    <small class="text-muted" style="font-size:.66rem;">🔥 Hot</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(16,185,129,.08);">
                                    <h6 class="mb-0 fw-bold text-success">${u.admitted}</h6>
                                    <small class="text-muted" style="font-size:.66rem;">✅ Admitted</small>
                                </div>
                            </div>
                        </div>

                        {{-- Admission rate bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Admission Rate</small>
                                <span class="badge bg-success">${admRate}%</span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar progress-bar-gradient"
                                     style="width:${Math.min(admRate,100)}%"
                                     role="progressbar"></div>
                            </div>
                        </div>

                        {{-- Call stats: connected / not connected --}}
                        <div class="value-highlight">
                            <div class="row g-2 mb-2">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block" style="font-size:.7rem;">📞 Connected</small>
                                    <h5 class="mb-0 fw-bold text-success">${u.calls_connected}</h5>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block" style="font-size:.7rem;">📵 Not Connected</small>
                                    <h5 class="mb-0 fw-bold text-danger">${u.calls_not_connected}</h5>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted" style="font-size:.7rem;">Connection Rate</small>
                                    <small class="fw-bold text-info">${connRate}%</small>
                                </div>
                                <div class="progress progress-thin">
                                    <div class="progress-bar-green"
                                         style="width:${Math.min(connRate,100)}%;height:100%;border-radius:10px;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Score --}}
                        <div class="mt-3">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                ⭐ Score: ${u.score}
                            </span>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        $('#topPerformersContainer').html(html);
    }

    // ── Full leaderboard table ─────────────────────────────────────
    function renderTable(board) {
        $('#userCount').text(board.length);

        if (!board.length) {
            $('#leaderboardTableBody').html(`
                <tr><td colspan="15" class="text-center py-5 text-muted">
                    <i class="las la-inbox" style="font-size:3rem;"></i>
                    <p class="mt-2">No data available for this period / filter</p>
                </td></tr>`);
            return;
        }

        let html = '';
        board.forEach(u => {
            const admRate   = u.admission_rate   ?? 0;
            const connRate  = u.connection_rate  ?? 0;

            const admBadge  = admRate  >= 50 ? 'bg-success'
                            : admRate  >= 25 ? 'bg-warning text-dark'
                            : 'bg-danger';
            const connBadge = connRate >= 60 ? 'bg-success'
                            : connRate >= 40 ? 'bg-warning text-dark'
                            : 'bg-danger';
            const overdueColor = u.overdue_followups > 0 ? 'bg-danger' : 'bg-success';
            const scoreBadge   = u.score >= 20 ? 'bg-success'
                               : u.score >= 10 ? 'bg-warning text-dark'
                               : 'bg-secondary';

            html += `
            <tr>
                <td>
                    <div class="rank-badge rank-${u.rank <= 3 ? u.rank : 'other'}"
                         style="width:40px;height:40px;font-size:.9rem;">
                        ${u.rank}
                    </div>
                </td>
                <td>
                    <a href="{{ url('/users') }}/${u.id}" class="text-decoration-none fw-bold">
                        ${u.name}
                    </a>
                    <br>
                    <small class="text-muted">${u.branch}</small>
                </td>
                <td>
                    <span class="role-chip role-${u.role}">${formatRole(u.role)}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-primary">${u.assigned}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-danger">${u.hot_leads}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-info">${u.contacted}</span>
                </td>
                <td class="text-center">
                    <span class="badge" style="background:#f97316;">${u.follow_up}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-success">${u.admitted}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${admBadge}">${admRate}%</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary">${u.calls_total}</span>
                </td>
                <td class="text-center">
                    <span class="call-chip call-connected">
                        <i class="las la-phone"></i>${u.calls_connected}
                    </span>
                </td>
                <td class="text-center">
                    <span class="call-chip call-not-connected">
                        <i class="las la-phone-slash"></i>${u.calls_not_connected}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge ${connBadge}">${connRate}%</span>
                </td>
                <td class="text-center">
                    <span class="badge ${overdueColor}">${u.overdue_followups}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${scoreBadge} fs-6">${u.score}</span>
                </td>
            </tr>`;
        });
        $('#leaderboardTableBody').html(html);
    }

    // ── Period label ───────────────────────────────────────────────
    function updatePeriodLabel(start, end) {
        $('#periodLabel').text(`${start}  →  ${end}`);
    }

    // ── Role formatter ─────────────────────────────────────────────
    function formatRole(role) {
        return (role || '').replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // ── Events ────────────────────────────────────────────────────
    $('#applyFilter').on('click', loadPerformanceData);
    loadPerformanceData(); // initial load
});
</script>
@endsection
