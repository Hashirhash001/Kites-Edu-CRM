@forelse($users as $user)
    <tr data-role="{{ $user->role }}" data-status="{{ $user->is_active ? '1' : '0' }}">

        {{-- Name --}}
        <td>
            <a href="{{ route('users.show', $user->id) }}" class="user-name-link">
                <h6 class="m-0">{{ $user->name }}</h6>
            </a>
        </td>

        {{-- Email --}}
        <td>
            <a href="mailto:{{ $user->email }}" class="text-body text-decoration-underline">
                {{ $user->email }}
            </a>
        </td>

        {{-- Phone --}}
        <td>{{ $user->phone ?? '—' }}</td>

        {{-- Role --}}
        <td>
            @php
                $roleColors = [
                    'super_admin'  => 'primary',
                    'lead_manager' => 'success',
                    'telecallers'  => 'warning',
                ];
                $color = $roleColors[$user->role] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $color }}">
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
            </span>
        </td>

        {{-- Joined --}}
        <td>{{ $user->created_at->format('d M Y') }}</td>

        {{-- Status --}}
        <td>
            @if($user->is_active)
                <span class="badge rounded text-success bg-success-subtle">Active</span>
            @else
                <span class="badge rounded text-secondary bg-secondary-subtle">Inactive</span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="text-end">
            @if(auth()->user()->role === 'super_admin')
                <a href="{{ route('users.show', $user->id) }}" title="View" class="me-1">
                    <i class="las la-eye text-info fs-18"></i>
                </a>
                <a href="javascript:void(0)" class="editBtn me-1" data-id="{{ $user->id }}" title="Edit">
                    <i class="las la-pen text-secondary fs-18"></i>
                </a>
                <a href="javascript:void(0)" class="deleteBtn" data-id="{{ $user->id }}" title="Delete">
                    <i class="las la-trash-alt text-danger fs-18"></i>
                </a>
            @else
                <a href="{{ route('users.show', $user->id) }}" title="View">
                    <i class="las la-eye text-info fs-18"></i>
                </a>
            @endif
        </td>

    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-4 text-muted">
            <i class="las la-users fs-24 d-block mb-2 opacity-25"></i>
            No users found
        </td>
    </tr>
@endforelse
