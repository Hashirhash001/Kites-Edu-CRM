@extends('layouts.app')

@section('title', 'Performance Leaderboard')

@section('extra-css')
<style>
    /* ── Background ───────────────────────────────────────────────── */
    body { background: #f7f9fc; }
    body::before {
        content: '';
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background:
            radial-gradient(circle at 20% 50%, rgba(99,102,241,.06), transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(236,72,153,.06), transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(139,92,246,.06), transparent 50%);
        pointer-events: none; z-index: 0;
    }
    .content-wrapper { position: relative; z-index: 1; }

    /* ── Page title ───────────────────────────────────────────────── */
    .page-title-box {
        background: rgba(255,255,255,.98);
        border-radius: 14px;
        padding: 18px 22px !important;
        box-shadow: 0 4px 20px rgba(99,102,241,.08);
    }
    .page-title {
        background: linear-gradient(135deg,#4f46e5,#7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 800; margin: 0;
    }

    /* ── Filter card ──────────────────────────────────────────────── */
    .filter-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(99,102,241,.07);
        border: 1px solid rgba(99,102,241,.08);
    }
    #applyFilter {
        background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
        border: none; font-weight: 700;
    }

    /* ── Summary stat cards ───────────────────────────────────────── */
    .stat-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(99,102,241,.07);
        border: 1px solid rgba(99,102,241,.06);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 28px rgba(99,102,241,.13);
    }
    .stat-card:hover .stat-icon { transform: scale(1.12) rotate(4deg); }
    .stat-icon { transition: transform .25s ease; flex-shrink: 0; }
    .stat-label { font-size: .78rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
    .stat-sub   { font-size: .72rem; }

    /* ── Rank badges ──────────────────────────────────────────────── */
    .rank-badge {
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; position: relative;
        box-shadow: 0 6px 16px rgba(0,0,0,.18);
        transition: transform .25s ease;
        flex-shrink: 0;
    }
    .rank-badge:hover { transform: scale(1.08) rotate(4deg); }
    .rank-badge-lg { width: 62px; height: 62px; font-size: 1.4rem; }
    .rank-badge-sm { width: 38px; height: 38px; font-size: .85rem; }

    .rank-1 { background: linear-gradient(135deg,#FFD700,#FFA500); color:#fff; box-shadow:0 8px 22px rgba(255,200,0,.4); }
    .rank-2 { background: linear-gradient(135deg,#E0E0E0,#B0B0B0); color:#555; box-shadow:0 8px 22px rgba(180,180,180,.4); }
    .rank-3 { background: linear-gradient(135deg,#E8A87C,#CD7F32); color:#fff; box-shadow:0 8px 22px rgba(200,140,60,.4); }
    .rank-other { background: linear-gradient(135deg,#a8edea,#fed6e3); color:#555; }

    .rank-1::after {
        content: '👑'; position: absolute; top: -30px; font-size: 1.8rem;
        animation: crownFloat 2s ease-in-out infinite;
    }
    @keyframes crownFloat {
        0%,100% { transform: translateY(0) rotate(-5deg); }
        50%      { transform: translateY(-6px) rotate(5deg); }
    }

    /* ── Winner cards ─────────────────────────────────────────────── */
    .winner-card {
        animation: slideInScale .55s cubic-bezier(.34,1.56,.64,1) both;
    }
    .winner-card:nth-child(1) { animation-delay:.08s; }
    .winner-card:nth-child(2) { animation-delay:.16s; }
    .winner-card:nth-child(3) { animation-delay:.24s; }
    @keyframes slideInScale {
        from { opacity: 0; transform: translateY(24px) scale(.92); }
        to   { opacity: 1; transform: translateY(0)    scale(1); }
    }

    .glass-card {
        background: #fff;
        border: 1px solid rgba(99,102,241,.1);
        box-shadow: 0 6px 24px rgba(99,102,241,.08);
        border-radius: 18px;
        transition: transform .3s ease, box-shadow .3s ease;
    }
    .glass-card:hover { transform: translateY(-5px); box-shadow: 0 16px 40px rgba(0,0,0,.12); }

    /* ── Progress bars ────────────────────────────────────────────── */
    .prog-wrap { height: 6px; background: rgba(0,0,0,.07); border-radius: 8px; overflow: hidden; }
    .prog-fill-purple { height: 100%; background: linear-gradient(90deg,#667eea,#764ba2); border-radius: 8px; transition: width 1s ease; }
    .prog-fill-green  { height: 100%; background: linear-gradient(90deg,#10b981,#059669); border-radius: 8px; transition: width 1s ease; }

    /* ── Value highlight box in winner cards ──────────────────────── */
    .value-highlight {
        background: linear-gradient(135deg,rgba(16,185,129,.07),rgba(6,182,212,.07));
        border-radius: 10px; padding: 12px;
        border: 1.5px solid rgba(16,185,129,.18);
    }

    /* ── Medal emoji ──────────────────────────────────────────────── */
    .medal-emoji {
        font-size: 2.4rem; display: inline-block;
        animation: medalPulse 2s ease-in-out infinite;
    }
    @keyframes medalPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }

    /* ── Role chips ───────────────────────────────────────────────── */
    .role-chip          { display:inline-block; font-size:.7rem; font-weight:700; padding:.22em .6em; border-radius:20px; }
    .role-lead_manager  { background:#dcfce7; color:#15803d; }
    .role-telecaller    { background:#dbeafe; color:#1d4ed8; }
    .role-super_admin   { background:#ede9fe; color:#6d28d9; }
    .role-operation_head{ background:#cffafe; color:#0e7490; }

    /* ── Call chips ───────────────────────────────────────────────── */
    .call-chip {
        display:inline-flex; align-items:center; gap:3px;
        font-size:.7rem; font-weight:700;
        padding:.2em .5em; border-radius:20px; white-space:nowrap;
    }
    .call-connected     { background:#dcfce7; color:#15803d; }
    .call-not-connected { background:#fee2e2; color:#b91c1c; }

    /* ── Leaderboard table ────────────────────────────────────────── */
    .table-outer {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        width: 100%;

        /* Custom slim scrollbar */
        scrollbar-width: thin;
        scrollbar-color: rgba(99,102,241,.4) rgba(99,102,241,.08);
    }

    /* Webkit scrollbar (Chrome/Safari) */
    .table-outer::-webkit-scrollbar {
        height: 6px;
    }
    .table-outer::-webkit-scrollbar-track {
        background: rgba(99,102,241,.06);
        border-radius: 10px;
    }
    .table-outer::-webkit-scrollbar-thumb {
        background: rgba(99,102,241,.35);
        border-radius: 10px;
    }
    .table-outer::-webkit-scrollbar-thumb:hover {
        background: rgba(99,102,241,.6);
    }
    .leaderboard-table {
        min-width: 1200px;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .leaderboard-table thead th {
        background: rgba(99,102,241,.07);
        color: #374151;
        font-weight: 700;
        text-transform: uppercase;
        font-size: .68rem;
        letter-spacing: .45px;
        padding: 11px 10px;
        border: none;
        white-space: nowrap;
    }
    .leaderboard-table tbody tr {
        background: #fff;
        transition: background .2s ease, transform .2s ease;
    }
    .leaderboard-table tbody tr:hover {
        background: #f5f3ff;
        transform: translateY(-1px);
    }
    .leaderboard-table tbody td {
        padding: 10px; vertical-align: middle;
        border: none;
        border-top: 1px solid #f1f5f9;
        font-size: .8rem;
    }
    .leaderboard-table tbody tr:first-child td { border-top: none; }

    /* Sticky first two columns */
    .leaderboard-table th:nth-child(1),
    .leaderboard-table td:nth-child(1) {
        position: sticky; left: 0; z-index: 2;
        background: inherit;
        box-shadow: 2px 0 5px rgba(0,0,0,.05);
        min-width: 52px;
    }
    .leaderboard-table th:nth-child(2),
    .leaderboard-table td:nth-child(2) {
        position: sticky; left: 52px; z-index: 2;
        background: inherit;
        box-shadow: 2px 0 5px rgba(0,0,0,.04);
        min-width: 140px;
    }
    .leaderboard-table thead th:nth-child(1),
    .leaderboard-table thead th:nth-child(2) {
        background: rgba(99,102,241,.07);
        z-index: 3;
    }

    /* Score column highlight */
    .leaderboard-table th:last-child,
    .leaderboard-table td:last-child {
        background: rgba(99,102,241,.04);
    }

    /* ── Badge & misc ─────────────────────────────────────────────── */
    .badge { font-weight: 600; padding: .35em .7em; }

    /* ── Spinner ──────────────────────────────────────────────────── */
    .spinner-professional {
        width: 44px; height: 44px;
        border: 4px solid rgba(102,126,234,.18);
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Confetti ─────────────────────────────────────────────────── */
    .confetti-piece {
        position: fixed; width: 7px; height: 11px; top: -10px;
        z-index: 9999; pointer-events: none;
        animation: confetti-fall linear forwards;
    }
    @keyframes confetti-fall {
        0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }

    /* ── Fade in up ───────────────────────────────────────────────── */
    .fade-in-up { animation: fadeInUp .5s ease-out both; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    h1,h2,h3,h4,h5,h6 { color: #1e293b; }
    .text-muted { color: #64748b !important; }

    @media (max-width: 576px) {
        .stat-value { font-size: 1.2rem; }
        .rank-badge-lg { width: 48px; height: 48px; font-size: 1.1rem; }
        .rank-1::after { font-size: 1.4rem; top: -24px; }
    }
</style>
@endsection


@section('content')
<div class="content-wrapper">
<div id="confettiContainer"></div>

{{-- ── Page Title ──────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="page-title mb-0">
                <i class="las la-trophy me-2" style="font-size:1.6rem; vertical-align:middle;"></i>
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
<div class="row mb-3">
    <div class="col-12">
        <div class="card filter-card border-0 mb-0">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">

                    <div class="col-xl-3 col-lg-3 col-md-6 col-12">
                        <label class="form-label fw-semibold small mb-1">⏱️ Time Period</label>
                        <select class="form-select form-select-sm" id="periodSelect">
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

                    <div class="col-xl-2 col-lg-2 col-md-6 col-6" id="startDateDiv" style="display:none;">
                        <label class="form-label fw-semibold small mb-1">📅 Start</label>
                        <input type="date" class="form-control form-control-sm" id="startDate">
                    </div>

                    <div class="col-xl-2 col-lg-2 col-md-6 col-6" id="endDateDiv" style="display:none;">
                        <label class="form-label fw-semibold small mb-1">📅 End</label>
                        <input type="date" class="form-control form-control-sm" id="endDate">
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="form-label fw-semibold small mb-1">👤 Role</label>
                        <select class="form-select form-select-sm" id="roleFilter">
                            <option value="telecaller">Telecallers</option>
                            <option value="lead_manager">Lead Managers</option>
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="form-label fw-semibold small mb-1">🏢 Branch</label>
                        <select class="form-select form-select-sm" id="branchFilter">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-1 col-lg-1 col-md-4 col-12">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="applyFilter">
                            <i class="las la-filter me-1"></i>Apply
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Summary Cards ───────────────────────────────────────────── --}}
<div class="row g-3 mb-4" id="summaryCards">
    {{-- Populated via AJAX --}}
</div>

{{-- ── Top 3 Podium ────────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-12 text-center mb-2">
        <h4 class="fw-bold mb-1">
            <i class="las la-medal me-2" style="font-size:1.6rem;vertical-align:middle;"></i>
            Top Performers
        </h4>
        <p class="text-muted small mb-0">Celebrating excellence in lead management</p>
    </div>
    <div class="col-12">
        <div class="row justify-content-center g-3" id="topPerformersContainer">
            <div class="col-12 text-center py-4">
                <div class="spinner-professional"></div>
                <p class="text-muted mt-3 small">Loading champions...</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Full Leaderboard Table ───────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:18px;">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="las la-list me-2 text-primary"></i>Full Rankings
                    <span class="badge bg-primary ms-2" id="userCount">0</span>
                </h5>
                <small class="text-muted" id="periodLabel"></small>
            </div>
            <div class="card-body p-0">
                <div class="table-outer">
                    <table class="table mb-0 leaderboard-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Role</th>
                                <th class="text-center">Assigned</th>
                                <th class="text-center">🔥 Hot</th>
                                <th class="text-center">Contacted</th>
                                <th class="text-center">Follow Up</th>
                                <th class="text-center">✅ Admitted</th>
                                <th class="text-center">Adm%</th>
                                <th class="text-center">⭐ Score</th>
                            </tr>
                        </thead>
                        <tbody id="leaderboardTableBody">
                            <tr>
                                <td colspan="15" class="text-center py-5">
                                    <div class="spinner-professional"></div>
                                    <p class="text-muted mt-3 small">Loading rankings...</p>
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
        for (let i = 0; i < 70; i++) {
            const el = $('<div class="confetti-piece"></div>');
            const dur = 2 + Math.random() * 2;
            el.css({
                left             : Math.random() * 100 + '%',
                background       : colors[Math.floor(Math.random() * colors.length)],
                animationDuration: dur + 's',
                animationDelay   : Math.random() * .6 + 's',
                borderRadius     : Math.random() > .5 ? '50%' : '2px',
            });
            $('#confettiContainer').append(el);
            setTimeout(() => el.remove(), dur * 1200);
        }
    }

    // ── Period toggle ──────────────────────────────────────────────
    $('#periodSelect').on('change', function () {
        $('#startDateDiv, #endDateDiv').toggle($(this).val() === 'custom');
    });

    // ── Load ───────────────────────────────────────────────────────
    function loadPerformanceData() {
        const data = {
            period    : $('#periodSelect').val(),
            role      : $('#roleFilter').val(),
            branch_id : $('#branchFilter').val(),
            start_date: $('#startDate').val(),
            end_date  : $('#endDate').val(),
        };

        const spinner = `
            <tr><td colspan="15" class="text-center py-5">
                <div class="spinner-professional"></div>
                <p class="text-muted mt-3 small">Loading...</p>
            </td></tr>`;

        $('#leaderboardTableBody').html(spinner);
        $('#topPerformersContainer').html(`
            <div class="col-12 text-center py-4">
                <div class="spinner-professional"></div>
                <p class="text-muted mt-3 small">Loading champions...</p>
            </div>`);

        $.ajax({
            url    : '{{ route("users.performance.data") }}',
            type   : 'GET',
            data   : data,
            success: function (res) {
                if (!res.success) return;
                renderSummaryCards(res.summary);
                renderTopPerformers(res.leaderboard.slice(0, 3));
                renderTable(res.leaderboard);
                updatePeriodLabel(res.start_date, res.end_date);
                if (res.leaderboard.length > 0) launchConfetti();
            },
            error: function () {
                Swal.fire({ icon:'error', title:'Oops…',
                    text:'Failed to load performance data.',
                    confirmButtonColor:'#667eea' });
            }
        });
    }

    // ── Summary cards — 6 cards, 2-per-row on mobile ───────────────
    function renderSummaryCards(s) {
        const cr = s.avg_connection_rate ?? 0;
        const crColor = cr >= 60 ? 'success' : cr >= 40 ? 'warning' : 'danger';

        const card = (delay, iconBg, icon, iconColor, label, value, sub) => `
            <div class="col-xl-3 col-lg-4 col-md-4 col-6 fade-in-up" style="animation-delay:${delay}s;">
                <div class="card stat-card border-0 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div style="min-width:0;">
                                <p class="stat-label text-muted mb-1">${label}</p>
                                <div class="stat-value">${value}</div>
                                ${sub ? `<div class="stat-sub text-muted mt-1">${sub}</div>` : ''}
                            </div>
                            <div class="bg-${iconBg} bg-opacity-10 p-2 rounded-circle stat-icon">
                                <i class="las ${icon} text-${iconColor}" style="font-size:1.3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

        $('#summaryCards').html(
            card(0,     'primary', 'la-clipboard-list','primary',
                 'Total Assigned',  s.total_assigned,  '') +
            card(0.10,  'danger',  'la-fire',           'danger',
                 '🔥 Hot Leads',    `<span class="text-danger">${s.total_hot}</span>`,  '') +
            card(0.20,  'success', 'la-phone-volume',   'success',
                 '📞 Connected',
                 `<span class="text-success">${s.total_connected}</span>`,
                 `<span class="text-danger">${s.total_not_connected} missed</span>`) +
            card(0.05,  'success', 'la-check-circle',  'success',
                 'Total Admitted',  `<span class="text-success">${s.total_admitted}</span>`,
                 `<span class="text-success fw-semibold">${s.avg_admission_rate}% avg</span>`)
        );
    }

    // ── Top 3 podium ───────────────────────────────────────────────
    function renderTopPerformers(top) {
        if (!top.length) {
            $('#topPerformersContainer').html(`
                <div class="col-12 text-center py-5">
                    <i class="las la-trophy" style="font-size:3.5rem;color:#ccc;"></i>
                    <p class="text-muted mt-3">No performance data yet for this period</p>
                </div>`);
            return;
        }

        const medals = ['🥇','🥈','🥉'];
        let html = '';
        top.forEach(u => {
            const adm  = u.admission_rate  ?? 0;
            const conn = u.connection_rate ?? 0;
            html += `
            <div class="col-xl-4 col-lg-5 col-md-8 col-12 winner-card">
                <div class="card glass-card border-0 h-100">
                    <div class="card-body p-4 text-center">
                        <div class="mb-2"><span class="medal-emoji">${medals[u.rank-1]}</span></div>
                        <div class="rank-badge rank-badge-lg rank-${u.rank} mx-auto mb-3">#${u.rank}</div>

                        <h5 class="fw-bold mb-1">${u.name}</h5>
                        <div class="mb-1"><span class="role-chip role-${u.role}">${formatRole(u.role)}</span></div>
                        <p class="text-muted small mb-3">${u.branch}</p>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(37,99,235,.07);">
                                    <div class="fw-bold text-primary">${u.assigned}</div>
                                    <small class="text-muted" style="font-size:.66rem;">Assigned</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(220,38,38,.07);">
                                    <div class="fw-bold text-danger">${u.hot_leads}</div>
                                    <small class="text-muted" style="font-size:.66rem;">🔥 Hot</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded" style="background:rgba(22,163,74,.07);">
                                    <div class="fw-bold text-success">${u.admitted}</div>
                                    <small class="text-muted" style="font-size:.66rem;">✅ Admitted</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Admission Rate</small>
                                <span class="badge bg-success">${adm}%</span>
                            </div>
                            <div class="prog-wrap">
                                <div class="prog-fill-purple" style="width:${Math.min(adm,100)}%;"></div>
                            </div>
                        </div>

                        <div class="value-highlight">
                            <div class="row g-2 mb-2 text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block" style="font-size:.68rem;">📞 Connected</small>
                                    <div class="fw-bold text-success fs-6">${u.calls_connected}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block" style="font-size:.68rem;">📵 Missed</small>
                                    <div class="fw-bold text-danger fs-6">${u.calls_not_connected}</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted" style="font-size:.68rem;">Connection Rate</small>
                                <small class="fw-bold text-info">${conn}%</small>
                            </div>
                            <div class="prog-wrap">
                                <div class="prog-fill-green" style="width:${Math.min(conn,100)}%;"></div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <span class="badge bg-primary px-3 py-2" style="font-size:.85rem;">
                                ⭐ Score: ${u.score}
                            </span>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        $('#topPerformersContainer').html(html);
    }

    // ── Full table ─────────────────────────────────────────────────
    function renderTable(board) {
        $('#userCount').text(board.length);

        if (!board.length) {
            $('#leaderboardTableBody').html(`
                <tr><td colspan="15" class="text-center py-5 text-muted">
                    <i class="las la-inbox" style="font-size:2.8rem;opacity:.3;"></i>
                    <p class="mt-2 mb-0">No data for this period / filter</p>
                </td></tr>`);
            return;
        }

        let html = '';
        board.forEach(u => {
            const adm  = u.admission_rate  ?? 0;
            const conn = u.connection_rate ?? 0;

            const admC  = adm  >= 50 ? 'bg-success' : adm  >= 25 ? 'bg-warning text-dark' : 'bg-danger';
            const connC = conn >= 60 ? 'bg-success' : conn >= 40 ? 'bg-warning text-dark' : 'bg-danger';
            const ovC   = u.overdue_followups > 0 ? 'bg-danger'  : 'bg-success';
            const scrC  = u.score >= 20 ? 'bg-success' : u.score >= 10 ? 'bg-warning text-dark' : 'bg-secondary';
            const rankCls = u.rank <= 3 ? u.rank : 'other';

            html += `
            <tr>
                <td>
                    <div class="rank-badge rank-badge-sm rank-${rankCls}">${u.rank}</div>
                </td>
                <td>
                    <a href="{{ url('/users') }}/${u.id}" class="fw-bold text-decoration-none text-dark">
                        ${u.name}
                    </a>
                    <div class="text-muted" style="font-size:.7rem;">${u.branch}</div>
                </td>
                <td><span class="role-chip role-${u.role}">${formatRole(u.role)}</span></td>
                <td class="text-center"><span class="badge bg-primary">${u.assigned}</span></td>
                <td class="text-center"><span class="badge bg-danger">${u.hot_leads}</span></td>
                <td class="text-center"><span class="badge bg-info">${u.contacted}</span></td>
                <td class="text-center"><span class="badge" style="background:#f97316;">${u.follow_up}</span></td>
                <td class="text-center"><span class="badge bg-success">${u.admitted}</span></td>
                <td class="text-center"><span class="badge ${admC}">${adm}%</span></td>
                <td class="text-center"><span class="badge ${scrC}">${u.score}</span></td>
            </tr>`;
        });
        $('#leaderboardTableBody').html(html);
    }

    function updatePeriodLabel(start, end) {
        $('#periodLabel').text(`${start} → ${end}`);
    }

    function formatRole(role) {
        return (role || '').replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    $('#applyFilter').on('click', loadPerformanceData);
    loadPerformanceData();
});
</script>
@endsection
