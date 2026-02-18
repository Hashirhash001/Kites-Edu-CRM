@forelse($leads as $lead)
<tr>
    @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
    <td class="checkbox-col">
        <div class="checkbox-wrapper">
            <input type="checkbox"
                   class="custom-checkbox lead-checkbox"
                   value="{{ $lead->id }}"
                   data-name="{{ $lead->name }}"
                   data-code="{{ $lead->lead_code }}">
        </div>
    </td>
    @endif

    <td>
        <span class="badge bg-primary">{{ $lead->lead_code }}</span>
    </td>

    <td>
        <a href="{{ route('edu-leads.show', $lead->id) }}" class="lead-name-link">
            <h6 class="mb-0">{{ $lead->name }}</h6>
        </a>
        @if($lead->email)
            <small class="text-muted d-block">{{ $lead->email }}</small>
        @endif
    </td>

    <td>
        <strong>{{ $lead->phone }}</strong>
        @if($lead->whatsapp_number)
            <br><small class="text-success">
                <i class="lab la-whatsapp"></i> {{ $lead->whatsapp_number }}
            </small>
        @endif
    </td>

    <td>
        @if($lead->country)
            <span class="badge bg-info">{{ $lead->country }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>

    <td>
        @if($lead->course)
            {{ $lead->course->name }}
        @elseif($lead->course_interested)
            <em class="text-muted">{{ $lead->course_interested }}</em>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>

    <td>
        @if($lead->interest_level)
            @if($lead->interest_level === 'hot')
                <span class="badge bg-danger">🔥 Hot</span>
            @elseif($lead->interest_level === 'warm')
                <span class="badge bg-warning text-dark">☀️ Warm</span>
            @else
                <span class="badge bg-info">❄️ Cold</span>
            @endif
        @else
            <span class="text-muted">-</span>
        @endif
    </td>

    <td>
        @if($lead->final_status === 'pending')
            <span class="badge bg-warning text-dark">Pending</span>
        @elseif($lead->final_status === 'admitted')
            <span class="badge bg-success">✅ Admitted</span>
        @elseif($lead->final_status === 'not_interested')
            <span class="badge bg-danger">Not Interested</span>
        @else
            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $lead->final_status)) }}</span>
        @endif
    </td>

    <td>
        @if($lead->leadSource)
            <span class="badge bg-light text-dark">{{ $lead->leadSource->name }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>

    <td>
        @if($lead->assignedTo)
            <div class="d-flex align-items-center gap-2">
                <i class="las la-user-circle fs-4 text-success"></i>
                <div>
                    <strong>{{ $lead->assignedTo->name }}</strong>
                </div>
            </div>
        @else
            <span class="badge bg-secondary">Unassigned</span>
        @endif
    </td>

    <td>
        <small class="text-muted">
            {{ $lead->created_at->format('d M Y') }}<br>
            {{ $lead->created_at->format('h:i A') }}
        </small>
    </td>

    <td>
        <div class="action-icons">
            <a href="{{ route('edu-leads.show', $lead->id) }}"
               class="text-info"
               title="View">
                <i class="las la-eye fs-4"></i>
            </a>

            @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']) ||
                (auth()->user()->role === 'telecallers' && $lead->assigned_to == auth()->id()))
                <a href="{{ route('edu-leads.edit', $lead->id) }}"
                   class="text-primary"
                   title="Edit">
                    <i class="las la-edit fs-4"></i>
                </a>
            @endif

            @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                @if(!$lead->assigned_to || auth()->user()->role === 'super_admin')
                    <a href="javascript:void(0)"
                       class="text-success assignLeadBtn"
                       data-id="{{ $lead->id }}"
                       data-name="{{ $lead->name }}"
                       data-code="{{ $lead->lead_code }}"
                       title="Assign">
                        <i class="las la-user-plus fs-4"></i>
                    </a>
                @endif

                <a href="javascript:void(0)"
                   class="text-danger deleteLeadBtn"
                   data-id="{{ $lead->id }}"
                   data-name="{{ $lead->name }}"
                   title="Delete">
                    <i class="las la-trash fs-4"></i>
                </a>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="12" class="text-center py-4">
        <div class="text-muted">
            <i class="las la-inbox fs-1 mb-2"></i>
            <p class="mb-0">No leads found</p>
        </div>
    </td>
</tr>
@endforelse
