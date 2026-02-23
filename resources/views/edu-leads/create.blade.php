@extends('layouts.app')
@section('title', 'Create Education Lead')

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
        padding-top:3px;
        color:#495057;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-color:#667eea;
        border-radius:6px;
        box-shadow:0 4px 16px rgba(102,126,234,0.15);
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color:#667eea !important;
    }
    .select2-container--bootstrap-5 .select2-search__field {
        border-color:#667eea !important;
        border-radius:4px !important;
    }
    .select2-container { width:100% !important; }
    /* Show is-invalid border on Select2 */
    .is-invalid + .select2-container--bootstrap-5 .select2-selection,
    .select2-hidden-accessible.is-invalid ~ * .select2-selection {
        border-color:#dc3545 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid mt-4">

    {{-- Page Header --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Create Education Lead</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('edu-leads.index') }}">Education Leads</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('edu-leads.index') }}" class="btn btn-secondary">
                    <i class="las la-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="las la-graduation-cap me-2"></i> New Education Lead</h4>
                    <p class="mb-0 mt-2 opacity-75">Fill in the details below to create a new education lead</p>
                </div>

                <div class="card-body">
                    <form id="createLeadForm" method="POST">
                        @csrf

                        {{-- ═══════════════════════════════════════
                             1. BASIC INFORMATION
                        ═══════════════════════════════════════ --}}
                        <div class="section-title"><i class="las la-user me-2"></i> Basic Information</div>

                        <div class="row">
                            {{-- Full Name --}}
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required-field">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       placeholder="Enter student's full name">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="student@example.com">
                                <div class="invalid-feedback"></div>
                                <small class="help-text">Optional — recommended for communication</small>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Phone --}}
                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label required-field">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       placeholder="+91 9876543210">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- WhatsApp --}}
                            <div class="col-md-4 mb-3">
                                <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                                <input type="text" class="form-control" id="whatsapp_number"
                                       name="whatsapp_number" placeholder="+91 9876543210">
                                <div class="invalid-feedback"></div>
                                <small class="help-text">Leave blank to copy from phone</small>
                            </div>

                            {{-- Application Number --}}
                            <div class="col-md-4 mb-3">
                                <label for="application_number_suffix" class="form-label">Application Number</label>
                                <div class="input-group app-num-group">
                                    <span class="input-group-text">AJK-</span>
                                    <input type="text" class="form-control" id="application_number_suffix"
                                           name="application_number_suffix"
                                           placeholder="e.g. 2026-0001" maxlength="20">
                                </div>
                                <div class="invalid-feedback" id="application_number_error"></div>
                                <small class="help-text">Saved as AJK-<em>your input</em></small>
                            </div>
                        </div>

                        <div class="row">
                            {{-- State --}}
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <select class="form-select" id="state" name="state">
                                    <option value=""></option>
                                    @foreach($states as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- District --}}
                            <div class="col-md-4 mb-3">
                                <label for="district" class="form-label">District</label>
                                <select class="form-select" id="district" name="district">
                                    <option value="">Select state first</option>
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

                        <div class="mb-3">
                            <label class="form-label">Institution Type</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="institution_type" id="inst_none" value="" checked>
                                <label class="btn btn-outline-secondary" for="inst_none">Not Specified</label>

                                <input type="radio" class="btn-check" name="institution_type" id="inst_school" value="school">
                                <label class="btn btn-outline-primary" for="inst_school">🏫 School</label>

                                <input type="radio" class="btn-check" name="institution_type" id="inst_college" value="college">
                                <label class="btn btn-outline-success" for="inst_college">🎓 College</label>

                                <input type="radio" class="btn-check" name="institution_type" id="inst_other" value="other">
                                <label class="btn btn-outline-warning" for="inst_other">Other</label>
                            </div>
                            <div class="invalid-feedback" id="institution_type_error"></div>
                        </div>

                        {{-- School fields --}}
                        <div class="row conditional-section hidden" id="schoolFields">
                            <div class="col-md-6 mb-3">
                                <label for="school" class="form-label">School Name</label>
                                <input type="text" class="form-control" id="school" name="school"
                                       placeholder="e.g. Kendriya Vidyalaya, Palakkad">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="school_department" class="form-label">Stream / Department</label>
                                <select class="form-select" id="school_department" name="school_department">
                                    <option value="">Select Stream</option>
                                    @foreach(['Computer Science', 'Biology Science','Commerce','Arts & Journalism', 'Humanities','Vocational','Other'] as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- College fields --}}
                        <div class="row conditional-section hidden" id="collegeFields">
                            <div class="col-md-6 mb-3">
                                <label for="college" class="form-label">College Name</label>
                                <input type="text" class="form-control" id="college" name="college"
                                       placeholder="e.g. Government Engineering College">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="college_department" class="form-label">Department</label>
                                <select class="form-select" id="college_department" name="college_department">
                                    <option value="">Select Department</option>
                                    @foreach(['Engineering','Medical','Arts','Commerce','Science','Law','Management','Other'] as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
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
                                    @foreach($states as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                                <small class="help-text">State the student prefers to study in</small>
                            </div>

                            {{-- Programme filter (UI only) --}}
                            <div class="col-md-4 mb-3">
                                <label for="programme_filter" class="form-label">Programme</label>
                                <select class="form-select" id="programme_filter">
                                    <option value=""></option>
                                    @foreach($programmes as $programme)
                                        <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                                    @endforeach
                                </select>
                                <small class="help-text">Filter courses by programme</small>
                            </div>

                            {{-- Specific Course --}}
                            <div class="col-md-4 mb-3">
                                <label for="course_id" class="form-label">Specific Course</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">No specific course yet</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}"
                                                data-programme="{{ $course->programme_id }}">
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Addon Course --}}
                            <div class="col-md-6 mb-3">
                                <label for="addon_course" class="form-label">
                                    Addon Course <small class="text-muted fw-normal">(secondary interest)</small>
                                </label>
                                <input type="text" class="form-control" id="addon_course"
                                       name="addon_course"
                                       placeholder="e.g. IELTS Preparation, Foundation Year...">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                             4. SOURCE & ASSIGNMENT
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-bullhorn me-2"></i> Source & Assignment
                        </div>

                        <div class="row">
                            {{-- Lead Source --}}
                            <div class="col-md-4 mb-3">
                                <label for="lead_source_id" class="form-label required-field">Lead Source</label>
                                <select class="form-select" id="lead_source_id" name="lead_source_id">
                                    <option value=""></option>
                                    @foreach($leadSources as $source)
                                        <option value="{{ $source->id }}"
                                                data-name="{{ strtolower($source->name) }}">
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Agent Name — shown only for Agent/Partner --}}
                            <div class="col-md-4 mb-3 conditional-section hidden" id="agentNameField">
                                <label for="agent_name" class="form-label">Agent / Partner Name</label>
                                <input type="text" class="form-control" id="agent_name"
                                       name="agent_name" placeholder="Agent or partner name...">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Referral Name — shown only for Referral source --}}
                            <div class="col-md-4 mb-3 conditional-section hidden" id="referralNameField">
                                <label for="referral_name" class="form-label">Referral Name</label>
                                <input type="text" class="form-control" id="referral_name"
                                    name="referral_name" placeholder="Name of the person who referred...">
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Interest Level --}}
                            <div class="col-md-4 mb-3">
                                <label for="interest_level" class="form-label">Interest Level</label>
                                <select class="form-select" id="interest_level" name="interest_level">
                                    <option value="">Not assessed yet</option>
                                    <option value="hot">🔥 Hot</option>
                                    <option value="warm">☀️ Warm</option>
                                    <option value="cold">❄️ Cold</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <small class="help-text">Can be assessed after first contact</small>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Branch --}}
                            @if(in_array(auth()->user()->role, ['super_admin', 'operation_head']))
                            <div class="col-md-4 mb-3">
                                <label for="branch_id" class="form-label">Branch</label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value=""></option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            @else
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Branch</label>
                                <input type="text" class="form-control bg-light"
                                       value="{{ auth()->user()->branch?->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="branch_id" value="{{ $userBranchId }}">
                            </div>
                            @endif
                        </div>

                        {{-- ═══════════════════════════════════════
                             5. ADDITIONAL NOTES
                        ═══════════════════════════════════════ --}}
                        {{-- <div class="section-title mt-4">
                            <i class="las la-info-circle me-2"></i> Additional Notes
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                          placeholder="Any remarks about this lead..."></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Any additional notes..."></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div> --}}

                        {{-- ═══════════════════════════════════════
                            6. NEXT ACTION
                        ═══════════════════════════════════════ --}}
                        <div class="section-title mt-4">
                            <i class="las la-tasks me-2"></i> Next Action
                        </div>
                        <div class="tracking-card">

                            <div class="row">

                                {{-- Status --}}
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">
                                        <i class="las la-toggle-on text-primary me-1"></i> Status
                                    </label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="" @selected(old('status', $eduLead->status ?? '') === '')>— Select Status —</option>
                                        <option value="whatsapp_link_submitted"    @selected(old('status', $eduLead->status ?? '') === 'whatsapp_link_submitted')>📲 WhatsApp Link Submitted</option>
                                        <option value="application_form_submitted" @selected(old('status', $eduLead->status ?? '') === 'application_form_submitted')>📋 Application Form Submitted</option>
                                        <option value="booking"                    @selected(old('status', $eduLead->status ?? '') === 'booking')>💳 Booking</option>
                                        <option value="cancelled"                  @selected(old('status', $eduLead->status ?? '') === 'cancelled')>🚫 Cancelled</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                {{-- Final Lead Status --}}
                                <div class="col-md-6 mb-3">
                                    <label for="final_status" class="form-label">
                                        <i class="las la-flag text-info me-1"></i> Final Lead Status
                                    </label>
                                    <select class="form-select" id="final_status" name="final_status">
                                        <option value="pending"        @selected(old('final_status', $eduLead->final_status ?? 'pending')        === 'pending')       >⏳ Pending</option>
                                        <option value="contacted"      @selected(old('final_status', $eduLead->final_status ?? '')               === 'contacted')      >📞 Contacted</option>
                                        <option value="follow_up"      @selected(old('final_status', $eduLead->final_status ?? '')               === 'follow_up')      >🔔 Follow Up</option>
                                        <option value="admitted"       @selected(old('final_status', $eduLead->final_status ?? '')               === 'admitted')       >✅ Admitted</option>
                                        <option value="not_interested" @selected(old('final_status', $eduLead->final_status ?? '')               === 'not_interested') >❌ Not Interested</option>
                                        <option value="dropped"        @selected(old('final_status', $eduLead->final_status ?? '')               === 'dropped')        >🚫 Dropped</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                            </div>

                            {{-- Booking fields — shown only when status = "booking" --}}
                            <div class="row conditional-section hidden" id="bookingFields">

                                <div class="col-md-6 mb-3">
                                    <label for="booking_payment" class="form-label">
                                        <i class="las la-rupee-sign text-warning me-1"></i> Booking Payment (₹)
                                    </label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        id="booking_payment" name="booking_payment"
                                        placeholder="0.00"
                                        value="{{ old('booking_payment', $eduLead->booking_payment ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fees_collection" class="form-label">
                                        <i class="las la-money-bill text-success me-1"></i> Fees Collected (₹)
                                    </label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        id="fees_collection" name="fees_collection"
                                        placeholder="0.00"
                                        value="{{ old('fees_collection', $eduLead->fees_collection ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                            </div>

                            {{-- Cancellation Reason — shown only when status = "cancelled" --}}
                            <div class="row conditional-section hidden" id="cancellationFields">

                                <div class="col-md-12 mb-3">
                                    <label for="cancellation_reason" class="form-label">
                                        <i class="las la-ban text-danger me-1"></i> Cancellation Reason
                                    </label>
                                    <textarea class="form-control" id="cancellation_reason" name="cancellation_reason"
                                            rows="2" placeholder="Reason for cancellation...">{{ old('cancellation_reason', $eduLead->cancellation_reason ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                            </div>

                        </div>

                        {{-- Action Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('edu-leads.index') }}" class="btn btn-secondary">
                                        <i class="las la-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="las la-save me-1"></i> Create Lead
                                    </button>
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
<script>
$(function () {

    var districtMap = @json($districtMap);

    function s2(sel, ph) {
        $(sel).select2({ theme: 'bootstrap-5', placeholder: ph, allowClear: true, width: '100%' });
    }

    function populateDistricts(state, sel) {
        var list = (state && Array.isArray(districtMap[state])) ? districtMap[state] : [];
        var $d = $('#district');
        if ($d.hasClass('select2-hidden-accessible')) $d.select2('destroy');
        $d.empty().append('<option value=""></option>');
        $.each(list, function (i, d) { $d.append(new Option(d, d, d === sel, d === sel)); });
        $('#district').select2({
            theme: 'bootstrap-5',
            placeholder: list.length ? 'Search district...' : 'Select a state first...',
            allowClear: true, width: '100%'
        });
    }

    // ── INIT SELECT2 ─────────────────────────────────────────────────
    s2('#state',              'Search state...');
    s2('#district',           'Search district...');
    s2('#preferred_state',    'Search preferred state...');
    s2('#programme_filter',   'Select programme...');
    s2('#course_id',          'Select course...');
    s2('#lead_source_id',     'Select source...');
    s2('#interest_level',     'Select interest level...');
    s2('#branch_id',          'Select branch...');
    s2('#school_department',  'Select stream...');
    s2('#college_department', 'Select department...');

    // ── MASTER COURSE LIST ────────────────────────────────────────────
    var allCourseOptions = [];
    $('#course_id option').each(function () {
        var v = $(this).val();
        if (!v) return;
        allCourseOptions.push({
            val:       v,
            text:      $(this).text().trim(),
            programme: String($(this).attr('data-programme') || '')
        });
    });

    // ── PROGRAMME → COURSE CASCADE ────────────────────────────────────
    function applyCascade(reset) {
        var pid = String($('#programme_filter').val() || '');
        var cur = $('#course_id').val();
        var $cs = $('#course_id');
        if ($cs.hasClass('select2-hidden-accessible')) $cs.select2('destroy');
        $cs.empty().append('<option value="">No specific course yet</option>');
        $.each(allCourseOptions, function (i, o) {
            if (pid && o.programme !== pid) return;
            $cs.append($('<option>', { value: o.val, text: o.text }).attr('data-programme', o.programme));
        });
        if (reset && !$cs.find('option[value="' + cur + '"]').length) $cs.val('');
        s2('#course_id', 'Select course...');
    }

    $('#programme_filter').on('change', function () { applyCascade(true); });
    $('#state').on('change', function () { populateDistricts($(this).val(), ''); });

    $('#phone').on('blur', function () {
        if (!$('#whatsapp_number').val()) $('#whatsapp_number').val($(this).val());
    });

    // ── LEAD SOURCE → conditional agent / referral name fields ────────
    $('#lead_source_id').on('change', function () {
        var name = $(this).find('option:selected').data('name') || '';
        var isAgent    = name.includes('agent') || name.includes('partner');
        var isReferral = name.includes('referral');

        $('#agentNameField').toggleClass('hidden', !isAgent);
        $('#referralNameField').toggleClass('hidden', !isReferral);

        if (!isAgent)    $('#agent_name').val('');
        if (!isReferral) $('#referral_name').val('');
    });

    // ── INSTITUTION TYPE ─────────────────────────────────────────────
    $('input[name="institution_type"]').on('change', function () {
        var v = $(this).val();
        $('#schoolFields').toggleClass('hidden',  v !== 'school');
        $('#collegeFields').toggleClass('hidden', v !== 'college');
        if (v !== 'school')  { $('#school').val('');  $('#school_department').val('').trigger('change'); }
        if (v !== 'college') { $('#college').val(''); $('#college_department').val('').trigger('change'); }
    });

    // ── STATUS → conditional booking / cancellation fields ────────────
    function handleStatusChange() {
        var val = $('#status').val();
        var isBooking    = val === 'booking';
        var isCancelled  = val === 'cancelled';

        $('#bookingFields').toggleClass('hidden', !isBooking);
        $('#cancellationFields').toggleClass('hidden', !isCancelled);

        // clear hidden fields so stale data isn't submitted
        if (!isBooking) {
            $('#booking_payment').val('');
            $('#fees_collection').val('');
        }
        if (!isCancelled) {
            $('#cancellation_reason').val('');
        }
    }

    $('#status').on('change', handleStatusChange);
    // Run on page load to handle old() repopulation
    handleStatusChange();

    // ── FORM SUBMIT ───────────────────────────────────────────────────
    $('#createLeadForm').on('submit', function (e) {
        e.preventDefault();
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#institution_type_error, #application_number_error').text('');

        var suffix = $('#application_number_suffix').val().trim();
        if (!$('#appNumFull').length) {
            $('<input type="hidden" id="appNumFull" name="application_number">').appendTo(this);
        }
        $('#appNumFull').val(suffix ? 'AJK-' + suffix : '');

        var form = this, $btn = $('#submitBtn'), orig = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        $.ajax({
            url: '{{ route("edu-leads.store") }}',
            method: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (r) {
                if (r.success) {
                    Swal.fire({
                        icon: 'success', title: 'Lead Created!',
                        html: 'Lead <strong>' + r.lead_code + '</strong> created successfully.',
                        confirmButtonText: 'View Lead', showCancelButton: true,
                        cancelButtonText: 'Create Another',
                        confirmButtonColor: '#667eea', cancelButtonColor: '#6c757d'
                    }).then(function (res) {
                        if (res.isConfirmed) {
                            window.location.href = r.redirect_url;
                        } else {
                            form.reset();
                            $('#appNumFull').remove();
                            $('#schoolFields, #collegeFields, #agentNameField, #referralNameField, #bookingFields, #cancellationFields').addClass('hidden');
                            $('#state, #district, #preferred_state, #programme_filter, #course_id, #lead_source_id, #interest_level, #branch_id, #school_department, #college_department')
                                .val('').trigger('change');
                            applyCascade(true);
                            $btn.prop('disabled', false).html(orig);
                        }
                    });
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html(orig);
                if (xhr.status === 422) {
                    var errs = xhr.responseJSON.errors, html = '<ul class="mb-0 text-start">';
                    $.each(errs, function (field, msgs) {
                        if (field === 'institution_type') {
                            $('#institution_type_error').text(msgs[0]);
                        } else if (field === 'application_number') {
                            $('#application_number_error').text(msgs[0]);
                        } else {
                            var $f = $('[name="' + field + '"]');
                            $f.addClass('is-invalid');
                            $f.closest('.mb-3').find('.invalid-feedback').first().text(msgs[0]);
                        }
                        html += '<li>' + msgs[0] + '</li>';
                    });
                    Swal.fire({ icon: 'error', title: 'Please fix the errors', html: html + '</ul>', confirmButtonColor: '#dc3545' });
                    var $first = $('.is-invalid').first();
                    if ($first.length) $('html,body').animate({ scrollTop: $first.offset().top - 120 }, 400);
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Server Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }
        });
    });

    $(document).on('input change', '.form-control, .form-select', function () {
        $(this).removeClass('is-invalid');
        $(this).closest('.mb-3').find('.invalid-feedback').first().text('');
    });

});
</script>
@endsection
