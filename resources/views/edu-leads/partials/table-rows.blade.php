@php
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();

    $statusLabels = [
        'pending'        => ['label' => '⏳ Pending',       'class' => 'fs-pending'],
        'contacted'      => ['label' => '📞 Contacted',     'class' => 'fs-contacted'],
        'not_attended'   => ['class' => 'fs-notattended',    'label' => '🚫 Not Attended'],
        'follow_up'      => ['label' => '🔔 Follow Up',     'class' => 'fs-follow_up'],
        'admitted'       => ['label' => '✅ Admitted',       'class' => 'fs-admitted'],
        'not_interested' => ['label' => '❌ Not Interested', 'class' => 'fs-not_interested'],
        'dropped'        => ['label' => '🚫 Dropped',        'class' => 'fs-dropped'],
    ];

    $interestIcons = ['hot' => '🔥', 'warm' => '☀️', 'cold' => '❄️'];
@endphp

@forelse($leads as $lead)
<tr>

    {{-- Checkbox --}}
    @if($authUser->canAssignLeads())
    <td class="checkbox-col">
        <div class="checkbox-wrapper">
            <input type="checkbox"
                   class="custom-checkbox lead-checkbox"
                   value="{{ $lead->id }}"
                   data-branch-id="{{ $lead->branch_id }}"
                   data-branch-name="{{ $lead->branch?->name }}">
        </div>
    </td>
    @endif

    {{-- Lead Code --}}
    <td>
        <span class="text-muted small fw-semibold" data-label="code">{{ $lead->lead_code }}</span>
    </td>

    {{-- Name --}}
    <td>
        <a href="{{ route('edu-leads.show', $lead->id) }}" class="lead-name-link fw-semibold">
            {{ $lead->name }}
        </a>
        {{-- Hidden branch name for bulk assign list display --}}
        <span class="lead-branch-name d-none">{{ $lead->branch?->name }}</span>
        @if($lead->institution_type)
            <div class="mt-1">{!! $lead->institution_type_badge !!}</div>
        @endif
    </td>

    {{-- Phone --}}
    <td>
        <a href="tel:{{ $lead->phone }}" class="text-body text-decoration-underline small">
            {{ $lead->phone }}
        </a>
        @if($lead->whatsapp_number && $lead->whatsapp_number !== $lead->phone)
            <div>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->whatsapp_number) }}"
                   target="_blank" class="text-success small" title="WhatsApp">
                    <i class="lab la-whatsapp"></i> WA
                </a>
            </div>
        @endif
    </td>

    {{-- Final Status --}}
    <td>
        @php
            $s = $statusLabels[$lead->final_status] ?? [
                'label' => ucfirst(str_replace('_', ' ', $lead->final_status ?? '')),
                'class' => 'fs-pending'
            ];
        @endphp
        <span class="fs-badge {{ $s['class'] }}">{{ $s['label'] }}</span>
    </td>

    {{-- Followups --}}
    <td>
        @php
            $totalFu   = $lead->followups->count();
            $pendingFu = $lead->followups->where('status', 'pending')->count();
            $doneFu    = $lead->followups->where('status', 'completed')->count();

            $overdueFu = $lead->followups->filter(function($f) {
                return $f->status === 'pending'
                    && \Carbon\Carbon::parse($f->followup_date)->startOfDay()->lt(\Carbon\Carbon::today());
            })->count();

            $todayFu = $lead->followups->filter(function($f) {
                return $f->status === 'pending'
                    && \Carbon\Carbon::parse($f->followup_date)->isToday();
            })->count();
        @endphp

        @if($totalFu > 0)
            <div class="d-flex flex-column gap-1" style="min-width:80px;">
                <span class="badge bg-secondary" title="Total followups">
                    <i class="las la-list"></i> {{ $totalFu }}
                </span>
                @if($overdueFu > 0)
                    <span class="badge bg-danger" style="font-size:10px;" title="Overdue (past dates)">
                        <i class="las la-exclamation-circle"></i> {{ $overdueFu }} overdue
                    </span>
                @endif
                @if($todayFu > 0)
                    <span class="badge bg-warning text-dark" style="font-size:10px;" title="Due today">
                        <i class="las la-bell"></i> {{ $todayFu }} today
                    </span>
                @endif
                @if(($pendingFu - $overdueFu - $todayFu) > 0)
                    <span class="badge bg-info text-dark" style="font-size:10px;" title="Upcoming">
                        <i class="las la-clock"></i> {{ $pendingFu - $overdueFu - $todayFu }} upcoming
                    </span>
                @endif
                @if($doneFu > 0)
                    <span class="badge bg-success" style="font-size:10px;" title="Completed">
                        <i class="las la-check"></i> {{ $doneFu }} done
                    </span>
                @endif
            </div>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    {{-- Interest Level --}}
    <td>
        @if($lead->interest_level)
            <span class="badge
                @if($lead->interest_level === 'hot') bg-danger
                @elseif($lead->interest_level === 'warm') bg-warning text-dark
                @else bg-info text-dark @endif">
                {{ $interestIcons[$lead->interest_level] ?? '' }} {{ ucfirst($lead->interest_level) }}
            </span>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    {{-- Agent / Referral --}}
    <td>
        @if($lead->agent_name)
            <span class="small"><i class="las la-user-tie text-info"></i> {{ $lead->agent_name }}</span>
        @endif

        @if($lead->referral_name)
            <div class="small mt-1">
                <i class="las la-share-alt text-success"></i>
                <span class="text-success fw-semibold" title="Referral">{{ $lead->referral_name }}</span>
            </div>
        @endif

        @if(!$lead->agent_name && !$lead->referral_name)
            <span class="text-muted">—</span>
        @endif
    </td>

    {{-- Institution --}}
    <td>
        <span class="small text-muted" title="{{ $lead->institution_summary }}">
            {{ Str::limit($lead->institution_summary, 28) }}
        </span>
    </td>

    {{-- Department --}}
    <td>
        @php
            $dept = match($lead->institution_type) {
                'school'  => $lead->school_department,
                'college' => $lead->college_department,
                default   => null,
            };
        @endphp
        @if($dept)
            <span class="small text-muted">{{ $dept }}</span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>

    {{-- State / District --}}
    <td>
        @if($lead->state || $lead->district)
            <span class="small text-muted">
                {{ implode(', ', array_filter([$lead->state, $lead->district])) }}
            </span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>

    {{-- Preferred State --}}
    <td>
        @if($lead->preferred_state)
            <span class="small text-muted">{{ $lead->preferred_state }}</span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>

    {{-- Course --}}
    <td>
        @if($lead->course)
            <span class="small fw-semibold">{{ $lead->course->name }}</span>
            @if($lead->course->programme)
                <div class="text-muted" style="font-size:11px;">{{ $lead->course->programme->name }}</div>
            @endif
        @elseif($lead->course_interested)
            <span class="small text-muted fst-italic">{{ Str::limit($lead->course_interested, 25) }}</span>
        @else
            <span class="text-muted">—</span>
        @endif
        @if($lead->addon_course)
            <div class="text-info" style="font-size:11px;" title="Addon">
                + {{ Str::limit($lead->addon_course, 20) }}
            </div>
        @endif
    </td>

    {{-- Source --}}
    <td>
        <span class="small">{{ $lead->leadSource->name ?? '—' }}</span>
    </td>

    {{-- Assigned To --}}
    <td>
        @if($lead->assignedTo)
            <span class="badge bg-secondary assigned-to-name">{{ $lead->assignedTo->name }}</span>
            @if($lead->assignedTo->branch)
                <div style="font-size:11px;" class="text-muted">{{ $lead->assignedTo->branch->name }}</div>
            @endif
        @else
            <span class="badge bg-light text-muted border">Unassigned</span>
        @endif
    </td>

    {{-- Branch --}}
    <td>
        @if($lead->branch)
            <span class="small text-muted">{{ $lead->branch->name }}</span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>

    {{-- Created --}}
    <td>
        <span class="text-muted small">{{ $lead->created_at->format('d M Y') }}</span>
    </td>

    {{-- Actions --}}
    <td>
        <div class="action-icons">
            <a href="{{ route('edu-leads.show', $lead->id) }}" title="View" class="text-info">
                <i class="las la-eye fs-18"></i>
            </a>
            @if(
                $authUser->isSuperAdmin() ||
                $authUser->isOperationHead() ||
                ($authUser->isLeadManager() && $lead->branch_id === $authUser->branch_id) ||
                ($authUser->isTelecaller()  && $lead->assigned_to == $authUser->id)
            )
            <a href="{{ route('edu-leads.edit', $lead->id) }}" title="Edit" class="text-secondary">
                <i class="las la-pen fs-18"></i>
            </a>
            @endif
            @if($authUser->canAssignLeads())
            <a href="javascript:void(0)"
               class="assignLeadBtn text-primary"
               title="Assign Lead"
               data-id="{{ $lead->id }}"
               data-code="{{ $lead->lead_code }}"
               data-name="{{ $lead->name }}"
               data-branch-id="{{ $lead->branch_id }}"
               data-branch-name="{{ $lead->branch?->name }}"
               data-assignee="{{ $lead->assignedTo?->name ?? '' }}">
                <i class="las la-user-plus fs-18"></i>
            </a>
            @endif
            @if($authUser->canDelete())
            <a href="javascript:void(0)"
               class="deleteLeadBtn text-danger"
               title="Delete Lead"
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
    <td colspan="18" class="text-center py-5 text-muted">
        <i class="las la-graduation-cap"
           style="font-size:3rem;opacity:.2;display:block;margin-bottom:8px;"></i>
        No leads found matching your filters
    </td>
</tr>
@endforelse
