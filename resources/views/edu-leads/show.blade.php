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

    /* Header Card */
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

    .interest-badge {
        padding: 0.4rem 1rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .interest-hot  { background: #fee2e2; color: #dc2626; }
    .interest-warm { background: #fef3c7; color: #d97706; }
    .interest-cold { background: #dbeafe; color: #2563eb; }

    /* Info Cards */
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

    .info-row:last-child { border-bottom: none; }

    .info-label {
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.9rem;
        flex-shrink: 0;
        min-width: 120px;
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
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        border-left: 3px solid var(--primary-blue);
    }

    .followup-item.overdue  { border-left-color: var(--danger-red);    background: #fef2f2; }
    .followup-item.completed{ border-left-color: var(--success-green); background: #f0fdf4; opacity: 0.85; }
    .followup-item.today    { border-left-color: var(--warning-orange); background: #fffbeb; }

    /* Call Logs & Notes */
    .call-log-item, .note-item {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        border-left: 3px solid var(--primary-blue);
    }

    .note-item { background: #fffbeb; border-left-color: var(--warning-orange); }

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
    .badge { border-radius: 6px; font-weight: 600; }
    .badge.bg-success { background: var(--success-green) !important; }
    .badge.bg-primary { background: var(--primary-blue) !important; }
    .badge.bg-warning { background: var(--warning-orange) !important; color: #fff !important; }
    .badge.bg-danger  { background: var(--danger-red) !important; }
    .badge.bg-info    { background: #0891b2 !important; }

    /* Delete buttons */
    .deleteFollowup, .deleteCall, .deleteNote {
        padding: 0.25rem 0.5rem;
        font-size: 0.85rem;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .deleteFollowup:hover, .deleteCall:hover, .deleteNote:hover { opacity: 1; }

    /* Responsive */
    @media (max-width: 768px) {
        .lead-header-card { padding: 1.5rem; }
        .info-card { padding: 1.2rem; }
    }

    /* ══════════════════════════════════════════════════════════
       APPLICATION & STATUS TRACKING CARD
    ══════════════════════════════════════════════════════════ */
    .tracking-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }

    .tracking-card-header {
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1.2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tracking-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1.1rem;
        display: flex;
        align-items: center;
    }

    .tracking-card-header h5 i {
        color: var(--primary-blue);
        margin-right: 0.5rem;
        font-size: 1.3rem;
    }

    /* Tracking field rows */
    .tracking-field-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f1f5f9;
        gap: 0.75rem;
    }

    .tracking-field-row:last-child { border-bottom: none; }

    .tracking-label {
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.875rem;
        flex-shrink: 0;
        min-width: 140px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tracking-label i { font-size: 1rem; color: var(--primary-blue); opacity: 0.7; }

    .tracking-value-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 1;
        justify-content: flex-end;
    }

    /* The static display badge/text */
    .tracking-display { font-weight: 600; font-size: 0.9rem; }

    /* Inline select — hidden by default */
    .tracking-select {
        display: none;
        font-size: 0.875rem;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--text-primary);
        font-weight: 500;
        cursor: pointer;
        min-width: 160px;
        transition: border-color 0.2s;
    }

    .tracking-select:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(102,126,234,0.15);
    }

    /* Inline number input — hidden by default */
    .tracking-input {
        display: none;
        font-size: 0.875rem;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--text-primary);
        font-weight: 500;
        width: 130px;
        transition: border-color 0.2s;
    }

    .tracking-input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(102,126,234,0.15);
    }

    /* Edit pencil button */
    .tracking-edit-btn {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 0.25rem 0.45rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.8rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .tracking-edit-btn:hover {
        background: var(--primary-blue);
        color: #fff;
        border-color: var(--primary-blue);
    }

    /* Global default — hidden until JS activates them */
    .tracking-save-btn {
        background: var(--success-green);
        color: #fff;
        border: none;
        padding: 0.28rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
        display: none;          /* hidden by default */
        align-items: center;
        gap: 4px;
        font-weight: 600;
        transition: opacity 0.2s;
    }

    .tracking-save-btn:hover { opacity: 0.88; }

    .tracking-cancel-btn {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        padding: 0.28rem 0.5rem;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
        display: none;          /* hidden by default */
        align-items: center;
        transition: all 0.2s;
    }

    .tracking-cancel-btn:hover { border-color: var(--danger-red); color: var(--danger-red); }

    /* ── Status edit row override — MUST come AFTER the rules above ── */
    #status-edit-row .tracking-save-btn,
    #status-edit-row .tracking-cancel-btn {
        display: inline-flex !important;
    }


    /* Status pill badges for the tracking statuses */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0.3rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1.5px solid transparent;
    }

    /* Final Status pills */
    .pill-pending        { background: #fef9c3; color: #854d0e; border-color: #fde047; }
    .pill-contacted      { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
    .pill-notattended   { background: #3730a3; color: #fff; border-color: #3730a3; }
    .pill-follow_up      { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .pill-admitted       { background: #dcfce7; color: #14532d; border-color: #86efac; }
    .pill-not_interested { background: #fee2e2; color: #7f1d1d; border-color: #fca5a5; }
    .pill-dropped        { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

    /* WhatsApp status pills */
    .pill-not_sent   { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }
    .pill-sent       { background: #eff6ff; color: #2563eb; border-color: #93c5fd; }
    .pill-delivered  { background: #f0fdf4; color: #16a34a; border-color: #86efac; }
    .pill-read       { background: #dcfce7; color: #14532d; border-color: #4ade80; }

    /* App form status */
    .pill-not_submitted { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }
    .pill-submitted     { background: #eff6ff; color: #2563eb; border-color: #93c5fd; }
    .pill-under_review  { background: #fef9c3; color: #854d0e; border-color: #fde047; }
    .pill-approved      { background: #dcfce7; color: #14532d; border-color: #86efac; }
    .pill-rejected      { background: #fee2e2; color: #7f1d1d; border-color: #fca5a5; }

    /* Booking status */
    .pill-not_paid  { background: #fee2e2; color: #7f1d1d; border-color: #fca5a5; }
    .pill-partial   { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .pill-paid      { background: #dcfce7; color: #14532d; border-color: #86efac; }
    .pill-refunded  { background: #f3e8ff; color: #6b21a8; border-color: #d8b4fe; }

    /* Finance amount display */
    .amount-display {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-primary);
    }

    .amount-display.has-value { color: #059669; }

    /* Section divider within tracking card */
    .tracking-section-divider {
        margin: 0.5rem 0;
        padding: 0.4rem 0 0.2rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-secondary);
        opacity: 0.6;
        border-top: 1px dashed #e2e8f0;
    }

    /* Saving spinner overlay for a row */
    .tracking-saving {
        opacity: 0.5;
        pointer-events: none;
    }

    /* Hidden utility */
    .hidden { display: none !important; }

    /* Status edit row buttons are always visible when parent is shown —
   override any global tracking-save-btn display:none rule */
    #status-edit-row .tracking-save-btn,
    #status-edit-row .tracking-cancel-btn {
        display: inline-flex !important;
    }

    .tracking-save-btn { display: none; }   /* ← this is almost certainly the cause */
    .tracking-cancel-btn { display: none; }

    /* ── Followup Timeline ─────────────────────────────────────── */
    .followup-timeline { position: relative; padding-left: 0; }

    .followup-node {
        position: relative;
        display: flex;
        gap: 16px;
        margin-bottom: 1.25rem;
    }

    .followup-node:last-child { margin-bottom: 0; }

    /* The vertical line running through all nodes */
    .followup-node::before {
        content: '';
        position: absolute;
        left: 22px;
        top: 44px;
        bottom: -20px;
        width: 2px;
        background: #e2e8f0;
        z-index: 0;
    }
    .followup-node:last-child::before { display: none; }

    /* The circle badge on the left */
    .followup-badge {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 800;
        z-index: 1;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.12);
    }
    .followup-badge.badge-pending   { background: #dbeafe; color: #1d4ed8; }
    .followup-badge.badge-completed { background: #dcfce7; color: #15803d; }
    .followup-badge.badge-overdue   { background: #fee2e2; color: #b91c1c; }
    .followup-badge.badge-today     { background: #fef9c3; color: #854d0e; }

    /* The card on the right */
    .followup-card {
        flex: 1;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.05);
        overflow: hidden;
        transition: box-shadow .2s;
    }
    .followup-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.1); }

    .followup-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        padding: .75rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .followup-card-body   { padding: .9rem 1rem; }
    .followup-card-footer {
        padding: .6rem 1rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Outcome block inside a completed followup */
    .outcome-block {
        background: linear-gradient(135deg, #f0fdf4, #f8fafc);
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: .75rem 1rem;
        margin-top: .75rem;
    }
    .outcome-block-header {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #16a34a;
        margin-bottom: .5rem;
    }
    .outcome-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: .75rem;
        font-weight: 600;
    }

    /* Status progression arrow strip */
    .status-progression {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: .5rem;
    }
    .progression-arrow {
        color: #94a3b8;
        font-size: .8rem;
    }

    /* No followups empty state */
    .followup-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #94a3b8;
    }
    .followup-empty i { font-size: 3rem; opacity: .2; display: block; margin-bottom: .75rem; }

</style>
@endsection

@section('content')
<div class="lead-detail-container">
    <div class="container-fluid">

        @php
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $finalStatusLabels = [
                'pending'        => ['label' => '⏳ Pending',        'class' => 'pill-pending'],
                'not_attended'   => ['label' => '🚫 Not Attended',      'class' => 'pill-notattended'],
                'contacted'      => ['label' => '📞 Contacted',      'class' => 'pill-contacted'],
                'follow_up'      => ['label' => '🔔 Follow Up',      'class' => 'pill-follow_up'],
                'admitted'       => ['label' => '✅ Admitted',        'class' => 'pill-admitted'],
                'not_interested' => ['label' => '❌ Not Interested',  'class' => 'pill-not_interested'],
                'dropped'        => ['label' => '🚫 Dropped',         'class' => 'pill-dropped'],
            ];

            $whatsappStatusLabels = [
                'not_sent'  => ['label' => '📭 Not Sent',   'class' => 'pill-not_sent'],
                'sent'      => ['label' => '📤 Sent',        'class' => 'pill-sent'],
                'delivered' => ['label' => '📬 Delivered',   'class' => 'pill-delivered'],
                'read'      => ['label' => '👀 Read',         'class' => 'pill-read'],
            ];

            $appFormStatusLabels = [
                'not_submitted' => ['label' => '📋 Not Submitted', 'class' => 'pill-not_submitted'],
                'submitted'     => ['label' => '📨 Submitted',     'class' => 'pill-submitted'],
                'under_review'  => ['label' => '🔍 Under Review',  'class' => 'pill-under_review'],
                'approved'      => ['label' => '✅ Approved',       'class' => 'pill-approved'],
                'rejected'      => ['label' => '❌ Rejected',       'class' => 'pill-rejected'],
            ];

            $bookingStatusLabels = [
                'not_paid' => ['label' => '💸 Not Paid', 'class' => 'pill-not_paid'],
                'partial'  => ['label' => '💰 Partial',  'class' => 'pill-partial'],
                'paid'     => ['label' => '✅ Paid',      'class' => 'pill-paid'],
                'refunded' => ['label' => '↩️ Refunded', 'class' => 'pill-refunded'],
            ];

            $canEdit = $user->isSuperAdmin()
                || $user->isOperationHead()
                || ($user->isLeadManager() && $eduLead->branch_id === $user->branch_id)
                || ($user->isTelecaller() && $eduLead->assigned_to === $user->id);

            $canChangeStatus = $canEdit;
        @endphp

        {{-- ── Lead Header (no final status here anymore) ──────────── --}}
        <div class="lead-header-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h2 class="mb-0">{{ $eduLead->name }}</h2>

                        {{-- Institution type badge --}}
                        {!! $eduLead->institution_type_badge !!}

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
                        @if($eduLead->assignedTo)
                        <span class="ms-3">
                            <i class="las la-user-check me-1"></i>{{ $eduLead->assignedTo->name }}
                        </span>
                        @endif
                    </p>
                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">
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
            <div class="col-lg-5">

                {{-- ── APPLICATION & STATUS TRACKING ─────────────────── --}}
                <div class="tracking-card">
                    <div class="tracking-card-header">
                        <h5>
                            <i class="las la-tasks"></i>
                            Application Tracking
                        </h5>
                        @if($canChangeStatus)
                        <span class="badge bg-light text-muted border" style="font-size:0.72rem; font-weight:500;">
                            <i class="las la-pen me-1"></i>Click ✎ to edit
                        </span>
                        @endif
                    </div>

                    {{-- ── CRM STATUS ────────────────────────────────────── --}}
                    <div class="tracking-section-divider">CRM Status</div>

                    {{-- Final Status --}}
                    <div class="tracking-field-row" id="row-final_status">
                        <span class="tracking-label">
                            <i class="las la-flag-checkered"></i>Final Status
                        </span>
                        <div class="tracking-value-wrap">
                            <span class="tracking-display" id="display-final_status">
                                <span class="status-pill {{ $finalStatusLabels[$eduLead->final_status]['class'] ?? 'pill-pending' }}">
                                    {{ $finalStatusLabels[$eduLead->final_status]['label'] ?? ucfirst($eduLead->final_status) }}
                                </span>
                            </span>
                            <select class="tracking-select" id="select-final_status"
                                    data-field="final_status" data-lead="{{ $eduLead->id }}">
                                @foreach($finalStatusLabels as $val => $meta)
                                    <option value="{{ $val }}" {{ $eduLead->final_status === $val ? 'selected' : '' }}>
                                        {{ $meta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="tracking-save-btn"   id="save-final_status">  <i class="las la-check"></i> Save   </button>
                            <button class="tracking-cancel-btn" id="cancel-final_status"><i class="las la-times"></i></button>
                            @if($canChangeStatus)
                            <button class="tracking-edit-btn" data-target="final_status" title="Edit Final Status">
                                <i class="las la-pen"></i>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- ── NEXT ACTION STATUS ────────────────────────────── --}}
                    <div class="tracking-section-divider">Next Action</div>

                    @php
                        $currentStatus = $eduLead->status ?? null;
                        $statusLabels  = [
                            'whatsapp_link_submitted'    => ['label' => '📲 WhatsApp Link Submitted',    'class' => 'pill-whatsapp'],
                            'application_form_submitted' => ['label' => '📋 Application Form Submitted', 'class' => 'pill-app_form'],
                            'booking'                    => ['label' => '💳 Booking',                    'class' => 'pill-booking'],
                            'cancelled'                  => ['label' => '🚫 Cancelled',                  'class' => 'pill-cancelled'],
                        ];
                    @endphp

                    <div class="tracking-field-row" id="row-status"
                        style="flex-direction: column; align-items: flex-start; gap: 8px;">

                        {{-- Top row: label + current pill + edit pencil --}}
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <span class="tracking-label mb-0">
                                <i class="las la-toggle-on"></i>Status
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="tracking-display" id="display-status">
                                    @if($currentStatus && isset($statusLabels[$currentStatus]))
                                        <span class="status-pill {{ $statusLabels[$currentStatus]['class'] }}">
                                            {{ $statusLabels[$currentStatus]['label'] }}
                                        </span>
                                    @else
                                        <span class="text-muted fw-normal" style="font-size:0.9rem;">—</span>
                                    @endif
                                </span>
                                @if($canChangeStatus)
                                <button class="tracking-edit-btn" data-target="status" title="Edit Status">
                                    <i class="las la-pen"></i>
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- Bottom row: full-width dropdown + save/cancel buttons --}}
                        <div class="w-100" id="status-edit-row" style="display: none;">
                            <select class="form-select form-select-sm w-100"
                                    id="select-status" data-field="status" data-lead="{{ $eduLead->id }}">
                                <option value="">— Select Status —</option>
                                @foreach($statusLabels as $val => $meta)
                                    <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>
                                        {{ $meta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-sm btn-success tracking-save-btn" id="save-status" type="button">
                                    <i class="las la-check"></i> Save
                                </button>
                                <button class="btn btn-sm btn-secondary tracking-cancel-btn" id="cancel-status" type="button">
                                    <i class="las la-times"></i> Cancel
                                </button>
                            </div>
                        </div>

                    </div>

                    {{-- ── BOOKING DETAILS — shown only when status = booking ── --}}
                    @if($currentStatus === 'booking')
                    <div class="tracking-section-divider">Payment</div>

                    {{-- Booking Payment (display only) --}}
                    <div class="tracking-field-row">
                        <span class="tracking-label">
                            <i class="las la-rupee-sign"></i>Booking Amt
                        </span>
                        <div class="tracking-value-wrap">
                            <span class="amount-display {{ $eduLead->booking_payment ? 'has-value' : '' }}">
                                @if($eduLead->booking_payment)
                                    ₹{{ number_format($eduLead->booking_payment, 2) }}
                                @else
                                    <span class="text-muted fw-normal">—</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Fees Collected (display only) --}}
                    <div class="tracking-field-row">
                        <span class="tracking-label">
                            <i class="las la-money-check-alt"></i>Fees Collected
                        </span>
                        <div class="tracking-value-wrap">
                            <span class="amount-display {{ $eduLead->fees_collection ? 'has-value' : '' }}">
                                @if($eduLead->fees_collection)
                                    ₹{{ number_format($eduLead->fees_collection, 2) }}
                                @else
                                    <span class="text-muted fw-normal">—</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif

                    {{-- ── APPLICATION NUMBER ────────────────────────────── --}}
                    <div class="tracking-section-divider">Application</div>

                    <div class="tracking-field-row">
                        <span class="tracking-label">
                            <i class="las la-id-card"></i>App Number
                        </span>
                        <div class="tracking-value-wrap">
                            @if($eduLead->application_number)
                                <span class="badge bg-light text-dark border fw-semibold"
                                    style="font-size:0.85rem; letter-spacing:0.3px;">
                                    {{ $eduLead->application_number }}
                                </span>
                            @else
                                <span class="text-muted fw-normal" style="font-size:0.9rem;">—</span>
                            @endif
                        </div>
                    </div>

                    {{-- ── CANCELLATION REASON — shown only when status = cancelled ── --}}
                    @if($currentStatus === 'cancelled' || $eduLead->cancellation_reason)
                    <div class="tracking-section-divider">Cancellation</div>

                    <div class="tracking-field-row" id="row-cancellation_reason"
                        style="flex-direction:column; align-items:flex-start; gap:6px;">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <span class="tracking-label mb-0">
                                <i class="las la-ban"></i>Cancel Reason
                            </span>
                            @if($canEdit)
                            <button class="tracking-edit-btn" data-target="cancellation_reason"
                                    data-type="input" title="Edit Cancellation Reason">
                                <i class="las la-pen"></i>
                            </button>
                            @endif
                        </div>
                        <span class="tracking-display text-muted small"
                            id="display-cancellation_reason" style="width:100%;">
                            {{ $eduLead->cancellation_reason ?? '—' }}
                        </span>
                        <input type="text" class="tracking-input w-100"
                            id="input-cancellation_reason"
                            data-field="cancellation_reason"
                            data-lead="{{ $eduLead->id }}"
                            value="{{ $eduLead->cancellation_reason ?? '' }}"
                            placeholder="Reason for cancellation...">
                        <div class="d-flex gap-2">
                            <button class="tracking-save-btn"   id="save-cancellation_reason">  <i class="las la-check"></i> Save   </button>
                            <button class="tracking-cancel-btn" id="cancel-cancellation_reason"><i class="las la-times"></i></button>
                        </div>
                    </div>
                    @endif

                </div>


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
                                    <span class="badge bg-light text-dark border"><i class="las la-stream me-1"></i>{{ $eduLead->school_department }}</span>
                                </span>
                            </div>
                            @endif
                            @if($eduLead->current_year)
                            <div class="info-row">
                                <span class="info-label">Current Year</span>
                                <span class="info-value"><span class="badge bg-light text-dark border">{{ $eduLead->current_year }}</span></span>
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
                                    <span class="badge bg-light text-dark border"><i class="las la-building me-1"></i>{{ $eduLead->college_department }}</span>
                                </span>
                            </div>
                            @endif
                            @if($eduLead->current_year)
                            <div class="info-row">
                                <span class="info-label">Current Year</span>
                                <span class="info-value"><span class="badge bg-light text-dark border">{{ $eduLead->current_year }}</span></span>
                            </div>
                            @endif
                        @else
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
                            <span class="badge bg-primary"><i class="las la-globe me-1"></i>{{ $eduLead->country }}</span>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->course?->programme)
                    <div class="info-row">
                        <span class="info-label">Programme</span>
                        <span class="info-value">
                            <span class="badge bg-info p-2"><i class="las la-layer-group me-1"></i>{{ $eduLead->course->programme->name }}</span>
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
                        <span class="info-label">Course Interest<br><small class="text-muted fw-normal">(free text)</small></span>
                        <span class="info-value fst-italic text-muted"><i class="las la-pen me-1"></i>{{ $eduLead->course_interested }}</span>
                    </div>
                    @endif

                    @if($eduLead->preferred_intake)
                    <div class="info-row">
                        <span class="info-label">Preferred Intake</span>
                        <span class="info-value">
                            <span class="badge bg-light text-dark border"><i class="las la-calendar me-1"></i>{{ $eduLead->preferred_intake }}</span>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->budget)
                    <div class="info-row">
                        <span class="info-label">Budget</span>
                        <span class="info-value fw-semibold"><i class="las la-money-bill me-1 text-success"></i>{{ $eduLead->budget }}</span>
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
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $eduLead->whatsapp_number) }}" target="_blank" class="text-success text-decoration-none">
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

                    @if($eduLead->district)
                    <div class="info-row">
                        <span class="info-label">District</span>
                        <span class="info-value">
                            <i class="las la-map-marker me-1 text-muted"></i>
                            {{ $eduLead->district }}
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

                    @if($eduLead->agent_name)
                    <div class="info-row">
                        <span class="info-label">Agent</span>
                        <span class="info-value">{{ $eduLead->agent_name ?? '—' }}</a>
                        </span>
                    </div>
                    @endif

                    @if($eduLead->referral_name)
                    <div class="info-row">
                        <span class="info-label">Referral</span>
                        <span class="info-value">{{ $eduLead->referral_name ?? '—' }}</span>
                    </div>
                    @endif

                    {{-- <div class="info-row">
                        <span class="info-label">Call Status</span>
                        <span class="info-value">{!! $eduLead->status_badge !!}</span>
                    </div> --}}

                    <div class="info-row">
                        <span class="info-label">Branch</span>
                        <span class="info-value"><i class="las la-building me-1 text-muted"></i>{{ $eduLead->branch->name ?? '—' }}</span>
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
                            <span class="badge {{ $eduLead->followup_date->isPast() ? 'bg-danger' : ($eduLead->followup_date->isToday() ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                                <i class="las la-calendar me-1"></i>{{ $eduLead->followup_date->format('d M Y') }}
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
            <div class="col-lg-7">

                {{-- ── FOLLOWUP TIMELINE ──────────────────────────────────────── --}}
                <div class="info-card">
                    <div class="info-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="las la-history me-2"></i>Followup Timeline
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">
                                {{ $eduLead->followups->count() }} total
                            </span>
                            @if($canEdit)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addFollowupModal">
                                <i class="las la-plus me-1"></i>Schedule
                            </button>
                            @endif
                        </div>
                    </div>

                    @php
                        $followups = $eduLead->followups()
                                            ->orderBy('followup_number')
                                            ->get()
                                            ->values();

                        $finalStatusLabelsLocal = [
                            'pending'        => ['label' => '⏳ Pending',        'bg' => '#fef9c3', 'color' => '#854d0e'],
                            'not_attended'   => ['label' => '🚫 Not Attended',   'bg' => '#ede9fe', 'color' => '#6d28d9'],
                            'contacted'      => ['label' => '📞 Contacted',      'bg' => '#dbeafe', 'color' => '#1d4ed8'],
                            'follow_up'      => ['label' => '🔔 Follow Up',      'bg' => '#ffedd5', 'color' => '#c2410c'],
                            'admitted'       => ['label' => '✅ Admitted',        'bg' => '#dcfce7', 'color' => '#15803d'],
                            'not_interested' => ['label' => '❌ Not Interested',  'bg' => '#fee2e2', 'color' => '#b91c1c'],
                            'dropped'        => ['label' => '🚫 Dropped',         'bg' => '#f1f5f9', 'color' => '#475569'],
                        ];

                        $nextActionLabelsLocal = [
                            'whatsapp_link_submitted'    => '📲 WhatsApp Submitted',
                            'application_form_submitted' => '📋 App Form Submitted',
                            'booking'                    => '💳 Booking',
                            'cancelled'                  => '🚫 Cancelled',
                        ];

                        $interestColors = [
                            'hot'  => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
                            'warm' => ['bg' => '#fef3c7', 'color' => '#b45309'],
                            'cold' => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
                        ];

                        // Helper closure — converts integer to ordinal suffix
                        $ordinal = function (int $n): string {
                            $suffix = match($n % 10) {
                                1 => $n % 100 === 11 ? 'th' : 'st',
                                2 => $n % 100 === 12 ? 'th' : 'nd',
                                3 => $n % 100 === 13 ? 'th' : 'rd',
                                default => 'th',
                            };
                            return $n . $suffix;
                        };
                    @endphp

                    <div style="padding: 1.25rem;">
                        @if($followups->isEmpty())
                            <div class="followup-empty">
                                <i class="las la-calendar-times"></i>
                                <p class="mb-1 fw-semibold">No followups scheduled yet</p>
                                <p class="small mb-0">Schedule the first followup to start tracking interactions.</p>
                            </div>
                        @else
                        <div class="followup-timeline">
                            @foreach($followups as $fu)
                            @php
                                $displayNum     = $loop->iteration;
                                $displayOrdinal = $ordinal($displayNum);

                                $isCompleted = $fu->status === 'completed';
                                $isOverdue   = !$isCompleted && \Carbon\Carbon::parse($fu->followup_date)->startOfDay()->lt(\Carbon\Carbon::today());
                                $isToday     = !$isCompleted && \Carbon\Carbon::parse($fu->followup_date)->isToday();

                                $badgeClass = $isCompleted ? 'badge-completed'
                                            : ($isOverdue  ? 'badge-overdue'
                                            : ($isToday    ? 'badge-today' : 'badge-pending'));

                                $cardBorderColor = $isCompleted ? '#86efac'
                                                : ($isOverdue  ? '#fca5a5'
                                                : ($isToday    ? '#fde68a' : '#bfdbfe'));

                                $outcomeFinalMeta = $finalStatusLabelsLocal[$fu->outcome_final_status] ?? null;
                                $outcomeInterest  = $interestColors[$fu->outcome_interest] ?? null;
                            @endphp

                            <div class="followup-node" id="followup-node-{{ $fu->id }}">

                                {{-- ── Circle badge ──────────────────────────────── --}}
                                <div class="followup-badge {{ $badgeClass }}"
                                    title="{{ $displayOrdinal }} Followup">
                                    {{ $displayNum }}
                                </div>

                                {{-- ── Card ──────────────────────────────────────── --}}
                                <div class="followup-card" style="border-color: {{ $cardBorderColor }};">

                                    {{-- Header --}}
                                    <div class="followup-card-header">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-700" style="font-size:.9rem;">
                                                {{ $displayOrdinal }} Followup
                                            </span>
                                            <span class="text-muted" style="font-size:.82rem;">
                                                <i class="las la-calendar me-1"></i>
                                                {{ $fu->followup_date->format('d M Y') }}
                                                @if($fu->followup_time)
                                                    · {{ \Carbon\Carbon::parse($fu->followup_time)->format('h:i A') }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($isCompleted)
                                                <span class="badge bg-success">✅ Completed</span>
                                            @elseif($isOverdue)
                                                <span class="badge bg-danger">⚠️ Overdue</span>
                                            @elseif($isToday)
                                                <span class="badge bg-warning text-dark">🔔 Due Today</span>
                                            @else
                                                <span class="badge bg-info text-dark">🕐 Upcoming</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Body --}}
                                    <div class="followup-card-body">

                                        @if($fu->notes)
                                        <div class="mb-2 small text-muted">
                                            <i class="las la-sticky-note me-1"></i>{{ $fu->notes }}
                                        </div>
                                        @endif

                                        <div class="d-flex gap-3 flex-wrap" style="font-size:.8rem; color:#64748b;">
                                            @if($fu->assignedToUser)
                                            <span>
                                                <i class="las la-user-check me-1"></i>{{ $fu->assignedToUser->name }}
                                            </span>
                                            @endif
                                            @if($fu->createdBy)
                                            <span>
                                                <i class="las la-user-edit me-1"></i>Created by {{ $fu->createdBy->name }}
                                            </span>
                                            @endif
                                        </div>

                                        {{-- Outcome block --}}
                                        @if($isCompleted && $fu->outcome_final_status)
                                        <div class="outcome-block mt-3">
                                            <div class="outcome-block-header">
                                                <i class="las la-check-circle me-1"></i>Outcome
                                            </div>

                                            <div class="status-progression">
                                                <span class="small text-muted fw-semibold">Final Status:</span>
                                                @if($outcomeFinalMeta)
                                                <span class="outcome-pill"
                                                    style="background:{{ $outcomeFinalMeta['bg'] }};
                                                            color:{{ $outcomeFinalMeta['color'] }};">
                                                    {{ $outcomeFinalMeta['label'] }}
                                                </span>
                                                @endif

                                                @if($fu->outcome_interest && $outcomeInterest)
                                                <span class="progression-arrow">·</span>
                                                <span class="outcome-pill"
                                                    style="background:{{ $outcomeInterest['bg'] }};
                                                            color:{{ $outcomeInterest['color'] }};">
                                                    @if($fu->outcome_interest === 'hot') 🔥
                                                    @elseif($fu->outcome_interest === 'warm') ☀️
                                                    @else ❄️ @endif
                                                    {{ ucfirst($fu->outcome_interest) }}
                                                </span>
                                                @endif

                                                @if($fu->outcome_status)
                                                <span class="progression-arrow">·</span>
                                                <span class="outcome-pill"
                                                    style="background:#f0f9ff; color:#0369a1;">
                                                    {{ $nextActionLabelsLocal[$fu->outcome_status] ?? $fu->outcome_status }}
                                                </span>
                                                @endif
                                            </div>

                                            @if($fu->outcome_notes)
                                            <div class="mt-2 small" style="color:#374151; line-height:1.5;">
                                                <i class="las la-comment-alt me-1 text-muted"></i>{{ $fu->outcome_notes }}
                                            </div>
                                            @endif

                                            @if($fu->next_action)
                                            <div class="mt-2 small" style="color:#6d28d9;">
                                                <i class="las la-arrow-right me-1"></i>
                                                <strong>Next:</strong> {{ $fu->next_action }}
                                            </div>
                                            @endif

                                            @if($fu->completed_at)
                                            <div class="mt-2" style="font-size:.72rem; color:#94a3b8;">
                                                <i class="las la-check me-1"></i>
                                                Completed {{ $fu->completed_at->format('d M Y, h:i A') }}
                                                @if($fu->assignedToUser)
                                                    by {{ $fu->assignedToUser->name }}
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        @endif

                                    </div>

                                    {{-- Footer --}}
                                    @if($canEdit)
                                    <div class="followup-card-footer">

                                        @if(!$isCompleted)
                                        <button type="button"
                                                class="btn btn-sm btn-success completeFollowupBtn"
                                                data-id="{{ $fu->id }}"
                                                data-number="{{ $displayNum }}"
                                                data-final-status="{{ $eduLead->final_status }}"
                                                data-status="{{ $eduLead->status }}"
                                                data-interest="{{ $eduLead->interest_level }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#completeFollowupModal">
                                            <i class="las la-check me-1"></i>Mark {{ $displayOrdinal }} Complete
                                        </button>
                                        @endif

                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary editFollowupBtn"
                                                data-id="{{ $fu->id }}"
                                                data-number="{{ $displayNum }}"
                                                data-date="{{ $fu->followup_date->format('Y-m-d') }}"
                                                data-time="{{ $fu->followup_time ?? '' }}"
                                                data-notes="{{ $fu->notes ?? '' }}"
                                                data-priority="{{ $fu->priority ?? 'medium' }}"
                                                data-outcome-final-status="{{ $fu->outcome_final_status ?? '' }}"
                                                data-outcome-status="{{ $fu->outcome_status ?? '' }}"
                                                data-outcome-interest="{{ $fu->outcome_interest ?? '' }}"
                                                data-outcome-notes="{{ $fu->outcome_notes ?? '' }}"
                                                data-next-action="{{ $fu->next_action ?? '' }}"
                                                data-is-completed="{{ $isCompleted ? '1' : '0' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editFollowupModal">
                                            <i class="las la-pen me-1"></i>Edit
                                        </button>

                                        {{-- @if(!$isCompleted) --}}
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger deleteFollowupBtn ms-auto"
                                                data-id="{{ $fu->id }}"
                                                data-number="{{ $displayNum }}">
                                            <i class="las la-trash me-1"></i>Delete
                                        </button>
                                        {{-- @endif --}}

                                    </div>
                                    @endif

                                </div>{{-- /.followup-card --}}
                            </div>{{-- /.followup-node --}}
                            @endforeach
                        </div>{{-- /.followup-timeline --}}
                        @endif
                    </div>
                </div>


                {{-- ── CALL LOGS ───────────────────────────────────────── --}}
                {{-- <div class="info-card">
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
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                            <strong>{{ $call->user->name ?? '—' }}</strong>

                                            @if(isset($call->call_status))
                                                @if($call->call_status === 'connected')
                                                    <span class="badge bg-success"><i class="las la-phone me-1"></i>Connected</span>
                                                @elseif($call->call_status === 'not_connected')
                                                    <span class="badge bg-danger"><i class="las la-phone-slash me-1"></i>Not Connected</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $call->call_status)) }}</span>
                                                @endif
                                            @endif

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

                                        @if($call->duration)
                                        <p class="mb-1 small text-muted">
                                            <i class="las la-stopwatch me-1"></i>Duration: {{ $call->duration }}
                                        </p>
                                        @endif

                                        @if($call->remarks)
                                        <div class="mb-1 p-2 bg-light rounded small">
                                            <i class="las la-comment-alt me-1 text-muted"></i>{{ $call->remarks }}
                                        </div>
                                        @endif

                                        @if($call->next_action)
                                        <p class="mb-0 small text-muted">
                                            <i class="las la-arrow-right me-1"></i>
                                            <strong>Next:</strong> {{ $call->next_action }}
                                        </p>
                                        @endif
                                    </div>

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
                </div> --}}

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
                <h5 class="modal-title"><i class="las la-calendar-plus me-2"></i>Schedule Followup</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFollowupForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="followup_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" class="form-control" name="followup_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="las la-save me-1"></i>Schedule</button>
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
                <h5 class="modal-title"><i class="las la-phone me-2"></i>Log Call</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCallForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Call Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="call_datetime" required max="{{ date('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Call Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="call_status" id="callStatusSelect" required>
                                <option value="">— Select —</option>
                                <option value="connected">📞 Connected</option>
                                <option value="not_connected">📵 Not Connected</option>
                            </select>
                        </div>

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
                                    <input type="text" class="form-control" name="duration" placeholder="e.g. 5 mins">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="3" placeholder="What was discussed..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Next Action</label>
                                    <input type="text" class="form-control" name="next_action" placeholder="e.g. Send brochure, Schedule campus visit...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Schedule Follow-up</label>
                                    <input type="date" class="form-control" name="followup_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    <small class="text-muted">Auto-creates a follow-up entry if filled</small>
                                </div>
                            </div>
                        </div>

                        <div id="notConnectedFields" class="col-12" style="display:none;">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Reason / Note</label>
                                    <textarea class="form-control" name="remarks" rows="2" placeholder="e.g. Switched off, No answer, Busy..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Schedule Retry</label>
                                    <input type="date" class="form-control" name="followup_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    <small class="text-muted">Schedule when to call back</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="las la-save me-1"></i>Save Call</button>
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
                <h5 class="modal-title"><i class="las la-sticky-note me-2"></i>Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" rows="4" required placeholder="Enter your note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="las la-save me-1"></i>Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── COMPLETE FOLLOWUP MODAL ────────────────────────────────── --}}
@if($canEdit)
<div class="modal fade" id="completeFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff;">
                <h5 class="modal-title">
                    <i class="las la-check-circle me-2"></i>
                    Complete <span id="completeFollowupLabel">Followup</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="completeFollowupForm">
                @csrf
                <input type="hidden" id="completeFollowupId" name="followup_id">

                <div class="modal-body">

                    {{-- Final Status --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-flag-checkered me-1 text-primary"></i>
                            Final Status <span class="text-danger">*</span>
                            <small class="text-muted fw-normal ms-1">— what is the lead's status now?</small>
                        </label>
                        <select class="form-select" name="outcome_final_status"
                                id="outcomeFinalStatus" required>
                            <option value="">— Select final status —</option>
                            <option value="pending">⏳ Pending</option>
                            <option value="not_attended">🚫 Not Attended</option>
                            <option value="contacted">📞 Contacted</option>
                            <option value="follow_up">🔔 Follow Up</option>
                            <option value="admitted">✅ Admitted</option>
                            <option value="not_interested">❌ Not Interested</option>
                            <option value="dropped">🚫 Dropped</option>
                        </select>
                    </div>

                    {{-- Next Action Status --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-toggle-on me-1 text-info"></i>
                            Next Action Status
                            <small class="text-muted fw-normal ms-1">— optional pipeline step</small>
                        </label>
                        <select class="form-select" name="outcome_status" id="outcomeStatus">
                            <option value="">— None —</option>
                            <option value="whatsapp_link_submitted">📲 WhatsApp Link Submitted</option>
                            <option value="application_form_submitted">📋 Application Form Submitted</option>
                            <option value="booking">💳 Booking</option>
                            <option value="cancelled">🚫 Cancelled</option>
                        </select>
                    </div>

                    {{-- Interest Level --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-fire me-1 text-danger"></i>
                            Interest Level
                            <small class="text-muted fw-normal ms-1">— how interested is the lead?</small>
                        </label>
                        <div class="d-flex gap-2">
                            @foreach(['hot' => '🔥 Hot', 'warm' => '☀️ Warm', 'cold' => '❄️ Cold'] as $val => $lbl)
                            <div class="form-check flex-fill text-center border rounded p-2"
                                 style="cursor:pointer;" id="interest-opt-{{ $val }}">
                                <input class="form-check-input d-none" type="radio"
                                       name="outcome_interest" value="{{ $val }}"
                                       id="interest{{ $val }}">
                                <label class="form-check-label fw-semibold w-100"
                                       for="interest{{ $val }}" style="cursor:pointer;">
                                    {{ $lbl }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Outcome Notes --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-comment-alt me-1 text-warning"></i>
                            What happened?
                            <small class="text-muted fw-normal ms-1">— summary of interaction</small>
                        </label>
                        <textarea class="form-control" name="outcome_notes" id="outcomeNotes"
                                  rows="3" placeholder="Student called, discussed fees, wants to apply..."></textarea>
                    </div>

                    {{-- Next Action --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            <i class="las la-arrow-right me-1 text-success"></i>
                            Next Action
                            <small class="text-muted fw-normal ms-1">— what's the plan?</small>
                        </label>
                        <input type="text" class="form-control" name="next_action" id="outcomeNextAction"
                               placeholder="Send brochure, schedule campus visit...">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="completeFollowupSubmitBtn">
                        <i class="las la-check me-1"></i>Save Outcome & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── EDIT FOLLOWUP MODAL ────────────────────────────────────── --}}
@if($canEdit)
<div class="modal fade" id="editFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="las la-pen me-2"></i>
                    Edit <span id="editFollowupLabel">Followup</span>
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <form id="editFollowupForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editFollowupId">

                <div class="modal-body">

                    {{-- ── SECTION 1: Schedule ──────────────────────── --}}
                    <div class="mb-1 pb-1"
                         style="font-size:.72rem; font-weight:700; text-transform:uppercase;
                                letter-spacing:.6px; color:#94a3b8;">
                        <i class="las la-calendar me-1"></i>Schedule
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control"
                                   name="followup_date" id="editFollowupDate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" class="form-control"
                                   name="followup_time" id="editFollowupTime">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Priority</label>
                            <select class="form-select" name="priority" id="editFollowupPriority">
                                <option value="low">🟢 Low</option>
                                <option value="medium">🟡 Medium</option>
                                <option value="high">🔴 High</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="las la-sticky-note me-1 text-warning"></i>Schedule Notes
                            </label>
                            <textarea class="form-control" name="notes" id="editFollowupNotes"
                                      rows="2" placeholder="Notes about this followup..."></textarea>
                        </div>
                    </div>

                    {{-- ── SECTION 2: Outcome (shown always, relevant for completed) ── --}}
                    <div class="p-3 rounded-3 mb-1"
                         style="background:#f0fdf4; border:1px solid #bbf7d0;">

                        <div class="mb-3 pb-1"
                             style="font-size:.72rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:.6px; color:#16a34a;">
                            <i class="las la-check-circle me-1"></i>Outcome
                            <span id="editOutcomePendingHint"
                                  class="text-muted fw-normal ms-2"
                                  style="font-size:.7rem; text-transform:none; letter-spacing:0;">
                                (fill when completing this followup)
                            </span>
                        </div>

                        <div class="row g-3">

                            {{-- Final Status --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="las la-flag-checkered me-1 text-primary"></i>
                                    Final Status
                                </label>
                                <select class="form-select" name="outcome_final_status"
                                        id="editOutcomeFinalStatus">
                                    <option value="">— No change —</option>
                                    <option value="pending">⏳ Pending</option>
                                    <option value="not_attended">🚫 Not Attended</option>
                                    <option value="contacted">📞 Contacted</option>
                                    <option value="follow_up">🔔 Follow Up</option>
                                    <option value="admitted">✅ Admitted</option>
                                    <option value="not_interested">❌ Not Interested</option>
                                    <option value="dropped">🚫 Dropped</option>
                                </select>
                            </div>

                            {{-- Next Action Status --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="las la-toggle-on me-1 text-info"></i>
                                    Next Action Status
                                </label>
                                <select class="form-select" name="outcome_status"
                                        id="editOutcomeStatus">
                                    <option value="">— None —</option>
                                    <option value="whatsapp_link_submitted">📲 WhatsApp Submitted</option>
                                    <option value="application_form_submitted">📋 App Form Submitted</option>
                                    <option value="booking">💳 Booking</option>
                                    <option value="cancelled">🚫 Cancelled</option>
                                </select>
                            </div>

                            {{-- Interest Level --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="las la-fire me-1 text-danger"></i>
                                    Interest Level
                                </label>
                                <div class="d-flex gap-2">
                                    <div class="form-check flex-fill text-center border rounded p-2"
                                         id="edit-interest-opt-none"
                                         style="cursor:pointer;">
                                        <input class="form-check-input d-none" type="radio"
                                               name="outcome_interest" value=""
                                               id="editInterestNone">
                                        <label class="form-check-label fw-semibold w-100"
                                               for="editInterestNone" style="cursor:pointer;">
                                            — None
                                        </label>
                                    </div>
                                    @foreach(['hot' => '🔥 Hot', 'warm' => '☀️ Warm', 'cold' => '❄️ Cold'] as $val => $lbl)
                                    <div class="form-check flex-fill text-center border rounded p-2"
                                         id="edit-interest-opt-{{ $val }}"
                                         style="cursor:pointer;">
                                        <input class="form-check-input d-none" type="radio"
                                               name="outcome_interest" value="{{ $val }}"
                                               id="editInterest{{ ucfirst($val) }}">
                                        <label class="form-check-label fw-semibold w-100"
                                               for="editInterest{{ ucfirst($val) }}"
                                               style="cursor:pointer;">
                                            {{ $lbl }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- What happened --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="las la-comment-alt me-1 text-warning"></i>
                                    What Happened?
                                </label>
                                <textarea class="form-control" name="outcome_notes"
                                          id="editOutcomeNotes" rows="3"
                                          placeholder="Describe what happened during this interaction..."></textarea>
                            </div>

                            {{-- Next Action --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="las la-arrow-right me-1 text-success"></i>
                                    Next Action
                                </label>
                                <input type="text" class="form-control"
                                       name="next_action" id="editNextAction"
                                       placeholder="Send brochure, schedule campus visit...">
                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editFollowupSubmitBtn">
                        <i class="las la-save me-1"></i>Save Changes
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

    const CSRF = '{{ csrf_token() }}';
    const LEAD = {{ $eduLead->id }};

    // Ordinal suffix helper — mirrors the blade $ordinal closure
    function ordinal(n) {
        n = parseInt(n);
        const s = ['th','st','nd','rd'];
        const v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    }

    // Re-numbers all visible followup nodes after a DOM deletion
    function reNumberFollowups() {
        $('.followup-node:visible').each(function (index) {
            const n    = index + 1;
            const ord  = ordinal(n);

            // Update bubble
            $(this).find('.followup-badge').text(n);

            // Update card title
            $(this).find('.fw-700').first().text(ord + ' Followup');

            // Update data-number on action buttons so next modal open is correct
            $(this).find('.completeFollowupBtn, .editFollowupBtn, .deleteFollowupBtn')
                .data('number', n)
                .attr('data-number', n);

            // Update "Mark Xth Complete" button text
            $(this).find('.completeFollowupBtn').html(
                `<i class="las la-check me-1"></i>Mark ${ord} Complete`
            );
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // TRACKING FIELD LABEL MAPS
    // ══════════════════════════════════════════════════════════════════
    const trackingLabels = {

        final_status: {
            pending        : { label: '⏳ Pending',        cls: 'pill-pending' },
            contacted      : { label: '📞 Contacted',      cls: 'pill-contacted' },
            not_attended   : { label: '🚫 Not Attended',     cls: 'pill-notattended' },
            follow_up      : { label: '🔔 Follow Up',      cls: 'pill-follow_up' },
            admitted       : { label: '✅ Admitted',        cls: 'pill-admitted' },
            not_interested : { label: '❌ Not Interested',  cls: 'pill-not_interested' },
            dropped        : { label: '🚫 Dropped',         cls: 'pill-dropped' },
        },

        status: {
            whatsapp_link_submitted    : { label: '📲 WhatsApp Link Submitted',    cls: 'pill-whatsapp' },
            application_form_submitted : { label: '📋 Application Form Submitted', cls: 'pill-app_form' },
            booking                    : { label: '💳 Booking',                    cls: 'pill-booking' },
            cancelled                  : { label: '🚫 Cancelled',                  cls: 'pill-cancelled' },
        },

    };

    const originalValues = {};

    // ══════════════════════════════════════════════════════════════════
    // HELPERS — enter / exit edit mode
    // ══════════════════════════════════════════════════════════════════

    function enterEdit(target, type) {
        originalValues[target] = $(`#display-${target}`).html();
        $(`#display-${target}`).hide();
        $(`[data-target="${target}"].tracking-edit-btn`).hide();

        if (target === 'status') {
            $('#status-edit-row').show();
            return;
        }

        if (type === 'input') {
            $(`#input-${target}`).css('display', 'inline-block').focus();
        } else {
            $(`#select-${target}`).css('display', 'inline-block').focus();
        }
        $(`#save-${target}`).css('display', 'inline-flex');
        $(`#cancel-${target}`).css('display', 'inline-flex');
    }

    function exitEdit(field) {
        $(`#display-${field}`).show();
        $(`[data-target="${field}"].tracking-edit-btn`).show();

        if (field === 'status') {
            $('#status-edit-row').hide();
            return;
        }

        $(`#select-${field}`).hide().css('display', '');
        $(`#input-${field}`).hide().css('display', '');
        $(`#save-${field}`).hide().css('display', '');
        $(`#cancel-${field}`).hide().css('display', '');
    }

    function cancelEdit(field) {
        $(`#display-${field}`).html(originalValues[field] || '');
        exitEdit(field);
    }

    // ══════════════════════════════════════════════════════════════════
    // EDIT / CANCEL BUTTON HANDLERS
    // ══════════════════════════════════════════════════════════════════

    $(document).on('click', '.tracking-edit-btn', function () {
        const target = $(this).data('target');
        const type   = $(this).data('type') || 'select';
        enterEdit(target, type);
    });

    $(document).on('click', '.tracking-cancel-btn', function () {
        cancelEdit(this.id.replace('cancel-', ''));
    });

    // ══════════════════════════════════════════════════════════════════
    // SAVE BUTTON HANDLER
    // ══════════════════════════════════════════════════════════════════

    $(document).on('click', '.tracking-save-btn', function () {
        const field   = this.id.replace('save-', '');
        const $select = $(`#select-${field}`);
        const $input  = $(`#input-${field}`);
        const value   = (field === 'status' || ($select.length && $select.css('display') !== 'none'))
            ? $select.val()
            : $input.val();

        saveTrackingField(field, value);
    });

    // ══════════════════════════════════════════════════════════════════
    // SAVE AJAX
    // ══════════════════════════════════════════════════════════════════

    function saveTrackingField(field, value) {
        const $row = $(`#row-${field}`);
        $row.addClass('tracking-saving');

        $.ajax({
            url    : '{{ route("edu-leads.updateTracking", $eduLead) }}',
            method : 'POST',
            data   : { _token: CSRF, _method: 'PATCH', field, value },
        }).done(function (response) {

            if (!response.success) {
                Swal.fire({ icon: 'error', title: 'Failed', text: response.message || 'Update failed.', confirmButtonColor: '#dc3545' });
                cancelEdit(field);
                return;
            }

            // ── Update display pill / text ────────────────────────────
            let newHtml = '';

            if (trackingLabels[field]) {
                const meta = trackingLabels[field][value];
                newHtml = meta
                    ? `<span class="status-pill ${meta.cls}">${meta.label}</span>`
                    : `<span class="text-muted fw-normal">—</span>`;

            } else if (field === 'booking_payment' || field === 'fees_collection') {
                const num = parseFloat(value);
                newHtml = (!value || isNaN(num))
                    ? '<span class="text-muted fw-normal">—</span>'
                    : `₹${num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                $(`#display-${field}`).toggleClass('has-value', !!value && !isNaN(num));

            } else if (field === 'application_number') {
                newHtml = value
                    ? `<span class="badge bg-light text-dark border fw-semibold" style="font-size:0.85rem;">AJK-${value}</span>`
                    : '<span class="text-muted fw-normal" style="font-size:0.9rem;">—</span>';

            } else if (field === 'cancellation_reason') {
                $(`#display-${field}`).text(value || '—');
                newHtml = null; // already set via .text()
            }

            if (newHtml !== null) {
                $(`#display-${field}`).html(newHtml);
            }

            // Store updated original for future cancel
            originalValues[field] = $(`#display-${field}`).html();

            // ── Exit edit mode (does NOT individually hide save/cancel
            //    for status — only hides the wrapper div) ───────────────
            exitEdit(field);

            // Success flash
            $(`#display-${field}`)
                .css({ transition: 'background 0.3s', background: '#d1fae5', borderRadius: '4px', padding: '2px 6px' });
            setTimeout(() => $(`#display-${field}`).css({ background: '', padding: '' }), 1200);

            // Reload after status change so Blade re-renders booking/cancellation sections
            if (field === 'status') {
                setTimeout(() => window.location.reload(), 900);
            }

        }).fail(function (xhr) {
            Swal.fire({
                icon: 'error', title: 'Error!',
                text: xhr.responseJSON?.message || 'Could not update field.',
                confirmButtonColor: '#dc3545',
            });
            cancelEdit(field);
        }).always(function () {
            $row.removeClass('tracking-saving');
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // CALL STATUS FIELD TOGGLE
    // ══════════════════════════════════════════════════════════════════
    $('#callStatusSelect').on('change', function () {
        const val = this.value;
        $('#connectedFields').toggle(val === 'connected');
        $('#notConnectedFields').toggle(val === 'not_connected');
    });

    $('#addCallModal').on('hidden.bs.modal', function () {
        $('#connectedFields').hide();
        $('#notConnectedFields').hide();
        $('#callStatusSelect').val('');
    });

    // ══════════════════════════════════════════════════════════════════
    // SHARED AJAX HELPER
    // ══════════════════════════════════════════════════════════════════
    function ajaxPost(url, formData) {
        return $.ajax({
            url, method: 'POST', data: formData, processData: false, contentType: false,
        }).fail(function (xhr) {
            let msg = 'An unexpected error occurred.';
            if (xhr.responseJSON?.errors)       msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
            Swal.fire({ icon: 'error', title: 'Error!', html: msg, confirmButtonColor: '#dc3545' });
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // SHARED FORM SUBMIT WRAPPER
    // ══════════════════════════════════════════════════════════════════
    function handleFormSubmit(formId, url, modalId, loadingText, successCb) {
        $(formId).on('submit', function (e) {
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            const orig = $btn.html();
            $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`);
            ajaxPost(url, new FormData(this))
                .done(function (response) { if (response.success) successCb(response); })
                .always(function () { $btn.prop('disabled', false).html(orig); });
        });
    }

    // ══════════════════════════════════════════════════════════════════
    // ADD FOLLOWUP
    // ══════════════════════════════════════════════════════════════════
    handleFormSubmit('#addFollowupForm', '{{ route("edu-leads.addFollowup", $eduLead) }}', '#addFollowupModal', 'Saving...', function (response) {
        $('#addFollowupModal').modal('hide');
        $('#addFollowupForm')[0].reset();
        Swal.fire({ icon: 'success', title: 'Followup Scheduled!', text: response.message, confirmButtonColor: '#667eea', timer: 2000, showConfirmButton: false })
            .then(() => {
                if (response.html) { $('#followupsContainer').find('.empty-state').remove().end().prepend(response.html); }
                else { location.reload(); }
            });
    });

    // ══════════════════════════════════════════════════════════════════
    // ADD CALL
    // ══════════════════════════════════════════════════════════════════
    handleFormSubmit('#addCallForm', '{{ route("edu-leads.addCall", $eduLead) }}', '#addCallModal', 'Saving...', function (response) {
        $('#addCallModal').modal('hide');
        $('#addCallForm')[0].reset();
        $('#connectedFields').hide();
        $('#notConnectedFields').hide();
        $('#callStatusSelect').val('');
        setCallDatetimeNow();
        Swal.fire({ icon: 'success', title: 'Call Logged!', text: response.message, confirmButtonColor: '#667eea', timer: 2000, showConfirmButton: false })
            .then(() => {
                if (response.html) { $('#callLogsContainer').find('.empty-state').remove().end().prepend(response.html); }
                else { location.reload(); }
            });
    });

    // ══════════════════════════════════════════════════════════════════
    // ADD NOTE
    // ══════════════════════════════════════════════════════════════════
    handleFormSubmit('#addNoteForm', '{{ route("edu-leads.addNote", $eduLead) }}', '#addNoteModal', 'Saving...', function (response) {
        $('#addNoteModal').modal('hide');
        $('#addNoteForm')[0].reset();
        Swal.fire({ icon: 'success', title: 'Note Added!', text: response.message, confirmButtonColor: '#667eea', timer: 2000, showConfirmButton: false })
            .then(() => {
                if (response.html) { $('#notesContainer').find('.empty-state').remove().end().prepend(response.html); }
                else { location.reload(); }
            });
    });

    // ── Complete Followup Modal ──────────────────────────────────────
    $(document).on('click', '.completeFollowupBtn', function () {
        const id           = $(this).data('id');
        const number       = $(this).data('number');
        const finalStatus  = $(this).data('final-status') || '';
        const status       = $(this).data('status')       || '';
        const interest     = $(this).data('interest')     || '';

        // ── Set followup id & label ──────────────────────────────────
        $('#completeFollowupId').val(id);
        $('#completeFollowupLabel').text('Followup #' + number);

        // ── Reset everything first ───────────────────────────────────
        $('#completeFollowupForm')[0].reset();
        $('.form-check[id^="interest-opt-"]').css({ background: '', 'border-color': '#dee2e6' });

        // ── Pre-select Final Status ──────────────────────────────────
        if (finalStatus) {
            $('#outcomeFinalStatus').val(finalStatus);
        }

        // ── Pre-select Next Action Status ────────────────────────────
        if (status) {
            $('#outcomeStatus').val(status);
        }

        // ── Pre-select & highlight Interest Level ────────────────────
        if (interest) {
            $('#interest' + interest).prop('checked', true);
            const colors = { hot: '#fee2e2', warm: '#fef3c7', cold: '#dbeafe' };
            $('#interest-opt-' + interest).css({
                background:    colors[interest] || '',
                'border-color': '#6366f1'
            });
        }
    });

    // ── Interest level visual toggle ─────────────────────────────────
    $(document).on('change', 'input[name="outcome_interest"]', function () {
        const colors = { hot: '#fee2e2', warm: '#fef3c7', cold: '#dbeafe' };
        $('.form-check[id^="interest-opt-"]').css({ background: '', 'border-color': '#dee2e6' });
        $('#interest-opt-' + this.value).css({
            background: colors[this.value],
            'border-color': '#6366f1'
        });
    });

    // ── Submit complete followup ─────────────────────────────────────
    $('#completeFollowupForm').on('submit', function (e) {
        e.preventDefault();

        const id  = $('#completeFollowupId').val();
        const btn = $('#completeFollowupSubmitBtn');
        const orig = btn.html();

        btn.prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url:  '/edu-leads/followups/' + id + '/complete',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#completeFollowupModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Followup Completed!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                }).then(() => window.location.reload());
            },
            error: function (xhr) {
                btn.prop('disabled', false).html(orig);
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    const msg = Object.values(errors).flat().join('\n');
                    Swal.fire({ icon: 'warning', title: 'Validation Error', text: msg });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            }
        });
    });

    // ── Open Edit Modal ──────────────────────────────────────────────
    $(document).on('click', '.editFollowupBtn', function () {
        const id                  = $(this).data('id');
        const number              = $(this).data('number');
        const date                = $(this).data('date');
        const time                = $(this).data('time')                 || '';
        const notes               = $(this).data('notes')               || '';
        const priority            = $(this).data('priority')            || 'medium';
        const outcomeFinalStatus  = $(this).data('outcome-final-status')|| '';
        const outcomeStatus       = $(this).data('outcome-status')      || '';
        const outcomeInterest     = $(this).data('outcome-interest')    || '';
        const outcomeNotes        = $(this).data('outcome-notes')       || '';
        const nextAction          = $(this).data('next-action')         || '';
        const isCompleted         = $(this).data('is-completed') == '1';

        // Basic fields
        $('#editFollowupId').val(id);
        $('#editFollowupLabel').text(ordinal(number) + ' Followup');
        $('#editFollowupDate').val(date);
        $('#editFollowupTime').val(time);
        $('#editFollowupNotes').val(notes);
        $('#editFollowupPriority').val(priority);

        // Outcome fields
        $('#editOutcomeFinalStatus').val(outcomeFinalStatus);
        $('#editOutcomeStatus').val(outcomeStatus);
        $('#editOutcomeNotes').val(outcomeNotes);
        $('#editNextAction').val(nextAction);

        // Reset all interest options styling
        const interestColors = { hot: '#fee2e2', warm: '#fef3c7', cold: '#dbeafe' };
        $('[id^="edit-interest-opt-"]').css({ background: '', 'border-color': '#dee2e6' });

        // Select correct interest radio
        const interestId = outcomeInterest
            ? '#editInterest' + outcomeInterest.charAt(0).toUpperCase() + outcomeInterest.slice(1)
            : '#editInterestNone';
        $(interestId).prop('checked', true);

        if (outcomeInterest && interestColors[outcomeInterest]) {
            $('#edit-interest-opt-' + outcomeInterest).css({
                background:     interestColors[outcomeInterest],
                'border-color': '#6366f1'
            });
        } else {
            $('#edit-interest-opt-none').css({
                background:     '#f1f5f9',
                'border-color': '#6366f1'
            });
        }

        // Show/hide the pending hint
        if (isCompleted) {
            $('#editOutcomePendingHint').hide();
        } else {
            $('#editOutcomePendingHint').show();
        }
    });

    // ── Interest toggle highlight in edit modal ──────────────────────
    $(document).on('change', '#editFollowupForm input[name="outcome_interest"]', function () {
        const colors = { hot: '#fee2e2', warm: '#fef3c7', cold: '#dbeafe' };
        $('[id^="edit-interest-opt-"]').css({ background: '', 'border-color': '#dee2e6' });
        const key = this.value || 'none';
        $('#edit-interest-opt-' + key).css({
            background:     colors[this.value] || '#f1f5f9',
            'border-color': '#6366f1'
        });
    });

    // ── Submit Edit ──────────────────────────────────────────────────
    $('#editFollowupForm').on('submit', function (e) {
        e.preventDefault();

        const id   = $('#editFollowupId').val();
        const btn  = $('#editFollowupSubmitBtn');
        const orig = btn.html();

        btn.prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url:  '/edu-leads/followups/' + id,
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#editFollowupModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                }).then(() => window.location.reload());
            },
            error: function (xhr) {
                btn.prop('disabled', false).html(orig);
                const errors = xhr.responseJSON?.errors;
                const msg = errors
                    ? Object.values(errors).flat().join('\n')
                    : (xhr.responseJSON?.message || 'Something went wrong.');
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });

    // ── Delete Followup ──────────────────────────────────────────────
    $(document).on('click', '.deleteFollowupBtn', function () {
        const id     = $(this).data('id');
        const number = $(this).data('number');

        Swal.fire({
            title: `Delete ${ordinal(number)} Followup?`,
            text:  'This action cannot be undone.',
            icon:  'warning',
            showCancelButton:   true,
            confirmButtonText:  'Yes, Delete',
            confirmButtonColor: '#dc3545',
            cancelButtonColor:  '#6c757d',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url:  '/edu-leads/followup/' + id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    Swal.fire({
                        icon: 'success', title: 'Deleted!',
                        text: res.message, timer: 1800, showConfirmButton: false,
                    }).then(() => {
                        $('#followup-node-' + id).fadeOut(300, function () {
                            $(this).remove();
                            reNumberFollowups(); // ← re-index remaining nodes
                        });
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error', title: 'Cannot Delete',
                        text: xhr.responseJSON?.message || 'Error deleting followup.',
                    });
                }
            });
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // DELETE HANDLERS
    // ══════════════════════════════════════════════════════════════════

    $(document).on('click', '.deleteCall', function () {
        const id = $(this).data('id');
        confirmDelete('Delete Call Log?').then(ok => {
            if (!ok) return;
            $.ajax({ url: `/edu-leads/call/${id}`, method: 'DELETE', data: { _token: CSRF } })
                .done(r => { if (r.success) fadeRemove(`#call-${id}`, '#callLogsContainer', 'call logs'); })
                .fail(deleteError);
        });
    });

    $(document).on('click', '.deleteNote', function () {
        const id = $(this).data('id');
        confirmDelete('Delete Note?').then(ok => {
            if (!ok) return;
            $.ajax({ url: `/edu-leads/note/${id}`, method: 'DELETE', data: { _token: CSRF } })
                .done(r => { if (r.success) fadeRemove(`#note-${id}`, '#notesContainer', 'notes'); })
                .fail(deleteError);
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // DELETE LEAD
    // ══════════════════════════════════════════════════════════════════
    $(document).on('click', '#deleteLeadBtn', function () {
        const name = $(this).data('name');
        Swal.fire({
            title: 'Delete Lead?',
            html: `Are you sure you want to delete <strong>${name}</strong>?<br><small class="text-muted">This cannot be undone.</small>`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, Delete',
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({ url: `{{ route('edu-leads.destroy', '') }}/${LEAD}`, method: 'DELETE', data: { _token: CSRF } })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false })
                        .then(() => { window.location.href = '{{ route("edu-leads.index") }}'; });
                })
                .fail(xhr => {
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Failed to delete.', confirmButtonColor: '#dc3545' });
                });
        });
    });

    // ══════════════════════════════════════════════════════════════════
    // UTILITIES
    // ══════════════════════════════════════════════════════════════════
    function confirmDelete(title) {
        return Swal.fire({
            title, text: 'This action cannot be undone.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, Delete',
        }).then(r => r.isConfirmed);
    }

    function fadeRemove(selector, containerId, entityName) {
        $(selector).fadeOut(300, function () {
            $(this).remove();
            const $c = $(containerId);
            if ($c.children(':not(.empty-state)').length === 0) {
                $c.html(`<div class="empty-state"><i class="las la-inbox"></i><p class="mb-0">No ${entityName} yet</p></div>`);
            }
        });
    }

    function deleteError(xhr) {
        Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Delete failed.', confirmButtonColor: '#dc3545' });
    }

    function setCallDatetimeNow() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('input[name="call_datetime"]').val(now.toISOString().slice(0, 16));
    }

    setCallDatetimeNow();
    $('#addCallModal').on('show.bs.modal', () => setCallDatetimeNow());

});
</script>
@endsection
