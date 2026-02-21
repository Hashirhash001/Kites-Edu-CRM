@extends('layouts.app')

@section('title', 'Lead Details - ' . $eduLead->name)

@section('extra-css')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Professional CRM Color Palette */
    :root {
        --primary-blue: #667eea;
        --primary-blue-dark: #5a67d8;
        --secondary-gray: #64748b;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --light-bg: #f8fafc;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
    }

    .lead-detail-container {
        background: var(--light-bg);
        min-height: calc(100vh - 100px);
        padding: 1.5rem 0;
    }

    /* Header Card - Professional Gradient */
    .lead-header-card {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 2rem;
        color: #fff;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        margin-bottom: 1.5rem;
    }

    .lead-header-card h2 {
        font-weight: 700;
        margin: 0;
    }

    .lead-status-badge {
        padding: 0.5rem 1.2rem;
        border-radius: 6px;
        font-weight: 600;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    /* Education Details Card - Teal Gradient */
    .education-card {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        border-radius: 12px;
        padding: 1.8rem;
        color: #fff;
        box-shadow: 0 4px 12px rgba(8, 145, 178, 0.2);
        margin-bottom: 1.5rem;
    }

    .education-card h5 {
        color: #fff;
        font-weight: 700;
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
    }

    .education-card h5 i {
        margin-right: 0.5rem;
        font-size: 1.4rem;
    }

    .edu-row {
        display: flex;
        justify-content: space-between;
        padding: 0.9rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.15);
        align-items: center;
    }

    .edu-row:last-of-type {
        border-bottom: none;
    }

    .edu-label {
        font-weight: 500;
        font-size: 0.95rem;
        opacity: 0.95;
    }

    .edu-value {
        font-weight: 700;
        font-size: 1.05rem;
        text-align: right;
    }

    .interest-badge {
        padding: 0.4rem 1rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .interest-hot {
        background: #fee2e2;
        color: #dc2626;
    }

    .interest-warm {
        background: #fef3c7;
        color: #d97706;
    }

    .interest-cold {
        background: #dbeafe;
        color: #2563eb;
    }

    /* Info Cards - Clean White */
    .info-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }

    .info-card-header {
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1.1rem;
        display: flex;
        align-items: center;
    }

    .info-card-header h5 i {
        color: var(--primary-blue);
        margin-right: 0.5rem;
        font-size: 1.3rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: center;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .info-value {
        color: var(--text-primary);
        font-weight: 600;
        text-align: right;
        font-size: 0.95rem;
    }

    /* Buttons */
    .action-button {
        border-radius: 8px;
        padding: 0.50rem 1rem;
        font-weight: 600;
        border: none;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .action-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Followup Timeline */
    .followup-item {
        position: relative;
        padding: 1.2rem;
        background: var(--card-bg);
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 3px solid var(--primary-blue);
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        border-left: 3px solid var(--primary-blue);
    }

    .followup-item.overdue {
        border-left-color: var(--danger-red);
        background: #fef2f2;
    }

    .followup-item.completed {
        border-left-color: var(--success-green);
        background: #f0fdf4;
        opacity: 0.85;
    }

    .followup-item.today {
        border-left-color: var(--warning-orange);
        background: #fffbeb;
    }

    /* Call Logs & Notes */
    .call-log-item, .note-item {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 3px solid var(--primary-blue);
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
    }

    .note-item {
        background: #fffbeb;
        border-left-color: var(--warning-orange);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 4rem;
        opacity: 0.2;
        margin-bottom: 1rem;
    }

    /* Badge Colors */
    .badge {
        border-radius: 6px;
        font-weight: 600;
        /* padding: 0.4rem 0.9rem; */
    }

    .badge.bg-success { background: var(--success-green) !important; }
    .badge.bg-primary { background: var(--primary-blue) !important; }
    .badge.bg-warning { background: var(--warning-orange) !important; color: #fff !important; }
    .badge.bg-danger { background: var(--danger-red) !important; }
    .badge.bg-info { background: #0891b2 !important; }

    /* Delete buttons */
    .deleteFollowup, .deleteCall, .deleteNote {
        padding: 0.25rem 0.5rem;
        font-size: 0.85rem;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .deleteFollowup:hover, .deleteCall:hover, .deleteNote:hover {
        opacity: 1;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .lead-header-card { padding: 1.5rem; }
        .education-card { padding: 1.3rem; }
        .info-card { padding: 1.2rem; }
    }

    /* Status container */
    .status-container {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    /* Colored Status Badge - Professional */
    .lead-status-badge {
        padding: 0.4rem 0.9rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.875rem;
        text-transform: capitalize;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid;
        color: white;
        transition: all 0.3s ease;
    }

    /* Status-specific colors */
    .lead-status-badge.status-pending {
        background: linear-gradient(135deg, #eab308 0%, #fbbf24 100%);
        border-color: #d97706;
    }

    .lead-status-badge.status-contacted {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        border-color: #2563eb;
    }

    .lead-status-badge.status-not_interested {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        border-color: #dc2626;
    }

    .lead-status-badge.status-follow_up {
        background: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
        border-color: #ea580c;
    }

    .lead-status-badge.status-admitted {
        background: linear-gradient(135deg, #10b981 0%, #4ade80 100%);
        border-color: #059669;
    }

    .lead-status-badge.status-dropped {
        background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
        border-color: #4b5563;
    }

    /* Status indicator dot */
    .status-indicator {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: white;
        opacity: 0.9;
    }

    /* Dropdown - Hidden by default */
    #statusDropdown {
        background: rgba(255, 255, 255, 0.15) !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        padding: 0.4rem 2rem 0.4rem 0.75rem !important;
        border-radius: 6px !important;
        cursor: pointer;
        transition: all 0.2s ease;
        text-transform: capitalize;
        min-width: 160px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='rgba(255,255,255,0.8)' d='M5 7L1 3h8z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.6rem center !important;
        background-size: 10px !important;
    }

    #statusDropdown:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }

    #statusDropdown:focus {
        outline: none;
        background-color: rgba(255, 255, 255, 0.25) !important;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
    }

    #statusDropdown option {
        background: #ffffff;
        color: #1e293b;
        padding: 8px 12px;
        font-weight: 500;
    }

    #statusDropdown:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    #statusDropdown:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.25) !important;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
    }

    #statusDropdown option {
        background: #ffffff;
        color: #1e293b;
        padding: 8px 12px;
        font-weight: 500;
    }

    /* Edit icon button */
    .status-edit-btn {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.4rem 0.6rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .status-edit-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-1px);
    }

    .status-edit-btn i {
        font-size: 0.95rem;
    }

    /* Hidden class */
    .hidden {
        display: none !important;
    }

</style>
@endsection

@section('content')
<div class="lead-detail-container">
    <div class="container-fluid">

        @php
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $finalStatusLabels = [
                'pending'        => ['label' => '⏳ Pending',        'class' => 'status-pending'],
                'contacted'      => ['label' => '📞 Contacted',      'class' => 'status-contacted'],
                'follow_up'      => ['label' => '🔔 Follow Up',      'class' => 'status-follow_up'],
                'admitted'       => ['label' => '✅ Admitted',        'class' => 'status-admitted'],
                'not_interested' => ['label' => '❌ Not Interested',  'class' => 'status-not_interested'],
                'dropped'        => ['label' => '🚫 Dropped',         'class' => 'status-dropped'],
            ];

            $canEdit = $user->isSuperAdmin()
                || $user->isOperationHead()
                || ($user->isLeadManager() && $eduLead->branch_id === $user->branch_id);

            $canChangeStatus = $canEdit || $eduLead->assigned_to === $user->id;
        @endphp

        {{-- ── Lead Header ──────────────────────────────────────── --}}
        <div class="lead-header-card">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h2 class="mb-0">{{ $eduLead->name }}</h2>

                        {{-- Institution type badge --}}
                        {!! $eduLead->institution_type_badge !!}

                        {{-- Editable final status --}}
                        <div class="status-container d-flex align-items-center gap-1">
                            @if($canChangeStatus)
                                <div class="lead-status-badge {{ $finalStatusLabels[$eduLead->final_status]['class'] ?? 'status-pending' }}"
                                     id="statusBadge">
                                    <span class="status-indicator"></span>
                                    <span id="statusText">
                                        {{ $finalStatusLabels[$eduLead->final_status]['label'] ?? ucfirst($eduLead->final_status) }}
                                    </span>
                                </div>

                                <select class="form-select form-select-sm d-none" id="statusDropdown" style="width:auto;">
                                    @foreach($finalStatusLabels as $val => $meta)
                                        <option value="{{ $val }}"
                                            {{ $eduLead->final_status === $val ? 'selected' : '' }}>
                                            {{ $meta['label'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="button" class="status-edit-btn" id="statusEditBtn" title="Change Status">
                                    <i class="las la-pen"></i>
                                </button>
                            @else
                                <div class="lead-status-badge {{ $finalStatusLabels[$eduLead->final_status]['class'] ?? 'status-pending' }}">
                                    <span class="status-indicator"></span>
                                    {{ $finalStatusLabels[$eduLead->final_status]['label'] ?? ucfirst($eduLead->final_status) }}
                                </div>
                            @endif
                        </div>

                        {{-- Interest badge --}}
                        @if($eduLead->interest_level)
                            {!! $eduLead->interest_level_badge !!}
                        @endif
                    </div>

                    <p class="mb-0" style="opacity:.9; font-weight:500;">
                        <i class="las la-tag me-1"></i>{{ $eduLead->lead_code }}
                        <span class="ms-3">
                            <i class="las la-calendar me-1"></i>{{ $eduLead->created_at->format('d M Y') }}
                        </span>
                        @if($eduLead->branch)
                        <span class="ms-3">
                            <i class="las la-code-branch me-1"></i>{{ $eduLead->branch->name }}
                        </span>
                        @endif
                    </p>
                </div>

                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('edu-leads.index') }}" class="btn btn-light action-button me-2">
                        <i class="las la-arrow-left me-1"></i> Back
                    </a>

                    @if($canEdit)
                    <a href="{{ route('edu-leads.edit', $eduLead->id) }}"
                       class="btn btn-primary action-button me-2">
                        <i class="las la-edit me-1"></i> Edit
                    </a>
                    @endif

                    @if($user->canDelete())
                    <button type="button" class="btn btn-danger action-button"
                            id="deleteLeadBtn"
                            data-id="{{ $eduLead->id }}"
                            data-name="{{ $eduLead->name }}">
                        <i class="las la-trash me-1"></i> Delete
                    </button>
                    @endif
                </div>
            </div>
        </div>


        <div class="row">

            {{-- ══════════════════════════════
                 LEFT COLUMN
            ══════════════════════════════ --}}
            <div class="col-lg-4">

                {{-- ── CURRENT INSTITUTION ────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-school me-2"></i>Current Institution</h5>
                    </div>

                    @if(!$eduLead->institution_type)
                        <div class="empty-state py-3">
                            <i class="las la-school" style="font-size:1.5rem;"></i>
                            <p class="mb-0 small text-muted mt-1">No institution details recorded</p>
                        </div>
                    @else

                        <div class="info-row">
                            <span class="info-label">Type</span>
                            <span class="info-value">{!! $eduLead->institution_type_badge !!}</span>
                        </div>

                        @if($eduLead->institution_type === 'school')

                            @if($eduLead->school)
                            <div class="info-row">
                                <span class="info-label">School Name</span>
                                <span class="info-value fw-semibold">{{ $eduLead->school }}</span>
                            </div>
                            @endif

                            @if($eduLead->school_department)
                            <div class="info-row">
                                <span class="info-label">Stream</span>
                                <span class="info-value">
                                    <span class="badge bg-light text-dark border">
                                        <i class="las la-stream me-1"></i>{{ $eduLead->school_department }}
                                    </span>
                                </span>
                            </div>
                            @endif

                            @if($eduLead->current_year)
                            <div class="info-row">
                                <span class="info-label">Current Year</span>
                                <span class="info-value">
                                    <span class="badge bg-light text-dark border">{{ $eduLead->current_year }}</span>
                                </span>
                            </div>
                            @endif

                        @elseif($eduLead->institution_type === 'college')

                            @if($eduLead->college)
                            <div class="info-row">
                                <span class="info-label">College Name</span>
                                <span class="info-value fw-semibold">{{ $eduLead->college }}</span>
                            </div>
                            @endif

                            @if($eduLead->college_department)
                            <div class="info-row">
                                <span class="info-label">Department</span>
                                <span class="info-value">
                                    <span class="badge bg-light text-dark border">
                                        <i class="las la-building me-1"></i>{{ $eduLead->college_department }}
                                    </span>
                                </span>
                            </div>
                            @endif

                            @if($eduLead->current_year)
                            <div class="info-row">
                                <span class="info-label">Current Year</span>
                                <span class="info-value">
                                    <span class="badge bg-light text-dark border">{{ $eduLead->current_year }}</span>
                                </span>
                            </div>
                            @endif

                        @else
                            {{-- Other institution type --}}
                            @if($eduLead->school)
                            <div class="info-row">
                                <span class="info-label">Institution</span>
                                <span class="info-value fw-semibold">{{ $eduLead->school }}</span>
                            </div>
                            @endif
                        @endif

                    @endif
                </div>

                {{-- ── STUDY INTEREST ──────────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-graduation-cap me-2"></i>Study Interest</h5>
                    </div>

                    @if($eduLead->country)
                    <div class="info-row">
                        <span class="info-label">Destination</span>
                        <span class="info-value">
                            <span class="badge bg-primary">
                                <i class="las la-globe me-1"></i>{{ $eduLead->country }}
                            </span>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->course?->programme)
                    <div class="info-row">
                        <span class="info-label">Programme</span>
                        <span class="info-value">
                            <span class="badge bg-info p-2">
                                <i class="las la-layer-group me-1"></i>
                                {{ $eduLead->course->programme->name }}
                            </span>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->course)
                    <div class="info-row">
                        <span class="info-label">Course</span>
                        <span class="info-value fw-semibold text-primary">
                            <i class="las la-book-open me-1"></i>{{ $eduLead->course->name }}
                        </span>
                    </div>
                    @endif

                    @if($eduLead->course_interested)
                    <div class="info-row">
                        <span class="info-label">
                            Course Interest
                            <br><small class="text-muted fw-normal">(free text)</small>
                        </span>
                        <span class="info-value fst-italic text-muted">
                            <i class="las la-pen me-1"></i>{{ $eduLead->course_interested }}
                        </span>
                    </div>
                    @endif

                    @if($eduLead->preferred_intake)
                    <div class="info-row">
                        <span class="info-label">Preferred Intake</span>
                        <span class="info-value">
                            <span class="badge bg-light text-dark border">
                                <i class="las la-calendar me-1"></i>{{ $eduLead->preferred_intake }}
                            </span>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->budget)
                    <div class="info-row">
                        <span class="info-label">Budget</span>
                        <span class="info-value fw-semibold">
                            <i class="las la-money-bill me-1 text-success"></i>{{ $eduLead->budget }}
                        </span>
                    </div>
                    @endif

                    @if(!$eduLead->country && !$eduLead->course && !$eduLead->course_interested)
                    <div class="empty-state py-3">
                        <i class="las la-search" style="font-size:1.5rem;"></i>
                        <p class="mb-0 small text-muted mt-1">No study interest recorded</p>
                    </div>
                    @endif

                    @if($eduLead->interest_level)
                    <div class="info-row mt-2 pt-2" style="border-top:1px dashed #e2e8f0;">
                        <span class="info-label">Interest Level</span>
                        <span class="info-value">
                            <span class="interest-badge interest-{{ $eduLead->interest_level }}">
                                @if($eduLead->interest_level === 'hot') 🔥
                                @elseif($eduLead->interest_level === 'warm') ☀️
                                @else ❄️ @endif
                                {{ ucfirst($eduLead->interest_level) }}
                            </span>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->admitted_at)
                    <div class="info-row">
                        <span class="info-label">Admitted On</span>
                        <span class="info-value text-success fw-semibold">
                            <i class="las la-check-circle me-1"></i>
                            {{ $eduLead->admitted_at->format('d M Y') }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- ── CONTACT INFORMATION ─────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-user me-2"></i>Contact Information</h5>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value">
                            <a href="tel:{{ $eduLead->phone }}" class="text-decoration-none">
                                <i class="las la-phone me-1 text-success"></i>{{ $eduLead->phone }}
                            </a>
                        </span>
                    </div>

                    @if($eduLead->whatsapp_number)
                    <div class="info-row">
                        <span class="info-label">WhatsApp</span>
                        <span class="info-value">
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $eduLead->whatsapp_number) }}"
                               target="_blank" class="text-success text-decoration-none">
                                <i class="lab la-whatsapp me-1"></i>{{ $eduLead->whatsapp_number }}
                            </a>
                        </span>
                    </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">
                            @if($eduLead->email)
                                <a href="mailto:{{ $eduLead->email }}" class="text-decoration-none">
                                    <i class="las la-envelope me-1 text-muted"></i>{{ $eduLead->email }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </div>

                    @if($eduLead->city || $eduLead->state)
                    <div class="info-row">
                        <span class="info-label">Location</span>
                        <span class="info-value">
                            <i class="las la-map-marker me-1 text-muted"></i>
                            {{ implode(', ', array_filter([$eduLead->city, $eduLead->state])) }}
                        </span>
                    </div>
                    @endif

                    @if($eduLead->address)
                    <div class="info-row" style="flex-direction:column; align-items:flex-start;">
                        <span class="info-label mb-1">Address</span>
                        <span class="info-value text-muted small">{{ $eduLead->address }}</span>
                    </div>
                    @endif
                </div>

                {{-- ── LEAD CRM DETAILS ────────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-info-circle me-2"></i>Lead Details</h5>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Source</span>
                        <span class="info-value">{{ $eduLead->leadSource->name ?? '—' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Call Status</span>
                        <span class="info-value">{!! $eduLead->status_badge !!}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Branch</span>
                        <span class="info-value">
                            <i class="las la-building me-1 text-muted"></i>{{ $eduLead->branch->name ?? '—' }}
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Created By</span>
                        <span class="info-value">{{ $eduLead->createdBy->name ?? '—' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Created At</span>
                        <span class="info-value text-muted">
                            <i class="las la-calendar me-1"></i>{{ $eduLead->created_at->format('d M Y, h:i A') }}
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Assigned To</span>
                        <span class="info-value">
                            @if($eduLead->assignedTo)
                                <span class="badge bg-secondary">{{ $eduLead->assignedTo->name }}</span>
                                @if($eduLead->assignedTo->branch)
                                    <small class="text-muted ms-1">— {{ $eduLead->assignedTo->branch->name }}</small>
                                @endif
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </span>
                    </div>

                    @if($eduLead->followup_date)
                    <div class="info-row">
                        <span class="info-label">Next Follow-up</span>
                        <span class="info-value">
                            <span class="badge {{ $eduLead->followup_date->isPast()
                                ? 'bg-danger'
                                : ($eduLead->followup_date->isToday()
                                    ? 'bg-warning text-dark'
                                    : 'bg-info text-dark') }}">
                                <i class="las la-calendar me-1"></i>
                                {{ $eduLead->followup_date->format('d M Y') }}
                            </span>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->next_action)
                    <div class="info-row">
                        <span class="info-label">Next Action</span>
                        <span class="info-value">{{ $eduLead->next_action }}</span>
                    </div>
                    @endif

                    @if($eduLead->remarks)
                    <div class="info-row" style="flex-direction:column; align-items:flex-start;">
                        <span class="info-label mb-1">Remarks</span>
                        <span class="info-value">{{ $eduLead->remarks }}</span>
                    </div>
                    @endif

                    @if($eduLead->description)
                    <div class="info-row" style="flex-direction:column; align-items:flex-start;">
                        <span class="info-label mb-1">Description</span>
                        <span class="info-value">{{ $eduLead->description }}</span>
                    </div>
                    @endif
                </div>

                {{-- ── STATUS HISTORY ──────────────────────────────────── --}}
                @if($eduLead->statusHistory && $eduLead->statusHistory->count() > 0)
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-history me-2"></i>Status History</h5>
                    </div>
                    @foreach($eduLead->statusHistory->sortByDesc('created_at') as $history)
                    <div class="info-row" style="flex-direction:column; align-items:flex-start; gap:2px;">
                        <div class="d-flex justify-content-between w-100">
                            <small class="fw-semibold">{{ $history->user->name ?? '—' }}</small>
                            <small class="text-muted">{{ $history->created_at->format('d M Y, h:i A') }}</small>
                        </div>
                        @if($history->old_status !== $history->new_status)
                        <small class="text-muted">
                            Status:
                            <span class="text-danger">{{ ucfirst(str_replace('_', ' ', $history->old_status ?? '—')) }}</span>
                            → <span class="text-success">{{ ucfirst(str_replace('_', ' ', $history->new_status ?? '—')) }}</span>
                        </small>
                        @endif
                        @if($history->old_interest_level !== $history->new_interest_level)
                        <small class="text-muted">
                            Interest:
                            <span class="text-danger">{{ ucfirst($history->old_interest_level ?? '—') }}</span>
                            → <span class="text-success">{{ ucfirst($history->new_interest_level ?? '—') }}</span>
                        </small>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

            </div>{{-- /col-lg-4 --}}


            {{-- ══════════════════════════════
                 RIGHT COLUMN
            ══════════════════════════════ --}}
            <div class="col-lg-8">

                {{-- ── SCHEDULED FOLLOWUPS ────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-calendar-check me-2"></i>Scheduled Followups</h5>
                            <button class="btn btn-sm btn-primary action-button"
                                    data-bs-toggle="modal" data-bs-target="#addFollowupModal">
                                <i class="las la-plus me-1"></i>Add Followup
                            </button>
                        </div>
                    </div>

                    <div id="followupsContainer">
                        @forelse($eduLead->followups as $followup)
                            <div class="followup-item
                                {{ $followup->status === 'completed' ? 'completed' : '' }}
                                {{ $followup->followup_date->isToday() && $followup->status === 'pending' ? 'today' : '' }}
                                {{ $followup->followup_date->isPast() && !$followup->followup_date->isToday() && $followup->status === 'pending' ? 'overdue' : '' }}"
                                id="followup-{{ $followup->id }}">

                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex gap-2 flex-wrap">
                                        {{-- Priority --}}
                                        <span class="badge bg-{{ $followup->priority === 'high' ? 'danger' : ($followup->priority === 'medium' ? 'warning text-dark' : 'info text-dark') }}">
                                            <i class="las la-flag me-1"></i>{{ ucfirst($followup->priority) }}
                                        </span>
                                        {{-- Today / Overdue markers --}}
                                        @if($followup->followup_date->isToday() && $followup->status === 'pending')
                                            <span class="badge bg-warning text-dark">📅 Today</span>
                                        @elseif($followup->followup_date->isPast() && !$followup->followup_date->isToday() && $followup->status === 'pending')
                                            <span class="badge bg-danger">⚠️ Overdue</span>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-{{ $followup->status === 'completed' ? 'success' : ($followup->status === 'cancelled' ? 'secondary' : 'primary') }}">
                                            {{ ucfirst($followup->status) }}
                                        </span>
                                        @if($user->isSuperAdmin() || $followup->created_by === $user->id || $followup->assigned_to === $user->id)
                                        <button class="btn btn-sm btn-outline-danger deleteFollowup"
                                                data-id="{{ $followup->id }}" title="Delete">
                                            <i class="las la-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-md-4">
                                        <p class="mb-0 small">
                                            <i class="las la-calendar me-1 text-muted"></i>
                                            <strong>{{ $followup->followup_date->format('d M Y') }}</strong>
                                            @if($followup->followup_time)
                                                &nbsp;@ {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-0 small">
                                            <i class="las la-user me-1 text-muted"></i>
                                            {{ $followup->assignedToUser->name ?? 'Unassigned' }}
                                        </p>
                                    </div>
                                    @if($followup->time_preference)
                                    <div class="col-md-4">
                                        <p class="mb-0 small">
                                            <i class="las la-clock me-1 text-muted"></i>
                                            <span class="text-muted">{{ ucfirst($followup->time_preference) }}</span>
                                        </p>
                                    </div>
                                    @endif
                                </div>

                                @if($followup->notes)
                                <div class="mt-1 p-2 bg-light rounded small text-muted">
                                    <i class="las la-comment-alt me-1"></i>{{ $followup->notes }}
                                </div>
                                @endif

                                @if($followup->status === 'pending' && ($user->isSuperAdmin() || $followup->assigned_to === $user->id))
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-success markFollowupComplete"
                                            data-id="{{ $followup->id }}">
                                        <i class="las la-check me-1"></i>Mark Complete
                                    </button>
                                </div>
                                @endif

                                @if($followup->status === 'completed' && $followup->completed_at)
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="las la-check-circle me-1"></i>
                                        Completed {{ $followup->completed_at->format('d M Y, h:i A') }}
                                    </small>
                                </div>
                                @endif
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="las la-calendar-times"></i>
                                <p class="mb-0">No followups scheduled yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ── CALL LOGS ───────────────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-phone me-2"></i>Call Logs</h5>
                            <button class="btn btn-sm btn-primary action-button"
                                    data-bs-toggle="modal" data-bs-target="#addCallModal">
                                <i class="las la-plus me-1"></i>Log Call
                            </button>
                        </div>
                    </div>

                    <div id="callLogsContainer">
                        @forelse($eduLead->callLogs->sortByDesc('call_datetime') as $call)
                            <div class="call-log-item" id="call-{{ $call->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">

                                        {{-- Top row: agent name + call status + timestamp --}}
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                            <strong>{{ $call->user->name ?? '—' }}</strong>

                                            {{-- Call Status badge (Connected / Not Connected) --}}
                                            @if(isset($call->call_status))
                                                @if($call->call_status === 'connected')
                                                    <span class="badge bg-success">
                                                        <i class="las la-phone me-1"></i>Connected
                                                    </span>
                                                @elseif($call->call_status === 'not_connected')
                                                    <span class="badge bg-danger">
                                                        <i class="las la-phone-slash me-1"></i>Not Connected
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        {{ ucfirst(str_replace('_', ' ', $call->call_status)) }}
                                                    </span>
                                                @endif
                                            @endif

                                            {{-- Interest level (if still stored) --}}
                                            @if($call->interest_level)
                                                <span class="badge bg-{{ $call->interest_level === 'hot' ? 'danger' : ($call->interest_level === 'warm' ? 'warning text-dark' : 'info text-dark') }}">
                                                    @if($call->interest_level === 'hot') 🔥
                                                    @elseif($call->interest_level === 'warm') ☀️
                                                    @else ❄️ @endif
                                                    {{ ucfirst($call->interest_level) }}
                                                </span>
                                            @endif

                                            <small class="text-muted ms-auto">
                                                <i class="las la-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($call->call_datetime)->format('d M Y, h:i A') }}
                                            </small>
                                        </div>

                                        {{-- Duration --}}
                                        @if($call->duration)
                                        <p class="mb-1 small text-muted">
                                            <i class="las la-stopwatch me-1"></i>Duration: {{ $call->duration }}
                                        </p>
                                        @endif

                                        {{-- Remarks --}}
                                        @if($call->remarks)
                                        <div class="mb-1 p-2 bg-light rounded small">
                                            <i class="las la-comment-alt me-1 text-muted"></i>{{ $call->remarks }}
                                        </div>
                                        @endif

                                        {{-- Next action --}}
                                        @if($call->next_action)
                                        <p class="mb-0 small text-muted">
                                            <i class="las la-arrow-right me-1"></i>
                                            <strong>Next:</strong> {{ $call->next_action }}
                                        </p>
                                        @endif

                                    </div>

                                    {{-- Delete button --}}
                                    @if($user->isSuperAdmin() || $user->isOperationHead() || $user->isLeadManager() || $call->user_id === $user->id)
                                    <button class="btn btn-sm btn-outline-danger deleteCall ms-2 flex-shrink-0"
                                            data-id="{{ $call->id }}" title="Delete Call">
                                        <i class="las la-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="las la-phone-slash"></i>
                                <p class="mb-0">No call logs yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ── NOTES ───────────────────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-sticky-note me-2"></i>Notes</h5>
                            <button class="btn btn-sm btn-primary action-button"
                                    data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="las la-plus me-1"></i>Add Note
                            </button>
                        </div>
                    </div>

                    <div id="notesContainer">
                        @forelse($eduLead->notes->sortByDesc('created_at') as $note)
                            <div class="note-item" id="note-{{ $note->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong>{{ $note->createdBy->name ?? '—' }}</strong>
                                            <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0 small">{{ $note->note }}</p>
                                    </div>
                                    @if($user->isSuperAdmin() || $user->isOperationHead() || $user->isLeadManager() || $note->created_by === $user->id)
                                    <button class="btn btn-sm btn-outline-danger ms-2 deleteNote"
                                            data-id="{{ $note->id }}" title="Delete Note">
                                        <i class="las la-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="las la-comment-slash"></i>
                                <p class="mb-0">No notes yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>{{-- /col-lg-8 --}}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ADD FOLLOWUP MODAL
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addFollowupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="las la-calendar-plus me-2"></i>Schedule Followup
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFollowupForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="followup_date"
                                   required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" class="form-control" name="followup_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Priority <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="priority" required>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"
                                  placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i>Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ADD CALL MODAL
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addCallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="las la-phone me-2"></i>Log Call
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCallForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Call Date & Time <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" class="form-control" name="call_datetime"
                                   required max="{{ date('Y-m-d\TH:i') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Call Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="call_status" id="callStatusSelect" required>
                                <option value="">— Select —</option>
                                <option value="connected">
                                    📞 Connected
                                </option>
                                <option value="not_connected">
                                    📵 Not Connected
                                </option>
                            </select>
                        </div>

                        {{-- Fields visible only when Connected --}}
                        <div id="connectedFields" class="col-12" style="display:none;">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Interest Level</label>
                                    <select class="form-select" name="interest_level">
                                        <option value="">— No change —</option>
                                        <option value="hot">🔥 Hot</option>
                                        <option value="warm">☀️ Warm</option>
                                        <option value="cold">❄️ Cold</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Call Duration</label>
                                    <input type="text" class="form-control" name="duration"
                                           placeholder="e.g. 5 mins">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="3"
                                              placeholder="What was discussed..."></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Next Action</label>
                                    <input type="text" class="form-control" name="next_action"
                                           placeholder="e.g. Send brochure, Schedule campus visit...">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Schedule Follow-up</label>
                                    <input type="date" class="form-control" name="followup_date"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    <small class="text-muted">Auto-creates a follow-up entry if filled</small>
                                </div>

                            </div>
                        </div>

                        {{-- Fields for Not Connected --}}
                        <div id="notConnectedFields" class="col-12" style="display:none;">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Reason / Note</label>
                                    <textarea class="form-control" name="remarks" rows="2"
                                              placeholder="e.g. Switched off, No answer, Busy..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Schedule Retry</label>
                                    <input type="date" class="form-control" name="followup_date"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    <small class="text-muted">Schedule when to call back</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="las la-save me-1"></i>Save Call
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ADD NOTE MODAL
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="las la-sticky-note me-2"></i>Add Note
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Note <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="note" rows="4" required
                                  placeholder="Enter your note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="las la-save me-1"></i>Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    const CSRF = '{{ csrf_token() }}';
    const LEAD = {{ $eduLead->id }};

    // ── Final status labels ────────────────────────────────────────────
    const statusLabels = {
        pending        : '⏳ Pending',
        contacted      : '📞 Contacted',
        follow_up      : '🔔 Follow Up',
        admitted       : '✅ Admitted',
        not_interested : '❌ Not Interested',
        dropped        : '🚫 Dropped',
    };

    // ── Shared AJAX helper ─────────────────────────────────────────────
    function ajaxPost(url, formData) {
        return $.ajax({
            url,
            method      : 'POST',
            data        : formData,
            processData : false,
            contentType : false,
        }).fail(function (xhr) {
            let msg = 'An unexpected error occurred.';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            } else if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire({ icon: 'error', title: 'Error!', html: msg, confirmButtonColor: '#dc3545' });
        });
    }

    // ── Shared form submit wrapper ─────────────────────────────────────
    function handleFormSubmit(formId, url, modalId, loadingText, successCb) {
        $(formId).on('submit', function (e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            const orig = $btn.html();
            $btn.prop('disabled', true).html(
                `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`
            );

            ajaxPost(url, new FormData(this))
                .done(function (response) {
                    if (response.success) successCb(response);
                })
                .always(function () {
                    $btn.prop('disabled', false).html(orig);
                });
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // CALL STATUS FIELD TOGGLE (Connected ↔ Not Connected)
    // ══════════════════════════════════════════════════════════════════
    $('#callStatusSelect').on('change', function () {
        const val = this.value;
        $('#connectedFields').toggle(val === 'connected');
        $('#notConnectedFields').toggle(val === 'not_connected');
    });

    // Reset call status fields when modal closes
    $('#addCallModal').on('hidden.bs.modal', function () {
        $('#connectedFields').hide();
        $('#notConnectedFields').hide();
        $('#callStatusSelect').val('');
    });

    // ══════════════════════════════════════════════════════════════════
    // ADD FOLLOWUP
    // ══════════════════════════════════════════════════════════════════
    handleFormSubmit(
        '#addFollowupForm',
        '{{ route("edu-leads.addFollowup", $eduLead) }}',
        '#addFollowupModal',
        'Saving...',
        function (response) {
            $('#addFollowupModal').modal('hide');
            $('#addFollowupForm')[0].reset();

            Swal.fire({
                icon              : 'success',
                title             : 'Followup Scheduled!',
                text              : response.message,
                confirmButtonColor: '#667eea',
                timer             : 2000,
                showConfirmButton : false,
            }).then(() => {
                if (response.html) {
                    const $container = $('#followupsContainer');
                    $container.find('.empty-state').remove();
                    $container.prepend(response.html);
                } else {
                    location.reload();
                }
            });
        }
    );

    // ══════════════════════════════════════════════════════════════════
    // ADD CALL  — now carries call_status (connected / not_connected)
    // ══════════════════════════════════════════════════════════════════
    handleFormSubmit(
        '#addCallForm',
        '{{ route("edu-leads.addCall", $eduLead) }}',
        '#addCallModal',
        'Saving...',
        function (response) {
            $('#addCallModal').modal('hide');
            $('#addCallForm')[0].reset();
            $('#connectedFields').hide();
            $('#notConnectedFields').hide();
            $('#callStatusSelect').val('');
            setCallDatetimeNow();

            Swal.fire({
                icon              : 'success',
                title             : 'Call Logged!',
                text              : response.message,
                confirmButtonColor: '#667eea',
                timer             : 2000,
                showConfirmButton : false,
            }).then(() => {
                if (response.html) {
                    const $container = $('#callLogsContainer');
                    $container.find('.empty-state').remove();
                    $container.prepend(response.html);
                } else {
                    location.reload();
                }
            });
        }
    );

    // ══════════════════════════════════════════════════════════════════
    // ADD NOTE
    // ══════════════════════════════════════════════════════════════════
    handleFormSubmit(
        '#addNoteForm',
        '{{ route("edu-leads.addNote", $eduLead) }}',
        '#addNoteModal',
        'Saving...',
        function (response) {
            $('#addNoteModal').modal('hide');
            $('#addNoteForm')[0].reset();

            Swal.fire({
                icon              : 'success',
                title             : 'Note Added!',
                text              : response.message,
                confirmButtonColor: '#667eea',
                timer             : 2000,
                showConfirmButton : false,
            }).then(() => {
                if (response.html) {
                    const $container = $('#notesContainer');
                    $container.find('.empty-state').remove();
                    $container.prepend(response.html);
                } else {
                    location.reload();
                }
            });
        }
    );

    // ══════════════════════════════════════════════════════════════════
    // MARK FOLLOWUP COMPLETE
    // ══════════════════════════════════════════════════════════════════
    $(document).on('click', '.markFollowupComplete', function () {
        const id   = $(this).data('id');
        const $btn = $(this);

        Swal.fire({
            title             : 'Mark as Complete?',
            text              : 'This will mark the followup as completed.',
            icon              : 'question',
            showCancelButton  : true,
            confirmButtonColor: '#10b981',
            cancelButtonColor : '#6c757d',
            confirmButtonText : 'Yes, Complete It',
        }).then(result => {
            if (!result.isConfirmed) return;

            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span>'
            );

            $.post(`/edu-lead-followups/${id}/complete`, { _token: CSRF })
                .done(function (response) {
                    if (response.success) {
                        const $item = $(`#followup-${id}`);

                        $item.removeClass('overdue today').addClass('completed');
                        $item.find('.markFollowupComplete').remove();

                        // Flip the status badge from primary → success
                        $item.find('.badge.bg-primary')
                             .removeClass('bg-primary')
                             .addClass('bg-success')
                             .text('Completed');

                        // Append completion timestamp
                        $item.append(`
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="las la-check-circle me-1"></i>
                                    Completed ${response.completed_at ?? 'just now'}
                                </small>
                            </div>
                        `);

                        Swal.fire({
                            icon             : 'success',
                            title            : 'Completed!',
                            timer            : 1500,
                            showConfirmButton : false,
                        });
                    }
                })
                .fail(function (xhr) {
                    $btn.prop('disabled', false)
                        .html('<i class="las la-check me-1"></i>Mark Complete');
                    Swal.fire({
                        icon: 'error', title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to complete followup.',
                        confirmButtonColor: '#dc3545',
                    });
                });
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // DELETE FOLLOWUP
    // ══════════════════════════════════════════════════════════════════
    $(document).on('click', '.deleteFollowup', function () {
        const id = $(this).data('id');
        confirmDelete('Delete Followup?').then(ok => {
            if (!ok) return;
            $.ajax({
                url   : `/edu-leads/followup/${id}`,
                method: 'DELETE',
                data  : { _token: CSRF },
            }).done(function (response) {
                if (response.success) fadeRemove(`#followup-${id}`, '#followupsContainer', 'followups');
            }).fail(deleteError);
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // DELETE CALL LOG
    // ══════════════════════════════════════════════════════════════════
    $(document).on('click', '.deleteCall', function () {
        const id = $(this).data('id');
        confirmDelete('Delete Call Log?').then(ok => {
            if (!ok) return;
            $.ajax({
                url   : `/edu-leads/call/${id}`,
                method: 'DELETE',
                data  : { _token: CSRF },
            }).done(function (response) {
                if (response.success) fadeRemove(`#call-${id}`, '#callLogsContainer', 'call logs');
            }).fail(deleteError);
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // DELETE NOTE
    // ══════════════════════════════════════════════════════════════════
    $(document).on('click', '.deleteNote', function () {
        const id = $(this).data('id');
        confirmDelete('Delete Note?').then(ok => {
            if (!ok) return;
            $.ajax({
                url   : `/edu-leads/note/${id}`,
                method: 'DELETE',
                data  : { _token: CSRF },
            }).done(function (response) {
                if (response.success) fadeRemove(`#note-${id}`, '#notesContainer', 'notes');
            }).fail(deleteError);
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // DELETE LEAD (header button)
    // ══════════════════════════════════════════════════════════════════
    $(document).on('click', '#deleteLeadBtn', function () {
        const name = $(this).data('name');

        Swal.fire({
            title             : 'Delete Lead?',
            html              : `Are you sure you want to delete <strong>${name}</strong>?<br><small class="text-muted">This cannot be undone.</small>`,
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor : '#6c757d',
            confirmButtonText : 'Yes, Delete',
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url   : `{{ route('edu-leads.destroy', '') }}/${LEAD}`,
                method: 'DELETE',
                data  : { _token: CSRF },
            }).done(function () {
                Swal.fire({
                    icon             : 'success',
                    title            : 'Deleted!',
                    timer            : 1500,
                    showConfirmButton : false,
                }).then(() => {
                    window.location.href = '{{ route("edu-leads.index") }}';
                });
            }).fail(function (xhr) {
                Swal.fire({
                    icon: 'error', title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to delete lead.',
                    confirmButtonColor: '#dc3545',
                });
            });
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // STATUS BADGE TOGGLE (badge ↔ dropdown)
    // ══════════════════════════════════════════════════════════════════
    $('#statusEditBtn').on('click', function () {
        const isDropdownVisible = !$('#statusDropdown').hasClass('d-none');

        if (isDropdownVisible) {
            // Cancel edit — restore badge
            $('#statusDropdown').addClass('d-none');
            $('#statusBadge').removeClass('d-none');
            $(this).find('i').removeClass('la-times').addClass('la-pen');
            $('#statusDropdown').val('{{ $eduLead->final_status }}');
        } else {
            // Enter edit mode
            $('#statusBadge').addClass('d-none');
            $('#statusDropdown').removeClass('d-none').focus();
            $(this).find('i').removeClass('la-pen').addClass('la-times');
        }
    });

    // ── Status change confirm & AJAX ──────────────────────────────────
    let currentStatus = '{{ $eduLead->final_status }}';

    $('#statusDropdown').on('change', function () {
        const newStatus = $(this).val();
        if (newStatus === currentStatus) return;

        Swal.fire({
            title             : 'Change Status?',
            html              : `Update to <strong>${statusLabels[newStatus]}</strong>?`,
            icon              : 'question',
            showCancelButton  : true,
            confirmButtonColor: '#667eea',
            cancelButtonColor : '#6c757d',
            confirmButtonText : 'Yes, Update',
        }).then(result => {
            if (!result.isConfirmed) {
                $('#statusDropdown').val(currentStatus);
                return;
            }

            Swal.fire({
                title: 'Updating...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url   : '{{ route("edu-leads.updateStatus", $eduLead) }}',
                method: 'POST',
                data  : {
                    _token       : CSRF,
                    _method      : 'PATCH',
                    final_status : newStatus,
                },
            }).done(function (response) {
                currentStatus = newStatus;

                // Update badge class + text in-place
                const $badge = $('#statusBadge');
                $badge.attr('class',
                    'lead-status-badge ' + (response.status_class ?? 'status-' + newStatus)
                );
                $('#statusText').text(statusLabels[newStatus]);

                // Restore badge, hide dropdown
                $('#statusDropdown').addClass('d-none');
                $badge.removeClass('d-none');
                $('#statusEditBtn i').removeClass('la-times').addClass('la-pen');

                Swal.fire({
                    icon             : 'success',
                    title            : 'Status Updated!',
                    timer            : 1800,
                    showConfirmButton : false,
                });
            }).fail(function (xhr) {
                $('#statusDropdown').val(currentStatus);
                Swal.fire({
                    icon: 'error', title: 'Failed',
                    text: xhr.responseJSON?.message || 'Could not update status.',
                    confirmButtonColor: '#dc3545',
                });
            });
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // UTILITIES
    // ══════════════════════════════════════════════════════════════════

    /** Shared delete confirm dialog — returns Promise<bool> */
    function confirmDelete(title) {
        return Swal.fire({
            title,
            text              : 'This action cannot be undone.',
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor : '#6c757d',
            confirmButtonText : 'Yes, Delete',
        }).then(r => r.isConfirmed);
    }

    /** Remove element with fade; show empty state if container becomes empty */
    function fadeRemove(selector, containerId, entityName) {
        $(selector).fadeOut(300, function () {
            $(this).remove();
            const $c = $(containerId);
            if ($c.children(':not(.empty-state)').length === 0) {
                $c.html(`
                    <div class="empty-state">
                        <i class="las la-inbox"></i>
                        <p class="mb-0">No ${entityName} yet</p>
                    </div>
                `);
            }
        });
    }

    /** Generic delete AJAX error handler */
    function deleteError(xhr) {
        Swal.fire({
            icon: 'error', title: 'Error!',
            text: xhr.responseJSON?.message || 'Delete failed.',
            confirmButtonColor: '#dc3545',
        });
    }

    /** Set call datetime input to current local time */
    function setCallDatetimeNow() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('input[name="call_datetime"]').val(now.toISOString().slice(0, 16));
    }

    // Set call datetime on page load and every time the modal opens
    setCallDatetimeNow();
    $('#addCallModal').on('show.bs.modal', function () {
        setCallDatetimeNow();
    });

});
</script>
@endsection

