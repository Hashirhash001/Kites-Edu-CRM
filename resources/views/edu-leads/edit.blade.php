@extends('layouts.app')

@section('title', 'Edit Lead - ' . $eduLead->name)

@section('extra-css')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 10px;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 10px 15px;
        transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* ERROR FIELD HIGHLIGHTING */
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545 !important;
        border-width: 2px !important;
        background-color: #fff5f5 !important;
    }

    .form-control.is-valid, .form-select.is-valid {
        border-color: #28a745 !important;
        border-width: 2px !important;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
        font-weight: 500;
    }

    .required-field::after {
        content: '*';
        color: #dc3545;
        margin-left: 4px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #667eea;
        margin-top: 25px;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #667eea;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-secondary {
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
    }

    .help-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }

    .icon-input {
        position: relative;
    }

    .icon-input i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        font-size: 18px;
    }

    .icon-input .form-control,
    .icon-input .form-select {
        padding-left: 45px;
    }

    .lead-info-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .lead-info-badge strong {
        font-size: 14px;
        opacity: 0.9;
    }

    .lead-info-badge span {
        font-size: 16px;
        font-weight: 700;
    }

    /* Loading spinner */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .card-header h4 {
            font-size: 18px;
        }

        .section-title {
            font-size: 14px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Edit Education Lead</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('edu-leads.index') }}">Education Leads</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('edu-leads.show', $eduLead) }}">{{ $eduLead->name }}</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('edu-leads.show', $eduLead) }}" class="btn btn-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="las la-edit me-2"></i> Edit Lead Information
                    </h4>
                    <p class="mb-0 mt-2 opacity-75">Update the details for this education lead</p>
                </div>
                <div class="card-body">

                    <!-- Lead Info Badge -->
                    <div class="lead-info-badge">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Lead Code:</strong><br>
                                <span>{{ $eduLead->lead_code }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Created:</strong><br>
                                <span>{{ $eduLead->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                <span>{{ ucfirst($eduLead->final_status) }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Interest:</strong><br>
                                <span>{{ $eduLead->interest_level ? ucfirst($eduLead->interest_level) : 'Not Set' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- AJAX FORM -->
                    <form id="editLeadForm" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information Section -->
                        <div class="section-title">
                            <i class="las la-user me-2"></i> Basic Information
                        </div>

                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required-field">Full Name</label>
                                <div class="icon-input">
                                    <i class="las la-user"></i>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $eduLead->name) }}"
                                           placeholder="Enter student's full name">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="icon-input">
                                    <i class="las la-envelope"></i>
                                    <input type="email"
                                           class="form-control"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $eduLead->email) }}"
                                           placeholder="student@example.com">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label required-field">Phone Number</label>
                                <div class="icon-input">
                                    <i class="las la-phone"></i>
                                    <input type="text"
                                           class="form-control"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone', $eduLead->phone) }}"
                                           placeholder="+91 9876543210">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- WhatsApp Number -->
                            <div class="col-md-6 mb-3">
                                <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                                <div class="icon-input">
                                    <i class="lab la-whatsapp"></i>
                                    <input type="text"
                                           class="form-control"
                                           id="whatsapp_number"
                                           name="whatsapp_number"
                                           value="{{ old('whatsapp_number', $eduLead->whatsapp_number) }}"
                                           placeholder="+91 9876543210">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Education Details Section -->
                        <div class="section-title mt-4">
                            <i class="las la-graduation-cap me-2"></i> Education Details
                        </div>

                        <div class="row">
                            <!-- Country -->
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <div class="icon-input">
                                    <i class="las la-globe"></i>
                                    <select class="form-select" id="country" name="country">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $countryOption)
                                            <option value="{{ $countryOption }}" {{ old('country', $eduLead->country) == $countryOption ? 'selected' : '' }}>
                                                {{ $countryOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- College/Institution -->
                            <div class="col-md-6 mb-3">
                                <label for="college" class="form-label">Current College/Institution</label>
                                <div class="icon-input">
                                    <i class="las la-university"></i>
                                    <input type="text"
                                           class="form-control"
                                           id="college"
                                           name="college"
                                           value="{{ old('college', $eduLead->college) }}"
                                           placeholder="Enter college name">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Course Interested -->
                            <div class="col-md-6 mb-3">
                                <label for="course_interested" class="form-label">Course Interested In</label>
                                <div class="icon-input">
                                    <i class="las la-book"></i>
                                    <input type="text"
                                           class="form-control"
                                           id="course_interested"
                                           name="course_interested"
                                           value="{{ old('course_interested', $eduLead->course_interested) }}"
                                           placeholder="e.g., MBA, MSc Computer Science">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Course (Dropdown) -->
                            <div class="col-md-6 mb-3">
                                <label for="course_id" class="form-label">Select Course (Optional)</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">No specific course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $eduLead->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Source & Priority Section -->
                        <div class="section-title mt-4">
                            <i class="las la-bullhorn me-2"></i> Source & Priority
                        </div>

                        <div class="row">
                            <!-- Lead Source -->
                            <div class="col-md-6 mb-3">
                                <label for="lead_source_id" class="form-label required-field">Lead Source</label>
                                <select class="form-select" id="lead_source_id" name="lead_source_id">
                                    <option value="">Select Source</option>
                                    @foreach($leadSources as $source)
                                        <option value="{{ $source->id }}" {{ old('lead_source_id', $eduLead->lead_source_id) == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Interest Level -->
                            <div class="col-md-6 mb-3">
                                <label for="interest_level" class="form-label">Interest Level</label>
                                <select class="form-select" id="interest_level" name="interest_level">
                                    <option value="">Not assessed yet</option>
                                    <option value="hot" {{ old('interest_level', $eduLead->interest_level) == 'hot' ? 'selected' : '' }}>🔥 Hot</option>
                                    <option value="warm" {{ old('interest_level', $eduLead->interest_level) == 'warm' ? 'selected' : '' }}>☀️ Warm</option>
                                    <option value="cold" {{ old('interest_level', $eduLead->interest_level) == 'cold' ? 'selected' : '' }}>❄️ Cold</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'lead_manager')
                        <div class="row">
                            <!-- Assign To -->
                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">Assign To</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">Unassigned</option>
                                    @foreach($telecallers as $telecaller)
                                        <option value="{{ $telecaller->id }}" {{ old('assigned_to', $eduLead->assigned_to) == $telecaller->id ? 'selected' : '' }}>
                                            {{ $telecaller->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        @endif

                        <!-- Additional Information Section -->
                        <div class="section-title mt-4">
                            <i class="las la-info-circle me-2"></i> Additional Information
                        </div>

                        <div class="row">
                            <!-- Description/Remarks -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description / Remarks</label>
                                <textarea class="form-control"
                                          id="description"
                                          name="description"
                                          rows="4"
                                          placeholder="Any additional notes or comments about this lead...">{{ old('description', $eduLead->description) }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('edu-leads.show', $eduLead) }}" class="btn btn-secondary">
                                        <i class="las la-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="las la-save me-1"></i> Update Lead
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
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // Auto-fill WhatsApp from phone if changed
    $('#phone').on('blur', function() {
        const whatsappField = $('#whatsapp_number');
        const currentWhatsapp = whatsappField.val();
        const originalWhatsapp = '{{ $eduLead->whatsapp_number }}';

        // Only auto-fill if WhatsApp is empty or same as original phone
        if (!currentWhatsapp || currentWhatsapp === '{{ $eduLead->phone }}') {
            whatsappField.val($(this).val());
        }
    });

    // AJAX FORM SUBMISSION
    $('#editLeadForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        $('.invalid-feedback').text('');

        // Disable submit button
        const submitBtn = $('#submitBtn');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Updating...'
        );

        // Get form data
        const formData = new FormData(this);

        // AJAX request
        $.ajax({
            url: '{{ route("edu-leads.update", $eduLead) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // SUCCESS - Show SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'View Lead',
                        showCancelButton: true,
                        cancelButtonText: 'Continue Editing',
                        confirmButtonColor: '#667eea',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to show page
                            window.location.href = response.redirect_url;
                        } else {
                            // Stay on edit page, re-enable button
                            submitBtn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalHtml);

                if (xhr.status === 422) {
                    // VALIDATION ERRORS - Highlight fields
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul class="mb-0">';

                    $.each(errors, function(field, messages) {
                        // Highlight the field
                        const fieldElement = $('[name="' + field + '"]');
                        fieldElement.addClass('is-invalid');

                        // Show error message under field
                        const feedbackElement = fieldElement.closest('.mb-3').find('.invalid-feedback');
                        feedbackElement.text(messages[0]);

                        // Add to error list
                        errorHtml += '<li>' + messages[0] + '</li>';
                    });

                    errorHtml += '</ul>';

                    // Show SweetAlert with all errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorHtml,
                        confirmButtonColor: '#dc3545'
                    });

                    // Scroll to first error
                    const firstError = $('.is-invalid:first');
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 500);
                    }

                } else if (xhr.status === 403) {
                    // AUTHORIZATION ERROR
                    Swal.fire({
                        icon: 'error',
                        title: 'Unauthorized',
                        text: xhr.responseJSON?.message || 'You are not authorized to edit this lead',
                        confirmButtonColor: '#dc3545'
                    });

                } else {
                    // SERVER ERROR
                    const errorMessage = xhr.responseJSON?.message || 'An error occurred while updating the lead';

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#dc3545'
                    });
                }
            }
        });
    });

    // Remove error highlight on input change
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).closest('.mb-3').find('.invalid-feedback').text('');
    });
});
</script>
@endsection
