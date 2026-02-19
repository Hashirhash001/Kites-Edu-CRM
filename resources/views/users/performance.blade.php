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

    /* ── Glass card ──────────────────────────────────── */
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

    /* ── Stat card ───────────────────────────────────── */
    .stat-card {
        background: white; border-radius: 16px;
        box-shadow: 0 4px 20px rgba(99,102,241,.08);
        border: 1px solid rgba(99,102,241,.05);
        transition: all .3s ease; color: #1e293b;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,.12); }
    .stat-card:hover .stat-icon { transform: scale(1.15) rotate(5deg); }
    .stat-icon { transition: transform .3s ease; }

    /* ── Rank badges ─────────────────────────────────── */
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

    /* ── Winner card animation ───────────────────────── */
    .winner-card { animation: slideInScale .6s cubic-bezier(.34,1.56,.64,1) both; }
    .winner-card:nth-child(1) { animation-delay: .1s; }
    .winner-card:nth-child(2) { animation-delay: .2s; }
    .winner-card:nth-child(3) { animation-delay: .3s; }
    @keyframes slideInScale {
        from { opacity: 0; transform: translateY(30px) scale(.9); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ── Progress ────────────────────────────────────── */
    .progress-thin { height: 8px; background: rgba(0,0,0,.05); border-radius: 10px; overflow: hidden; }
    .progress-bar-gradient {
        background: linear-gradient(90deg,#667eea,#764ba2);
        transition: width 1s ease-in-out;
    }

    /* ── Table ───────────────────────────────────────── */
    .leaderboard-table { border-collapse: separate; border-spacing: 0; width: 100%; }
    .leaderboard-table thead th {
        background: rgba(99,102,241,.08); color: #1e293b;
        font-weight: 700; text-transform: uppercase;
        font-size: .75rem; letter-spacing: .5px;
        padding: 15px; border: none;
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
        padding: 14px 15px; vertical-align: middle;
        border: none; border-top: 6px solid transparent;
    }
    .leaderboard-table tbody tr:first-child td { border-top: none; }

    /* ── Misc ────────────────────────────────────────── */
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
                    <div class="col-md-3" id="startDateDiv" style="display:none;">
                        <label class="form-label fw-bold">📅 Start Date</label>
                        <input type="date" class="form-control shadow-sm" id="startDate" style="border-radius:10px;">
                    </div>
                    <div class="col-md-3" id="endDateDiv" style="display:none;">
                        <label class="form-label fw-bold">📅 End Date</label>
                        <input type="date" class="form-control shadow-sm" id="endDate" style="border-radius:10px;">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100 shadow" id="applyFilter"
                                style="border-radius:10px; font-weight:bold;">
                            <i class="las la-filter me-1"></i> Apply Filter
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
            <p class="text-muted">Celebrating excellence in education lead management</p>
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
                <h5 class="card-title mb-0 fw-bold">
                    <i class="las la-list me-2"></i>
                    Full Rankings
                    <span class="badge bg-primary ms-2" id="userCount">0</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 leaderboard-table">
                        <thead>
                            <tr>
                                <th width="70">Rank</th>
                                <th>User</th>
                                <th>Role</th>
                                <th class="text-center">Assigned</th>
                                <th class="text-center">🔥 Hot</th>
                                <th class="text-center">📞 Contacted</th>
                                <th class="text-center">🔔 Follow Up</th>
                                <th class="text-center">✅ Admitted</th>
                                <th class="text-center">Admission Rate</th>
                                <th class="text-center">Calls Logged</th>
                                <th class="text-center">Overdue F/ups</th>
                            </tr>
                        </thead>
                        <tbody id="leaderboardTableBody">
                            <tr>
                                <td colspan="11" class="text-center py-5">
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

    // ── Confetti ──────────────────────────────────────────────────
    function launchConfetti() {
        const colors = ['#667eea','#764ba2','#10b981','#f59e0b','#ef4444','#06b6d4','#f97316'];
        const container = $('#confettiContainer');
        for (let i = 0; i < 80; i++) {
            const el = $('<div class="confetti-piece"></div>');
            const dur = 2 + Math.random() * 2;
            el.css({
                left: Math.random() * 100 + '%',
                background: colors[Math.floor(Math.random() * colors.length)],
                animationDuration: dur + 's',
                animationDelay: Math.random() * .5 + 's',
                borderRadius: Math.random() > .5 ? '50%' : '2px',
            });
            container.append(el);
            setTimeout(() => el.remove(), dur * 1200);
        }
    }

    // ── Period toggle ─────────────────────────────────────────────
    $('#periodSelect').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#startDateDiv, #endDateDiv').slideDown(300);
        } else {
            $('#startDateDiv, #endDateDiv').slideUp(300);
        }
    });

    // ── Load data ─────────────────────────────────────────────────
    function loadPerformanceData() {
        const data = {
            period:     $('#periodSelect').val(),
            start_date: $('#startDate').val(),
            end_date:   $('#endDate').val(),
        };

        $('#leaderboardTableBody').html(`
            <tr><td colspan="11" class="text-center py-5">
                <div class="spinner-professional mx-auto"></div>
                <p class="text-muted mt-2">Loading rankings...</p>
            </td></tr>`);

        $('#topPerformersContainer').html(`
            <div class="col-12 text-center py-4">
                <div class="spinner-professional mx-auto"></div>
                <p class="text-muted mt-3">Loading champions...</p>
            </div>`);

        $.ajax({
            url:  '{{ route("users.performance.data") }}',
            type: 'GET',
            data: data,
            success: function (res) {
                if (res.success) {
                    renderSummaryCards(res.summary);
                    renderTopPerformers(res.leaderboard.slice(0, 3));
                    renderTable(res.leaderboard);
                    launchConfetti();
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Oops…', text: 'Failed to load performance data.',
                            confirmButtonColor: '#667eea' });
            }
        });
    }

    // ── Summary cards ─────────────────────────────────────────────
    function renderSummaryCards(s) {
        $('#summaryCards').html(`
            <div class="col-md-3 fade-in-up">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Total Assigned</p>
                                <h2 class="mb-0 fw-bold">${s.total_assigned}</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-clipboard-list text-primary" style="font-size:2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay:.1s;">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Total Admitted</p>
                                <h2 class="mb-0 fw-bold text-success">${s.total_admitted}</h2>
                                <small class="text-success fw-bold">${s.avg_admission_rate}% avg rate</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-check-circle text-success" style="font-size:2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay:.2s;">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">🔥 Hot Leads</p>
                                <h2 class="mb-0 fw-bold text-danger">${s.total_hot}</h2>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-fire text-danger" style="font-size:2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 fade-in-up" style="animation-delay:.3s;">
                <div class="card stat-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">Calls Logged</p>
                                <h2 class="mb-0 fw-bold text-info">${s.total_calls}</h2>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle stat-icon">
                                <i class="las la-phone text-info" style="font-size:2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // ── Top 3 podium ──────────────────────────────────────────────
    function renderTopPerformers(top) {
        if (!top.length) {
            $('#topPerformersContainer').html(`
                <div class="col-12 text-center py-5">
                    <div class="glass-card p-5 mx-auto" style="max-width:400px;">
                        <i class="las la-trophy" style="font-size:4rem;color:#ccc;"></i>
                        <p class="text-muted mt-3 mb-0">No performance data yet</p>
                    </div>
                </div>`);
            return;
        }

        const medals = ['🥇','🥈','🥉'];
        let html = '';
        top.forEach(u => {
            const admRate = u.admission_rate ?? 0;
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
                        <p class="text-muted small mb-3">
                            <span class="badge bg-light text-dark">${formatRole(u.role)}</span>
                        </p>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(13,110,253,.08);">
                                    <h6 class="mb-0 fw-bold text-primary">${u.assigned}</h6>
                                    <small class="text-muted" style="font-size:.68rem;">Assigned</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(239,68,68,.08);">
                                    <h6 class="mb-0 fw-bold text-danger">${u.hot_leads}</h6>
                                    <small class="text-muted" style="font-size:.68rem;">🔥 Hot</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(16,185,129,.08);">
                                    <h6 class="mb-0 fw-bold text-success">${u.admitted}</h6>
                                    <small class="text-muted" style="font-size:.68rem;">✅ Admitted</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Admission Rate</small>
                                <span class="badge bg-success">${admRate}%</span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar progress-bar-gradient"
                                     style="width:${Math.min(admRate, 100)}%"
                                     role="progressbar"></div>
                            </div>
                        </div>

                        <div class="value-highlight">
                            <div class="row g-2">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">Calls</small>
                                    <h5 class="mb-0 fw-bold text-info">${u.calls_logged}</h5>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Follow-ups</small>
                                    <h5 class="mb-0 fw-bold text-warning">${u.followups_pending}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        $('#topPerformersContainer').html(html);
    }

    // ── Full table ────────────────────────────────────────────────
    function renderTable(board) {
        $('#userCount').text(board.length);

        if (!board.length) {
            $('#leaderboardTableBody').html(`
                <tr><td colspan="11" class="text-center py-5 text-muted">
                    <i class="las la-inbox" style="font-size:3rem;"></i>
                    <p class="mt-2">No data available for this period</p>
                </td></tr>`);
            return;
        }

        let html = '';
        board.forEach(u => {
            const admRate   = u.admission_rate ?? 0;
            const rateBadge = admRate >= 50 ? 'bg-success' : (admRate >= 25 ? 'bg-warning text-dark' : 'bg-danger');
            const overdueBadge = u.overdue_followups > 0 ? 'bg-danger' : 'bg-success';

            html += `
            <tr>
                <td>
                    <div class="rank-badge rank-${u.rank <= 3 ? u.rank : 'other'}"
                         style="width:42px;height:42px;font-size:.95rem;">
                        ${u.rank}
                    </div>
                </td>
                <td>
                    <a href="{{ url('/users') }}/${u.id}" class="text-decoration-none fw-bold">
                        ${u.name}
                    </a>
                    <br><small class="text-muted">${u.email}</small>
                </td>
                <td><span class="badge bg-secondary">${formatRole(u.role)}</span></td>
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
                    <span class="badge ${rateBadge}">${admRate}%</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-info">${u.calls_logged}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${overdueBadge}">${u.overdue_followups}</span>
                </td>
            </tr>`;
        });
        $('#leaderboardTableBody').html(html);
    }

    // ── Helpers ───────────────────────────────────────────────────
    function formatRole(role) {
        return (role || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // ── Events ───────────────────────────────────────────────────
    $('#applyFilter').on('click', loadPerformanceData);
    loadPerformanceData();   // initial load
});
</script>
@endsection
