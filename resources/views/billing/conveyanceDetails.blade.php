@extends('layouts.main')
@section('content')
<div class="container border rounded p-4 shadow-sm bg-white" id="printable-content">
    <!-- Print Button -->
    <div class="d-flex justify-content-between mb-3 no-print">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i> Back
        </a>
        <button type="button" class="btn btn-outline-primary" onclick="printContent()">
            <i class="fas fa-print me-2"></i>Print Statement
        </button>
    </div>

    {{-- Header --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 text-center">
            <h4 class="text-uppercase mb-1" style="font-weight: 900; font-size: 1.8rem; letter-spacing: 1px; color: #2c3e50; font-family: 'Poppins', 'Segoe UI', sans-serif;">
                SUGS LLOYD LIMITED
            </h4>
            <h4 class="fw-bold text-uppercase mb-1">NOIDA, UTTAR PRADESH</h4>
            <h5 class="text-decoration-underline fw-semibold">Conveyance Expense Statement</h5>
        </div>
    </div>

    {{-- Employee Info Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Name:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="{{ $details->first()->user->firstName ?? "N/A" }} {{ $details->first()->user->lastName ?? "N/A" }}" readonly>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Email:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="{{ $details->first()->user->email ?? "N/A" }}" readonly>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Role:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="@if ($details->first()->user->role==0) Admin @elseif ($details->first()->user->role==1) Employee @elseif ($details->first()->user->role==3) Vendor @endif" readonly>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Department:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="ACCOUNTS" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <img src="{{ asset($details->first()->user->image ?? 'images/default.png') }}" alt="Employee Photo" class="rounded-circle border" width="120" height="120">
                </div>
            </div>
        </div>
    </div>

    {{-- Approve / Reject Buttons --}}
    <div class="d-flex justify-content-end mb-3 no-print">
        <button id="approveBtn" class="btn btn-success me-2" style="display:none;">
            <i class="mdi mdi-check-circle-outline me-2 fs-5"></i> Approve
        </button>
        <button id="rejectBtn" class="btn btn-danger" style="display:none;">
            <i class="mdi mdi-close-circle-outline me-2 fs-5"></i> Reject
        </button>
    </div>

    {{-- Travel Details --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0 fw-bold">Conveyance Travel Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Date & Time</th>
                            <th>Vehicle</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Distance (KM)</th>
                            <th>Amount (Rs.)</th>
                            <th>Status</th>
                            <th class="no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAmount = 0; @endphp
                        @foreach ($details as $detail)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($detail->created_at)->format('d-m-Y') }} {{ $detail->time }}</td>
                            <td>{{ $detail->vehicle->category ?? 'N/A' }}</td>
                            <td>{{ $detail->from ?? 'N/A' }}</td>
                            <td>{{ $detail->to ?? 'N/A' }}</td>
                            <td>{{ $detail->kilometer ?? 'N/A' }}</td>
                            <td>₹{{ number_format($detail->amount ?? 0, 2) }}</td>
                            <td>
                                @if ($detail->status === null)
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif ($detail->status == 1)
                                    <span class="badge bg-success">Accepted</span>
                                @elseif ($detail->status == 0)
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                @if ($detail->status === null)
                                    <form action="{{ route("conveyance.accept", $detail->id) }}" method="POST"
                                    style="display: inline-block;" class="action-form" data-action="accept">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success action-btn" data-toggle="tooltip"
                                        title="Accept">
                                        <i class="mdi mdi-check"></i>
                                    </button>
                                    </form>
                                    <form action="{{ route('conveyance.reject', $detail->id) }}" method="POST"
                                        style="display: inline-block;" class="action-form" data-action="reject">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger action-btn" data-toggle="tooltip" title="Reject">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @php 
                            if($detail->status == 1) {
                                $totalAmount += $detail->amount ?? 0; 
                            }
                        @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Summary Section --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Total Records</h6>
                    <input type="text" class="form-control" value="{{ $details->count() }}" readonly>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Approved Amount</h6>
                    <input type="text" class="form-control" value="₹{{ number_format($totalAmount, 2) }}" readonly>
                </div>
            </div>
        </div>
    </div>

    {{-- Signature Section --}}
    <div class="row m-3 mb-4">
        <div class="col-md-6">
            <label class="form-label"><strong>Checked By:</strong></label>
            <input type="text" class="form-control" placeholder="Enter name/signature">
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong>Signature of Dept. Head:</strong></label>
            <input type="text" class="form-control" placeholder="Enter name/signature">
        </div>
    </div>

    {{-- Notes --}}
    <div class="card">
        <div class="card-header fw-bold">Note</div>
        <div class="card-body">
            <ol class="ps-4">
                <li>All conveyance claims must be submitted within 7 days of travel.</li>
                <li>Supporting documents (fuel bills, parking receipts) must be attached.</li>
                <li>Distance calculation should be based on actual route taken.</li>
                <li>Vehicle category rates are as per company policy.</li>
                <li>Any discrepancy in claims will be subject to verification.</li>
                <li>Approved amounts will be processed in the next payroll cycle.</li>
            </ol>
        </div>
    </div>

    {{-- DataTable Export Buttons (Hidden in Print) --}}
    <div class="text-end mt-4 no-print">
        <div id="dataTableButtons"></div>
    </div>
</div>

<script>
    function printContent() {
        // Get the content to print
        const printContent = document.getElementById('printable-content').innerHTML;
        
        // Store original content
        const originalContent = document.body.innerHTML;
        
        // Store original title
        const originalTitle = document.title;
        
        // Set new title for print
        document.title = 'Conveyance Statement - {{ $details->first()->user->firstName ?? "Employee" }} {{ $details->first()->user->lastName ?? "" }}';
        
        // Create print styles
        const printStyles = `
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    line-height: 1.4;
                }
                
                .container {
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    box-shadow: none !important;
                    border: none !important;
                }
                
                .no-print {
                    display: none !important;
                }
                
                .table {
                    font-size: 11px;
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .table th,
                .table td {
                    padding: 4px !important;
                    border: 1px solid #000 !important;
                    text-align: center;
                }
                
                .table-secondary th {
                    background-color: #f8f9fa !important;
                }
                
                .form-control {
                    border: none !important;
                    box-shadow: none !important;
                    background: transparent !important;
                    padding: 2px !important;
                    font-size: 11px;
                    width: 100%;
                }
                
                .card {
                    border: 1px solid #000 !important;
                    box-shadow: none !important;
                    margin-bottom: 15px !important;
                    page-break-inside: avoid;
                }
                
                .card-header {
                    background-color: #f8f9fa !important;
                    border-bottom: 1px solid #000 !important;
                    padding: 8px 12px !important;
                    font-weight: bold;
                    color: #000 !important;
                }
                
                .card-body {
                    padding: 12px !important;
                }
                
                h4, h5, h6 {
                    margin-bottom: 8px !important;
                    color: #000 !important;
                }
                
                .row {
                    display: flex;
                    flex-wrap: wrap;
                    margin: 0 -5px;
                }
                
                .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-md-12 {
                    padding: 0 5px;
                    margin-bottom: 8px;
                }
                
                .col-md-3 { width: 25%; }
                .col-md-4 { width: 33.333%; }
                .col-md-6 { width: 50%; }
                .col-md-8 { width: 66.666%; }
                .col-md-12 { width: 100%; }
                
                .text-center { text-align: center; }
                .text-end { text-align: right; }
                .fw-bold { font-weight: bold; }
                .text-uppercase { text-transform: uppercase; }
                .text-decoration-underline { text-decoration: underline; }
                
                .badge {
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-size: 10px;
                }
                
                .bg-warning { background-color: #ffc107 !important; color: #000; }
                .bg-success { background-color: #198754 !important; color: #fff; }
                .bg-danger { background-color: #dc3545 !important; color: #fff; }
                
                ol {
                    padding-left: 20px;
                }
                
                li {
                    margin-bottom: 4px;
                }
                
                .mb-1 { margin-bottom: 4px !important; }
                .mb-2 { margin-bottom: 8px !important; }
                .mb-3 { margin-bottom: 12px !important; }
                .mb-4 { margin-bottom: 16px !important; }
                
                .rounded-circle {
                    border-radius: 50% !important;
                    border: 2px solid #000 !important;
                }
                
                img {
                    max-width: 100px !important;
                    max-height: 100px !important;
                }
            </style>
        `;
        
        // Replace body content with print content
        document.body.innerHTML = printStyles + '<div class="container">' + printContent + '</div>';
        
        // Print
        window.print();
        
        // Restore original content and title
        document.body.innerHTML = originalContent;
        document.title = originalTitle;
        
        // Re-attach event listeners (since we replaced the DOM)
        location.reload();
    }
    
    // Optional: Add keyboard shortcut for printing (Ctrl+P)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printContent();
        }
    });
</script>
@endsection

{{-- Scripts --}}
@push('scripts')
<script>
    $(document).ready(function () {
        // Initialize DataTable with export buttons
        var table = $('#travelTable').DataTable({
            dom: "<'row d-flex align-items-center justify-content-between'" +
                "<'col-md-6 d-flex align-items-center' f>" +
                "<'col-md-6 d-flex justify-content-end' B>" +
                ">" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5 d-flex align-items-center' i><'col-sm-7 d-flex justify-content-start' p>>",
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="mdi mdi-file-excel"></i>',
                    className: 'btn btn-sm btn-success',
                    titleAttr: 'Export to Excel'
                },
                {
                    extend: 'pdf',
                    text: '<i class="mdi mdi-file-pdf"></i>',
                    className: 'btn btn-sm btn-danger',
                    titleAttr: 'Export to PDF'
                },
                {
                    extend: 'print',
                    text: '<i class="mdi mdi-printer"></i>',
                    className: 'btn btn-sm btn-info',
                    titleAttr: 'Print Table'
                }
            ],
            paging: true,
            pageLength: 50,
            searching: true,
            ordering: true,
            order: [[0, 'desc']], // Sort by Date & Time descending
            responsive: true,
            language: {
                search: '',
                searchPlaceholder: 'Search Travel Records'
            }
        });

        // Move DataTable buttons to our custom location
        table.buttons().container().appendTo('#dataTableButtons');

        $('.dataTables_filter input').addClass('form-control form-control-sm');
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Handle Select All (if needed)
    if(document.getElementById('selectAll')) {
        document.getElementById('selectAll').addEventListener('change', function(e) {
            document.querySelectorAll('.checkboxItem').forEach(cb => cb.checked = e.target.checked);
            toggleApproveRejectButtons();
        });
    }

    // Handle individual checkbox toggle (if needed)
    document.querySelectorAll('.checkboxItem').forEach(cb => {
        cb.addEventListener('change', toggleApproveRejectButtons);
    });

    function toggleApproveRejectButtons() {
        // Add your logic here if needed
    }
</script>
@endpush

{{-- Styles --}}
@push('styles')
<style>
    /* Remove horizontal scroll */
    .table-responsive {
        overflow: hidden;
    }
    
    /* Ensure table doesn't exceed container */
    .table {
        width: 100% !important;
        table-layout: auto;
    }
    
    .btn.btn-secondary:hover {
        color: black !important;
    }
    
    /* Print specific styles */
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush