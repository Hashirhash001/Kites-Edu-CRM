@extends('layouts.app')
@section('title', 'Edit Lead - ' . $eduLead->name)

@section('extra-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<style>
    .card { border:none; box-shadow:0 0 20px rgba(0,0,0,0.1); border-radius:10px; }
    .card-header {
        background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
        color:white; border-radius:10px 10px 0 0 !important; padding:20px;
    }
    .form-label { font-weight:600; color:#495057; margin-bottom:8px; }
    .form-control, .form-select {
        border:1px solid #e0e0e0; border-radius:6px; padding:10px 15px; transition:all 0.3s;
    }
    .form-control:focus, .form-select:focus {
        border-color:#667eea; box-shadow:0 0 0 0.2rem rgba(102,126,234,0.25);
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color:#dc3545 !important; border-width:2px !important;
        background-color:#fff5f5 !important;
    }
    .invalid-feedback { display:block; color:#dc3545; font-size:13px; margin-top:5px; font-weight:500; }
    .required-field::after { content:'*'; color:#dc3545; margin-left:4px; }
    .section-title {
        font-size:16px; font-weight:700; color:#667eea;
        margin-top:25px; margin-bottom:15px;
        padding-bottom:8px; border-bottom:2px solid #667eea;
    }
    .btn-primary {
        background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
        border:none; padding:12px 30px; border-radius:6px; font-weight:600; transition:all 0.3s;
    }
    .btn-primary:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 5px 15px rgba(102,126,234,0.4); }
    .btn-primary:disabled { opacity:.6; cursor:not-allowed; }
    .btn-secondary { padding:12px 30px; border-radius:6px; font-weight:600; }
    .help-text { font-size:12px; color:#6c757d; margin-top:4px; }
    .app-num-group .input-group-text { background:#667eea; color:#fff; font-weight:700; border-color:#667eea; border-radius:6px 0 0 6px; }
    .spinner-border-sm { width:1rem; height:1rem; border-width:.15em; }
    .conditional-section { transition:opacity .25s ease; }
    .conditional-section.hidden { display:none; }
    .lead-info-badge {
        background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
        color:white; padding:15px; border-radius:8px; margin-bottom:20px;
    }
    .lead-info-badge strong { font-size:14px; opacity:.9; }
    .lead-info-badge span   { font-size:16px; font-weight:700; }
    .tracking-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:20px; margin-top:8px; }

    /* ── Select2 custom theme ─────────────────────────────────── */
    .select2-container--bootstrap-5 .select2-selection {
        border:1px solid #e0e0e0 !important;
        border-radius:6px !important;
        min-height:44px !important;
        padding:6px 12px !important;
        font-size:14px;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color:#667eea !important;
        box-shadow:0 0 0 0.2rem rgba(102,126,234,0.25) !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-top:3px; color:#495057;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-color:#667eea; border-radius:6px;
        box-shadow:0 4px 16px rgba(102,126,234,0.15);
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color:#667eea !important;
    }
    .select2-container--bootstrap-5 .select2-search__field {
        border-color:#667eea !important; border-radius:4px !important;
    }
    .select2-container { width:100% !important; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-4">

    {{-- Page Header --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Edit Education Lead</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('edu-leads.index') }}">Education Leads</a></li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('edu-leads.show', $eduLead) }}">{{ $eduLead->name }}</a>
                            </li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('edu-leads.show', $eduLead) }}" class="btn btn-secondary">
                    <i class="las la-arrow-left me-1"></i> Back to Details
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="las la-edit me-2"></i> Edit Lead Information</h4>
                    <p class="mb-0 mt-2 opacity-75">Update the details for this education lead</p>
                </div>

                <div class="card-body">

                    {{-- Lead Info Badge --}}
                    <div class="lead-info-badge">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Lead Code</strong><br>
                                <span>{{ $eduLead->lead_code }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Created</strong><br>
                                <span>{{ $eduLead->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Final Status</strong><br>
                                <span>{{ ucfirst(str_replace('_', ' ', $eduLead->final_status)) }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Interest</strong><br>
                                <span>{{ $eduLead->interest_level ? ucfirst($eduLead->interest_level) : 'Not Set' }}</span>
                            </div>
                        </div>
                    </div>

                    <form id="editLeadForm" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- ═══════════════════════════════════════
                             1. BASIC INFORMATION
                        ═══════════════════════════════════════ --}}
                        <div class="section-title"><i class="las la-user me-2"></i> Basic Information</div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required-field">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="{{ old('name', $eduLead->name) }}"
                                       placeholder="Enter student's full name">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="{{ old('email', $eduLead->email) }}"
                                       placeholder="student@example.com">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label required-field">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="{{ old('phone', $eduLead->phone) }}"
                                       placeholder="+91 9876543210">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                                <input type="text" class="form-control" id="whatsapp_number"
                                       name="whatsapp_number"
                                       value="{{ old('whatsapp_number', $eduLead->whatsapp_number) }}"
                                       placeholder="+91 9876543210">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Application Number --}}
                            <div class="col-md-4 mb-3">
                                <label for="application_number_suffix" class="form-label">Application Number</label>
                                @php
                                    $raw = old('application_number', $eduLead->application_number ?? '');
                                    $appSuffix = str_starts_with($raw, 'AJK-') ? substr($raw, 4) : $raw;
                                @endphp
                                <div class="input-group app-num-group">
                                    <span class="input-group-text">AJK-</span>
                                    <input type="text" class="form-control" id="application_number_suffix"
                                           name="application_number_suffix"
                                           value="{{ $appSuffix }}"
                                           placeholder="e.g. 2026-0001" maxlength="20">
                                </div>
                                <div class="invalid-feedback" id="application_number_error"></div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- State --}}
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <select class="form-select" id="state" name="state">
                                    <option value=""></option>
                                    @foreach($states as $stateOption)
                                        <option value="{{ $stateOption }}"
                                            {{ old('state', $eduLead->state) === $stateOption ? 'selected' : '' }}>
                                            {{ $stateOption }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- District --}}
                            <div class="col-md-4 mb-3">
                                <label for="district" class="form-label">District</label>
                                <select class="form-select" id="district" name="district">
                                    <option value=""></option>
                                    {{-- Options injected by JS on load --}}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                             2. CURRENT EDUCATIONAL DETAILS
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-school me-2"></i> Current Educational Details
                        </div>

                        @php $instType = old('institution_type', $eduLead->institution_type ?? ''); @endphp

                        <div class="mb-3">
                            <label class="form-label">Institution Type</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="institution_type" id="inst_none" value=""
                                       {{ $instType === '' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary" for="inst_none">Not Specified</label>

                                <input type="radio" class="btn-check" name="institution_type" id="inst_school" value="school"
                                       {{ $instType === 'school' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="inst_school">🏫 School</label>

                                <input type="radio" class="btn-check" name="institution_type" id="inst_college" value="college"
                                       {{ $instType === 'college' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="inst_college">🎓 College</label>

                                <input type="radio" class="btn-check" name="institution_type" id="inst_other" value="other"
                                       {{ $instType === 'other' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning" for="inst_other">Other</label>
                            </div>
                            <div class="invalid-feedback" id="institution_type_error"></div>
                        </div>

                        {{-- School fields --}}
                        <div class="row conditional-section {{ $instType === 'school' ? '' : 'hidden' }}" id="schoolFields">
                            <div class="col-md-6 mb-3">
                                <label for="school" class="form-label">School Name</label>
                                <input type="text" class="form-control" id="school" name="school"
                                       value="{{ old('school', $eduLead->school) }}"
                                       placeholder="e.g. Kendriya Vidyalaya, Palakkad">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="school_department" class="form-label">Stream / Department</label>
                                <select class="form-select" id="school_department" name="school_department">
                                    <option value="">Select Stream</option>
                                    @foreach(['Science','Commerce','Arts','Vocational','Other'] as $s)
                                        <option value="{{ $s }}"
                                            {{ old('school_department', $eduLead->school_department) === $s ? 'selected' : '' }}>
                                            {{ $s }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- College fields --}}
                        <div class="row conditional-section {{ $instType === 'college' ? '' : 'hidden' }}" id="collegeFields">
                            <div class="col-md-6 mb-3">
                                <label for="college" class="form-label">College Name</label>
                                <input type="text" class="form-control" id="college" name="college"
                                       value="{{ old('college', $eduLead->college) }}"
                                       placeholder="e.g. Government Engineering College">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="college_department" class="form-label">Department</label>
                                <select class="form-select" id="college_department" name="college_department">
                                    <option value="">Select Department</option>
                                    @foreach(['Engineering','Medical','Arts','Commerce','Science','Law','Management','Other'] as $d)
                                        <option value="{{ $d }}"
                                            {{ old('college_department', $eduLead->college_department) === $d ? 'selected' : '' }}>
                                            {{ $d }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                             3. PROGRAMME & COURSE INTEREST
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-book me-2"></i> Programme & Course Interest
                        </div>

                        <div class="row">
                            {{-- Preferred State --}}
                            <div class="col-md-4 mb-3">
                                <label for="preferred_state" class="form-label">Preferred State</label>
                                <select class="form-select" id="preferred_state" name="preferred_state">
                                    <option value=""></option>
                                    @foreach($states as $stateOption)
                                        <option value="{{ $stateOption }}"
                                            {{ old('preferred_state', $eduLead->preferred_state) === $stateOption ? 'selected' : '' }}>
                                            {{ $stateOption }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                                <small class="help-text">State the student prefers to study in</small>
                            </div>

                            {{-- Programme filter --}}
                            <div class="col-md-4 mb-3">
                                <label for="programme_filter" class="form-label">Programme</label>
                                <select class="form-select" id="programme_filter">
                                    <option value=""></option>
                                    @foreach($programmes as $programme)
                                        <option value="{{ $programme->id }}"
                                            {{ old('programme_filter', $eduLead->course?->programme_id) == $programme->id ? 'selected' : '' }}>
                                            {{ $programme->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help-text">Filter courses by programme</small>
                            </div>

                            {{-- Specific Course --}}
                            <div class="col-md-4 mb-3">
                                <label for="course_id" class="form-label">Specific Course</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">No specific course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}"
                                                data-programme="{{ $course->programme_id }}"
                                            {{ old('course_id', $eduLead->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="addon_course" class="form-label">
                                    Addon Course <small class="text-muted fw-normal">(secondary interest)</small>
                                </label>
                                <input type="text" class="form-control" id="addon_course" name="addon_course"
                                       value="{{ old('addon_course', $eduLead->addon_course) }}"
                                       placeholder="e.g. IELTS Preparation, Foundation Year...">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                             4. STATUS & TRACKING
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-tasks me-2"></i> Status & Tracking
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Call Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Select Status</option>
                                    @foreach([
                                        'pending'             => 'Pending',
                                        'connected'           => 'Connected',
                                        'not_connected'       => 'Not Connected',
                                        'interested'          => 'Interested',
                                        'not_interested'      => 'Not Interested',
                                        'follow_up_scheduled' => 'Follow-up Scheduled',
                                        'admitted'            => '✅ Admitted',
                                        'closed'              => 'Closed',
                                    ] as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('status', $eduLead->status) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="final_status" class="form-label">Final Status</label>
                                <select class="form-select" id="final_status" name="final_status">
                                    <option value="">Select Final Status</option>
                                    @foreach([
                                        'pending'        => '⏳ Pending',
                                        'contacted'      => '📞 Contacted',
                                        'follow_up'      => '🔔 Follow Up',
                                        'admitted'       => '✅ Admitted',
                                        'not_interested' => '❌ Not Interested',
                                        'dropped'        => '🚫 Dropped',
                                    ] as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('final_status', $eduLead->final_status) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="interest_level" class="form-label">Interest Level</label>
                                <select class="form-select" id="interest_level" name="interest_level">
                                    <option value="">Not assessed yet</option>
                                    <option value="hot"  {{ old('interest_level', $eduLead->interest_level) === 'hot'  ? 'selected' : '' }}>🔥 Hot</option>
                                    <option value="warm" {{ old('interest_level', $eduLead->interest_level) === 'warm' ? 'selected' : '' }}>☀️ Warm</option>
                                    <option value="cold" {{ old('interest_level', $eduLead->interest_level) === 'cold' ? 'selected' : '' }}>❄️ Cold</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="next_action" class="form-label">Next Action</label>
                                <input type="text" class="form-control" id="next_action" name="next_action"
                                       value="{{ old('next_action', $eduLead->next_action) }}"
                                       placeholder="e.g. Send brochure, Schedule campus tour...">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="followup_date" class="form-label">Follow-up Date</label>
                                <input type="date" class="form-control" id="followup_date" name="followup_date"
                                       value="{{ old('followup_date', $eduLead->followup_date?->format('Y-m-d')) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                             5. SOURCE & ASSIGNMENT
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-bullhorn me-2"></i> Source & Assignment
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="lead_source_id" class="form-label required-field">Lead Source</label>
                                <select class="form-select" id="lead_source_id" name="lead_source_id">
                                    <option value=""></option>
                                    @foreach($leadSources as $source)
                                        <option value="{{ $source->id }}"
                                                data-name="{{ strtolower($source->name) }}"
                                            {{ old('lead_source_id', $eduLead->lead_source_id) == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Agent Name --}}
                            @php
                                $srcName = strtolower($eduLead->leadSource?->name ?? '');
                                $isAgent = str_contains($srcName, 'agent') || str_contains($srcName, 'partner');
                            @endphp
                            <div class="col-md-4 mb-3 conditional-section {{ $isAgent ? '' : 'hidden' }}" id="agentNameField">
                                <label for="agent_name" class="form-label">Agent / Partner Name</label>
                                <input type="text" class="form-control" id="agent_name" name="agent_name"
                                       value="{{ old('agent_name', $eduLead->agent_name) }}"
                                       placeholder="Agent or partner name...">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Assign To --}}
                            @if(in_array(auth()->user()->role, ['super_admin', 'operation_head']) || auth()->user()->isLeadManager())
                            <div class="col-md-4 mb-3">
                                <label for="assigned_to" class="form-label">Assign To (Telecaller)</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">— Unassigned —</option>
                                    @foreach($assignableUsers as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('assigned_to', $eduLead->assigned_to) == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                            @if($u->branch) — {{ $u->branch->name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            @endif
                        </div>

                        <div class="row">
                            @if(in_array(auth()->user()->role, ['super_admin', 'operation_head']))
                            <div class="col-md-4 mb-3">
                                <label for="branch_id" class="form-label">Branch</label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value=""></option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ old('branch_id', $eduLead->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            @else
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Branch</label>
                                <input type="text" class="form-control bg-light"
                                       value="{{ auth()->user()->branch?->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            </div>
                            @endif
                        </div>

                        {{-- ═══════════════════════════════════════
                             6. ADDITIONAL NOTES
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-info-circle me-2"></i> Additional Notes
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                          placeholder="Any remarks about this lead...">{{ old('remarks', $eduLead->remarks) }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Any additional notes...">{{ old('description', $eduLead->description) }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                             7. APPLICATION & PAYMENT TRACKING
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-file-invoice-dollar me-2"></i> Application & Payment Tracking
                        </div>

                        <div class="tracking-card">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="whatsapp_link" class="form-label">
                                        <i class="lab la-whatsapp text-success me-1"></i> WhatsApp Link
                                    </label>
                                    <input type="url" class="form-control" id="whatsapp_link"
                                           name="whatsapp_link"
                                           value="{{ old('whatsapp_link', $eduLead->whatsapp_link) }}"
                                           placeholder="https://wa.me/group/...">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="application_form_url" class="form-label">
                                        <i class="las la-file-alt text-primary me-1"></i> Application Form
                                    </label>
                                    <input type="url" class="form-control" id="application_form_url"
                                           name="application_form_url"
                                           value="{{ old('application_form_url', $eduLead->application_form_url) }}"
                                           placeholder="https://university.com/apply/...">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="booking_payment" class="form-label">
                                        <i class="las la-rupee-sign text-warning me-1"></i> Booking Payment
                                    </label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                           id="booking_payment" name="booking_payment"
                                           value="{{ old('booking_payment', $eduLead->booking_payment) }}"
                                           placeholder="0.00">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="fees_collection" class="form-label">
                                        <i class="las la-money-bill text-success me-1"></i> Fees Collection
                                    </label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                           id="fees_collection" name="fees_collection"
                                           value="{{ old('fees_collection', $eduLead->fees_collection) }}"
                                           placeholder="0.00">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cancellation_reason" class="form-label">
                                        <i class="las la-ban text-danger me-1"></i> Cancellation Reason
                                    </label>
                                    <textarea class="form-control" id="cancellation_reason"
                                              name="cancellation_reason" rows="1"
                                              placeholder="Reason if applicable...">{{ old('cancellation_reason', $eduLead->cancellation_reason) }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="las la-info-circle me-1"></i>
                                        Last updated: {{ $eduLead->updated_at->format('d M Y, h:i A') }}
                                        @if($eduLead->updatedBy ?? false) by {{ $eduLead->updatedBy->name }} @endif
                                    </small>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('edu-leads.show', $eduLead) }}" class="btn btn-secondary">
                                            <i class="las la-times me-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="las la-save me-1"></i> Update Lead
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const districtMap = @json($districtMap);

// ── Select2 factory ───────────────────────────────────────────────────
function s2(selector, placeholder) {
    $(selector).select2({
        theme:       'bootstrap-5',
        placeholder: placeholder,
        allowClear:  true,
        width:       '100%',
    });
}

// ── Populate district options then re-init Select2 ────────────────────
function populateDistricts(state, selectedDistrict) {
    const districts = districtMap[state] || [];
    const $d = $('#district');
    $d.select2('destroy');
    $d.empty().append('<option value=""></option>');
    districts.forEach(function (d) {
        $d.append(new Option(d, d, d === selectedDistrict, d === selectedDistrict));
    });
    s2('#district', 'Search district...');
}

$(document).ready(function () {

    // ── Init all Select2 dropdowns ────────────────────────────────────
    s2('#state',              'Search state...');
    s2('#district',           'Search district...');
    s2('#preferred_state',    'Search preferred state...');
    s2('#programme_filter',   'Select programme...');
    s2('#course_id',          'Select course...');
    s2('#lead_source_id',     'Select source...');
    s2('#interest_level',     'Select interest level...');
    s2('#branch_id',          'Select branch...');
    s2('#assigned_to',        'Select telecaller...');
    s2('#status',             'Select call status...');
    s2('#final_status',       'Select final status...');
    s2('#school_department',  'Select stream...');
    s2('#college_department', 'Select department...');

    // ── State → District cascade (restore saved on load) ──────────────
    populateDistricts(
        '{{ old("state", $eduLead->state ?? "") }}',
        '{{ old("district", $eduLead->district ?? "") }}'
    );

    $('#state').on('change', function () {
        populateDistricts($(this).val(), '');
    });

    // ── Auto-fill WhatsApp ────────────────────────────────────────────
    $('#phone').on('blur', function () {
        const wa = $('#whatsapp_number');
        if (!wa.val() || wa.val() === '{{ $eduLead->phone }}') {
            wa.val($(this).val());
        }
    });

    // ── Lead source → Agent Name ──────────────────────────────────────
    function toggleAgent() {
        const name = $('#lead_source_id option:selected').data('name') || '';
        const show = name.includes('agent') || name.includes('partner');
        $('#agentNameField').toggleClass('hidden', !show);
        if (!show) $('#agent_name').val('');
    }
    $('#lead_source_id').on('change', toggleAgent);

    // ── Institution type toggle ───────────────────────────────────────
    $('input[name="institution_type"]').on('change', function () {
        const val = $(this).val();
        $('#schoolFields').toggleClass('hidden', val !== 'school');
        $('#collegeFields').toggleClass('hidden', val !== 'college');
        if (val !== 'school')  { $('#school').val('');  $('#school_department').val('').trigger('change'); }
        if (val !== 'college') { $('#college').val(''); $('#college_department').val('').trigger('change'); }
    });

    // ── Programme → Course cascade ────────────────────────────────────
    function applyCascade(resetCurrent) {
        const programmeId = $('#programme_filter').val();
        const current     = $('#course_id').val();

        $('#course_id option').each(function () {
            const $o = $(this);
            if (!$o.val()) return;
            const match = !programmeId || String($o.data('programme')) === String(programmeId);
            $o.prop('disabled', !match);
        });

        $('#course_id').trigger('change.select2');

        if (resetCurrent && current) {
            const ok = $('#course_id option[value="' + current + '"]:not([disabled])').length > 0;
            if (!ok) $('#course_id').val('').trigger('change');
        }
    }

    $('#programme_filter').on('change', function () { applyCascade(true); });
    applyCascade(false); // restore pre-selected without wiping

    // ── Form submission ───────────────────────────────────────────────
    $('#editLeadForm').on('submit', function (e) {
        e.preventDefault();

        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#institution_type_error, #application_number_error').text('');

        // Build full application number
        const suffix = $('#application_number_suffix').val().trim();
        if (!$('#_appNumFull').length) {
            $('<input type="hidden" id="_appNumFull" name="application_number">').appendTo(this);
        }
        $('#_appNumFull').val(suffix ? 'AJK-' + suffix : '');

        const $form      = $(this);
        const $btn       = $('#submitBtn');
        const origHtml   = $btn.html();

        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Updating...'
        );

        // Temporarily re-enable disabled options so FormData sends them
        $('#course_id option:disabled').prop('disabled', false);
        const formData = new FormData($form[0]);
        applyCascade(false);

        $.ajax({
            url:         '{{ route("edu-leads.update", $eduLead) }}',
            method:      'POST',
            data:        formData,
            processData: false,
            contentType: false,
            headers:     { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },

            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        html: 'Lead <strong>' + response.lead_code + '</strong> updated successfully.',
                        confirmButtonText:  'View Lead',
                        showCancelButton:   true,
                        cancelButtonText:   'Continue Editing',
                        confirmButtonColor: '#667eea',
                        cancelButtonColor:  '#6c757d',
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = response.redirect_url;
                        } else {
                            $btn.prop('disabled', false).html(origHtml);
                        }
                    });
                }
            },

            error: function (xhr) {
                $btn.prop('disabled', false).html(origHtml);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let html = '<ul class="mb-0 text-start">';

                    $.each(errors, function (field, messages) {
                        if (field === 'institution_type') {
                            $('#institution_type_error').text(messages[0]);
                        } else if (field === 'application_number') {
                            $('#application_number_error').text(messages[0]);
                        } else {
                            const $f = $('[name="' + field + '"]');
                            $f.addClass('is-invalid');
                            $f.closest('.mb-3').find('.invalid-feedback').first().text(messages[0]);
                        }
                        html += '<li>' + messages[0] + '</li>';
                    });

                    html += '</ul>';
                    Swal.fire({ icon: 'error', title: 'Please fix the errors', html, confirmButtonColor: '#dc3545' });

                    const $first = $('.is-invalid:first');
                    if ($first.length) {
                        $('html, body').animate({ scrollTop: $first.offset().top - 120 }, 400);
                    }
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Server Error',
                        text: xhr.responseJSON?.message || 'Something went wrong.',
                        confirmButtonColor: '#dc3545',
                    });
                }
            }
        });
    });

    // ── Clear validation state on input ──────────────────────────────
    $(document).on('input change', '.form-control, .form-select', function () {
        $(this).removeClass('is-invalid');
        $(this).closest('.mb-3').find('.invalid-feedback').first().text('');
    });

});
</script>
@endsection
