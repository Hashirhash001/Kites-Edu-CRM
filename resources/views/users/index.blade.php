@extends('layouts.app')

@use(App\Models\User)

@section('title', 'Users Management')

@section('extra-css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* ── Design tokens (match dashboard) ───────────────────────────── */
    :root {
        --primary    : #2563eb;
        --border     : #e5e7eb;
        --bg-subtle  : #f8fafc;
        --text-muted : #64748b;
        --radius-md  : 12px;
        --shadow-sm  : 0 1px 3px rgba(0,0,0,0.08);
        --shadow-md  : 0 4px 12px rgba(0,0,0,0.10);
        --transition : all 0.22s ease;
    }

    /* ── Filter card ────────────────────────────────────────────────── */
    .filter-card {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
    }

    /* ── Table ──────────────────────────────────────────────────────── */
    .table thead th {
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        background: var(--bg-subtle);
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
        padding: 0.75rem 1rem;
    }
    .table tbody td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr:hover td      { background: #f8faff; }

    /* ── Role badges ────────────────────────────────────────────────── */
    .badge-role          { font-size: 0.75rem; padding: 0.35em 0.8em; border-radius: 6px; font-weight: 600; }
    .badge-super_admin   { background: #ede9fe; color: #6d28d9; }
    .badge-operation_head{ background: #cffafe; color: #0e7490; }
    .badge-lead_manager  { background: #dcfce7; color: #15803d; }

    /* ── User name link ─────────────────────────────────────────────── */
    .user-name-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.18s ease;
    }
    .user-name-link:hover { color: #1d4ed8; text-decoration: underline; }

    /* ── Action buttons ─────────────────────────────────────────────── */
    .action-btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.82rem;
        border-radius: 6px;
        transition: var(--transition);
    }
    .action-btn:hover { transform: translateY(-1px); }

    /* ── Stat chips (below name in table) ───────────────────────────── */
    .user-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.15rem 0.55rem;
        border-radius: 20px;
        white-space: nowrap;
    }
    .chip-connected     { background: #dcfce7; color: #15803d; }
    .chip-not-connected { background: #fee2e2; color: #b91c1c; }
    .chip-leads         { background: #dbeafe; color: #1d4ed8; }

    /* ── Status toggle ──────────────────────────────────────────────── */
    .form-switch .form-check-input { cursor: pointer; }

    /* ── Password optional note ─────────────────────────────────────── */
    #passwordFields .required-star  { }
    #passwordFields .optional-note  { }

    /* ── Empty state ────────────────────────────────────────────────── */
    .empty-row td {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--text-muted);
    }
    .empty-row i { font-size: 2.5rem; opacity: 0.2; display: block; margin-bottom: 0.5rem; }

    /* ── Responsive ─────────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .table thead { display: none; }
        .table tbody td { display: block; padding: 0.4rem 0.75rem; }
        .table tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-muted);
            display: block;
            margin-bottom: 0.15rem;
        }
    }
</style>
@endsection


@section('content')
@php /** @var \App\Models\User $authUser */ $authUser = auth()->user(); @endphp

{{-- ── Page Header ──────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <div>
                <h4 class="page-title mb-1">Users Management</h4>
                <p class="text-muted mb-0 small">Manage system users, roles and branch assignments</p>
            </div>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </div>
    </div>
</div>

{{-- ── Filters ───────────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card filter-card mb-0">
            <div class="card-body py-3">
                <div class="row align-items-end g-2">

                    {{-- Role --}}
                    <div class="col-xl-2 col-md-3 col-6">
                        <label class="form-label fw-semibold mb-1 small">Role</label>
                        <select class="form-select form-select-sm" id="roleFilter">
                            <option value="">All Roles</option>
                            @foreach(User::ROLES as $value => $label)
                                <option value="{{ $value }}"
                                    {{ request('role') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Branch --}}
                    <div class="col-xl-2 col-md-3 col-6">
                        <label class="form-label fw-semibold mb-1 small">Branch</label>
                        <select class="form-select form-select-sm" id="branchFilter">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-xl-2 col-md-2 col-6">
                        <label class="form-label fw-semibold mb-1 small">Status</label>
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    {{-- Search --}}
                    <div class="col-xl-3 col-md-4 col-6">
                        <label class="form-label fw-semibold mb-1 small">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="las la-search"></i></span>
                            <input type="text" class="form-control" id="searchUser"
                                   value="{{ request('search') }}"
                                   placeholder="Name, email, phone…">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="col-xl-3 col-md-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFilters">
                                <i class="las la-redo me-1"></i>Reset
                            </button>
                            @if($authUser->isSuperAdmin())
                            <button type="button" class="btn btn-sm btn-primary" id="addUserBtn">
                                <i class="las la-plus me-1"></i>Add User
                            </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Users Table ───────────────────────────────────────────────── --}}
<div class="row">
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="las la-users me-2 text-primary"></i>Users List
                    <span class="badge bg-secondary ms-2" id="userCount">{{ $users->total() }}</span>
                </h5>
                <small class="text-muted" id="filterStatus"></small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Branch</th>
                                {{-- <th>Call Stats</th> --}}
                                <th>Leads</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @include('users.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-2">
                <div id="paginationContainer">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     ADD / EDIT USER MODAL  (super_admin only)
══════════════════════════════════════════════════════════════════ --}}
@if($authUser->isSuperAdmin())
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="userModalLabel">
                    <i class="las la-user-plus me-2"></i>Add User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="userForm" novalidate>
                @csrf
                <input type="hidden" id="user_id"     name="user_id">
                <input type="hidden" id="form_method" name="_method" value="POST">

                <div class="modal-body">

                    {{-- Row 1: Name + Email --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="m_name" name="name"
                                   placeholder="Enter full name">
                            <div class="invalid-feedback name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="m_email" name="email"
                                   placeholder="user@example.com">
                            <div class="invalid-feedback email-error"></div>
                        </div>
                    </div>

                    {{-- Row 2: Password --}}
                    <div class="row mb-3" id="passwordFields">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Password
                                <span class="text-danger required-star">*</span>
                                <small class="text-muted optional-note fw-normal" style="display:none;">
                                    (leave blank to keep current)
                                </small>
                            </label>
                            <input type="password" class="form-control" id="m_password" name="password"
                                   placeholder="Min 8 characters" autocomplete="new-password">
                            <div class="invalid-feedback password-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Confirm Password
                                <span class="text-danger required-star">*</span>
                            </label>
                            <input type="password" class="form-control"
                                   id="m_password_confirmation" name="password_confirmation"
                                   placeholder="Repeat password" autocomplete="new-password">
                        </div>
                    </div>

                    {{-- Row 3: Phone + Role --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="las la-phone"></i></span>
                                <input type="text" class="form-control" id="m_phone" name="phone"
                                       placeholder="+91 9876543210">
                            </div>
                            <div class="invalid-feedback phone-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="m_role" name="role">
                                <option value="">— Select Role —</option>
                                @foreach(User::ROLES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback role-error"></div>
                        </div>
                    </div>

                    {{-- Row 4: Branch + Status --}}
                    <div class="row mb-3" id="branchRow">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Branch
                                <span class="text-danger branch-star">*</span>
                            </label>
                            <select class="form-select" id="m_branch_id" name="branch_id">
                                <option value="">— Select Branch —</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback branch_id-error"></div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" type="checkbox"
                                       id="m_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="m_is_active">
                                    Active User
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="las la-save me-1"></i>
                        <span id="saveBtnText">Save User</span>
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

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Roles that don't need a branch assignment
    const BRANCH_FREE_ROLES = ['super_admin', 'operation_head'];

    // ══════════════════════════════════════════════════════════════════
    // LOAD USERS — AJAX
    // ══════════════════════════════════════════════════════════════════
    function loadUsers(url) {
        const params = {
            role      : $('#roleFilter').val(),
            status    : $('#statusFilter').val(),
            branch_id : $('#branchFilter').val(),
            search    : $('#searchUser').val(),
        };

        $('#usersTableBody').html(`
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Loading users…
                </td>
            </tr>
        `);

        $.ajax({
            url    : url || '{{ route("users.index") }}',
            type   : 'GET',
            data   : params,
            success: function (res) {
                $('#usersTableBody').html(res.html);
                $('#paginationContainer').html(res.pagination);
                $('#userCount').text(res.total);

                // Update filter status label
                const activeFilters = Object.values(params).filter(v => v !== '').length;
                $('#filterStatus').text(
                    activeFilters > 0
                        ? `${res.total} result${res.total !== 1 ? 's' : ''} (${activeFilters} filter${activeFilters !== 1 ? 's' : ''} active)`
                        : ''
                );
            },
            error: function () {
                $('#usersTableBody').html(`
                    <tr class="empty-row">
                        <td colspan="9">
                            <i class="las la-exclamation-triangle"></i>
                            Failed to load users. Please try again.
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ── Paginate via AJAX ─────────────────────────────────────────────
    $(document).on('click', '#paginationContainer .pagination a', function (e) {
        e.preventDefault();
        loadUsers($(this).attr('href'));
    });

    // ── Filters — debounced ───────────────────────────────────────────
    let filterTimer;
    $('#roleFilter, #statusFilter, #branchFilter').on('change', function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => loadUsers(), 200);
    });
    $('#searchUser').on('keyup', function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => loadUsers(), 350);
    });

    // ── Reset ─────────────────────────────────────────────────────────
    $('#resetFilters').on('click', function () {
        $('#roleFilter, #statusFilter, #branchFilter').val('');
        $('#searchUser').val('');
        loadUsers();
    });

    // ══════════════════════════════════════════════════════════════════
    // BRANCH ROW — show/hide based on role
    // ══════════════════════════════════════════════════════════════════
    function toggleBranchRow(role) {
        if (BRANCH_FREE_ROLES.includes(role)) {
            $('#branchRow').hide();
            $('#m_branch_id').val('');
        } else {
            $('#branchRow').show();
        }
    }

    $('#m_role').on('change', function () {
        toggleBranchRow($(this).val());
    });


    // ══════════════════════════════════════════════════════════════════
    // SUPER ADMIN ONLY
    // ══════════════════════════════════════════════════════════════════
    @if($authUser->isSuperAdmin())

    // ── Clear all field errors ────────────────────────────────────────
    function clearErrors() {
        $('#userForm .form-control, #userForm .form-select')
            .removeClass('is-invalid');
        $('#userForm .invalid-feedback').text('');
    }

    // ── Show Laravel validation errors ───────────────────────────────
    function showErrors(errors) {
        $.each(errors, function (field, messages) {
            const $field = $('#userForm [name="' + field + '"]');
            $field.addClass('is-invalid');
            // Target sibling invalid-feedback OR class-based fallback
            $field.siblings('.invalid-feedback').text(messages[0]);
            $('.' + field + '-error').text(messages[0]);
        });
    }

    // ── Open ADD modal ────────────────────────────────────────────────
    $('#addUserBtn').on('click', function () {
        $('#userForm')[0].reset();
        clearErrors();

        $('#user_id').val('');
        $('#form_method').val('POST');
        $('#userModalLabel').html('<i class="las la-user-plus me-2"></i>Add User');
        $('#saveBtnText').text('Save User');

        // Password required for new users
        $('#m_password').attr('placeholder', 'Min 8 characters');
        $('.required-star').show();
        $('.optional-note').hide();

        // Reset branch visibility
        $('#branchRow').show();
        $('#m_is_active').prop('checked', true);

        $('#userModal').modal('show');
    });

    // ── Open EDIT modal ───────────────────────────────────────────────
    $(document).on('click', '.editBtn', function () {
        const userId = $(this).data('id');

        $.get('/users/' + userId + '/edit')
            .done(function (res) {
                if (!res.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    return;
                }

                const u = res.user;

                $('#userForm')[0].reset();
                clearErrors();

                $('#user_id').val(u.id);
                $('#form_method').val('PUT');
                $('#userModalLabel').html('<i class="las la-pen me-2"></i>Edit User');
                $('#saveBtnText').text('Update User');

                // Populate fields
                $('#m_name').val(u.name);
                $('#m_email').val(u.email);
                $('#m_phone').val(u.phone ?? '');
                $('#m_role').val(u.role);
                $('#m_branch_id').val(u.branch_id ?? '');
                $('#m_is_active').prop('checked', !!u.is_active);

                // Password optional on edit
                $('#m_password').val('').attr('placeholder', 'Leave blank to keep current');
                $('.required-star').hide();
                $('.optional-note').show();

                toggleBranchRow(u.role);

                $('#userModal').modal('show');
            })
            .fail(function (xhr) {
                Swal.fire({
                    icon: 'error', title: 'Error',
                    text: xhr.responseJSON?.message ?? 'Failed to fetch user data.',
                    confirmButtonColor: '#dc3545',
                });
            });
    });

    // ── SAVE (create / update) ────────────────────────────────────────
    $('#userForm').on('submit', function (e) {
        e.preventDefault();

        const userId = $('#user_id').val();
        const url    = userId ? '/users/' + userId : '/users';

        clearErrors();

        const $btn = $('#saveBtn');
        const orig = $btn.html();
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Saving…'
        );

        $.ajax({
            url         : url,
            type        : 'POST',
            data        : new FormData(this),
            processData : false,
            contentType : false,
            success: function (res) {
                $('#userModal').modal('hide');
                Swal.fire({
                    icon             : 'success',
                    title            : 'Success!',
                    text             : res.message,
                    timer            : 2000,
                    showConfirmButton : false,
                }).then(() => loadUsers());
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    showErrors(xhr.responseJSON?.errors ?? {});
                    if (xhr.responseJSON?.message) {
                        Swal.fire({
                            icon: 'warning', title: 'Validation Error',
                            text: xhr.responseJSON.message,
                            confirmButtonColor: '#f59e0b',
                        });
                    }
                } else if (xhr.status === 403) {
                    Swal.fire({
                        icon: 'error', title: 'Unauthorized',
                        text: xhr.responseJSON?.message ?? 'Access denied.',
                        confirmButtonColor: '#dc3545',
                    });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong.',
                        confirmButtonColor: '#dc3545',
                    });
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html(orig);
            }
        });
    });

    // ── DELETE ────────────────────────────────────────────────────────
    $(document).on('click', '.deleteBtn', function () {
        const userId = $(this).data('id');
        const name   = $(this).data('name');

        Swal.fire({
            title             : 'Delete User?',
            html              : `Are you sure you want to delete <strong>${name}</strong>?<br>
                                 <small class="text-muted">All their assigned leads will be unassigned.</small>`,
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor : '#6c757d',
            confirmButtonText : 'Yes, Delete',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url  : '/users/' + userId,
                type : 'DELETE',
            }).done(function (res) {
                Swal.fire({
                    icon: 'success', title: 'Deleted!',
                    text: res.message,
                    timer: 2000, showConfirmButton: false,
                }).then(() => loadUsers());
            }).fail(function (xhr) {
                Swal.fire({
                    icon: 'error', title: 'Error',
                    text: xhr.responseJSON?.message ?? 'Failed to delete user.',
                    confirmButtonColor: '#dc3545',
                });
            });
        });
    });

    @endif // end super_admin block

});
</script>
@endsection
