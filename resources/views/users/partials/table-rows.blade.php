{{-- users/partials/table-rows.blade.php --}}
@forelse($users as $user)
<tr>
    {{-- User --}}
    <td data-label="User">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar-xs flex-shrink-0">
                <span class="avatar-title rounded-circle
                    {{ $user->role === 'super_admin'    ? 'bg-soft-purple text-purple'
                    : ($user->role === 'operation_head' ? 'bg-soft-info text-info'
                    : 'bg-soft-success text-success') }}"
                    style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-weight:700;font-size:.85rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
            </div>
            <div>
                <a href="{{ route('users.show', $user) }}" class="user-name-link d-block">
                    {{ $user->name }}
                </a>
                <small class="text-muted">ID #{{ $user->id }}</small>
            </div>
        </div>
    </td>

    {{-- Contact --}}
    <td data-label="Contact">
        <div class="small">
            <div><i class="las la-envelope me-1 text-muted"></i>{{ $user->email }}</div>
            @if($user->phone)
            <div class="text-muted"><i class="las la-phone me-1"></i>{{ $user->phone }}</div>
            @endif
        </div>
    </td>

    {{-- Role --}}
    <td data-label="Role">
        <span class="badge-role badge-{{ $user->role }}">
            {{ $user->role_label }}
        </span>
    </td>

    {{-- Branch --}}
    <td data-label="Branch">
        @if($user->branch)
            <span class="badge bg-light text-dark border small">
                <i class="las la-building me-1"></i>{{ $user->branch->name }}
            </span>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    {{-- Call Stats (connected / not-connected) --}}
    {{-- <td data-label="Call Stats">
        @if($user->isLeadManager())
            @php
                $totalCalls    = $user->eduCallLogs()->count();
                $connected     = $user->eduCallLogs()->where('call_status', 'connected')->count();
                $notConnected  = $user->eduCallLogs()->where('call_status', 'not_connected')->count();
                $connRate      = $totalCalls > 0 ? round(($connected / $totalCalls) * 100) : 0;
            @endphp
            <div class="d-flex flex-wrap gap-1">
                <span class="user-stat-chip chip-connected" title="Connected">
                    <i class="las la-phone"></i>{{ $connected }}
                </span>
                <span class="user-stat-chip chip-not-connected" title="Not Connected">
                    <i class="las la-phone-slash"></i>{{ $notConnected }}
                </span>
                @if($totalCalls > 0)
                <span class="user-stat-chip bg-light text-muted border" title="Connection rate">
                    {{ $connRate }}%
                </span>
                @endif
            </div>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td> --}}

    {{-- Leads --}}
    <td data-label="Leads">
        @if($user->isLeadManager())
            @php
                $totalLeads   = $user->assignedEduLeads()->count();
                $admitted     = $user->assignedEduLeads()->where('final_status', 'admitted')->count();
            @endphp
            <div class="d-flex flex-wrap gap-1">
                <span class="user-stat-chip chip-leads" title="Total assigned">
                    <i class="las la-user-graduate"></i>{{ $totalLeads }}
                </span>
                @if($admitted > 0)
                <span class="user-stat-chip bg-soft-success text-success" title="Admitted">
                    <i class="las la-check-circle"></i>{{ $admitted }}
                </span>
                @endif
            </div>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    {{-- Joined --}}
    <td data-label="Joined">
        <span class="small text-muted">{{ $user->created_at->format('d M Y') }}</span>
    </td>

    {{-- Status --}}
    <td data-label="Status">
        @if($user->is_active)
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="las la-check-circle me-1"></i>Active
            </span>
        @else
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                <i class="las la-times-circle me-1"></i>Inactive
            </span>
        @endif
    </td>

    {{-- Actions --}}
    <td data-label="Actions" class="text-end">
        <div class="d-flex gap-1 justify-content-end">
            <a href="{{ route('users.show', $user) }}"
               class="btn btn-sm btn-outline-info action-btn" title="View Profile">
                <i class="las la-eye"></i>
            </a>
            @if(auth()->user()->isSuperAdmin())
            <button class="btn btn-sm btn-outline-primary action-btn editBtn"
                    data-id="{{ $user->id }}" title="Edit">
                <i class="las la-pen"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger action-btn deleteBtn"
                    data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}" title="Delete">
                <i class="las la-trash"></i>
            </button>
            @endif
        </div>
    </td>
</tr>
@empty
<tr class="empty-row">
    <td colspan="9">
        <i class="las la-users"></i>
        No users found matching your filters
    </td>
</tr>
@endforelse
