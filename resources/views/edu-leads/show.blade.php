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
        padding: 0.4rem 0.9rem;
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
    display: none;
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
    min-width: 140px;
}

#statusDropdown.active {
    display: inline-block;
}

/* Custom dropdown arrow */
#statusDropdown {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='rgba(255,255,255,0.8)' d='M5 7L1 3h8z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.6rem center;
    background-size: 10px;
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

        <!-- Lead Header with Action Buttons -->
        <div class="lead-header-card">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <h2 class="mb-0 me-3">{{ $eduLead->name }}</h2>

                        <!-- Status Display with Edit Toggle -->
                        <div class="status-container">
                            @php
                                $statusLabels = [
                                    'pending' => 'Pending',
                                    'contacted' => 'Contacted',
                                    'not_interest' => 'Not Interested',
                                    'follow_up' => 'Follow Up',
                                    'admitted' => 'Admitted',
                                    'dropped' => 'Dropped'
                                ];
                            @endphp

                            @if(auth()->user()->role === 'super_admin' ||
                                auth()->user()->role === 'lead_manager' ||
                                $eduLead->assigned_to === auth()->id())

                                <!-- Status Badge (shown by default) -->
                                <div class="lead-status-badge status-{{ $eduLead->final_status }}" id="statusBadge">
                                    <span class="status-indicator"></span>
                                    <span id="statusText">{{ $statusLabels[$eduLead->final_status] ?? ucfirst($eduLead->final_status) }}</span>
                                </div>

                                <!-- Dropdown (hidden by default) -->
                                <select class="form-select form-select-sm" id="statusDropdown">
                                    <option value="pending" {{ $eduLead->final_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="contacted" {{ $eduLead->final_status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                    <option value="not_interested" {{ $eduLead->final_status === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                                    <option value="follow_up" {{ $eduLead->final_status === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                                    <option value="admitted" {{ $eduLead->final_status === 'admitted' ? 'selected' : '' }}>Admitted</option>
                                    <option value="dropped" {{ $eduLead->final_status === 'dropped' ? 'selected' : '' }}>Dropped</option>
                                </select>

                                <!-- Edit icon button -->
                                <button type="button" class="status-edit-btn" id="statusEditBtn" title="Change Status">
                                    <i class="las la-pen"></i>
                                </button>

                            @else
                                <!-- Non-editable badge for users without permission -->
                                <div class="lead-status-badge status-{{ $eduLead->final_status }}">
                                    <span class="status-indicator"></span>
                                    {{ $statusLabels[$eduLead->final_status] ?? ucfirst($eduLead->final_status) }}
                                </div>
                            @endif
                        </div>

                    </div>

                    <p class="mb-0" style="opacity: 0.95; font-weight: 500;">
                        <i class="las la-tag me-2"></i>{{ $eduLead->lead_code }}
                        <i class="las la-calendar ms-3 me-2"></i>{{ $eduLead->created_at->format('d M Y') }}
                    </p>
                </div>

                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('edu-leads.index') }}" class="btn btn-light action-button me-2">
                        <i class="las la-arrow-left me-2"></i>Back to Leads
                    </a>

                    @php $user = auth()->user(); @endphp

                    {{-- EDIT button --}}
                    @if(
                        $user->role === 'super_admin'
                        || ($user->role === 'lead_manager' && $eduLead->created_by === $user->id)
                        || ($user->role === 'telecallers' && $eduLead->assigned_to === $user->id)
                    )
                        <a href="{{ route('edu-leads.edit', $eduLead->id) }}" class="btn btn-primary action-button me-2">
                            <i class="las la-edit me-2"></i>Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">

                <!-- EDUCATION DETAILS CARD -->
                <div class="education-card">
                    <h5><i class="las la-graduation-cap"></i>Education Details</h5>

                    @if($eduLead->course)
                    <div class="edu-row">
                        <span class="edu-label">Course</span>
                        <span class="edu-value">{{ $eduLead->course->name }}</span>
                    </div>
                    @endif

                    @if($eduLead->course_interested)
                    <div class="edu-row">
                        <span class="edu-label">Course Interested</span>
                        <span class="edu-value">{{ $eduLead->course_interested }}</span>
                    </div>
                    @endif

                    @if($eduLead->country)
                    <div class="edu-row">
                        <span class="edu-label">Country</span>
                        <span class="edu-value">{{ $eduLead->country }}</span>
                    </div>
                    @endif

                    @if($eduLead->college)
                    <div class="edu-row">
                        <span class="edu-label">College</span>
                        <span class="edu-value">{{ $eduLead->college }}</span>
                    </div>
                    @endif

                    @if($eduLead->interest_level)
                    <div class="edu-row">
                        <span class="edu-label">Interest Level</span>
                        <span class="edu-value">
                            <span class="interest-badge interest-{{ $eduLead->interest_level }}">
                                @if($eduLead->interest_level === 'hot') 🔥
                                @elseif($eduLead->interest_level === 'warm') ☀️
                                @else ❄️
                                @endif
                                {{ ucfirst($eduLead->interest_level) }}
                            </span>
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Contact Information -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-user"></i>Contact Information</h5>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $eduLead->email ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value">{{ $eduLead->phone }}</span>
                    </div>
                    @if($eduLead->whatsapp_number)
                    <div class="info-row">
                        <span class="info-label">WhatsApp</span>
                        <span class="info-value">{{ $eduLead->whatsapp_number }}</span>
                    </div>
                    @endif
                </div>

                <!-- Lead Details -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="las la-info-circle"></i>Lead Details</h5>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Source</span>
                        <span class="info-value">{{ $eduLead->leadSource->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created By</span>
                        <span class="info-value">{{ $eduLead->createdBy->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Assigned To</span>
                        <span class="info-value">{{ $eduLead->assignedTo->name ?? 'Unassigned' }}</span>
                    </div>

                    @if($eduLead->description)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                        <span class="info-label mb-2">Description</span>
                        <span class="info-value" style="text-align: left;">{{ $eduLead->description }}</span>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-lg-8">

                <!-- Scheduled Followups -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-calendar-check"></i>Scheduled Followups</h5>
                            <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addFollowupModal">
                                <i class="las la-plus me-1"></i>Add Followup
                            </button>
                        </div>
                    </div>

                    <div id="followupsContainer">
                        @if($eduLead->followups && $eduLead->followups->count() > 0)
                            @foreach($eduLead->followups as $followup)
                                <div class="followup-item {{ $followup->followup_date->isToday() ? 'today' : '' }} {{ $followup->followup_date->isPast() && $followup->status === 'pending' ? 'overdue' : '' }} {{ $followup->status === 'completed' ? 'completed' : '' }}" id="followup-{{ $followup->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-{{ $followup->priority === 'high' ? 'danger' : ($followup->priority === 'medium' ? 'warning' : 'info') }}">
                                                <i class="las la-flag me-1"></i>{{ ucfirst($followup->priority) }}
                                            </span>
                                            @if($followup->followup_date->isToday())
                                                <span class="badge bg-warning ms-2">Today</span>
                                            @endif
                                            @if($followup->followup_date->isPast() && $followup->status === 'pending')
                                                <span class="badge bg-danger ms-2">Overdue</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $followup->status === 'completed' ? 'success' : ($followup->status === 'cancelled' ? 'secondary' : 'primary') }}">
                                                {{ ucfirst($followup->status) }}
                                            </span>
                                            @if(auth()->user()->role === 'super_admin' || $followup->created_by === auth()->id() || $followup->assigned_to === auth()->id())
                                                <button class="btn btn-sm btn-danger ms-2 deleteFollowup" data-id="{{ $followup->id }}" title="Delete Followup">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong><i class="las la-calendar me-1"></i>Date:</strong>
                                                {{ $followup->followup_date->format('d M Y') }}
                                                @if($followup->followup_time)
                                                    <br><strong><i class="las la-clock me-1"></i>Time:</strong>
                                                    {{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong><i class="las la-user me-1"></i>Assigned To:</strong>
                                                {{ $followup->assignedToUser->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if($followup->notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small class="text-muted">{{ $followup->notes }}</small>
                                        </div>
                                    @endif

                                    @if($followup->status === 'pending' && (auth()->user()->role === 'super_admin' || $followup->assigned_to === auth()->id()))
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-success markFollowupComplete" data-id="{{ $followup->id }}">
                                                <i class="las la-check me-1"></i>Mark Complete
                                            </button>
                                        </div>
                                    @endif

                                    @if($followup->status === 'completed' && $followup->completed_at)
                                        <div class="mt-2">
                                            <small class="text-success">
                                                <i class="las la-check-circle me-1"></i>
                                                Completed on {{ $followup->completed_at->format('d M Y, h:i A') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="las la-calendar-times"></i>
                                <p class="mb-0">No followups scheduled yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Call Logs -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-phone"></i>Call Logs</h5>
                            <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addCallModal">
                                <i class="las la-plus me-1"></i>Add Call
                            </button>
                        </div>
                    </div>

                    <div id="callLogsContainer">
                        @if($eduLead->callLogs && $eduLead->callLogs->count() > 0)
                            @foreach($eduLead->callLogs as $call)
                                <div class="call-log-item" id="call-{{ $call->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <strong>{{ $call->user->name }}</strong>
                                            @if($call->interest_level)
                                                <span class="badge bg-{{ $call->interest_level === 'hot' ? 'danger' : ($call->interest_level === 'warm' ? 'warning' : 'info') }} ms-2">
                                                    {{ ucfirst($call->interest_level) }}
                                                </span>
                                            @endif
                                            <p class="text-muted small mb-1 mt-1">
                                                {{ \Carbon\Carbon::parse($call->call_datetime)->format('d M Y, h:i A') }}
                                            </p>
                                            @if($call->remarks)
                                                <p class="mb-0 small">{{ $call->remarks }}</p>
                                            @endif
                                        </div>
                                        @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager' || $call->user_id === auth()->id())
                                            <button class="btn btn-sm btn-danger deleteCall" data-id="{{ $call->id }}" title="Delete Call">
                                                <i class="las la-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="las la-phone-slash"></i>
                                <p class="mb-0">No call logs yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="las la-sticky-note"></i>Notes</h5>
                            <button class="btn btn-sm btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="las la-plus me-1"></i>Add Note
                            </button>
                        </div>
                    </div>

                    <div id="notesContainer">
                        @if($eduLead->notes && $eduLead->notes->count() > 0)
                            @foreach($eduLead->notes as $note)
                                <div class="note-item" id="note-{{ $note->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $note->createdBy->name }}</strong>
                                                <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0 mt-2">{{ $note->note }}</p>
                                        </div>
                                        @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager' || $note->created_by === auth()->id())
                                            <button class="btn btn-sm btn-danger ms-2 deleteNote" data-id="{{ $note->id }}" title="Delete Note">
                                                <i class="las la-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="las la-comment-slash"></i>
                                <p class="mb-0">No notes yet</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Add Followup Modal -->
<div class="modal fade" id="addFollowupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="las la-calendar-plus me-2"></i>Schedule Followup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFollowupForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Followup Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="followup_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Followup Time</label>
                            <input type="time" class="form-control" name="followup_time">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" required>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i>Schedule Followup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Call Modal -->
<div class="modal fade" id="addCallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="las la-phone me-2"></i>Log Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCallForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Call Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="call_datetime" required max="{{ date('Y-m-d\TH:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Interest Level</label>
                        <select class="form-select" name="interest_level">
                            <option value="">Select Level</option>
                            <option value="hot">🔥 Hot</option>
                            <option value="warm">☀️ Warm</option>
                            <option value="cold">❄️ Cold</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="3" placeholder="Call notes..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Next Action</label>
                        <input type="text" class="form-control" name="next_action" placeholder="Next steps...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Followup Date (Optional)</label>
                        <input type="date" class="form-control" name="followup_date" min="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i>Log Call
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="las la-sticky-note me-2"></i>Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" rows="4" required placeholder="Enter your note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i>Add Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // ========== ADD FOLLOWUP ==========
    $('#addFollowupForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '{{ route("edu-leads.addFollowup", $eduLead) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        $('#addFollowupModal').modal('hide');
                        $('#addFollowupForm')[0].reset();
                        location.reload(); // Reload to show new followup
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to schedule followup',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // ========== ADD CALL ==========
    $('#addCallForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '{{ route("edu-leads.addCall", $eduLead) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        $('#addCallModal').modal('hide');
                        $('#addCallForm')[0].reset();
                        location.reload(); // Reload to show new call
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to log call',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // ========== ADD NOTE ==========
    $('#addNoteForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '{{ route("edu-leads.addNote", $eduLead) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        $('#addNoteModal').modal('hide');
                        $('#addNoteForm')[0].reset();
                        location.reload(); // Reload to show new note
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to add note',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // ========== MARK FOLLOWUP COMPLETE ==========
    $(document).on('click', '.markFollowupComplete', function() {
        const followupId = $(this).data('id');

        Swal.fire({
            title: 'Mark as Complete?',
            text: 'This will mark the followup as completed',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Mark Complete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/edu-lead-followups/' + followupId + '/complete',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Completed!',
                                text: response.message,
                                confirmButtonColor: '#667eea'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to complete followup',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // ========== DELETE FOLLOWUP ==========
    $(document).on('click', '.deleteFollowup', function() {
        const followupId = $(this).data('id');

        Swal.fire({
            title: 'Delete Followup?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/edu-leads/followup/' + followupId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                confirmButtonColor: '#667eea'
                            }).then(() => {
                                $('#followup-' + followupId).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete followup',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // ========== DELETE CALL ==========
    $(document).on('click', '.deleteCall', function() {
        const callId = $(this).data('id');

        Swal.fire({
            title: 'Delete Call Log?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/edu-leads/call/' + callId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                confirmButtonColor: '#667eea'
                            }).then(() => {
                                $('#call-' + callId).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete call',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // ========== DELETE NOTE ==========
    $(document).on('click', '.deleteNote', function() {
        const noteId = $(this).data('id');

        Swal.fire({
            title: 'Delete Note?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/edu-leads/note/' + noteId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                confirmButtonColor: '#667eea'
                            }).then(() => {
                                $('#note-' + noteId).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete note',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // Toggle between badge and dropdown
    $('#statusEditBtn').on('click', function() {
        const badge = $('#statusBadge');
        const dropdown = $('#statusDropdown');

        if (dropdown.hasClass('active')) {
            dropdown.removeClass('active');
            badge.removeClass('hidden');
            $(this).find('i').removeClass('la-times').addClass('la-pen');
        } else {
            badge.addClass('hidden');
            dropdown.addClass('active');
            dropdown.focus();
            $(this).find('i').removeClass('la-pen').addClass('la-times');
        }
    });

    // Handle status change
    $('#statusDropdown').on('change', function() {
        const newStatus = $(this).val();
        const currentStatus = '{{ $eduLead->final_status }}';

        if (newStatus === currentStatus) return;

        const statusLabels = {
            'pending': 'Pending',
            'contacted': 'Contacted',
            'not_interested': 'Not Interested',
            'follow_up': 'Follow Up',
            'admitted': 'Admitted',
            'dropped': 'Dropped'
        };

        Swal.fire({
            title: 'Change Status?',
            html: `Update lead status to <strong>${statusLabels[newStatus]}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            confirmButtonText: 'Yes, Update'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Updating...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("edu-leads.update", $eduLead) }}',
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        final_status: newStatus,
                        name: '{{ $eduLead->name }}',
                        phone: '{{ $eduLead->phone }}',
                        lead_source_id: '{{ $eduLead->lead_source_id }}'
                    },
                    success: function(response) {
                        const badge = $('#statusBadge');
                        const statusText = $('#statusText');

                        // Update badge color
                        badge.removeClass('status-pending status-contacted status-not_interested status-follow_up status-admitted status-dropped');
                        badge.addClass('status-' + newStatus);
                        statusText.text(statusLabels[newStatus]);

                        // Hide dropdown, show badge
                        $('#statusDropdown').removeClass('active');
                        badge.removeClass('hidden');
                        $('#statusEditBtn').find('i').removeClass('la-times').addClass('la-pen');

                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        $('#statusDropdown').val(currentStatus);
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: xhr.responseJSON?.message || 'Update failed'
                        });
                    }
                });
            } else {
                $(this).val(currentStatus);
                $('#statusDropdown').removeClass('active');
                $('#statusBadge').removeClass('hidden');
                $('#statusEditBtn').find('i').removeClass('la-times').addClass('la-pen');
            }
        });
    });

    // Set default call datetime to now
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('input[name="call_datetime"]').val(now.toISOString().slice(0, 16));
});
</script>
@endsection
