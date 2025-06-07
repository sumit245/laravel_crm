@extends("layouts.main")

@section("content")
  <div class="container mt-2">

    {{-- Summary Cards --}}
    <div class="row mb-4">
      <div class="col-md-3 col-sm-6">
        <div class="card-summary">
          <h6>Total Trips</h6>
          <div class="value">{{ $count_trip ?? 0 }}</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card-summary">
          <h6>Total KM</h6>
          <div class="value">{{ $total_km  ?? 0 }}</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card-summary">
          <h6>Total Expense</h6>
          <div class="value">Rs.{{ $total_amount ?? 0 }}</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card-summary">
          <h6>Pending Claims</h6>
          <div class="value">{{ $pendingclaimcount ?? 0 }}</div>
        </div>
      </div>
    </div>

    {{-- Approve / Reject Buttons --}}
    <div class="d-flex justify-content-end mb-3">
      <button id="approveBtn" class="btn btn-success me-2" style="display:none;">
        <i class="mdi mdi-check-circle-outline fs-5 me-2"></i> Approve
      </button>
      <button id="rejectBtn" class="btn btn-danger" style="display:none;">
        <i class="mdi mdi-close-circle-outline fs-5 me-2"></i> Reject
      </button>
    </div>

    {{-- TADA Table --}}
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="bi bi-table me-2"></i> TADA Records</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="tadaTable" class="table-bordered table-striped table-sm mt-4 table">
            <thead class="table-white">
              <tr>
                <th>User</th>
                <th>From</th>
                <th>To</th>
                <th>Kilometer</th>
                <th>Date</th>
                <!-- <th>Time</th> -->
                <th>Vehicle</th>
                <th>Rate</th>
                <th>Total</th>
                <th>Bills</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($tadas as $tada)
                <tr>
                  <td>{{ $tada->user->firstName ?? "N/A" }}</td>
                  <td>{{ $tada->from_city ?? "N/A" }}</td>
                  <td>{{ $tada->to_city ?? "N/A" }}</td>
                  <td>{{ $tada->total_km ?? "N/A" }}</td>
                  <td>{{ $tada->created_at ?? "N/A" }}</td>
                  <!-- <td>{{ $tada->time ?? "N/A" }}</td> -->
                  <td>{{ $tada->category }}</td>
                  <td>{{ $tada->rate_per_km }}</td>
                  <td>{{ $tada->rate_per_km * $tada->total_km ?? "N/A" }}</td>
                  <td>{{ $tada->from ?? "N/A" }}</td>
                  <!-- <td>Pending</td>
                                <td>
                                <a href="{{ route("view.bills") }}" class="btn btn-sm btn-outline-primary" >
                                   <i class="bi bi-eye"></i> View Bills
                                </a>
                                </td>  -->
                  <td>
                    @if ($tada->status === null)
                      <span class="badge badge-status badge-pending">Pending</span>
                      <div class="mt-2">
                        <button type="button" class="badge badge-status badge-success accept-tada"
                          data-id="{{ $tada->id }}" title="Accept">
                          <i class="mdi mdi-check"></i> Accept
                        </button>
                        <button type="button" class="badge badge-status badge-rejected reject-tada"
                          data-id="{{ $tada->id }}" title="Reject">
                          <i class="mdi mdi-close"></i> Reject
                        </button>
                      </div>
                    @elseif($tada->status == 0)
                      <span class="badge badge-status badge-rejected"
                        style="background-color: red; color: white">Rejected</span>
                    @elseif($tada->status == 1)
                      <span class="badge badge-status badge-success"
                        style="background-color: mediumseagreen; color: #212529"> Accepted</span>
                    @endif

                  </td>
                  <td class="action-btns">
                    <a href="{{ route("billing.tadaDetails", $tada->user_id) }}" class="btn btn-info btn-sm"
                      title="View Details">
                      <i class="mdi mdi-eye"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Details Modal --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold" id="detailsModalLabel">TADA Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="container">
              <div class="row mb-3">
                <div class="col-md-4"><strong>From</strong><br><span>Madhepura</span></div>
                <div class="col-md-4"><strong>To</strong><br><span>Delhi</span></div>
                <div class="col-md-4"><strong>Kilometer</strong><br><span>986Kms</span></div>
              </div>
              <div class="row mb-3">
                <div class="col-md-4"><strong>Mode of Transport</strong><br><span>Train</span></div>
                <div class="col-md-4"><strong>Date</strong><br><span>5 April 2025</span></div>
                <div class="col-md-4"><strong>Time</strong><br><span>18:00</span></div>
              </div>
              <div class="row mb-3">
                <div class="col-md-4"><strong>Trip Price</strong><br><span>1980</span></div>
                <div class="col-md-4"><strong>Total</strong><br><span>2785</span></div>
                <div class="col-md-4"><strong>Status</strong><br>
                  <span class="badge badge-status badge-pending">Pending</span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    {{-- Updated Bills Modal --}}
    <div class="modal fade" id="billsModal" tabindex="-1" aria-labelledby="billsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fw-bold" id="billsModalLabel"><i class="bi bi-receipt-cutoff me-2"></i> Uploaded Bills
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
              aria-label="Close"></button>
          </div>
          <div class="modal-body px-4 py-4">
            <div class="row g-3">
              @foreach ([["title" => "Train Ticket", "file" => "sample-bill1.pdf"], ["title" => "Hotel Receipt", "file" => "sample-bill2.pdf"], ["title" => "Food", "file" => "sample-bill3.pdf"], ["title" => "Food", "file" => "sample-bill3.pdf"]] as $bill)
                <div class="col-md-4">
                  <div class="bill-card h-100 d-flex flex-column justify-content-between rounded border p-3 shadow-sm">
                    <div class="mb-2">
                      <div class="d-flex align-items-center">
                        <div class="file-icon me-2">
                          <i class="bi bi-file-earmark-pdf text-danger fs-2"></i>
                        </div>
                        <div>
                          <h6 class="fw-semibold mb-1">{{ $bill["title"] }}</h6>
                          <small class="text-muted">{{ $bill["file"] }}</small>
                        </div>
                      </div>
                    </div>
                    <div class="d-flex justify-content-end mt-auto">
                      <a href="{{ asset("storage/bills/" . $bill["file"]) }}" target="_blank"
                        class="btn btn-sm btn-outline-info me-2">
                        <i class="bi bi-eye"></i> View
                      </a>
                      <a href="{{ asset("storage/bills/" . $bill["file"]) }}" download
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

  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#tadaTable').DataTable({
        dom: "<'row d-flex align-items-center justify-content-between'" +
          "<'col-md-6 d-flex align-items-center' f>" +
          "<'col-md-6 d-flex justify-content-end' B>" +
          ">" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5 d-flex align-items-center' i><'col-sm-7 d-flex justify-content-start' p>>",
        buttons: [{
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
        order: [
          [4, 'desc']
        ],
        responsive: true,
        language: {
          search: '',
          searchPlaceholder: 'Search TADA Records'
        }
      });

      $('.dataTables_filter input').addClass('form-control form-control-sm');
    });

    // Approve and Reject buttons Begins
    $('.accept-tada').on('click', function() {
      const tadaId = $(this).data('id');
      updateTadaStatus(tadaId, 1);
    });

    $('.reject-tada').on('click', function() {
      const tadaId = $(this).data('id');
      updateTadaStatus(tadaId, 0);
    });

    function updateTadaStatus(tadaId, status) {
      // Show confirmation dialog
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
          // Send AJAX request to update status
          $.ajax({
            url: `/tada/update-status/${tadaId}`,
            type: 'POST',
            data: {
              status: status,
              _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              Swal.fire(
                'Updated!',
                `TADA record has been ${action}ed.`,
                'success'
              ).then(() => {
                // Reload the page to reflect changes
                location.reload();
              });
            },
            error: function(xhr) {
              Swal.fire(
                'Error!',
                `Failed to ${action} TADA record.`,
                'error'
              );
              console.error(xhr.responseText);
            }
          });
        }
      });
    }
    // Approve and Reject buttons Ends

    // Checkbox logic
    document.getElementById('selectAll').addEventListener('change', function(e) {
      document.querySelectorAll('.checkboxItem').forEach(cb => cb.checked = e.target.checked);
      toggleApproveRejectButtons();
    });

    document.querySelectorAll('.checkboxItem').forEach(cb => {
      cb.addEventListener('change', toggleApproveRejectButtons);
    });

    function toggleApproveRejectButtons() {
      const selected = document.querySelectorAll('.checkboxItem:checked').length;
      document.getElementById('approveBtn').style.display = selected ? 'inline-block' : 'none';
      document.getElementById('rejectBtn').style.display = selected ? 'inline-block' : 'none';
    }
  </script>
@endpush

@push("styles")
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

    .table-responsive {
      overflow: hidden;
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

    .btn-outline-primary i {
      margin-right: 4px;
    }

    .list-group-item .btn {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }

    /* Bill Modal Enhancements */
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
