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
    .fs-notattended {
        background: #e0e7ff;
        color: #3730a3;
    }

    .status-tab[data-status="not_attended"].active {
        background: #3730a3;
    }

    /* ── Filter panel ──────────────────────────────────────── */
    .filter-card-header {
        display:flex; justify-content:space-between; align-items:center;
        padding:.85rem 1.25rem; background:#e9f4ff; border:1px solid #dad4ff;
        border-radius:8px 8px 0 0 !important; cursor:pointer; user-select:none; transition:background .2s ease;
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

    /* ── Per-page select in header ─────────────────────────── */
    .per-page-wrap { display:flex; align-items:center; gap:6px; }
    .per-page-wrap label { font-size:.82rem; color:#6c757d; white-space:nowrap; margin:0; }
    #perPageSelect { width:70px; font-size:.82rem; padding:3px 6px; }

    /* ── Active Filters Bar ────────────────────────────────────── */
    #activeFiltersBar {
        display: none;          /* hidden when no filters active    */
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #f0f4ff;
        border-bottom: 1px solid #c7d2fe;
        position: sticky;
        top: 0;                 /* sticks to viewport top on scroll */
        z-index: 50;
    }
    #activeFiltersBar .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #fff;
        border: 1px solid #a5b4fc;
        border-radius: 20px;
        padding: 3px 10px 3px 10px;
        font-size: .76rem;
        font-weight: 600;
        color: #3730a3;
        white-space: nowrap;
    }
    #activeFiltersBar .filter-chip .chip-remove {
        cursor: pointer;
        color: #6366f1;
        font-size: .9rem;
        line-height: 1;
        margin-left: 2px;
        transition: color .15s;
    }
    #activeFiltersBar .filter-chip .chip-remove:hover { color: #dc2626; }
    #activeFiltersBar .clear-all-btn {
        margin-left: auto;
        font-size: .75rem;
        font-weight: 700;
        color: #dc2626;
        cursor: pointer;
        padding: 3px 10px;
        border-radius: 20px;
        border: 1px solid #fca5a5;
        background: #fff;
        transition: background .15s;
    }
    #activeFiltersBar .clear-all-btn:hover { background: #fee2e2; }
    #activeFiltersBar .filters-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6366f1;
        white-space: nowrap;
    }

    /* ── Fixed-height scrollable table ────────────────────────── */
    .table-container {
        overflow-x: auto;
        overflow-y: auto;          /* ← was 'visible' */
        max-height: 62vh;          /* adjust to taste  */
        width: 100%;
    }
    /* Keep thead sticky inside the scroll container */
    .table-container thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
        box-shadow: 0 2px 2px -1px rgba(0,0,0,.1);
    }

    /* ── Desktop table: visible on md+ ──────────────────────────── */
    .leads-desktop-view { display: table-row; }
    .leads-mobile-cards { display: none; }

    @media (max-width: 767px) {
        /* Hide desktop table rows */
        .leads-desktop-view { display: none !important; }

        /* Show mobile cards container */
        .leads-mobile-cards { display: block; }

        /* Hide thead on mobile */
        #leadsTable thead { display: none; }

        /* Remove table padding */
        .table-container { padding: 0; }
        #leadsTable { border: none; }
    }

    /* ══════════════════════════════════════════════════════════════
    LEADS TABLE — MOBILE CARD VIEW
    ══════════════════════════════════════════════════════════════ */
    .leads-desktop-view  { display: table-row; }
    .leads-mobile-cards  { display: none; }

    @media (max-width: 767px) {
        .leads-desktop-view          { display: none !important; }
        .leads-mobile-cards          { display: table-row !important; }
        #leadsTable thead            { display: none; }
        .table-container             { overflow-x: unset; }
        #leadsTable                  { border: none; background: transparent; }
        #leadsTable > tbody > tr > td { border: none; }

        /* Card wrapper */
        .lm-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: .85rem;
            display: flex;
            flex-direction: column;
            gap: .65rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        /* Top row: name + status */
        .lm-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }

        .lm-name-block {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 1;
        }

        .lm-checkbox { flex-shrink: 0; }

        .lm-name {
            font-size: .9rem;
            font-weight: 700;
            color: #1e293b;
            text-decoration: none;
            display: block;
            line-height: 1.2;
        }
        .lm-name:hover { color: #2563eb; }

        .lm-code {
            font-size: .68rem;
            font-weight: 600;
            color: #94a3b8;
            background: #f1f5f9;
            padding: 1px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 2px;
        }

        /* Info grid — 2 columns */
        .lm-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .4rem .75rem;
        }

        .lm-field {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }

        .lm-field-label {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .lm-field-label i { font-size: .75rem; }

        .lm-field-val {
            font-size: .78rem;
            font-weight: 500;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .lm-field-val.fw-semibold { font-weight: 700; color: #1e293b; }

        /* Followup badges */
        .lm-followups {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding-top: .35rem;
            border-top: 1px solid #f1f5f9;
        }

        /* Action buttons */
        .lm-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding-top: .35rem;
            border-top: 1px solid #f1f5f9;
        }
        .lm-actions .btn { font-size: .75rem; padding: .28rem .65rem; }
    }

    /* ══════════════════════════════════════════════════════════════
    MOBILE — Kill horizontal overflow
    ══════════════════════════════════════════════════════════════ */
    @media (max-width: 767px) {

        body,
        .container-fluid,
        .container         { overflow-x: hidden; }

        .table-container   { overflow-x: hidden !important; }

        #leadsTable {
            width: 100% !important;
            table-layout: fixed;
        }

        .leads-desktop-view { display: none !important; }
        .leads-mobile-cards { display: table-row !important; }
        #leadsTable thead   { display: none; }

        .leads-card,
        .card-body.p-0     { overflow: hidden !important; }

        .leads-mobile-cards td {
            width: 100% !important;
            max-width: 100vw !important;
            padding: .5rem .6rem !important;
        }

        .lm-card {
            width: 100%;
            box-sizing: border-box;
            overflow: hidden;
            padding: .85rem;
            gap: .65rem;
        }

        /* Info grid — 2 columns */
        .lm-grid {
            grid-template-columns: 1fr 1fr;
        }

        .lm-name    { font-size: .88rem; }
        .lm-actions .btn { font-size: .75rem; padding: .28rem .65rem; }
    }

    /* ══════════════════════════════════════════════════════════════
    SMALL PHONES — 480px and below (e.g. iPhone SE, Galaxy A)
    ══════════════════════════════════════════════════════════════ */
    @media (max-width: 480px) {

        .container-fluid,
        .container { padding-left: 8px !important; padding-right: 8px !important; }

        .leads-mobile-cards td { padding: .4rem .4rem !important; }

        .lm-card { padding: .7rem; gap: .55rem; border-radius: 8px; }

        /* Single column info grid on tiny screens */
        .lm-grid { grid-template-columns: 1fr 1fr; gap: .35rem .5rem; }

        .lm-name        { font-size: .82rem; }
        .lm-code        { font-size: .64rem; }

        .lm-field-label { font-size: .58rem; }
        .lm-field-val   { font-size: .74rem; }

        /* Badges smaller */
        .lm-followups .badge { font-size: .65rem; padding: .18rem .45rem; }

        /* Action buttons — icon only on very small */
        .lm-actions .btn {
            font-size: .7rem;
            padding: .25rem .55rem;
        }

        /* Status badge in top row */
        .fs-badge { font-size: .68rem !important; padding: .2rem .5rem !important; }

        /* Filter card */
        .filter-card-header { padding: .6rem .85rem; }
        .filter-group-label { font-size: .72rem; }
        .filter-label       { font-size: .72rem; }
        .form-select-sm,
        .form-control       { font-size: .78rem; }
    }

    /* ══════════════════════════════════════════════════════════════
    VERY SMALL PHONES — 360px and below (e.g. older Androids)
    ══════════════════════════════════════════════════════════════ */
    @media (max-width: 360px) {

        /* Status tabs — scroll horizontally */
        .status-tabs {
            overflow-x: auto;
            flex-wrap: nowrap !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .status-tabs::-webkit-scrollbar { display: none; }
        .status-tab { white-space: nowrap; flex-shrink: 0; }

        /* Per page + count bar */
        .per-page-wrap { font-size: .72rem; }
        .card-header   { padding: .6rem .75rem; }
    }

    /* ── Mobile action buttons — icon only ───────────────────────── */
    .lm-actions {
        display: flex;
        gap: 8px;
        padding-top: .4rem;
        border-top: 1px solid #f1f5f9;
        flex-wrap: nowrap;
    }

    .lm-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        text-decoration: none;
        transition: all .2s ease;
        border: 1px solid transparent;
    }

    .lm-action-btn.btn-view   { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .lm-action-btn.btn-edit   { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
    .lm-action-btn.btn-assign { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .lm-action-btn.btn-delete { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }

    .lm-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,.12);
    }

    .lm-action-btn.btn-view:hover   { background: #bae6fd; }
    .lm-action-btn.btn-edit:hover   { background: #e2e8f0; }
    .lm-action-btn.btn-assign:hover { background: #dbeafe; }
    .lm-action-btn.btn-delete:hover { background: #fca5a5; }

    /* ══════════════════════════════════════════════════════════════
    FILTER CARD — MOBILE
    ══════════════════════════════════════════════════════════════ */
    @media (max-width: 767px) {

        /* Filter card */
        #filterCard {
            border-radius: 0 !important;
            margin-left: 0;
            margin-right: 0;
        }

        .filter-card-header {
            padding: .7rem 1rem;
            flex-wrap: nowrap;
            gap: 8px;
        }

        /* Hide Apply/Reset text labels — keep icons only */
        #applyFiltersBtn .btn-text,
        #resetBtn .btn-text { display: none; }

        #applyFiltersBtn,
        #resetBtn {
            padding: .3rem .6rem !important;
            font-size: .78rem;
        }

        /* Filter body */
        #filterBody {
            padding: .75rem .85rem !important;
        }

        /* All filter rows go single column */
        #filterBody .row > [class*="col-"] {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }

        .filter-group-label {
            font-size: .72rem;
            margin-bottom: .4rem;
        }

        .filter-label {
            font-size: .72rem;
            margin-bottom: 2px;
        }

        .form-select-sm,
        .form-control {
            font-size: .8rem;
            padding: .35rem .6rem;
        }

        .filter-divider { margin: .6rem 0; }

        /* Date range — side by side on mobile */
        #dateFrom,
        #dateTo {
            font-size: .75rem;
        }

        /* Active filters bar */
        #activeFiltersBar .filters-label { font-size: .72rem; }
        #filterChipsContainer            { gap: 4px; }
        .clear-all-btn                   { font-size: .72rem; padding: .2rem .6rem; }
    }

    /* ══════════════════════════════════════════════════════════════
    FILTER CARD — SMALL PHONES (≤ 480px)
    ══════════════════════════════════════════════════════════════ */
    @media (max-width: 480px) {

        .filter-card-header { padding: .6rem .75rem; gap: 6px; }

        /* Header left side — tighter */
        .filter-card-header .fw-semibold { font-size: .82rem; }
        .filter-card-header .fs-18       { font-size: 1rem !important; }

        /* Buttons icon only */
        #applyFiltersBtn i,
        #resetBtn i { margin: 0 !important; }

        #applyFiltersBtn,
        #resetBtn {
            padding: .28rem .55rem !important;
            min-width: 32px;
        }

        #filterBody { padding: .6rem .7rem !important; }

        .form-select-sm,
        .form-control    { font-size: .76rem; }

        .filter-group-label { font-size: .68rem; }
        .filter-label       { font-size: .68rem; }

        /* Date range inputs stack */
        .input-group #dateFrom,
        .input-group #dateTo {
            min-width: 0;
            font-size: .72rem;
        }

        /* Active filter chips */
        #activeFiltersBar {
            padding: 0;
        }
        #activeFiltersBar > div {
            padding: 6px 10px !important;
            gap: 4px !important;
        }
    }

    /* ══════════════════════════════════════════════════════════════
    FILTER CARD — VERY SMALL PHONES (≤ 360px)
    ══════════════════════════════════════════════════════════════ */
    @media (max-width: 360px) {

        .filter-card-header { padding: .5rem .6rem; }

        /* Stack header into two rows */
        .filter-card-header {
            flex-wrap: wrap;
        }

        /* Filter body even tighter */
        #filterBody { padding: .5rem .6rem !important; }

        .form-select-sm,
        .form-control { font-size: .72rem; padding: .28rem .5rem; }

        /* Date range — stack from row to column */
        #dateFrom ~ .input-group-text,
        #dateTo {
            font-size: .7rem;
        }

        /* Collapse pill */
        .filter-collapse-pill {
            padding: .2rem .4rem;
        }
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
                    {{-- <span class="badge bg-warning text-dark fs-6">
                        <i class="las la-clock"></i>
                        <span id="pendingFollowupsBadge">{{ $pendingFollowupsCount }}</span> Followups
                    </span> --}}
                </div>
            </div>
        </div>
    </div>

    {{-- ── FILTERS CARD ──────────────────────────────────────── --}}
    <div class="card mb-3" id="filterCard" style="border-radius: 0;">

        <div class="filter-card-header" onclick="toggleFilters()">
            <div class="d-flex align-items-center gap-2">
                <i class="las la-sliders-h fs-18 text-primary"></i>
                <span class="fw-semibold">Filters</span>
                <span class="badge bg-primary rounded-pill ms-1" id="activeFilterCount" style="display:none;">0</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-primary px-3" id="applyFiltersBtn"
                        onclick="event.stopPropagation()">
                    <i class="las la-check"></i><span class="btn-text ms-1">Apply</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 text-dark" id="resetBtn"
                        onclick="event.stopPropagation()">
                    <i class="las la-redo"></i><span class="btn-text ms-1">Reset</span>
                </button>
                <div class="filter-collapse-pill" onclick="toggleFilters(); event.stopPropagation();">
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
                <div class="row g-3 mb-1">

                    {{-- Unified Search --}}
                    <div class="col-xl-4 col-lg-5 col-md-12">
                        <label class="filter-label">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Name, phone, email, agent, referral...">
                        </div>
                        {{-- <div class="text-muted mt-1" style="font-size:.7rem; line-height:1.3;">
                            <i class="las la-info-circle"></i>
                            Searches: name · phone · email · lead code · app no ·
                            <i class="las la-user-tie"></i> agent · <i class="las la-share-alt"></i> referral
                        </div> --}}
                    </div>

                    {{-- Interest --}}
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-fire text-danger"></i> Interest</label>
                        <select class="form-select form-select-sm" id="filterInterestLevel">
                            <option value="">All Interest</option>
                            <option value="hot">Hot</option>
                            <option value="warm">Warm</option>
                            <option value="cold">Cold</option>
                        </select>
                    </div>

                    {{-- Lead Source --}}
                    <div class="col-xl-2 col-lg-4 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-bullhorn text-info"></i> Lead Source</label>
                        <select class="form-select form-select-sm" id="filterSource">
                            <option value="">All Sources</option>
                            @foreach($leadSources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-xl-4 col-lg-5 col-md-6">
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
                <div class="row g-3 mb-1">

                    <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                        <label class="filter-label">Institution Type</label>
                        <select class="form-select form-select-sm" id="filterInstitutionType">
                            <option value="" {{ request('institution_type', '') === '' ? 'selected' : '' }}>
                                🏫🎓 All
                            </option>
                            <option value="school" {{ request('institution_type') === 'school' ? 'selected' : '' }}>
                                🏫 School
                            </option>
                            <option value="college" {{ request('institution_type') === 'college' ? 'selected' : '' }}>
                                🎓 College
                            </option>
                        </select>

                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4" id="schoolDeptWrap">
                        <label class="filter-label">School Department</label>
                        <select class="form-select form-select-sm" id="filterSchoolDepartment">
                            <option value="">All Departments</option>
                            @foreach(['Biology Science', 'Computer Science','Commerce','Arts & Journalism', 'Humanities','Vocational', 'Other'] as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4" id="collegeDeptWrap">
                        <label class="filter-label">College Department</label>
                        <select class="form-select form-select-sm" id="filterCollegeDepartment">
                            <option value="">All Departments</option>
                            @foreach([
                                'BCA',
                                'BBA',
                                'B.Com',
                                'B.Tech',
                                'B.Sc',
                                'B.A',
                                'B.Ed',
                                'MBA',
                                'MCA',
                                'M.Com',
                                'M.Sc',
                                'M.Tech',
                                'LLB',
                                'Pharmacy',
                                'Nursing',
                                'Other'
                            ] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-map text-muted"></i> State</label>
                        <select class="form-select form-select-sm" id="filterState">
                            <option value=""></option>
                            @foreach($states as $stateOption)
                                <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-map-pin text-muted"></i> District</label>
                        <select class="form-select form-select-sm" id="filterDistrict">
                            <option value=""></option>
                        </select>
                    </div>

                </div>

                <hr class="filter-divider">

                {{-- GROUP 3: Status Filters --}}
                <div class="filter-group-label"><i class="las la-tasks"></i> Status & Activity</div>
                <div class="row g-3 mb-1">

                    {{-- Call Status ✨ --}}
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-phone text-success"></i> Call Status</label>
                        <select class="form-select form-select-sm" id="filterCallStatus">
                            <option value="">All</option>
                            <option value="contacted">✉️ Contacted</option>
                            <option value="not_attended">🚫 Not Attended</option>
                        </select>
                    </div>

                    {{-- Counseling Stage ✨ --}}
                    <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-toggle-on text-primary"></i> Counseling Stage</label>
                        <select class="form-select form-select-sm" id="filterCounselingStage">
                            <option value="">All</option>
                            <option value="whatsapp_link_submitted">WhatsApp Link Submitted</option>
                            <option value="application_form_submitted">Application Form Submitted</option>
                            <option value="booking">Booking</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    {{-- Followup Count ✨ --}}
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="filter-label"><i class="las la-history text-warning"></i> Followups</label>
                        <select class="form-select form-select-sm" id="filterFollowupCount">
                            <option value="">Any</option>
                            <option value="0">None (0)</option>
                            <option value="1">Exactly 1</option>
                            <option value="2">Exactly 2</option>
                            <option value="3">3 or more</option>
                        </select>
                    </div>

                    {{-- Programme (moved here from removed group) --}}
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <label class="filter-label">Programme</label>
                        <select class="form-select form-select-sm" id="filterProgramme">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $programme)
                                <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Specific Course --}}
                    <div class="col-xl-3 col-lg-4 col-md-5">
                        <label class="filter-label">
                            Specific Course <small class="text-muted fw-normal ms-1">filtered by programme</small>
                        </label>
                        <select class="form-select form-select-sm" id="filterCourse">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" data-programme="{{ $course->programme_id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <hr class="filter-divider">

                {{-- ── GROUP 4: Assignment ──────────────────────────── --}}
                <div class="filter-group-label"><i class="las la-users-cog"></i> Assignment</div>
                <div class="row g-3 mb-1">
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
                    @if(in_array(auth()->user()->role, ['super_admin', 'operation_head']))
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
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{-- ── Active Filters Bar ─────────────────────────────────── --}}
    <div id="activeFiltersBar" class="card mb-0" style="border-radius:0; border-top:none; display:none;">
        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:6px; padding:8px 14px;">
            <span class="filters-label"><i class="las la-filter me-1"></i>Active:</span>
            <div id="filterChipsContainer" style="display:inline-flex; flex-wrap:wrap; gap:5px;"></div>
            <button type="button" class="clear-all-btn ms-auto" id="clearAllFiltersBtn">
                <i class="las la-times me-1"></i>Clear All
            </button>
        </div>
    </div>

    {{-- ── Leads Table Card ──────────────────────────────────────── --}}
    <div class="card leads-card">

        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
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
                    {{-- Per-page selector moved here from filters --}}
                    <div class="per-page-wrap">
                        <label for="perPageSelect" class="text-muted">Show</label>
                        <select class="form-select form-select-sm" id="perPageSelect">
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-muted" style="font-size:.82rem;">/ page</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    {{-- <span class="badge bg-primary py-2 px-3">
                        <i class="las la-school me-1"></i>Schools
                        <span id="ic-school">{{ $institutionCounts['school'] ?? 0 }}</span>
                    </span>
                    <span class="badge bg-success py-2 px-3">
                        <i class="las la-graduation-cap me-1"></i>Colleges
                        <span id="ic-college">{{ $institutionCounts['college'] ?? 0 }}</span>
                    </span> --}}

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
                            <th class="sortable" data-column="final_status">Candidate Status</th>
                            <th>Call Status</th>
                            <th>Followups</th>
                            <th>Latest Followup</th>
                            <th class="sortable" data-column="interest_level">Interest</th>
                            <th>Agent/Referral</th>
                            <th>Institution</th>
                            <th>Department</th>
                            <th>State / District</th>
                            <th class="sortable" data-column="course_id">Course</th>
                            <th class="sortable" data-column="lead_source_id">Source</th>
                            <th class="sortable" data-column="assigned_to">Assigned To</th>
                            <th class="sortable" data-column="branch_id">Branch</th>
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
                <input type="hidden" id="assignLeadId"     name="lead_id">
                <input type="hidden" id="assignLeadBranch" name="lead_branch_id">
                <div class="modal-body">

                    <div class="alert alert-light border mb-3 py-2 px-3">
                        <div class="d-flex gap-3 flex-wrap">
                            <div>
                                <small class="text-muted d-block">Lead Code</small>
                                <strong id="assignLeadCode" class="text-primary"></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Name</small>
                                <strong id="assignLeadName"></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Branch</small>
                                <span id="assignLeadBranchLabel"
                                      class="badge bg-soft-primary text-primary border border-primary fw-semibold">
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="currentAssignmentBlock" style="display:none">
                        <small class="text-muted">Currently assigned to</small>
                        <span class="badge bg-secondary ms-2" id="currentAssigneeLabel"></span>
                    </div>

                    <div class="alert alert-info d-flex align-items-start gap-2 py-2 px-3 mb-3">
                        <i class="las la-info-circle mt-1" style="font-size:1.1rem;flex-shrink:0;"></i>
                        <div class="small">
                            Only telecallers assigned to <strong id="assignBranchNotice">this lead's branch</strong>
                            are shown below. Assigning across branches is not allowed.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-user-tie me-1"></i>Assign To Telecaller
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="assignTelecaller" name="assigned_to" required>
                            <option value="">Choose telecaller...</option>
                        </select>
                        <div id="assignTelecallerEmpty" class="text-danger small mt-1" style="display:none;">
                            <i class="las la-exclamation-triangle me-1"></i>
                            No telecallers are available in this branch.
                            Please add a telecaller to <strong id="assignBranchEmptyName"></strong> first.
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">
                            <i class="las la-comment me-1"></i>Notes
                            <small class="text-muted">optional</small>
                        </label>
                        <textarea class="form-control" id="assignNotes" name="notes" rows="2"
                                  placeholder="Any notes about this assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="assignSubmitBtn">
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

                    <div class="alert alert-warning d-flex align-items-start gap-2 py-2 px-3 mb-3"
                         id="bulkBranchWarning" style="display:none !important;">
                        <i class="las la-exclamation-triangle mt-1" style="font-size:1.1rem;flex-shrink:0;"></i>
                        <div class="small">
                            <strong>Mixed branches detected.</strong>
                            Leads from different branches are selected. Only leads matching the
                            selected telecaller's branch will be assigned — others will be skipped automatically.
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-start gap-2 py-2 px-3 mb-3"
                         id="bulkBranchInfo">
                        <i class="las la-info-circle mt-1" style="font-size:1.1rem;flex-shrink:0;"></i>
                        <div class="small">
                            The selected telecaller must belong to the <strong>same branch</strong> as the leads.
                            Leads from a different branch will be <strong>skipped</strong> automatically.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small mb-1">
                            <i class="las la-list me-1"></i>Selected Leads
                        </label>
                        <div id="selectedLeadsList"
                             style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;
                                    border-radius:8px;background:#f8f9fa;padding:8px;">
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
                                <option value="{{ $u->id }}"
                                        data-branch="{{ $u->branch_id }}"
                                        data-branch-name="{{ $u->branch?->name }}">
                                    {{ $u->name }}
                                    @if($u->branch) — {{ $u->branch->name }} @endif
                                </option>
                            @endforeach
                        </select>
                        <div id="bulkBranchMatchInfo" class="small mt-2" style="display:none;"></div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            <i class="las la-comment me-1"></i>Notes
                            <small class="text-muted">optional</small>
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

    let currentSort   = { column: 'created_at', direction: 'desc' };
    let activeStatus  = '';
    let initialized   = false;

    // ✅ pagination + request stability
    let currentPage   = 1;
    let activeXhr     = null;  // abort in-flight requests to stop “flash then revert”
    let requestSeq    = 0;     // ignore late responses

    // ── SELECT2 FACTORY ─────────────────────────────────────────────
    function s2(selector, placeholder) {
        $(selector).select2({ theme: 'bootstrap-5', placeholder, allowClear: true, width: '100%' });
    }

    // ── INIT ALL SELECT2 ─────────────────────────────────────────────
    s2('#filterState',          'Search state...');
    s2('#filterDistrict',       'Select district...');
    s2('#filterPreferredState', 'Search preferred state...');
    s2('#filterProgramme',      'All programmes...');
    s2('#filterCourse',         'All courses...');
    s2('#filterSource',         'All sources...');
    s2('#filterAssignedTo',     'All telecallers...');
    s2('#filterBranch',         'All branches...');
    s2('#assignTelecaller',     'Choose telecaller...');
    s2('#bulkTelecaller',       'Choose telecaller...');

    // ── BUILD MASTER COURSE LIST from initial DOM ────────────────────
    $('#filterCourse option').each(function () {
        if (!$(this).val()) return; // skip placeholder
        allCourseOptions.push({
            val:       $(this).val(),
            text:      $(this).text(),
            programme: $(this).data('programme')
        });
    });

    // ── DISTRICT CASCADE ─────────────────────────────────────────────
    function populateFilterDistricts(state, selectedDistrict) {
        const districts = (state && Array.isArray(districtMap[state])) ? districtMap[state] : [];
        const $d = $('#filterDistrict');

        try { if ($d.hasClass('select2-hidden-accessible')) $d.select2('destroy'); } catch(e) {}

        $d.empty().append('<option value=""></option>');
        districts.forEach(function (dist) {
            const isSelected = (dist === selectedDistrict);
            $d.append(new Option(dist, dist, isSelected, isSelected));
        });

        $('#filterDistrict').select2({
            theme: 'bootstrap-5',
            placeholder: districts.length > 0 ? 'Search district...' : 'Select a state first...',
            allowClear: true,
            width: '100%'
        });
    }

    // State cascade
    $('#filterState').on('change', function () {
        populateFilterDistricts($(this).val(), '');
        updateActiveFilterCount();
        if (initialized) loadLeads(1);
    });

    $('#filterCallStatus, #filterCounselingStage, #filterFollowupCount').on('change', function () {
        updateActiveFilterCount();
        if (initialized) loadLeads(1);
    });

    // ── PROGRAMME → COURSE CASCADE ───────────────────────────────────
    function cascadeCourses() {
        const programmeId = $('#filterProgramme').val();
        const currentVal  = $('#filterCourse').val();
        const $fc = $('#filterCourse');

        if ($fc.hasClass('select2-hidden-accessible')) $fc.select2('destroy');

        $fc.empty().append('<option value="">All Courses</option>');
        allCourseOptions.forEach(function (opt) {
            if (!programmeId || String(opt.programme) === String(programmeId)) {
                const o = new Option(opt.text, opt.val, opt.val === currentVal, opt.val === currentVal);
                $(o).attr('data-programme', opt.programme);
                $fc.append(o);
            }
        });

        const stillExists = $fc.find(`option[value="${currentVal}"]`).length > 0;
        $fc.val(stillExists ? currentVal : '');

        s2('#filterCourse', 'All courses...');
    }

    $('#filterProgramme').on('change', function () {
        cascadeCourses();
        updateActiveFilterCount();
        if (initialized) loadLeads(1);
    });

    // ── FILTER PANEL TOGGLE ──────────────────────────────────────────
    window.toggleFilters = function () {
        const $body = $('#filterBody');
        const $icon = $('#filterToggleIcon');
        const isHidden = $body.is(':hidden');
        $body.slideToggle(200);
        $icon.toggleClass('open', isHidden);
    };

    // ── INSTITUTION TYPE UI ──────────────────────────────────────────
    function applyInstitutionTypeUi(type, silent) {
        if (type === 'college') {
            $('#collegeDeptWrap').show().removeClass('dimmed');
            $('#schoolDeptWrap').hide().addClass('dimmed');
            if (!silent) $('#filterSchoolDepartment').val('').trigger('change');
            else         $('#filterSchoolDepartment').val('');
        } else if (type === 'school') {
            $('#schoolDeptWrap').show().removeClass('dimmed');
            $('#collegeDeptWrap').hide().addClass('dimmed');
            if (!silent) $('#filterCollegeDepartment').val('').trigger('change');
            else         $('#filterCollegeDepartment').val('');
        } else {
            // ✅ "All" — show both dept panels, no filter applied
            $('#schoolDeptWrap').show().removeClass('dimmed');
            $('#collegeDeptWrap').show().removeClass('dimmed');
        }
    }

    $('#filterInstitutionType').on('change', function () {
        applyInstitutionTypeUi($(this).val(), false);
        updateActiveFilterCount();
        if (initialized) loadLeads(1);
    });

    // ─────────────────────────────────────────────────────────────────
    // ACTIVE FILTERS BAR — meta map, chip renderer, & count badge
    // ─────────────────────────────────────────────────────────────────

    const filterMeta = [
        {
            id: 'searchInput',
            label: 'Search',
            clear: () => $('#searchInput').val('')
        },
        {
            id: 'filterInterestLevel',
            label: 'Interest',
            clear: () => $('#filterInterestLevel').val('').trigger('change')
        },
        {
            id: 'filterAgentName',
            label: 'Agent/Referral',
            clear: () => $('#filterAgentName').val('')
        },
        {
            id: 'filterSource',
            label: 'Source',
            clear: () => $('#filterSource').val(null).trigger('change')
        },
        {
            id: 'dateFrom',
            label: 'From',
            clear: () => $('#dateFrom').val('').trigger('change')
        },
        {
            id: 'dateTo',
            label: 'To',
            clear: () => $('#dateTo').val('').trigger('change')
        },
        {
            id: 'filterInstitutionType',
            label: 'Institution',
            clear: () => $('#filterInstitutionType').val('').trigger('change')
        },
        {
            id: 'filterSchoolDepartment',
            label: 'School Dept',
            clear: () => $('#filterSchoolDepartment').val('').trigger('change')
        },
        {
            id: 'filterCollegeDepartment',
            label: 'College Dept',
            clear: () => $('#filterCollegeDepartment').val('').trigger('change')
        },
        {
            id: 'filterState',
            label: 'State',
            clear: () => $('#filterState').val(null).trigger('change')
        },
        {
            id: 'filterDistrict',
            label: 'District',
            clear: () => $('#filterDistrict').val(null).trigger('change')
        },
        {
            id: 'filterPreferredState',
            label: 'Pref. State',
            clear: () => $('#filterPreferredState').val(null).trigger('change')
        },
        {
            id: 'filterProgramme',
            label: 'Programme',
            clear: () => $('#filterProgramme').val(null).trigger('change')
        },
        {
            id: 'filterCourse',
            label: 'Course',
            clear: () => $('#filterCourse').val(null).trigger('change')
        },
        {
            id: 'filterBranch',
            label: 'Branch',
            clear: () => $('#filterBranch').val(null).trigger('change')
        },
        {
            id: 'filterAssignedTo',
            label: 'Assigned To',
            clear: () => $('#filterAssignedTo').val(null).trigger('change')
        },
        { id: 'filterCallStatus',      label: 'Call Status',      clear: () => $('#filterCallStatus').val('').trigger('change') },
        { id: 'filterCounselingStage', label: 'Counseling Stage', clear: () => $('#filterCounselingStage').val('').trigger('change') },
        { id: 'filterFollowupCount',   label: 'Followups',        clear: () => $('#filterFollowupCount').val('').trigger('change') },

    ];

    /**
     * Returns the human-readable display value of a filter field,
     * or null if the field is empty / default.
     */
    function getReadableValue(id) {
        const el = document.getElementById(id);
        if (!el || !el.value || el.value === '') return null;

        if (el.tagName === 'SELECT') {
            const opt = el.options[el.selectedIndex];
            // Treat placeholder options (value="") as empty
            if (!opt || opt.value === '') return null;
            return opt.text || null;
        }

        return el.value.trim() || null;
    }

    /**
     * Rebuilds the active-filters chip bar and updates the badge count.
     * Drop-in replacement for the original updateActiveFilterCount().
     */
    function updateActiveFilterCount() {
        let count = 0;
        const $chips = $('#filterChipsContainer').empty();

        // — One chip per active filter field —
        filterMeta.forEach(function (f) {
            const val = getReadableValue(f.id);
            if (!val) return;
            count++;

            $chips.append(
                $('<span class="filter-chip"></span>').append(
                    $('<span class="chip-label"></span>').html(
                        f.label + ': <strong>' + $('<span>').text(val).html() + '</strong>'
                    ),
                    $('<span class="chip-remove" title="Remove filter">&times;</span>')
                        .attr('data-filter-id', f.id)
                )
            );
        });

        // — Chip for the active status tab (if not "All") —
        if (activeStatus) {
            count++;
            // Grab tab label text without the count badge text
            const $tab   = $('.status-tab[data-status="' + activeStatus + '"]');
            const tabLabel = $tab.contents()
                .filter(function () { return this.nodeType === 3; }) // text nodes only
                .first().text().trim()
                || $tab.text().replace(/\d+/g, '').trim(); // fallback

            $chips.append(
                $('<span class="filter-chip" style="border-color:#f97316;color:#c2410c;"></span>').append(
                    $('<span class="chip-label"></span>').html(
                        'Status: <strong>' + $('<span>').text(tabLabel).html() + '</strong>'
                    ),
                    $('<span class="chip-remove" title="Remove filter">&times;</span>')
                        .attr('data-filter-id', '__status__')
                )
            );
        }

        // — Badge on the filter card header —
        const $badge = $('#activeFilterCount');
        if (count > 0) {
            $badge.text(count).show();
        } else {
            $badge.hide();
        }

        // — Show/hide the sticky bar —
        $('#activeFiltersBar').toggle(count > 0);
    }

    // ── Individual chip × removal ─────────────────────────────────────
    $(document).on('click', '.chip-remove', function () {
        const id = $(this).data('filter-id');

        if (id === '__status__') {
            // Reset status tab to "All"
            activeStatus = '';
            $('.status-tab').removeClass('active');
            $('.status-tab[data-status=""]').addClass('active');
            updateActiveFilterCount();
            loadLeads(1);
            return;
        }

        const meta = filterMeta.find(f => f.id === id);
        if (meta) {
            meta.clear();            // clear the field
            updateActiveFilterCount();
            loadLeads(1);
        }
    });

    // ── "Clear All" button ────────────────────────────────────────────
    $('#clearAllFiltersBtn').on('click', function () {
        $('#resetBtn').trigger('click');  // reuses your existing full-reset logic
    });


    // ── COLLECT FILTER PARAMS ────────────────────────────────────────
    // ✅ Always include _json=1, page, per_page
    function getFilterParams(page) {
        const pp = parseInt($('#perPageSelect').val(), 10);
        const institutionType = $('#filterInstitutionType').val() || 'school'; // ✅ never send empty

        return {
            _json: 1,

            search:             $('#searchInput').val()             || '',
            interest_level:     $('#filterInterestLevel').val()    || '',
            institution_type: $('#filterInstitutionType').val() || '',
            school_department:  $('#filterSchoolDepartment').val() || '',
            college_department: $('#filterCollegeDepartment').val()|| '',
            programme_id:       $('#filterProgramme').val()        || '',
            course_id:          $('#filterCourse').val()           || '',
            state:              $('#filterState').val()            || '',
            district:           $('#filterDistrict').val()         || '',
            preferred_state:    $('#filterPreferredState').val()   || '',
            agent_name:         $('#filterAgentName').val()        || '',
            lead_source_id:     $('#filterSource').val()           || '',
            assigned_to:        $('#filterAssignedTo').val()       || '',
            branch_id:          $('#filterBranch').val()           || '',
            date_from:          $('#dateFrom').val()               || '',
            date_to:            $('#dateTo').val()                 || '',
            call_status:      $('#filterCallStatus').val(),
            counseling_stage: $('#filterCounselingStage').val(),
            followup_count:   $('#filterFollowupCount').val(),

            final_status:       activeStatus,
            sort_column:        currentSort.column,
            sort_direction:     currentSort.direction,

            per_page:           Number.isFinite(pp) ? pp : 15,
            page:               parseInt(page, 10) || 1,
        };
    }

    // ✅ backend is source of truth for per_page: sync UI after each response
    function syncPerPageUi(perPageFromBackend) {
        const pp = parseInt(perPageFromBackend, 10);
        if (!pp) return;
        if ($('#perPageSelect option[value="'+pp+'"]').length === 0) {
            $('#perPageSelect').append(`<option value="${pp}">${pp}</option>`);
        }
        // ✅ Use prop approach to set without triggering change
        $('#perPageSelect')[0].value = String(pp);
    }

    // ── PRE-POPULATE FILTERS FROM URL ────────────────────────────────
    function prePopulateFilters() {
        const p = new URLSearchParams(window.location.search);
        const map = {
            search:             '#searchInput',
            interest_level:     '#filterInterestLevel',
            institution_type:   '#filterInstitutionType',
            school_department:  '#filterSchoolDepartment',
            college_department: '#filterCollegeDepartment',
            programme_id:       '#filterProgramme',
            course_id:          '#filterCourse',
            state:              '#filterState',
            district:           '#filterDistrict',
            preferred_state:    '#filterPreferredState',
            agent_name:         '#filterAgentName',
            lead_source_id:     '#filterSource',
            assigned_to:        '#filterAssignedTo',
            branch_id:          '#filterBranch',
            date_from:          '#dateFrom',
            date_to:            '#dateTo',
            per_page:           '#perPageSelect',
        };

        // silent .val() only
        $.each(map, function (param, selector) {
            const val = p.get(param);
            if (val) $(selector).val(val);
        });

        const preState    = p.get('state');
        const preDistrict = p.get('district');
        if (preState) populateFilterDistricts(preState, preDistrict || '');

        if (p.get('programme_id')) cascadeCourses();

        if (p.get('sort_column'))    currentSort.column    = p.get('sort_column');
        if (p.get('sort_direction')) currentSort.direction = p.get('sort_direction');
        if (currentSort.column) $(`.sortable[data-column="${currentSort.column}"]`).addClass(currentSort.direction);

        if (p.get('final_status')) {
            activeStatus = p.get('final_status');
            $('.status-tab').removeClass('active');
            $(`.status-tab[data-status="${activeStatus}"]`).addClass('active');
        }

        if (p.get('page')) currentPage = parseInt(p.get('page'), 10) || 1;

        applyInstitutionTypeUi(p.get('institution_type') || '', true);
        updateActiveFilterCount();

        const hasUrlFilters = [...p.values()].some(v => v.trim() !== '');
        if (hasUrlFilters) {
            document.getElementById('filterBody').style.display = 'block';
            document.getElementById('filterToggleIcon').classList.add('open');
        }

        initialized = true;
    }

    // ── LOAD LEADS AJAX ──────────────────────────────────────────────
    function loadLeads(page) {
        if (!initialized) return;

        const targetPage = parseInt(page, 10) > 0 ? parseInt(page, 10) : 1;
        currentPage = targetPage;

        // abort in-flight to prevent “flash then revert”
        if (activeXhr && activeXhr.readyState !== 4) activeXhr.abort();

        const seq = ++requestSeq;
        const params = getFilterParams(targetPage);

        $('#leadsTable').addClass('table-loading');

        activeXhr = $.ajax({
            url: "{{ route('edu-leads.index') }}",
            type: 'GET',
            data: params,
            dataType: 'json',
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                if (seq !== requestSeq) return;

                if (!res || res.success !== true) {
                    $('#leadsTable').removeClass('table-loading');
                    console.error('Bad response', res);
                    return;
                }

                // backend per_page wins
                syncPerPageUi(res.per_page);

                // clamp invalid page -> last_page (you should return last_page from backend)
                const lastPage = parseInt(res.last_page, 10) || 1;
                if (targetPage > lastPage && lastPage > 0) {
                    return loadLeads(lastPage);
                }

                $('#leadsTableBody').html(res.html || '');
                $('#paginationContainer').html(res.pagination || '');

                const total = parseInt(res.total, 10) || 0;
                const from  = parseInt(res.from, 10)  || 0;
                const to    = parseInt(res.to, 10)    || 0;

                $('#leadCount').text(total);
                $('#pageInfo').text(total > 0 ? `${from}–${to} of ${total}` : '0–0 of 0');

                if (res.status_counts) {
                    $('#tc-all').text(res.status_counts.all ?? 0);
                    $.each(res.status_counts, function (k, v) {
                        if (k !== 'all') $(`#tc-${k}`).text(v);
                    });
                }
                if (res.institution_counts) {
                    $('#ic-school').text(res.institution_counts.school  ?? 0);
                    $('#ic-college').text(res.institution_counts.college ?? 0);
                }

                $('#leadsTable').removeClass('table-loading');
                $('#selectAll').prop('checked', false);
                updateBulkBtn();
            },
            error: function (xhr, status) {
                if (seq !== requestSeq) return;
                $('#leadsTable').removeClass('table-loading');
                if (status === 'abort') return;
                console.error(xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load leads.' });
            }
        });
    }

    // ── EXPORT ───────────────────────────────────────────────────────
    window.exportWithFilters = function () {
        const params = new URLSearchParams();
        const data = getFilterParams(1);
        $.each(data, function (k, v) {
            if (v !== null && v !== undefined && String(v) !== '') params.set(k, v);
        });
        // you may want to remove _json for export
        params.delete('_json');
        params.delete('page');
        window.location.href = "{{ route('edu-leads.export') }}?" + params.toString();
    };

    // ── FILTER CONTROLS ──────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () { updateActiveFilterCount(); loadLeads(1); });

    $('#resetBtn').on('click', function () {
        initialized = false;

        $('#filterForm')[0].reset();
        $('#filterInstitutionType').val('');

        // reset select2 fields
        $('#filterState, #filterPreferredState, #filterProgramme, #filterSource, #filterAssignedTo, #filterBranch')
            .val(null).trigger('change');

        populateFilterDistricts('', '');
        cascadeCourses();
        applyInstitutionTypeUi('school', true);

        activeStatus = '';
        currentPage  = 1;
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
        searchTimer = setTimeout(function () { updateActiveFilterCount(); loadLeads(1); }, 450);
    });

    // Agent name – debounced
    let agentTimer;
    $('#filterAgentName').on('keyup', function () {
        clearTimeout(agentTimer);
        agentTimer = setTimeout(function () { updateActiveFilterCount(); loadLeads(1); }, 450);
    });

    // Instant-change selects (excluding programme/state handled separately above)
    $('#filterPreferredState, #filterCourse, #filterSource, #filterAssignedTo, #filterBranch,' +
      '#filterSchoolDepartment, #filterCollegeDepartment, #filterDistrict, #filterInterestLevel'
    ).on('change', function () { updateActiveFilterCount(); if (initialized) loadLeads(1); });

    $('#dateFrom, #dateTo').on('change', function () { updateActiveFilterCount(); if (initialized) loadLeads(1); });

    // per_page -> page 1
    $('#perPageSelect').on('change', function () { if (initialized) loadLeads(1); });

    // ── PAGINATION ───────────────────────────────────────────────────
    $(document).on('click', '#paginationContainer .pagination a', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (!href) return;

        // extract only page number
        let pageNum = 1;
        try { pageNum = parseInt(new URL(href, window.location.origin).searchParams.get('page'), 10) || 1; }
        catch (e) { pageNum = 1; }

        loadLeads(pageNum);
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
        loadLeads(1);
    });

    // ── QUICK STATUS TABS ─────────────────────────────────────────────
    $(document).on('click', '.status-tab', function () {
        $('.status-tab').removeClass('active');
        $(this).addClass('active');
        activeStatus = $(this).data('status');
        loadLeads(1);
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

    @if(auth()->user()->canAssignLeads())

    // ── Telecaller pool — keyed by branch_id ────────────────────────
    const telecallersByBranch = {};
    @foreach($assignableUsers as $u)
        (telecallersByBranch['{{ $u->branch_id }}'] ??= []).push({
            id:     {{ $u->id }},
            name:   '{{ addslashes($u->name) }}',
            branch: '{{ addslashes($u->branch?->name ?? '') }}',
        });
    @endforeach

    // ── SINGLE ASSIGN ────────────────────────────────────────────────
    $(document).on('click', '.assignLeadBtn', function () {
        const btn        = $(this);
        const branchId   = btn.data('branch-id');
        const branchName = btn.data('branch-name') || 'this branch';

        $('#assignLeadId').val(btn.data('id'));
        $('#assignLeadBranch').val(branchId);
        $('#assignLeadCode').text(btn.data('code'));
        $('#assignLeadName').text(btn.data('name'));
        $('#assignLeadBranchLabel').text(branchName);
        $('#assignBranchNotice').text(branchName);
        $('#assignBranchEmptyName').text(branchName);
        $('#assignNotes').val('');

        const $select = $('#assignTelecaller');
        const options = telecallersByBranch[branchId] || [];

        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.empty().append('<option value="">Choose telecaller...</option>');

        if (options.length === 0) {
            $('#assignTelecallerEmpty').show();
            $('#assignSubmitBtn').prop('disabled', true);
        } else {
            $('#assignTelecallerEmpty').hide();
            $('#assignSubmitBtn').prop('disabled', false);
            options.forEach(function (u) {
                $select.append(new Option(`${u.name} — ${u.branch}`, u.id));
            });
        }

        $select.select2({
            theme: 'bootstrap-5',
            placeholder: 'Choose telecaller...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#assignLeadModal')
        });

        const assignee = btn.data('assignee');
        $('#currentAssigneeLabel').text(assignee);
        $('#currentAssignmentBlock').toggle(!!assignee);

        $('#assignLeadModal').modal('show');
    });

    $('#assignLeadForm').on('submit', function (e) {
        e.preventDefault();

        const telecaller = $('#assignTelecaller').val();
        if (!telecaller) {
            Swal.fire({ icon: 'warning', title: 'Select Telecaller', text: 'Please choose a telecaller.' });
            return;
        }

        const $btn  = $(this).find('[type=submit]');
        const orig  = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Assigning...');

        $.ajax({
            url:  `/edu-leads/${$('#assignLeadId').val()}/assign`,
            type: 'POST',
            data: { assigned_to: telecaller, notes: $('#assignNotes').val() },
            success: function (res) {
                $('#assignLeadModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Assigned!',
                    html: `Lead assigned to <strong>${res.telecaller_name}</strong>`,
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => loadLeads(currentPage));
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html(orig);
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
        $('#bulkBranchMatchInfo').hide();

        const list     = $('#selectedLeadsList').empty();
        const branches = new Set();

        if (!selected.length) {
            list.html('<p class="text-muted text-center py-2 mb-0"><small>No leads selected</small></p>');
        } else {
            selected.forEach(function (id) {
                const row        = $(`input.lead-checkbox[value="${id}"]`).closest('tr');
                const code       = row.find('[data-label="code"]').text().trim() || row.find('td').eq(1).text().trim();
                const name       = row.find('.lead-name-link').text().trim();
                const assignee   = row.find('.assigned-to-name').text().trim();
                const branchName = row.find('.lead-branch-name').text().trim();
                const branchId   = row.find('input.lead-checkbox').data('branch-id');

                if (branchId) branches.add(String(branchId));

                list.append(`
                    <div class="bulk-lead-item d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-secondary">${code}</span>
                        <span class="fw-semibold">${name}</span>
                        ${branchName ? `<span class="badge bg-soft-primary text-primary border border-primary ms-1">${branchName}</span>` : ''}
                        ${assignee  ? `<span class="text-muted small ms-auto">${assignee}</span>` : ''}
                    </div>
                `);
            });
        }

        if (branches.size > 1) $('#bulkBranchWarning').show();
        else                   $('#bulkBranchWarning').hide();

        $('#bulkAssignModal').modal('show');
    });

    // Live branch match preview
    $('#bulkTelecaller').on('change', function () {
        const selected         = getSelectedIds();
        const telecallerBranch = String($(this).find(':selected').data('branch') || '');
        const branchName       = $(this).find(':selected').data('branch-name') || '';
        const $info            = $('#bulkBranchMatchInfo');

        if (!$(this).val() || !selected.length) { $info.hide(); return; }

        let match = 0, skip = 0;
        selected.forEach(function (id) {
            const row        = $(`input.lead-checkbox[value="${id}"]`).closest('tr');
            const leadBranch = String(row.find('input.lead-checkbox').data('branch-id') || '');
            (leadBranch === telecallerBranch) ? match++ : skip++;
        });

        if (skip === 0) {
            $info.html(`<span class="text-success"><i class="las la-check-circle me-1"></i>
                All <strong>${match}</strong> selected leads are in <strong>${branchName}</strong>. Ready to assign.</span>`).show();
        } else if (match === 0) {
            $info.html(`<span class="text-danger"><i class="las la-times-circle me-1"></i>
                None of the selected leads belong to <strong>${branchName}</strong>. All will be skipped.</span>`).show();
        } else {
            $info.html(`<span class="text-warning"><i class="las la-exclamation-triangle me-1"></i>
                <strong>${match}</strong> lead(s) will be assigned, <strong>${skip}</strong> will be skipped
                (not in <strong>${branchName}</strong>).</span>`).show();
        }
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
                let html = `<strong>${res.count}</strong> lead(s) assigned to <strong>${res.telecaller_name}</strong>.`;
                if (res.skipped > 0) html += `<br><small class="text-muted">${res.skipped} skipped (branch mismatch).</small>`;

                Swal.fire({ icon: 'success', title: 'Done!', html, timer: 3500 })
                    .then(function () {
                        $('.lead-checkbox, #selectAll').prop('checked', false);
                        updateBulkBtn();
                        loadLeads(currentPage);
                    });
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
            icon:  'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url:  `/edu-leads/${id}`,
                type: 'DELETE',
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => loadLeads(1));
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Could not delete lead.' });
                }
            });
        });
    });

    // ── INITIALISE ────────────────────────────────────────────────────
    prePopulateFilters();
    loadLeads(currentPage);

});
</script>
@endsection
