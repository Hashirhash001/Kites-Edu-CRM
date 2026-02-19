@php
$statusLabels = [
    'pending'        => ['label' => 'Pending',        'class' => 'fs-pending'],
    'contacted'      => ['label' => 'Contacted',      'class' => 'fs-contacted'],
    'follow_up'      => ['label' => 'Follow Up',      'class' => 'fs-follow_up'],
    'admitted'       => ['label' => 'Admitted',       'class' => 'fs-admitted'],
    'not_interested' => ['label' => 'Not Interested', 'class' => 'fs-not_interested'],
    'dropped'        => ['label' => 'Dropped',        'class' => 'fs-dropped'],
];
$interestIcons = ['hot' => '🔥', 'warm' => '☀️', 'cold' => '❄️'];
@endphp

@forelse($leads as $lead)
<tr>
    @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
    <td class="checkbox-col">
        <div class="checkbox-wrapper">
            <input type="checkbox" class="custom-checkbox lead-checkbox" value="{{ $lead->id }}">
        </div>
    </td>
    @endif

    {{-- Lead Code --}}
    <td>
        <span class="text-muted small fw-semibold">{{ $lead->lead_code }}</span>
    </td>

    {{-- Name --}}
    <td>
        <a href="{{ route('edu-leads.show', $lead->id) }}" class="lead-name-link">
            {{ $lead->name }}
        </a>
    </td>

    {{-- Phone --}}
    <td>
        <a href="tel:{{ $lead->phone }}" class="text-body text-decoration-underline small">
            {{ $lead->phone }}
        </a>
    </td>

    {{-- Country --}}
    <td>{{ $lead->country ?? '—' }}</td>

    {{-- Course --}}
    <td>{{ $lead->course->name ?? '—' }}</td>

    {{-- Interest Level --}}
    <td>
        @if($lead->interest_level)
            <span class="badge
                @if($lead->interest_level === 'hot')    bg-danger
                @elseif($lead->interest_level === 'warm') bg-warning text-dark
                @else bg-info text-dark
                @endif">
                {{ $interestIcons[$lead->interest_level] ?? '' }}
                {{ ucfirst($lead->interest_level) }}
            </span>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    {{-- Final Status --}}
    <td>
        @php
            $s = $statusLabels[$lead->final_status] ?? ['label' => ucfirst($lead->final_status), 'class' => 'fs-pending'];
        @endphp
        <span class="fs-badge {{ $s['class'] }}">{{ $s['label'] }}</span>
    </td>

    {{-- Source --}}
    <td>{{ $lead->leadSource->name ?? '—' }}</td>

    {{-- Assigned To --}}
    <td>
        @if($lead->assignedTo)
            <span class="badge bg-secondary">{{ $lead->assignedTo->name }}</span>
        @else
            <span class="badge bg-light text-muted border">Unassigned</span>
        @endif
    </td>

    {{-- Created --}}
    <td><span class="text-muted small">{{ $lead->created_at->format('d M Y') }}</span></td>

    {{-- Actions --}}
    <td>
        <div class="action-icons">
            {{-- View --}}
            <a href="{{ route('edu-leads.show', $lead->id) }}" title="View" class="text-info">
                <i class="las la-eye fs-18"></i>
            </a>

            @if(
                auth()->user()->role === 'super_admin' ||
                (auth()->user()->role === 'lead_manager' && $lead->created_by === auth()->id()) ||
                (auth()->user()->role === 'telecallers'  && $lead->assigned_to === auth()->id())
            )
            {{-- Edit --}}
            <a href="{{ route('edu-leads.edit', $lead->id) }}" title="Edit" class="text-secondary">
                <i class="las la-pen fs-18"></i>
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
            {{-- Single Assign --}}
            <a href="javascript:void(0)"
               class="assignLeadBtn text-primary"
               title="Assign"
               data-id="{{ $lead->id }}"
               data-code="{{ $lead->lead_code }}"
               data-name="{{ $lead->name }}"
               data-assignee="{{ $lead->assignedTo->name ?? '' }}">
                <i class="las la-user-plus fs-18"></i>
            </a>
            @endif

            @if(auth()->user()->role === 'super_admin')
            {{-- Delete --}}
            <a href="javascript:void(0)"
               class="deleteLeadBtn text-danger"
               title="Delete"
               data-id="{{ $lead->id }}"
               data-name="{{ $lead->name }}">
                <i class="las la-trash-alt fs-18"></i>
            </a>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="12" class="text-center py-5 text-muted">
        <i class="las la-graduation-cap" style="font-size:3rem; opacity:.2; display:block; margin-bottom:8px;"></i>
        No leads found matching your filters
    </td>
</tr>
@endforelse
