@extends('layouts.main')

@section('content')
    <div class="container mt-2">
        {{-- Summary Cards --}}
        <div class="row mb-3">
            <div class="col-md-3 col-sm-3">
                <div class="card-summary">
                    <h6>Total Expense</h6>
                    <div class="value">Rs.{{ $grandTotalAmount ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="card-summary">
                    <h6>Pending Claims</h6>
                    <div class="value">{{ $pendingclaimcount ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="card-summary">
                    <h6>Accepted Claims</h6>
                    <div class="value">{{ $acceptedClaim ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="card-summary">
                    <h6>Rejected Claims</h6>
                    <div class="value">{{ $rejectedClaim ?? 0 }}</div>
                </div>
            </div>
        </div>

        {{-- Approve / Reject Buttons --}}
        <div class="d-flex justify-content-end mb-3" id="bulkTadaActions" style="display: none;">
            <button id="approveBtn" class="btn btn-success btn-sm me-2">Accept</button>
            <button id="rejectBtn" class="btn btn-danger btn-sm">Reject</button>
        </div>

        <x-datatable id="tadaTable" title="TADA Records" :columns="[
            ['title' => 'User'],
            ['title' => 'Visiting to'],
            ['title' => 'Date'],
            ['title' => 'Amount'],
            ['title' => 'Status'],
        ]" :exportEnabled="true" :importEnabled="false"
            :bulkDeleteEnabled="true" :bulkDeleteRoute="null" :viewRoute="route('billing.tadaDetails', ':id')" pageLength="50" searchPlaceholder="Search TADA Records..."
            :filters="[
                [
                    'type' => 'text',
                    'name' => 'user',
                    'label' => 'User',
                    'column' => 0,
                    'width' => 3,
                ],
                [
                    'type' => 'text',
                    'name' => 'visiting_to',
                    'label' => 'Visiting to',
                    'column' => 1,
                    'width' => 3,
                ],
                [
                    'type' => 'date',
                    'name' => 'date_from',
                    'label' => 'Date From',
                    'column' => 2,
                    'width' => 3,
                ],
                [
                    'type' => 'date',
                    'name' => 'date_to',
                    'label' => 'Date To',
                    'column' => 2,
                    'width' => 3,
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'column' => 4,
                    'width' => 3,
                    'options' => [
                        '' => 'All',
                        'pending' => 'Pending',
                        '1' => 'Accepted',
                        '0' => 'Rejected',
                    ],
                ],
            ]">
            @foreach ($tadasWithTotals as $data)
                @php $tada = $data['tada']; @endphp
                <tr>
                    <td>
                        <input type="checkbox" class="row-checkbox tada-checkbox" value="{{ $tada->id }}">
                    </td>
                    <td>{{ $tada->user->firstName ?? 'N/A' }} {{ $tada->user->lastName ?? 'N/A' }}</td>
                    <td>{{ $tada->visiting_to ?? 'N/A' }}</td>
                    <td>{{ $tada->created_at ? $tada->created_at->format('d M Y') : 'N/A' }}</td>
                    <td>₹{{ number_format($data['total_amount'], 2) }}</td>
                    <td>
                        @if ($tada->status === null)
                            <span class="badge badge-status badge-pending">Pending</span>
                        @elseif($tada->status == 0)
                            <span class="badge badge-status badge-rejected"
                                style="background-color: red; color: white">Rejected</span>
                        @elseif($tada->status == 1)
                            <span class="badge badge-status badge-success"
                                style="background-color: mediumseagreen; color: #212529">Accepted</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('billing.tadaDetails', $tada->id) }}" class="btn btn-icon btn-info"
                            title="View Details">
                            <i class="mdi mdi-eye"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-datatable>
    </div>

    {{-- Updated Bills Modal --}}
    <div class="modal fade" id="billsModal" tabindex="-1" aria-labelledby="billsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="billsModalLabel"><i class="bi bi-receipt-cutoff me-2"></i> Uploaded
                        Bills</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="row g-3">
                        @foreach ([['title' => 'Train Ticket', 'file' => 'sample-bill1.pdf'], ['title' => 'Hotel Receipt', 'file' => 'sample-bill2.pdf'], ['title' => 'Food', 'file' => 'sample-bill3.pdf'], ['title' => 'Food', 'file' => 'sample-bill3.pdf']] as $bill)
                            <div class="col-md-4">
                                <div
                                    class="bill-card h-100 d-flex flex-column justify-content-between rounded border p-3 shadow-sm">
                                    <div class="mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="file-icon me-2">
                                                <i class="bi bi-file-earmark-pdf text-danger fs-2"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-1">{{ $bill['title'] }}</h6>
                                                <small class="text-muted">{{ $bill['file'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-auto">
                                        <a href="{{ asset('storage/bills/' . $bill['file']) }}" target="_blank"
                                            class="btn btn-sm btn-outline-info me-2">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ asset('storage/bills/' . $bill['file']) }}" download
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Toggle approve/reject buttons based on checkbox selection
            function toggleApproveRejectButtons() {
                const selected = $('.tada-checkbox:checked').length;
                $('#bulkTadaActions').toggle(selected > 0);
            }

            // Bind checkbox change events
            $(document).on('change', '.tada-checkbox', function() {
                toggleApproveRejectButtons();
            });

            // Update status via AJAX
            function updateTadaStatus(tadaId, status) {
                const action = status === 1 ? 'accept' : 'reject';

                Swal.fire({
                    title: `Confirm ${action}?`,
                    text: `Are you sure you want to ${action} this TADA record?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: status === 1 ? '#28a745' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${action} it!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/tada/update-status/${tadaId}`,
                            type: 'POST',
                            data: {
                                status: status,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                Swal.fire('Updated!', `TADA record has been ${action}ed.`,
                                    'success').then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', `Failed to ${action} TADA record.`,
                                    'error');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            }

            // Bulk update
            function bulkUpdateStatus(status) {
                const action = status === 1 ? 'accept' : 'reject';
                const selectedIds = $('.tada-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return;

                Swal.fire({
                    title: `Confirm ${action}?`,
                    text: `Are you sure you want to ${action} selected TADA records?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: status === 1 ? '#28a745' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${action} them!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/tada/bulk-update-status`,
                            type: 'POST',
                            data: {
                                ids: selectedIds,
                                status: status,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                Swal.fire('Updated!', `TADA records have been ${action}ed.`,
                                    'success').then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', `Failed to ${action} records.`, 'error');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            }

            $('#approveBtn').on('click', function() {
                bulkUpdateStatus(1);
            });

            $('#rejectBtn').on('click', function() {
                bulkUpdateStatus(0);
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .card-summary {
            background: #fff;
            border-left: 5px solid #0d6efd;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
            transition: all 0.3s ease-in-out;
        }

        .card-summary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-summary h6 {
            margin-bottom: 0.4rem;
            color: #6c757d;
            font-weight: 500;
        }

        .card-summary .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.35em 0.6em;
            border-radius: 0.25rem;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-approved {
            background-color: #198754;
            color: #fff;
        }

        .badge-rejected {
            background-color: #dc3545;
            color: #fff;
        }

        .bill-card {
            transition: all 0.2s ease-in-out;
            background-color: #fff;
        }

        .bill-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .file-icon i {
            font-size: 2rem;
            color: #dc3545;
        }

        .bill-card h6 {
            font-size: 1rem;
        }

        .bill-card small {
            font-size: 0.75rem;
        }
    </style>
@endpush
