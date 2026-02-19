@if($leads->count() > 0)
<div class="table-responsive">
    <table class="table table-hover table-sm align-middle">
        <thead>
            <tr>
                <th>Lead Code</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Course</th>
                <th>Country</th>
                <th>Interest</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($leads as $lead)
            <tr>
                <td>
                    <span class="badge bg-primary">{{ $lead->lead_code }}</span>
                </td>
                <td><strong>{{ $lead->name }}</strong></td>
                <td>
                    <a href="tel:{{ $lead->phone }}" class="btn btn-sm btn-success py-0 px-2">
                        <i class="las la-phone"></i> {{ $lead->phone }}
                    </a>
                </td>
                <td>
                    <small>{{ $lead->course->name ?? '—' }}</small>
                </td>
                <td>
                    <small>{{ $lead->country ?? '—' }}</small>
                </td>
                <td>
                    @php
                        $interestMap = [
                            'hot'  => ['bg' => 'bg-danger',  'label' => '🔥 Hot'],
                            'warm' => ['bg' => 'bg-warning text-dark', 'label' => '☀️ Warm'],
                            'cold' => ['bg' => 'bg-info',    'label' => '❄️ Cold'],
                        ];
                        $interest = $interestMap[$lead->interest_level] ?? ['bg' => 'bg-secondary', 'label' => ucfirst($lead->interest_level ?? '—')];
                    @endphp
                    <span class="badge {{ $interest['bg'] }}">{{ $interest['label'] }}</span>
                </td>
                <td>
                    @php
                        $statusMap = [
                            'pending'        => ['bg' => 'bg-warning text-dark', 'label' => '⏳ Pending'],
                            'contacted'      => ['bg' => 'bg-info',              'label' => '📞 Contacted'],
                            'follow_up'      => ['bg' => 'bg-orange text-white', 'label' => '🔔 Follow Up'],
                            'admitted'       => ['bg' => 'bg-success',           'label' => '✅ Admitted'],
                            'not_interested' => ['bg' => 'bg-danger',            'label' => '❌ Not Interested'],
                            'dropped'        => ['bg' => 'bg-secondary',         'label' => '🚫 Dropped'],
                        ];
                        $status = $statusMap[$lead->final_status] ?? ['bg' => 'bg-secondary', 'label' => ucfirst($lead->final_status ?? '—')];
                    @endphp
                    <span class="badge {{ $status['bg'] }}" style="font-size:.75rem;">{{ $status['label'] }}</span>
                </td>
                <td>
                    <small>{{ $lead->assignedTo->name ?? '<span class="text-muted">Unassigned</span>' }}</small>
                </td>
                <td>
                    <small class="text-muted">{{ $lead->created_at->format('d M Y') }}</small>
                </td>
                <td>
                    <a href="{{ route('edu-leads.show', $lead->id) }}" target="_blank"
                       class="btn btn-sm btn-outline-primary py-0">
                        <i class="las la-external-link-alt"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $leads->links('pagination::bootstrap-5') }}

@else
<div class="text-center py-5">
    <i class="las la-inbox" style="font-size:4rem; color:#cbd5e0;"></i>
    <p class="text-muted mt-3 mb-0">No leads found</p>
</div>
@endif
