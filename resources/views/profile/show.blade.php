@extends('layouts.app')

@section('title', 'My Profile')

@section('extra-css')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .profile-avatar-large {
        width: 100px; height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        border: 4px solid rgba(255,255,255,0.3);
        font-size: 2.5rem; font-weight: 700;
        color: white; letter-spacing: 2px;
        flex-shrink: 0;
    }
    .stat-card { transition: transform 0.2s, box-shadow 0.2s; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,.1); }
    .info-row { padding: .85rem 0; border-bottom: 1px solid #e9ecef; }
    .info-row:last-child { border-bottom: none; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">My Profile</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profile</li>
            </ol>
        </div>
    </div>
</div>

{{-- ── Profile Header ──────────────────────────────────────────── --}}
<div class="profile-header">
    <div class="d-flex align-items-center gap-4 flex-wrap">
        <div class="profile-avatar-large">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="flex-grow-1">
            <h3 class="mb-1 fw-bold">{{ $user->name }}</h3>
            <p class="mb-1 text-white-50">
                <i class="las la-user-tag me-1"></i>
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
            </p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-light">
            <i class="las la-edit me-1"></i> Edit Profile
        </a>
    </div>
</div>

{{-- ── Stats (only for lead-facing roles) ────────────────────────── --}}
@if(in_array($user->role, ['lead_manager', 'telecallers']))
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body text-center py-4">
                <i class="las la-clipboard-list text-primary" style="font-size:3rem;"></i>
                {{-- Assigned Leads stat --}}
                <h3 class="mb-0 mt-2 text-primary">{{ $user->assigned_edu_leads_count }}</h3>
                <small class="text-muted fw-semibold">Assigned Leads</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body text-center py-4">
                <i class="las la-fire text-danger" style="font-size:3rem;"></i>
                <h3 class="mb-0 mt-2 text-danger">{{ $user->hot_leads_count }}</h3>
                <small class="text-muted fw-semibold">Hot Leads</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body text-center py-4">
                <i class="las la-check-circle text-success" style="font-size:3rem;"></i>
                <h3 class="mb-0 mt-2 text-success">{{ $user->admitted_leads_count }}</h3>
                <small class="text-muted fw-semibold">Admitted</small>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Details ─────────────────────────────────────────────────────── --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-user-circle me-2"></i>Personal Information
                </h5>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <strong class="text-muted d-block mb-1 small">Full Name</strong>
                    <span>{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1 small">Email Address</strong>
                    <span>{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1 small">Phone Number</strong>
                    <span>{{ $user->phone ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1 small">Role</strong>
                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-info-circle me-2"></i>Account Details
                </h5>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <strong class="text-muted d-block mb-1 small">Member Since</strong>
                    <span>{{ $user->created_at->format('d M Y') }}</span>
                    <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1 small">Account Status</strong>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="info-row">
                    <strong class="text-muted d-block mb-1 small">Last Updated</strong>
                    <span>{{ $user->updated_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-lock me-2"></i>Security
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Use a strong password with uppercase, lowercase, and numbers.</p>
                <a href="{{ route('profile.edit') }}#change-password" class="btn btn-outline-primary w-100">
                    <i class="las la-key me-1"></i> Change Password
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session("success") }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Error!', text: '{{ session("error") }}' });
    @endif
</script>
@endsection
