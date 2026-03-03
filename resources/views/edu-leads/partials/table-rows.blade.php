@php
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();

    $followupNumber = $followupNumber ?? null;

    $statusLabels = [
        'pending'        => ['label' => '⏳ Pending',        'class' => 'fs-pending'],
        'contacted'      => ['label' => '📞 Contacted',      'class' => 'fs-contacted'],
        'not_attended'   => ['label' => '🚫 Not Attended',   'class' => 'fs-notattended'],
        'follow_up'      => ['label' => '🔔 Follow Up',      'class' => 'fs-followup'],
        'admitted'       => ['label' => '✅ Admitted',        'class' => 'fs-admitted'],
        'not_interested' => ['label' => '❌ Not Interested',  'class' => 'fs-notinterested'],
        'dropped'        => ['label' => '🚫 Dropped',         'class' => 'fs-dropped'],
    ];

    $interestIcons = ['hot' => '🔥', 'warm' => '☀️', 'cold' => '❄️'];

    // Ordinal suffix helper
    $ordinalSuffix = function(int $n): string {
        $suffix = match($n % 10) {
            1 => $n % 100 === 11 ? 'th' : 'st',
            2 => $n % 100 === 12 ? 'th' : 'nd',
            3 => $n % 100 === 13 ? 'th' : 'rd',
            default => 'th',
        };
        return $n . $suffix;
    };

    $fuColumnLabel = $followupNumber
        ? $ordinalSuffix($followupNumber) . ' Followup'
        : 'Latest Followup';
@endphp

@forelse($leads as $lead)
@php
    $s = $statusLabels[$lead->final_status] ?? [
        'label' => ucfirst(str_replace('_', ' ', $lead->final_status ?? '')),
        'class' => 'fs-pending',
    ];

    $totalFu   = $lead->followups_count;
    $pendingFu = $lead->followups->where('status', 'pending')->count();
    $doneFu    = $lead->followups->where('status', 'completed')->count();
    $overdueFu = $lead->followups->filter(fn($f) =>
        $f->status === 'pending' &&
        \Carbon\Carbon::parse($f->followup_date)->startOfDay()->lt(\Carbon\Carbon::today())
    )->count();
    $todayFu = $lead->followups->filter(fn($f) =>
        $f->status === 'pending' &&
        \Carbon\Carbon::parse($f->followup_date)->isToday()
    )->count();

    // ── Pick the followup to display ────────────────────────────────────────
    if ($followupNumber) {
        // Cast both sides to int to avoid string vs int mismatch
        $displayFu = $lead->followups
            ->sortBy('followup_number')
            ->values()
            ->get((int)$followupNumber - 1); // 0-indexed
    } else {
        // Default: latest completed followup, fallback to latest overall
        $displayFu = $lead->followups
            ->where('status', 'completed')
            ->sortByDesc('followup_number')
            ->first()
            ?? $lead->latestFollowup;
    }


    // ── Pre-compute display vars (used in both desktop & mobile) ────────────
    $fuDate    = null;
    $dateBadge = 'bg-info text-dark';
    $isComplete = false;
    $isOverdue  = false;
    $isToday    = false;
    $fuNote     = null;

    if ($displayFu) {
        $displayFuPosition = $lead->followups
        ->sortBy('followup_number')
        ->values()
        ->search(fn($f) => $f->id === $displayFu->id) + 1;
        $fuDate     = \Carbon\Carbon::parse($displayFu->followup_date);
        $isOverdue  = $displayFu->status === 'pending' && $fuDate->isPast() && !$fuDate->isToday();
        $isToday    = $displayFu->status === 'pending' && $fuDate->isToday();
        $isComplete = $displayFu->status === 'completed';
        $dateBadge  = $isOverdue  ? 'bg-danger'
                    : ($isToday   ? 'bg-warning text-dark'
                    : ($isComplete ? 'bg-success'
                                   : 'bg-info text-dark'));
        $fuNote = $isComplete && $displayFu->outcome_notes
            ? $displayFu->outcome_notes
            : $displayFu->notes;
    }

    $dept = match($lead->institution_type) {
        'school'  => $lead->school_department,
        'college' => $lead->college_department,
        default   => null,
    };
@endphp

{{-- ════════════════════════════════════════════════════
     DESKTOP ROW
════════════════════════════════════════════════════ --}}
<tr class="leads-desktop-view">

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

    <td><span class="text-muted small fw-semibold">{{ $lead->lead_code }}</span></td>

    <td>
        <a href="{{ route('edu-leads.show', $lead->id) }}" class="lead-name-link fw-semibold">
            {{ $lead->name }}
        </a>
        <span class="lead-branch-name d-none">{{ $lead->branch?->name }}</span>
        @if($lead->institution_type)
            <div class="mt-1">{!! $lead->institution_type_badge !!}</div>
        @endif
    </td>

    <td>
        <a href="tel:{{ $lead->phone }}" class="text-body text-decoration-underline small">
            {{ $lead->phone }}
        </a>
        @if($lead->whatsapp_number && $lead->whatsapp_number !== $lead->phone)
            <div>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->whatsapp_number) }}"
                   target="_blank" class="text-success small">
                    <i class="lab la-whatsapp"></i> WA
                </a>
            </div>
        @endif
    </td>

    <td><span class="fs-badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>

    {{-- Call Status --}}
    <td>
        @if($lead->call_status)
            {!! $lead->call_status_badge !!}
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    {{-- Followups count --}}
    <td>
        @if($totalFu > 0)
        <div class="d-flex flex-column gap-1" style="min-width:80px;">
            <span class="badge bg-secondary"><i class="las la-list"></i> {{ $totalFu }}</span>
            @if($overdueFu > 0)
                <span class="badge bg-danger" style="font-size:10px;"><i class="las la-exclamation-circle"></i> {{ $overdueFu }} overdue</span>
            @endif
            @if($todayFu > 0)
                <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="las la-bell"></i> {{ $todayFu }} today</span>
            @endif
            @if(($pendingFu - $overdueFu - $todayFu) > 0)
                <span class="badge bg-info text-dark" style="font-size:10px;"><i class="las la-clock"></i> {{ $pendingFu - $overdueFu - $todayFu }} upcoming</span>
            @endif
            @if($doneFu > 0)
                <span class="badge bg-success" style="font-size:10px;"><i class="las la-check"></i> {{ $doneFu }} done</span>
            @endif
        </div>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    {{-- Followup column (latest completed OR specific number) --}}
    <td style="min-width:140px;">
        @if($displayFu)
            <div style="font-size:.78rem; line-height:1.5;">
                <span class="badge {{ $dateBadge }}">
                    <i class="las la-calendar me-1"></i>{{ $fuDate->format('d M Y') }}
                </span>
                @if($displayFu->followup_time)
                    <span class="text-muted ms-1" style="font-size:.72rem;">
                        {{ \Carbon\Carbon::parse($displayFu->followup_time)->format('h:i A') }}
                    </span>
                @endif
                @if($fuNote)
                    <div class="text-muted mt-1" style="max-width:160px; white-space:normal; font-size:.72rem; line-height:1.3;">
                        {{ Str::limit($fuNote, 55) }}
                    </div>
                @endif
                @if($isComplete)
                    <span style="font-size:.7rem; color:#16a34a;"><i class="las la-check-circle"></i> Done</span>
                @elseif($isOverdue)
                    <span style="font-size:.7rem; color:#dc2626;"><i class="las la-exclamation-circle"></i> Overdue</span>
                @elseif($isToday)
                    <span style="font-size:.7rem; color:#d97706;"><i class="las la-bell"></i> Today</span>
                @endif
                <div class="text-muted" style="font-size:.68rem; margin-top:2px;">
                    #{{ $displayFuPosition }}
                </div>
            </div>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>

    <td>
        @if($lead->interest_level)
            <span class="badge @if($lead->interest_level==='hot') bg-danger @elseif($lead->interest_level==='warm') bg-warning text-dark @else bg-info text-dark @endif">
                {{ $interestIcons[$lead->interest_level] ?? '' }} {{ ucfirst($lead->interest_level) }}
            </span>
        @else <span class="text-muted small">—</span> @endif
    </td>

    <td>
        @if($lead->agent_name) <span class="small"><i class="las la-user-tie text-info"></i> {{ $lead->agent_name }}</span> @endif
        @if($lead->referral_name) <div class="small mt-1"><i class="las la-share-alt text-success"></i> <span class="text-success fw-semibold">{{ $lead->referral_name }}</span></div> @endif
        @if(!$lead->agent_name && !$lead->referral_name) <span class="text-muted">—</span> @endif
    </td>

    <td><span class="small text-muted" title="{{ $lead->institution_summary }}">{{ Str::limit($lead->institution_summary, 28) }}</span></td>

    <td>
        @if($dept) <span class="small text-muted">{{ $dept }}</span>
        @else <span class="text-muted">—</span> @endif
    </td>

    <td>
        @if($lead->state || $lead->district)
            <span class="small text-muted">{{ implode(', ', array_filter([$lead->state, $lead->district])) }}</span>
        @else <span class="text-muted">—</span> @endif
    </td>

    <td>
        @if($lead->course)
            <span class="small fw-semibold">{{ $lead->course->name }}</span>
            @if($lead->course->programme) <div class="text-muted" style="font-size:11px;">{{ $lead->course->programme->name }}</div> @endif
        @elseif($lead->course_interested)
            <span class="small text-muted fst-italic">{{ Str::limit($lead->course_interested, 25) }}</span>
        @else <span class="text-muted">—</span> @endif
        @if($lead->addon_course) <div class="text-info" style="font-size:11px;">+ {{ Str::limit($lead->addon_course, 20) }}</div> @endif
    </td>

    <td><span class="small">{{ $lead->leadSource->name ?? '—' }}</span></td>

    <td>
        @if($lead->assignedTo)
            <span class="badge bg-secondary assigned-to-name">{{ $lead->assignedTo->name }}</span>
            @if($lead->assignedTo->branch) <div style="font-size:11px;" class="text-muted">{{ $lead->assignedTo->branch->name }}</div> @endif
        @else <span class="badge bg-light text-muted border">Unassigned</span> @endif
    </td>

    <td>
        @if($lead->branch) <span class="small text-muted">{{ $lead->branch->name }}</span>
        @else <span class="text-muted">—</span> @endif
    </td>

    <td><span class="text-muted small">{{ $lead->created_at->format('d M Y') }}</span></td>

    <td>
        <div class="action-icons">
            <a href="{{ route('edu-leads.show', $lead->id) }}" title="View" class="text-info"><i class="las la-eye fs-18"></i></a>
            @if($authUser->isSuperAdmin() || $authUser->isOperationHead() || ($authUser->isLeadManager() && $lead->branch_id === $authUser->branch_id) || ($authUser->isTelecaller() && $lead->assigned_to == $authUser->id))
            <a href="{{ route('edu-leads.edit', $lead->id) }}" title="Edit" class="text-secondary"><i class="las la-pen fs-18"></i></a>
            @endif
            @if($authUser->canAssignLeads())
            <a href="javascript:void(0)" class="assignLeadBtn text-primary" title="Assign"
               data-id="{{ $lead->id }}" data-code="{{ $lead->lead_code }}" data-name="{{ $lead->name }}"
               data-branch-id="{{ $lead->branch_id }}" data-branch-name="{{ $lead->branch?->name }}"
               data-assignee="{{ $lead->assignedTo?->name ?? '' }}">
                <i class="las la-user-plus fs-18"></i>
            </a>
            @endif
            @if($authUser->canDelete())
            <a href="javascript:void(0)" class="deleteLeadBtn text-danger" title="Delete"
               data-id="{{ $lead->id }}" data-name="{{ $lead->name }}">
                <i class="las la-trash-alt fs-18"></i>
            </a>
            @endif
        </div>
    </td>
</tr>

{{-- ════════════════════════════════════════════════════
     MOBILE CARD ROW
════════════════════════════════════════════════════ --}}
<tr class="leads-mobile-cards">
    <td colspan="18" style="padding: .5rem .75rem; border-bottom: 2px solid #e2e8f0;">
        <div class="lm-card">

            {{-- Top row: name + status --}}
            <div class="lm-top">
                <div class="lm-name-block">
                    @if($authUser->canAssignLeads())
                    <input type="checkbox"
                           class="custom-checkbox lead-checkbox lm-checkbox"
                           value="{{ $lead->id }}"
                           data-branch-id="{{ $lead->branch_id }}"
                           data-branch-name="{{ $lead->branch?->name }}">
                    @endif
                    <div>
                        <a href="{{ route('edu-leads.show', $lead->id) }}" class="lm-name">{{ $lead->name }}</a>
                        <span class="lm-code">{{ $lead->lead_code }}</span>
                    </div>
                </div>
                <span class="fs-badge {{ $s['class'] }}">{{ $s['label'] }}</span>
            </div>

            {{-- Info grid --}}
            <div class="lm-grid">

                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-phone"></i> Phone</span>
                    <span class="lm-field-val">
                        <a href="tel:{{ $lead->phone }}" class="text-body text-decoration-underline">{{ $lead->phone }}</a>
                        @if($lead->whatsapp_number && $lead->whatsapp_number !== $lead->phone)
                            &nbsp;<a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->whatsapp_number) }}" target="_blank" class="text-success"><i class="lab la-whatsapp"></i></a>
                        @endif
                    </span>
                </div>

                @if($lead->interest_level)
                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-fire"></i> Interest</span>
                    <span class="lm-field-val">
                        <span class="badge @if($lead->interest_level==='hot') bg-danger @elseif($lead->interest_level==='warm') bg-warning text-dark @else bg-info text-dark @endif">
                            {{ $interestIcons[$lead->interest_level] }} {{ ucfirst($lead->interest_level) }}
                        </span>
                    </span>
                </div>
                @endif

                @if($lead->call_status)
                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-phone-volume"></i> Call Status</span>
                    <span class="lm-field-val">{!! $lead->call_status_badge !!}</span>
                </div>
                @endif

                @if($lead->institution_summary !== 'N/A')
                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-school"></i> Institution</span>
                    <span class="lm-field-val">{{ Str::limit($lead->institution_summary, 32) }}</span>
                </div>
                @endif

                @if($dept)
                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-layer-group"></i> Department</span>
                    <span class="lm-field-val">{{ $dept }}</span>
                </div>
                @endif

                @if($lead->course)
                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-book"></i> Course</span>
                    <span class="lm-field-val fw-semibold">{{ $lead->course->name }}</span>
                </div>
                @endif

                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-user-check"></i> Assigned</span>
                    <span class="lm-field-val">
                        @if($lead->assignedTo)
                            <span class="badge bg-secondary">{{ $lead->assignedTo->name }}</span>
                        @else
                            <span class="badge bg-light text-muted border">Unassigned</span>
                        @endif
                    </span>
                </div>

                @if($lead->leadSource)
                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-bullhorn"></i> Source</span>
                    <span class="lm-field-val">{{ $lead->leadSource->name }}</span>
                </div>
                @endif

                @if($lead->state || $lead->district)
                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-map-pin"></i> Location</span>
                    <span class="lm-field-val">{{ implode(', ', array_filter([$lead->district, $lead->state])) }}</span>
                </div>
                @endif

                {{-- Followup column (latest completed OR specific number) --}}
                @if($displayFu)
                <div class="lm-field" style="grid-column: span 2;">
                    <span class="lm-field-label">
                        <i class="las la-history"></i> {{ $fuColumnLabel }}
                        <span class="text-muted" style="font-size:.6rem;">#{{ $displayFuPosition }}</span>
                    </span>
                    <span class="lm-field-val">
                        <span class="badge {{ $dateBadge }}">{{ $fuDate->format('d M Y') }}</span>
                        @if($fuNote)
                            <span class="text-muted ms-1" style="font-size:.72rem;">{{ Str::limit($fuNote, 40) }}</span>
                        @endif
                    </span>
                </div>
                @endif

                <div class="lm-field">
                    <span class="lm-field-label"><i class="las la-calendar"></i> Created</span>
                    <span class="lm-field-val">{{ $lead->created_at->format('d M Y') }}</span>
                </div>

            </div>

            {{-- Followup badges --}}
            @if($totalFu > 0)
            <div class="lm-followups">
                <span class="badge bg-secondary"><i class="las la-list"></i> {{ $totalFu }} followup{{ $totalFu > 1 ? 's' : '' }}</span>
                @if($overdueFu > 0) <span class="badge bg-danger"><i class="las la-exclamation-circle"></i> {{ $overdueFu }} overdue</span> @endif
                @if($todayFu > 0)  <span class="badge bg-warning text-dark"><i class="las la-bell"></i> {{ $todayFu }} today</span> @endif
                @if($doneFu > 0)   <span class="badge bg-success"><i class="las la-check"></i> {{ $doneFu }} done</span> @endif
            </div>
            @endif

            {{-- Action buttons --}}
            <div class="lm-actions">
                <a href="{{ route('edu-leads.show', $lead->id) }}" class="lm-action-btn btn-view" title="View">
                    <i class="las la-eye"></i>
                </a>
                @if($authUser->isSuperAdmin() || $authUser->isOperationHead() || ($authUser->isLeadManager() && $lead->branch_id === $authUser->branch_id) || ($authUser->isTelecaller() && $lead->assigned_to == $authUser->id))
                <a href="{{ route('edu-leads.edit', $lead->id) }}" class="lm-action-btn btn-edit" title="Edit">
                    <i class="las la-pen"></i>
                </a>
                @endif
                @if($authUser->canAssignLeads())
                <a href="javascript:void(0)" class="lm-action-btn btn-assign assignLeadBtn" title="Assign"
                   data-id="{{ $lead->id }}" data-code="{{ $lead->lead_code }}" data-name="{{ $lead->name }}"
                   data-branch-id="{{ $lead->branch_id }}" data-branch-name="{{ $lead->branch?->name }}"
                   data-assignee="{{ $lead->assignedTo?->name ?? '' }}">
                    <i class="las la-user-plus"></i>
                </a>
                @endif
                @if($authUser->canDelete())
                <a href="javascript:void(0)" class="lm-action-btn btn-delete deleteLeadBtn" title="Delete"
                   data-id="{{ $lead->id }}" data-name="{{ $lead->name }}">
                    <i class="las la-trash-alt"></i>
                </a>
                @endif
            </div>

        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="18" class="text-center py-5 text-muted">
        <i class="las la-graduation-cap" style="font-size:3rem;opacity:.2;display:block;margin-bottom:8px;"></i>
        No leads found matching your filters
    </td>
</tr>
@endforelse
