@if($callLogs->count() > 0)
<div class="table-responsive">
    <table class="table table-hover table-sm align-middle">
        <thead>
            <tr>
                <th>Lead</th>
                <th>Student</th>
                <th>Call Date & Time</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Notes</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($callLogs as $log)
            <tr>
                <td>
                    <span class="badge bg-primary">{{ $log->eduLead->lead_code ?? '—' }}</span>
                </td>
                <td><strong>{{ $log->eduLead->name ?? '—' }}</strong></td>
                <td>
                    <small>{{ $log->call_datetime->format('d M Y, h:i A') }}</small>
                    <br>
                    <small class="text-muted">{{ $log->call_datetime->diffForHumans() }}</small>
                </td>
                <td>
                    @if($log->duration)
                        <span class="badge bg-light text-dark border">
                            <i class="las la-clock me-1"></i>{{ $log->duration }} min
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @php
                        $callStatusMap = [
                            'connected'    => 'bg-success',
                            'not_answered' => 'bg-warning text-dark',
                            'busy'         => 'bg-danger',
                            'voicemail'    => 'bg-secondary',
                        ];
                        $callBadge = $callStatusMap[$log->call_status] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $callBadge }}">
                        {{ ucfirst(str_replace('_', ' ', $log->call_status ?? '—')) }}
                    </span>
                </td>
                <td>
                    <small class="text-muted">{{ Str::limit($log->notes ?? '—', 70) }}</small>
                </td>
                <td>
                    <a href="{{ route('edu-leads.show', $log->edu_lead_id) }}" target="_blank"
                       class="btn btn-sm btn-outline-primary py-0">
                        <i class="las la-external-link-alt"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $callLogs->links('pagination::bootstrap-5') }}

@else
<div class="text-center py-5">
    <i class="las la-phone-slash" style="font-size:4rem; color:#cbd5e0;"></i>
    <p class="text-muted mt-3 mb-0">No call logs found</p>
</div>
@endif
