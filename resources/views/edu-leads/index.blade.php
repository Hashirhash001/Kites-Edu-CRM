@extends('layouts.app')

@section('title', 'Education Leads')

@section('extra-css')
    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <style>
        /* [Keep all the existing CSS - same styles as before] */
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-rejected { background-color: #dc3545; color: #fff; }
        body { overflow-x: hidden; }
        .page-content { overflow-x: hidden; }
        .row { margin-left: 0; margin-right: 0; }
        .row>* { padding-left: 12px; padding-right: 12px; }
        .card { overflow: hidden; }
        .leads-card .card-body { padding: 0; overflow: hidden; }
        .leads-card .card-footer { padding: 15px 20px; background-color: #f8f9fa; }

        /* Table Container */
        .table-container {
            overflow-x: auto;
            overflow-y: visible;
            max-height: none;
            position: relative;
            width: 100%;
        }

        .table-container::-webkit-scrollbar { width: 10px; height: 10px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .table-container::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        .table-container::-webkit-scrollbar-thumb:hover { background: #555; }

        .table-container table { margin-bottom: 0; min-width: 100%; }
        .table-container thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            padding: 12px 15px;
            vertical-align: middle;
            font-weight: 600;
        }

        .table-container tbody td {
            white-space: nowrap;
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table-container tbody tr:hover td { background-color: #f8f9fa; }

        /* Checkbox Styling */
        .checkbox-col { width: 50px; min-width: 50px; text-align: center; padding: 12px 10px !important; }
        .custom-checkbox {
            width: 12px;
            height: 12px;
            cursor: pointer;
            accent-color: #0d6efd;
            transform: scale(1.2);
        }
        .custom-checkbox:hover { transform: scale(1.3); }
        #selectAll {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: #198754;
            transform: scale(1.3);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }

        .checkbox-wrapper input[type="checkbox"] { margin: 0; }
        .table-container tbody tr:has(.custom-checkbox:checked) {
            background-color: #e7f3ff;
            border-left: 3px solid #0d6efd;
        }

        /* Sortable headers */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 20px !important;
        }
        .sortable:hover { background-color: #e9ecef; }
        .sortable::after { content: '⇅'; position: absolute; right: 8px; opacity: 0.3; }
        .sortable.asc::after { content: '↑'; opacity: 1; }
        .sortable.desc::after { content: '↓'; opacity: 1; }

        .action-icons {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
        }

        .action-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .lead-name-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }
        .lead-name-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        /* Loading Overlay */
        .table-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(3px);
        }

        .loading-content {
            background: white;
            padding: 30px 50px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInScale 0.3s ease;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .table-loading { position: relative; opacity: 0.5; pointer-events: none; }

        /* Responsive */
        @media (max-width: 768px) {
            .card-body form .col-3, .card-body form .col-6 { width: 100%; max-width: 100%; flex: 0 0 100%; }
            .table-container table { min-width: 1200px; }
        }
    </style>
@endsection

@section('content')
<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Education Leads</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Education Leads</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-danger fs-6" id="hotLeadsCount">
                        <i class="las la-fire"></i> {{ $hotLeadsCount }} Hot Leads
                    </span>
                    <span class="badge bg-warning fs-6" id="pendingFollowupsCount">
                        <i class="las la-clock"></i> {{ $pendingFollowupsCount }} Pending Followups
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card (NO BRANCH FILTER) -->
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="las la-search"></i> Search
                        </label>
                        <input type="text"
                               class="form-control"
                               id="searchInput"
                               placeholder="Name, Phone, Email, Code...">
                    </div>

                    <!-- Interest Level -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-fire"></i> Interest Level
                        </label>
                        <select class="form-select" id="filterInterestLevel" name="interest_level">
                            <option value="">All Levels</option>
                            <option value="hot">🔥 Hot</option>
                            <option value="warm">☀️ Warm</option>
                            <option value="cold">❄️ Cold</option>
                        </select>
                    </div>

                    <!-- Final Status -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-flag"></i> Status
                        </label>
                        <select class="form-select" id="filterStatus" name="final_status">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="admitted">✅ Admitted</option>
                            <option value="not_interested">Not Interested</option>
                        </select>
                    </div>

                    <!-- Course -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-book"></i> Course
                        </label>
                        <select class="form-select" id="filterCourse" name="course_id">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Country (NEW) -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-globe"></i> Country
                        </label>
                        <select class="form-select" id="filterCountry" name="country">
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lead Source -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-bullhorn"></i> Source
                        </label>
                        <select class="form-select" id="filterSource" name="lead_source_id">
                            <option value="">All Sources</option>
                            @foreach($leadSources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned To -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-user-tie"></i> Assigned To
                        </label>
                        <select class="form-select" id="filterAssignedTo" name="assigned_to">
                            <option value="">All</option>
                            <option value="unassigned">Unassigned</option>
                            @if(auth()->user()->role === 'telecallers')
                                <option value="me">My Leads</option>
                            @else
                                @foreach($telecallers as $telecaller)
                                    <option value="{{ $telecaller->id }}">{{ $telecaller->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-calendar"></i> From Date
                        </label>
                        <input type="date" class="form-control" id="dateFrom" name="date_from">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-calendar"></i> To Date
                        </label>
                        <input type="date" class="form-control" id="dateTo" name="date_to">
                    </div>

                    <!-- Per Page -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="las la-list"></i> Per Page
                        </label>
                        <select class="form-select" id="perPageSelect">
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                                <i class="las la-filter"></i> Apply
                            </button>
                            <button type="button" class="btn btn-secondary" id="resetBtn">
                                <i class="las la-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Leads Table Card -->
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong id="leadCount">{{ $leads->total() }}</strong> leads found
                    <span class="text-muted ms-2" id="pageInfo">
                        @if($leads->total() > 0)
                            {{ $leads->firstItem() ?? 1 }} - {{ $leads->lastItem() ?? $leads->total() }} of {{ $leads->total() }}
                        @else
                            0-0 of 0
                        @endif
                    </span>
                </div>
                <div class="d-flex gap-2">
                    @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                    <button type="button" class="btn btn-info" id="bulkAssignBtn" style="display: none;">
                        <i class="las la-users me-1"></i> Bulk Assign (<span id="bulkcount">0</span>)
                    </button>
                    @endif
                    <a href="{{ route('edu-leads.export') }}" class="btn btn-success" id="exportLeadsBtn">
                        <i class="las la-file-download me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('edu-leads.create') }}" class="btn btn-primary">
                        <i class="las la-plus me-1"></i> Create Lead
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-container">
                <table class="table table-hover mb-0" id="leadsTable">
                    <thead class="table-light">
                        <tr>
                            @if(in_array(auth()->user()->role, ['super_admin', 'lead_manager']))
                            <th class="checkbox-col">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" class="custom-checkbox" id="selectAll" title="Select All">
                                </div>
                            </th>
                            @endif
                            <th class="sortable" data-column="lead_code">Lead Code</th>
                            <th class="sortable" data-column="name">Name</th>
                            <th class="sortable" data-column="phone">Phone</th>
                            <th class="sortable" data-column="country">Country</th>
                            <th class="sortable" data-column="course_id">Course</th>
                            <th class="sortable" data-column="interest_level">Interest</th>
                            <th class="sortable" data-column="final_status">Status</th>
                            <th class="sortable" data-column="lead_source_id">Source</th>
                            <th class="sortable" data-column="assigned_to">Assigned To</th>
                            <th class="sortable" data-column="created_at">Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="leadsTableBody">
                        @include('edu-leads.partials.table-rows')
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <div id="paginationContainer">
                {!! $leads->links('pagination::bootstrap-5') !!}
            </div>
        </div>
    </div>
</div>

<!-- Assign Lead Modal (NO BRANCH) -->
<div class="modal fade" id="assignLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="las la-user-plus me-2"></i>Assign Lead
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignLeadForm">
                @csrf
                <input type="hidden" id="assignLeadId" name="lead_id">

                <div class="modal-body">
                    <!-- Lead Information -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="las la-info-circle fs-20 me-2"></i>
                            <div>
                                <strong>Lead Code:</strong> <span id="assignLeadCode"></span><br>
                                <strong>Name:</strong> <span id="assignLeadName"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Telecaller Selection -->
                    <div class="mb-3">
                        <label for="assignTelecaller" class="form-label fw-semibold">
                            <i class="las la-user-tie me-1"></i>Select Telecaller <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="assignTelecaller" name="assigned_to" required>
                            <option value="">Choose telecaller...</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="assignNotes" class="form-label">
                            <i class="las la-comment me-1"></i>Assignment Notes (Optional)
                        </label>
                        <textarea class="form-control" id="assignNotes" name="notes" rows="2"
                                  placeholder="Add any notes about this assignment..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="las la-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-check me-1"></i>Assign Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal (NO BRANCH) -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="las la-users me-2"></i>Bulk Assign Leads to Telecaller
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAssignForm">
                @csrf
                <div class="modal-body">
                    <!-- Selected Count -->
                    <div class="alert alert-light border mb-3"
                         style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border-left: 4px solid #ffc107;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="las la-check-circle" style="font-size: 28px; color: #997404;"></i>
                            <div>
                                <strong id="selectedCount" style="font-size: 24px; color: #664d03;">0</strong>
                                <span style="font-size: 16px; color: #664d03;"> leads selected for assignment</span>
                            </div>
                        </div>
                    </div>

                    <!-- Telecaller Selection -->
                    <div class="mb-3">
                        <label for="bulkTelecaller" class="form-label fw-semibold">
                            <i class="las la-user-tie me-1"></i>Assign To Telecaller <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="bulkTelecaller" name="assigned_to" required>
                            <option value="">Choose telecaller...</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="bulkNotes" class="form-label fw-semibold">
                            <i class="las la-comment me-1"></i>Notes (Optional)
                        </label>
                        <textarea class="form-control" id="bulkNotes" name="notes" rows="3"
                                  placeholder="Add any notes about this bulk assignment..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="las la-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="las la-check-double me-1"></i> Assign All Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Data from server (NO BRANCH FILTER)
    const allTelecallers = @json($telecallers->map(function($t) {
        return ['id' => $t->id, 'name' => $t->name];
    }));

    let currentSort = {
        column: 'created_at',
        direction: 'desc'
    };

    // Load Leads Function (NO BRANCH PARAM)
    function loadLeads(url = null) {
        let requestUrl = url || '{{ route("edu-leads.index") }}';

        let params = {
            interest_level: $('#filterInterestLevel').val(),
            final_status: $('#filterStatus').val(),
            country: $('#filterCountry').val(),  // NEW
            lead_source_id: $('#filterSource').val(),
            course_id: $('#filterCourse').val(),
            search: $('#searchInput').val(),
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            assigned_to: $('#filterAssignedTo').val(),
            sort_column: currentSort.column,
            sort_direction: currentSort.direction,
            per_page: $('#perPageSelect').val()
        };

        $('#leadsTable').addClass('table-loading');

        $.ajax({
            url: requestUrl,
            type: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#leadsTableBody').html(response.html);
                $('#paginationContainer').html(response.pagination);
                $('#leadCount').text(response.total);

                if (response.from && response.to) {
                    $('#pageInfo').text(response.from + ' - ' + response.to + ' of ' + response.total);
                } else if (response.total === 0) {
                    $('#pageInfo').text('0-0 of 0');
                } else {
                    $('#pageInfo').text('1-' + response.total + ' of ' + response.total);
                }

                $('#leadsTable').removeClass('table-loading');
            },
            error: function(xhr) {
                console.error('Error loading leads', xhr);
                $('#leadsTable').removeClass('table-loading');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load leads'
                });
            }
        });
    }

    // Apply Filters
    $('#applyFiltersBtn').click(function() {
        loadLeads();
    });

    // Reset Filters
    $('#resetBtn').click(function(e) {
        e.preventDefault();
        $('#filterForm')[0].reset();
        currentSort = { column: 'created_at', direction: 'desc' };
        $('.sortable').removeClass('asc desc');
        window.location.href = '{{ route("edu-leads.index") }}';
    });

    // Search with Debounce
    let searchTimeout;
    $('#searchInput').keyup(function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadLeads();
        }, 500);
    });

    // Per Page Change
    $('#perPageSelect').change(function() {
        loadLeads();
    });

    // Pagination Click
    $(document).on('click', '#paginationContainer .pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        if (url) {
            loadLeads(url);
        }
    });

    // Sorting
    $(document).on('click', '.sortable', function() {
        let column = $(this).data('column');

        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }

        $('.sortable').removeClass('asc desc');
        $(this).addClass(currentSort.direction);

        loadLeads();
    });

    // Select All Checkbox
    $('#selectAll').change(function() {
        $('.lead-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkAssignButton();
    });

    // Individual Checkbox
    $(document).on('change', '.lead-checkbox', function() {
        updateBulkAssignButton();

        let total = $('.lead-checkbox').length;
        let checked = $('.lead-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });

    // Update Bulk Assign Button
    function updateBulkAssignButton() {
        let checkedCount = $('.lead-checkbox:checked').length;
        $('#bulkcount').text(checkedCount);

        if (checkedCount > 0) {
            $('#bulkAssignBtn').fadeIn();
        } else {
            $('#bulkAssignBtn').fadeOut();
        }
    }

    // Bulk Assign (NO BRANCH CHECK)
    $('#bulkAssignBtn').click(function() {
        let selectedLeads = [];
        $('.lead-checkbox:checked').each(function() {
            selectedLeads.push($(this).val());
        });

        $('#selectedCount').text(selectedLeads.length);

        // Load all telecallers (NO BRANCH FILTER)
        const telecallerSelect = $('#bulkTelecaller');
        telecallerSelect.html('<option value="">Choose telecaller...</option>');

        allTelecallers.forEach(function(telecaller) {
            telecallerSelect.append($('<option>', {
                value: telecaller.id,
                text: telecaller.name
            }));
        });

        telecallerSelect.prop('disabled', false);
        $('#bulkAssignModal').modal('show');
    });

    // Submit Bulk Assign Form
    $('#bulkAssignForm').submit(function(e) {
        e.preventDefault();

        let selectedLeads = [];
        $('.lead-checkbox:checked').each(function() {
            selectedLeads.push($(this).val());
        });

        let assignedTo = $('#bulkTelecaller').val();
        let notes = $('#bulkNotes').val();

        if (selectedLeads.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'No Leads Selected',
                text: 'Please select at least one lead to assign'
            });
            return false;
        }

        if (!assignedTo) {
            Swal.fire({
                icon: 'error',
                title: 'No Telecaller Selected',
                text: 'Please select a telecaller'
            });
            return false;
        }

        $('#bulkAssignModal').modal('hide');

        $.ajax({
            url: '{{ route("edu-leads.bulk-assign") }}',
            type: 'POST',
            data: {
                lead_ids: selectedLeads,
                assigned_to: assignedTo,
                notes: notes
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: '<strong>' + response.count + '</strong> leads assigned to <strong>' + response.telecaller_name + '</strong>',
                    timer: 3000
                }).then(() => {
                    $('.lead-checkbox').prop('checked', false);
                    $('#selectAll').prop('checked', false);
                    updateBulkAssignButton();
                    loadLeads();
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to assign leads', 'error');
            }
        });

        return false;
    });

    // Delete Lead
    $(document).on('click', '.deleteLeadBtn', function() {
        let leadId = $(this).data('id');
        let leadName = $(this).data('name');

        Swal.fire({
            title: 'Delete Lead?',
            html: 'Are you sure you want to delete <strong>' + leadName + '</strong>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/edu-leads/${leadId}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                loadLeads();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Could not delete lead.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
