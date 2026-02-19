@if($followups->count() > 0)
<div class="table-responsive">
    <table class="table table-hover table-sm align-middle">
        <thead>
            <tr>
                <th>Lead</th>
                <th>Student</th>
                <th>Phone</th>
                <th>Follow-up Date</th>
                <th>Notes</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($followups as $followup)
            @php
                $isOverdue = $followup->followup_date->lt(today()) && $followup->status === 'pending';
            @endphp
            <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                <td>
                    <span class="badge bg-primary">{{ $followup->eduLead->lead_code ?? '—' }}</span>
                </td>
                <td><strong>{{ $followup->eduLead->name ?? '—' }}</strong></td>
                <td>
                    @if($followup->eduLead?->phone)
                        <a href="tel:{{ $followup->eduLead->phone }}" class="btn btn-sm btn-success py-0 px-2">
                            <i class="las la-phone"></i> {{ $followup->eduLead->phone }}
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                        {{ $followup->followup_date->format('d M Y') }}
                    </span>
                    @if($isOverdue)
                        <br><small class="text-danger">{{ $followup->followup_date->diffForHumans() }}</small>
                    @else
                        <br><small class="text-muted">{{ $followup->followup_date->diffForHumans() }}</small>
                    @endif
                </td>
                <td>
                    <small class="text-muted">{{ Str::limit($followup->notes ?? '—', 60) }}</small>
                </td>
                <td>
                    @if($followup->status === 'completed')
                        <span class="badge bg-success">Completed</span>
                    @elseif($isOverdue)
                        <span class="badge bg-danger">Overdue</span>
                    @else
                        <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('edu-leads.show', $followup->edu_lead_id) }}" target="_blank"
                       class="btn btn-sm btn-outline-primary py-0">
                        <i class="las la-external-link-alt"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $followups->links('pagination::bootstrap-5') }}

@else
<div class="text-center py-5">
    <i class="las la-calendar-check" style="font-size:4rem; color:#cbd5e0;"></i>
    <p class="text-muted mt-3 mb-0">No follow-ups found</p>
</div>
@endif
