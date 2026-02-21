{{-- ── Institution & Department Stats ──────────────────────────────── --}}
<div class="row mb-3">

    {{-- School count --}}
    <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
        <a href="{{ route('edu-leads.index', ['institution_type' => 'school']) }}" class="text-decoration-none">
            <div class="card stat-card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Schools</p>
                            <h4 class="mt-0 mb-0 fw-semibold">{{ $schoolLeads }}</h4>
                            <small class="text-muted">{{ $otherInstLeads }} other</small>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-primary text-primary rounded">
                                <i class="las la-school fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- College count --}}
    <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
        <a href="{{ route('edu-leads.index', ['institution_type' => 'college']) }}" class="text-decoration-none">
            <div class="card stat-card mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <p class="text-muted text-uppercase mb-1 fw-medium fs-12">Colleges</p>
                            <h4 class="mt-0 mb-0 fw-semibold">{{ $collegeLeads }}</h4>
                            <small class="text-muted">institution leads</small>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-purple text-purple rounded">
                                <i class="las la-graduation-cap fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- School Streams breakdown --}}
    <div class="col-xl-4 col-lg-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-header py-2">
                <h6 class="card-title mb-0 fs-14">
                    <i class="las la-school me-1 text-primary"></i>School Streams
                </h6>
            </div>
            <div class="card-body py-2 px-3">
                <div class="row g-2">
                    @foreach($schoolDepts as $stream => $count)
                    <div class="col-6">
                        <div class="d-flex justify-content-between align-items-center
                                    border rounded px-2 py-1">
                            <small class="text-muted fw-medium">{{ $stream }}</small>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                {{ $count }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- College Departments breakdown --}}
    <div class="col-xl-4 col-lg-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-header py-2">
                <h6 class="card-title mb-0 fs-14">
                    <i class="las la-graduation-cap me-1 text-purple"></i>College Departments
                </h6>
            </div>
            <div class="card-body py-2 px-3">
                <div class="row g-2">
                    @foreach($collegeDepts as $dept => $count)
                    <div class="col-6">
                        <div class="d-flex justify-content-between align-items-center
                                    border rounded px-2 py-1">
                            <small class="text-muted fw-medium">{{ $dept }}</small>
                            <span class="badge bg-purple bg-opacity-10 text-purple fw-semibold">
                                {{ $count }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
