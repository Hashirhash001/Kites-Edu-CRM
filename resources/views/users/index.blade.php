@extends('layouts.app')

@section('title', 'Users Management')

@section('extra-css')
<style>
    .user-name-link {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 600;
    }
    .user-name-link:hover {
        color: #0a58ca;
        text-decoration: underline;
    }
    .user-name-link h6 {
        color: inherit;
        font-weight: 600;
        margin: 0;
    }
</style>
@endsection

@section('content')

{{-- Page Title --}}
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Users Management</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end g-2">

                    {{-- Role Filter --}}
                    @if(auth()->user()->role !== 'lead_manager')
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">Role</label>
                        <select class="form-select" id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="super_admin"  {{ request('role') == 'super_admin'  ? 'selected' : '' }}>Super Admin</option>
                            <option value="lead_manager" {{ request('role') == 'lead_manager' ? 'selected' : '' }}>Lead Manager</option>
                            <option value="telecallers"  {{ request('role') == 'telecallers'  ? 'selected' : '' }}>Telecaller</option>
                        </select>
                    </div>
                    @else
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">Role</label>
                        <input type="text" class="form-control" value="Telecallers Only" disabled>
                        <small class="text-muted">You can only manage telecallers</small>
                    </div>
                    @endif

                    {{-- Status Filter --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    {{-- Search --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchUser" placeholder="Name, email or phone...">
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary flex-grow-1" id="resetFilters">
                                <i class="las la-redo me-1"></i> Reset
                            </button>
                            @if(auth()->user()->role === 'super_admin')
                            <button type="button" class="btn btn-primary flex-grow-1" id="addUserBtn">
                                <i class="las la-plus me-1"></i> Add User
                            </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Users Table --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    Users List
                    <span class="badge bg-secondary ms-2" id="userCount">{{ $users->total() }}</span>
                </h4>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="usersTable">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @include('users.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Modal (super_admin only) --}}
@if(auth()->user()->role === 'super_admin')
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                @csrf
                <input type="hidden" id="user_id"      name="user_id">
                <input type="hidden" id="form_method"  name="_method" value="POST">

                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" required>
                            <span class="text-danger error-text name_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                            <span class="text-danger error-text email_error"></span>
                        </div>
                    </div>

                    <div class="row mb-3" id="passwordFields">
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Min 8 characters">
                            <span class="text-danger error-text password_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repeat password">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number">
                            <span class="text-danger error-text phone_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="lead_manager">Lead Manager</option>
                                <option value="telecallers">Telecaller</option>
                            </select>
                            <span class="text-danger error-text role_error"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch form-switch-success mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('extra-scripts')
<script>
$(document).ready(function () {

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── LOAD USERS ─────────────────────────────────────────────
    function loadUsers(url) {
        const requestUrl = url || '{{ route("users.index") }}';
        const params = {
            role:   $('#roleFilter').val(),
            status: $('#statusFilter').val(),
            search: $('#searchUser').val(),
        };

        $.ajax({
            url: requestUrl,
            type: 'GET',
            data: params,
            success: function (res) {
                $('#usersTableBody').html(res.html);
                $('#paginationContainer').html(res.pagination);
                $('#userCount').text(res.total);
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load users.' });
            }
        });
    }

    // Pagination
    $(document).on('click', '#paginationContainer .pagination a', function (e) {
        e.preventDefault();
        loadUsers($(this).attr('href'));
    });

    // Filter change
    let filterTimeout;
    $('#roleFilter, #statusFilter, #searchUser').on('change keyup', function () {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(loadUsers, 300);
    });

    // Reset
    $('#resetFilters').on('click', function () {
        $('#roleFilter, #statusFilter, #searchUser').val('');
        window.location.href = '{{ route("users.index") }}';
    });

    @if(auth()->user()->role === 'super_admin')

    // ── ADD USER ────────────────────────────────────────────────
    $('#addUserBtn').on('click', function () {
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('#form_method').val('POST');
        $('#userModalLabel').text('Add User');
        $('#passwordFields').show();
        $('#password, #password_confirmation').attr('required', true);
        $('.error-text').text('');
        $('#userModal').modal('show');
    });

    // ── EDIT USER ────────────────────────────────────────────────
    $(document).on('click', '.editBtn', function () {
        const userId = $(this).data('id');

        $.get('/users/' + userId + '/edit', function (res) {
            const u = res.user;
            $('#userModalLabel').text('Edit User');
            $('#user_id').val(u.id);
            $('#form_method').val('PUT');
            $('#name').val(u.name);
            $('#email').val(u.email);
            $('#phone').val(u.phone);
            $('#role').val(u.role);
            $('#is_active').prop('checked', u.is_active);
            $('#passwordFields').hide();
            $('#password, #password_confirmation').attr('required', false);
            $('.error-text').text('');
            $('#userModal').modal('show');
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to fetch user data.' });
        });
    });

    // ── SAVE FORM ────────────────────────────────────────────────
    $('#userForm').on('submit', function (e) {
        e.preventDefault();
        const userId = $('#user_id').val();
        const url    = userId ? '/users/' + userId : '/users';

        $('.error-text').text('');

        $.ajax({
            url:         url,
            type:        'POST',
            data:        new FormData(this),
            processData: false,
            contentType: false,
            success: function (res) {
                $('#userModal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Success!', text: res.message, timer: 2000, showConfirmButton: false })
                    .then(() => loadUsers());
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, val) {
                        $('.' + key + '_error').text(val[0]);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong!' });
                }
            }
        });
    });

    // ── DELETE ───────────────────────────────────────────────────
    $(document).on('click', '.deleteBtn', function () {
        const userId = $(this).data('id');

        Swal.fire({
            title: 'Delete this user?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/users/' + userId,
                    type: 'DELETE',
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, timer: 2000, showConfirmButton: false })
                            .then(() => loadUsers());
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete user.' });
                    }
                });
            }
        });
    });

    @endif
});
</script>
@endsection
