@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Edit Profile</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Left column: forms ───────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Profile Info Form --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-user-edit me-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name"
                               value="{{ old('name', $user->name) }}" required>
                        <div class="invalid-feedback" id="name-error">@error('name'){{ $message }}@enderror</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email"
                               value="{{ old('email', $user->email) }}" required>
                        <div class="invalid-feedback" id="email-error">@error('email'){{ $message }}@enderror</div>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="form-label fw-semibold">Phone Number</label>
                        <input type="text"
                               class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="e.g. +91 98765 43210">
                        <div class="invalid-feedback" id="phone-error">@error('phone'){{ $message }}@enderror</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                            <i class="las la-save me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password Form --}}
        <div class="card mt-3" id="change-password">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-lock me-2"></i>Change Password
                </h5>
            </div>
            <div class="card-body">
                <form id="passwordForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="current_password">
                                <i class="las la-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block" id="current_password-error">
                            @error('current_password'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="password">
                                <i class="las la-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Min 8 characters, with uppercase, lowercase &amp; numbers.</div>
                        <div class="invalid-feedback d-block" id="password-error">
                            @error('password'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="password_confirmation">
                                <i class="las la-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block" id="password_confirmation-error"></div>
                    </div>

                    <button type="submit" class="btn btn-danger" id="savePasswordBtn">
                        <i class="las la-key me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Right column: avatar card ────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <div class="mx-auto mb-3" style="width:100px;height:100px;border-radius:50%;
                     background:linear-gradient(135deg,#667eea,#764ba2);
                     display:flex;align-items:center;justify-content:center;
                     font-size:2.5rem;font-weight:700;color:#fff;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
            </div>
        </div>

        {{-- Tips card --}}
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="las la-lightbulb me-1"></i> Password Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-1"><i class="las la-check text-success me-1"></i> At least 8 characters</li>
                    <li class="mb-1"><i class="las la-check text-success me-1"></i> Mix uppercase &amp; lowercase</li>
                    <li class="mb-1"><i class="las la-check text-success me-1"></i> Include numbers</li>
                    <li><i class="las la-times text-danger me-1"></i> Avoid common words</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── Password visibility toggle ────────────────────────────────
    $(document).on('click', '.toggle-password', function () {
        const targetId = $(this).data('target');
        const input    = $('#' + targetId);
        const icon     = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('la-eye').addClass('la-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('la-eye-slash').addClass('la-eye');
        }
    });

    // ── Helper: clear all field errors ───────────────────────────
    function clearErrors(form) {
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
    }

    // ── Helper: show field errors from Laravel 422 ────────────────
    function showErrors(form, errors) {
        $.each(errors, function (field, messages) {
            const input = form.find('[name="' + field + '"]');
            input.addClass('is-invalid');
            const errDiv = $('#' + field + '-error');
            if (errDiv.length) {
                errDiv.text(messages[0]).addClass('d-block');
            } else {
                input.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
            }
        });
    }

    // ── Profile form ──────────────────────────────────────────────
    $('#profileForm').on('submit', function (e) {
        e.preventDefault();
        const form    = $(this);
        const btn     = $('#saveProfileBtn');
        const formData = new FormData(this);

        clearErrors(form);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url:         '{{ route("profile.update") }}',
            type:        'POST',          // POST + _method=PUT in FormData
            data:        formData,
            processData: false,
            contentType: false,
            success: function (res) {
                Swal.fire({
                    icon: 'success', title: 'Profile Updated!',
                    text: res.message, timer: 2000, showConfirmButton: false
                }).then(() => { window.location.href = res.redirect; });
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="las la-save me-1"></i> Save Changes');
                if (xhr.status === 422) {
                    showErrors(form, xhr.responseJSON.errors);
                    Swal.fire({ icon: 'error', title: 'Validation Error',
                                text: 'Please fix the highlighted fields.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!',
                                text: xhr.responseJSON?.message || 'An error occurred.' });
                }
            }
        });
    });

    // ── Password form ─────────────────────────────────────────────
    $('#passwordForm').on('submit', function (e) {
        e.preventDefault();
        const form    = $(this);
        const btn     = $('#savePasswordBtn');
        const formData = new FormData(this);

        clearErrors(form);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        $.ajax({
            url:         '{{ route("profile.password.update") }}',
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function (res) {
                btn.prop('disabled', false).html('<i class="las la-key me-1"></i> Update Password');
                Swal.fire({
                    icon: 'success', title: 'Password Updated!',
                    text: res.message, timer: 2500, showConfirmButton: false
                }).then(() => { form[0].reset(); });
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="las la-key me-1"></i> Update Password');
                if (xhr.status === 422) {
                    showErrors(form, xhr.responseJSON.errors);
                    const firstMsg = Object.values(xhr.responseJSON.errors)[0][0];
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: firstMsg });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!',
                                text: xhr.responseJSON?.message || 'An error occurred.' });
                }
            }
        });
    });

    // ── Clear field error on input ─────────────────────────────
    $(document).on('input change', 'input', function () {
        $(this).removeClass('is-invalid');
        const fieldName = $(this).attr('name');
        if (fieldName) $('#' + fieldName + '-error').text('').removeClass('d-block');
    });

    // ── Auto-scroll to #change-password if in URL hash ───────────
    if (window.location.hash === '#change-password') {
        setTimeout(() => {
            document.getElementById('change-password')?.scrollIntoView({ behavior: 'smooth' });
            $('#current_password').focus();
        }, 300);
    }
});

@if(session('success'))
    Swal.fire({ icon: 'success', title: 'Success!', text: '{{ session("success") }}',
                timer: 3000, showConfirmButton: false });
@endif
@if(session('error'))
    Swal.fire({ icon: 'error', title: 'Error!', text: '{{ session("error") }}' });
@endif
</script>
@endsection
