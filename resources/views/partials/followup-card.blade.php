@php
    $isOverdue = $followup->followup_date->lt(today());
    $isToday   = $followup->followup_date->isToday();
    $lead      = $followup->eduLead;
@endphp

<div class="followup-item">
    <div class="followup-header">
        <div>
            <div class="followup-title">
                @if($lead)
                    <a href="{{ route('edu-leads.show', $lead) }}">
                        {{ $lead->name }}
                    </a>
                    <small class="text-muted fw-normal ms-1">{{ $lead->lead_code }}</small>
                @else
                    <span class="text-muted">Lead Deleted</span>
                @endif
            </div>
            <div class="followup-meta">
                @if($lead?->phone)
                    <span><i class="las la-phone"></i> {{ $lead->phone }}</span>
                @endif
                @if($lead?->course)
                    <span><i class="las la-graduation-cap"></i> {{ $lead->course->name }}</span>
                @endif
                @if($followup->assignedToUser)
                    <span><i class="las la-user"></i> {{ $followup->assignedToUser->name }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column align-items-end gap-2">

            {{-- Overdue label --}}
            @if($isOverdue)
                <span class="overdue-badge">
                    <i class="las la-exclamation-triangle me-1"></i>Overdue
                </span>
            @endif
        </div>
    </div>

    {{-- Date / Time --}}
    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="time-preference-badge">
            <i class="las la-calendar-day"></i>
            {{ $followup->followup_date->format('D, d M Y') }}
            @if($followup->followup_time)
                &nbsp;·&nbsp;{{ \Carbon\Carbon::parse($followup->followup_time)->format('h:i A') }}
            @endif
        </div>
    </div>

    {{-- Notes --}}
    @if($followup->notes)
        <div class="followup-notes">
            <small>{{ $followup->notes }}</small>
        </div>
    @endif

    {{-- Actions --}}
    <div class="d-flex justify-content-end mt-3">
        @if($lead)
            <a href="{{ route('edu-leads.show', $lead) }}" class="btn btn-sm btn-outline-secondary me-2 text-black">
                <i class="las la-eye me-1"></i> View Lead
            </a>
        @endif
        {{-- <button class="btn-complete btn btn-sm btn-complete-followup" data-id="{{ $followup->id }}">
            <i class="las la-check me-1"></i> Mark Complete
        </button> --}}
    </div>
</div>
