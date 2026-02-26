<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Education CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Education Lead Management System" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/logos/icon.png') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    @yield('extra-css')

    <style>
        .avatar-circle {
            width: 35px; height: 35px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(102,126,234,.4);
            transition: transform .2s;
        }
        .avatar-circle:hover { transform: scale(1.05); }
        .avatar-initials { color: white; font-weight: 600; font-size: 14px; letter-spacing: .5px; }

        .avatar-circle-large {
            width: 50px; height: 50px; border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            border: 2px solid rgba(255,255,255,.3);
        }
        .avatar-initials-large { color: white; font-weight: 700; font-size: 18px; letter-spacing: 1px; }

        .dropdown-menu { border: none; box-shadow: 0 10px 30px rgba(0,0,0,.15); }
        .dropdown-item { transition: all .2s; font-weight: 500; }
        .dropdown-item:hover { background-color: #f8f9fa; padding-left: 1.25rem; }

        .profile-sidebar-card {
            background: rgba(102,126,234,.1);
            border-radius: 10px; padding: 12px; margin: 10px;
            border-left: 3px solid #667eea;
            transition: all .3s; cursor: pointer;
            text-decoration: none; display: block;
        }
        .profile-sidebar-card:hover { background: rgba(102,126,234,.15); transform: translateX(2px); }

        .profile-sidebar-avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: white;
            letter-spacing: 1px; flex-shrink: 0;
        }
        .profile-sidebar-info  { flex-grow: 1; min-width: 0; }
        .profile-sidebar-name  {
            font-size: 14px; font-weight: 600; color: #ffffff;
            margin-bottom: 2px; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }
        .profile-sidebar-role  { font-size: 12px; color: #667eea; font-weight: 500; }

        .account-label {
            text-transform: uppercase; font-size: 11px; font-weight: 600;
            color: #a0aec0; padding: 0 20px; margin-top: auto; margin-bottom: 8px;
        }

        .table-loading { opacity: .6; pointer-events: none; }
        .sortable { cursor: pointer; user-select: none; }
        .sortable:hover { background-color: #f8f9fa; }
        .sortable.asc::after  { content: " ▲"; font-size: 10px; }
        .sortable.desc::after { content: " ▼"; font-size: 10px; }

        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .5; } }
        .badge-pulse { animation: pulse 2s infinite; }
    </style>
</head>

<body>

<!-- ── Top Bar ─────────────────────────────────────────────────── -->
<div class="topbar d-print-none">
    <div class="container-fluid">
        <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">
            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                <li>
                    <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                        <i class="iconoir-menu"></i>
                    </button>
                </li>
            </ul>

            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                <li class="dropdown topbar-item">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon"
                       data-bs-toggle="dropdown" href="#" role="button"
                       aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                        <div class="avatar-circle">
                            <span class="avatar-initials">
                                <i class="iconoir-user" style="color:#fff;"></i>
                            </span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="min-width:250px;">
                        <div class="d-flex align-items-center dropdown-item py-3 bg-primary bg-gradient">
                            <div class="flex-shrink-0">
                                <div class="avatar-circle-large">
                                    <span class="avatar-initials-large">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3 text-truncate">
                                <h6 class="my-0 fw-semibold text-white">{{ auth()->user()->name }}</h6>
                                <small class="text-white-50">
                                    {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                                </small>
                            </div>
                        </div>
                        <div class="dropdown-divider my-0"></div>
                        <small class="text-muted px-3 py-2 d-block fw-semibold">Account</small>
                        <a class="dropdown-item py-2" href="{{ route('profile.show') }}">
                            <i class="las la-user-circle fs-18 me-2 align-text-bottom text-primary"></i>
                            My Profile
                        </a>
                        <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                            <i class="las la-user-edit fs-18 me-2 align-text-bottom text-info"></i>
                            Edit Profile
                        </a>
                        <div class="dropdown-divider my-0"></div>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2 w-100 text-start">
                                <i class="las la-power-off fs-18 me-2 align-text-bottom"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</div>
<!-- ── Top Bar End ──────────────────────────────────────────────── -->

<!-- ── Sidebar ─────────────────────────────────────────────────── -->
<div class="startbar d-print-none">

    <div class="brand justify-content-start">
        <a href="{{ route('dashboard') }}" class="logo">
            <span>
                <img src="{{ asset('assets/images/logos/icon.png') }}"
                     alt="logo-small" class="logo-sm p-2">
            </span>
            <span class="px-4">
                <img src="{{ asset('assets/images/logos/kites-logo-white.png') }}"
                     alt="logo-large" class="logo-lg logo-light"
                     style="max-width:180px; height:unset;">
            </span>
        </a>
    </div>

    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <ul class="navbar-nav mb-auto w-100">

                    <li class="menu-label mt-2"><span>Main Menu</span></li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="iconoir-home menu-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    {{-- ── Education CRM ──────────────────────────── --}}
                    <li class="menu-label mt-3"><span>🎓 Education CRM</span></li>

                    @php
                        try {
                            $authUser   = auth()->user();
                            $authRole   = $authUser->role;
                            $authId     = $authUser->id;

                            // Hot leads — scoped by role
                            $hotQuery = \App\Models\EduLead::where('interest_level', 'hot');

                            if ($authRole === 'telecaller') {
                                $hotQuery->where('assigned_to', $authId);
                            } elseif ($authRole === 'lead_manager') {
                                $hotQuery->where('branch_id', $authUser->branch_id);
                            }
                            $hotLeadsCount = $hotQuery->count();

                            // Overdue follow-ups — scoped by role
                            $overdueQuery = \App\Models\EduLeadFollowup::where('status', 'pending')
                                ->whereDate('followup_date', '<', today());

                            if ($authRole === 'telecaller') {
                                $overdueQuery->where('assigned_to', $authId);
                            } elseif ($authRole === 'lead_manager') {
                                $overdueQuery->whereHas('eduLead', fn($q) =>
                                    $q->where('branch_id', $authUser->branch_id)
                                );
                            }
                            $overdueFollowupsCount = $overdueQuery->count();

                            // My assigned pending leads (telecaller)
                            $myPendingLeads = $authRole === 'telecaller'
                                ? \App\Models\EduLead::where('assigned_to', $authId)
                                    ->where('final_status', 'pending')->count()
                                : 0;

                        } catch (\Exception $e) {
                            $hotLeadsCount = $overdueFollowupsCount = $myPendingLeads = 0;
                        }
                    @endphp

                    {{-- Education Leads --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('edu-leads.*') ? 'active' : '' }}"
                           href="{{ route('edu-leads.index') }}">
                            <i class="las la-graduation-cap menu-icon"></i>
                            <span>Education Leads
                                @if(in_array($authRole, ['super_admin', 'operation_head']) && $hotLeadsCount > 0)
                                    <span class="badge bg-danger ms-2 badge-pulse">🔥 {{ $hotLeadsCount }}</span>
                                @elseif($authRole === 'lead_manager' && $hotLeadsCount > 0)
                                    <span class="badge bg-danger ms-2 badge-pulse">🔥 {{ $hotLeadsCount }}</span>
                                @elseif($authRole === 'telecaller' && $myPendingLeads > 0)
                                    <span class="badge bg-warning text-dark ms-2">{{ $myPendingLeads }}</span>
                                @endif
                            </span>
                        </a>
                    </li>

                    {{-- Quick Actions — all roles that can create --}}
                    @if($authUser->canCreateLeads())
                    <li class="menu-label mt-3"><span>Quick Actions</span></li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('edu-leads.create') ? 'active' : '' }}"
                           href="{{ route('edu-leads.create') }}">
                            <i class="las la-plus-circle menu-icon"></i>
                            <span>New Lead</span>
                        </a>
                    </li>
                    @endif

                    {{-- Bulk Import & Export — only managers and above --}}
                    @if(in_array($authRole, ['super_admin', 'operation_head', 'lead_manager']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('edu-leads.bulk-import') ? 'active' : '' }}"
                           href="{{ route('edu-leads.bulk-import') }}">
                            <i class="las la-file-upload menu-icon"></i>
                            <span>Bulk Import</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('edu-leads.export') }}">
                            <i class="las la-file-download menu-icon"></i>
                            <span>Export All Leads</span>
                        </a>
                    </li>
                    @endif

                    {{-- Administration — super_admin & operation_head --}}
                    @if(in_array($authRole, ['super_admin', 'operation_head']))
                    <li class="menu-label mt-3"><span>Administration</span></li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                           href="{{ route('users.index') }}">
                            <i class="las la-users menu-icon"></i>
                            <span>Users</span>
                        </a>
                    </li>

                    @if($authRole === 'super_admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.performance*') ? 'active' : '' }}"
                           href="{{ route('users.performance') }}">
                            <i class="las la-trophy menu-icon"></i>
                            <span>Performance</span>
                        </a>
                    </li>
                    @endif
                    @endif

                    {{-- Account --}}
                    <div class="account-label mt-auto pt-3">ACCOUNT</div>

                    <a href="{{ route('profile.show') }}" class="profile-sidebar-card text-decoration-none">
                        <div class="d-flex align-items-center">
                            <div class="profile-sidebar-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="profile-sidebar-info ms-3">
                                <div class="profile-sidebar-name">{{ auth()->user()->name }}</div>
                                <div class="profile-sidebar-role">
                                    {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                                </div>
                            </div>
                        </div>
                    </a>

                </ul>
            </div>
        </div>
    </div>

</div>
<div class="startbar-overlay d-print-none"></div>
<!-- ── Sidebar End ──────────────────────────────────────────────── -->

<!-- ── Page Wrapper ────────────────────────────────────────────── -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">

            @if(session('success'))
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="las la-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="las la-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="las la-exclamation-triangle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            @endif

            @yield('content')

        </div>

        <footer class="footer text-center text-sm-start d-print-none">
            <div class="container-fluid" style="padding: 0 !important; margin: 0 !important;">
                <div class="w-100" style="padding: 0 !important; margin: 0 !important;">
                    <div class="w-100">
                        <div class="card mb-0 rounded-bottom-0">
                            <div class="card-body">
                                <p class="text-muted mb-0">
                                    {{-- © <span id="year"></span> Education CRM. All rights reserved. --}}
                                    <span class="text-muted d-none d-sm-inline-block float-end">
                                        Crafted with <i class="las la-heart text-danger"></i> for Education
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('year').textContent = new Date().getFullYear();
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
</script>

@yield('extra-scripts')
</body>
</html>
