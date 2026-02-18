@extends('layouts.app')

@section('title', 'Create Education Lead')

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

    /* ✅ ERROR FIELD HIGHLIGHTING */
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
<div class="container-fluid mt-4">
    <!-- Page Header -->
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
                <div>
                    <a href="{{ route('edu-leads.index') }}" class="btn btn-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="las la-graduation-cap me-2"></i> New Education Lead
                    </h4>
                    <p class="mb-0 mt-2 opacity-75">Fill in the details below to create a new education lead</p>
                </div>
                <div class="card-body">
                    <!-- ✅ AJAX FORM -->
                    <form id="createLeadForm" method="POST">
                        @csrf

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
                                           placeholder="student@example.com">
                                </div>
                                <div class="invalid-feedback"></div>
                                <small class="help-text">Optional - but recommended for communication</small>
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
                                           placeholder="+91 9876543210">
                                </div>
                                <div class="invalid-feedback"></div>
                                <small class="help-text">Leave blank if same as phone</small>
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
                                        <option value="India">India</option>
                                        <option value="USA">USA</option>
                                        <option value="UK">UK</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Other">Other</option>
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
                                           placeholder="e.g., MBA, MSc Computer Science">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Course (Dropdown) -->
                            <div class="col-md-6 mb-3">
                                <label for="course_id" class="form-label">Select Course (Optional)</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">No specific course yet</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Lead Source & Priority Section -->
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
                                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Interest Level -->
                            <div class="col-md-6 mb-3">
                                <label for="interest_level" class="form-label">Interest Level</label>
                                <select class="form-select" id="interest_level" name="interest_level">
                                    <option value="">Not assessed yet</option>
                                    <option value="hot">🔥 Hot</option>
                                    <option value="warm">☀️ Warm</option>
                                    <option value="cold">❄️ Cold</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <small class="help-text">Will be assessed after first contact</small>
                            </div>
                        </div>

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
                                          placeholder="Any additional notes or comments about this lead..."></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
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
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // Auto-fill WhatsApp from phone if left blank
    $('#phone').on('blur', function() {
        const whatsappField = $('#whatsapp_number');
        if (!whatsappField.val()) {
            whatsappField.val($(this).val());
        }
    });

    // ✅ AJAX FORM SUBMISSION
    $('#createLeadForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        $('.invalid-feedback').text('');

        // Disable submit button
        const submitBtn = $('#submitBtn');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Creating...'
        );

        // Get form data
        const formData = new FormData(this);

        // AJAX request
        $.ajax({
            url: '{{ route("edu-leads.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // ✅ SUCCESS - Show SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'View Lead',
                        showCancelButton: true,
                        cancelButtonText: 'Create Another',
                        confirmButtonColor: '#667eea',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to show page
                            window.location.href = response.redirect_url;
                        } else {
                            // Reset form for another entry
                            $('#createLeadForm')[0].reset();
                            submitBtn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalHtml);

                if (xhr.status === 422) {
                    // ✅ VALIDATION ERRORS - Highlight fields
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

                } else {
                    // ✅ SERVER ERROR
                    const errorMessage = xhr.responseJSON?.message || 'An error occurred while creating the lead';

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
