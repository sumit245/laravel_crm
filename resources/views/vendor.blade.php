@extends("layouts.main")
@section("content")

<style>
  .vendor-overview-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: box-shadow 0.3s ease;
    background: #fff;
  }

  .vendor-overview-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .profile-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
  }

  .metric {
    font-size: 0.9rem;
    margin-bottom: 5px;
  }

  .badge-performance {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .badge-high {
    background-color: #d4edda;
    color: #155724;
  }

  .badge-medium {
    background-color: #fff3cd;
    color: #856404;
  }

  .badge-low {
    background-color: #f8d7da;
    color: #721c24;
  }

  .action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
  }

  .action-buttons .btn {
    flex: 1;
    min-width: 80px;
    font-size: 0.8rem;
    padding: 5px 8px;
  }

  .supplier-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: #f8f9fa;
  }

  .detail-metric {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
  }

  .detail-metric:last-child {
    border-bottom: none;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-top: 15px;
  }

  .stat-item {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
  }

  .table-responsive {
    margin-top: 10px;
  }

  .performance-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: box-shadow 0.3s ease;
    background: #fff;
  }

  .performance-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
</style>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Vendors</h3>
        <small class="text-muted">A quick summary of vendor activity under this manager.</small>
    </div>

    <div class="row">
        @foreach ($vendorPoleCounts as $vendorId => $overallData)
            @php
                // Get today's data for this vendor
                $todayData = $vendorPoleCountsToday[$vendorId] ?? null;
                
                // Calculate performance percentage
                $performancePercentage = $overallData['total_poles'] > 0 
                    ? round(($overallData['install'] / $overallData['total_poles']) * 100) 
                    : 0;
                
                // Determine badge class based on performance
                $badgeClass = 'badge-low';
                if ($performancePercentage >= 80) {
                    $badgeClass = 'badge-high';
                } elseif ($performancePercentage >= 50) {
                    $badgeClass = 'badge-medium';
                }
                
                // Calculate backlog count
                $backlogCount = $todayData ? $todayData['backlog'] : 0;
            @endphp
            
            <div class="col-md-6 mb-4">
                <div class="vendor-overview-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="/placeholder.svg?height=50&width=50" alt="Vendor Profile" class="profile-img me-3">
                        <div>
                            <h6 class="mb-0">{{ $overallData['vendor_name'] }}</h6>
                            <small class="text-muted">🧾 Vendor Info</small>
                        </div>
                    </div>

                    <div class="mt-3 mb-4">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $performancePercentage }}%;"></div>
                        </div>
                        <div class="text-end mt-1">
                            <span class="badge badge-performance {{ $badgeClass }}">{{ $performancePercentage }}%</span>
                        </div>
                    </div>

                    <!-- <div class="metric">📱 Mobile: <strong>+1-234-567-8901</strong></div>
                    <div class="metric">👤 Manager: <strong>Manager Name</strong></div>
                     -->
                    <!-- <div class="action-buttons mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="showVendorDetails('{{ $overallData['vendor_name'] }}', {{ $vendorId }})">
                            View Details
                        </button>
                    </div> -->

                    @if($backlogCount > 0)
                    <div class="vendor-overview-card mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-dark">📋 <strong>Total Backlog</strong></h6>
                            <span class="badge badge-performance badge-medium">{{ $backlogCount }}</span>
                        </div>

                        <div class="metric text-dark">⏳ Pending Installations: <strong>{{ $backlogCount }}</strong></div>

                        <div class="action-buttons mt-3">
                            <button class="btn btn-sm btn-outline-dark" onclick="showBacklogModal('{{ $overallData['vendor_name'] }}', {{ $vendorId }})">
                                View Backlog
                            </button>
                        </div>

                        <p class="text-muted mt-2 mb-0" style="font-size: 0.75rem; font-style: italic;">
                            Represents all unfulfilled installation targets.
                        </p>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-6">
                            <div class="vendor-overview-card">
                                <h6 class="text-center mb-3">📅 Today's Stats</h6>
                                <div class="detail-metric">
                                    <span class="text-muted">Total Poles</span>
                                    <strong>{{ $todayData['total_poles'] ?? 0 }}</strong>
                                </div>
                                <div class="detail-metric">
                                    <span class="text-muted">Surveyed</span>
                                    <strong>{{ $todayData['survey'] ?? 0 }}</strong>
                                </div>
                                <div class="detail-metric">
                                    <span class="text-muted">Installed</span>
                                    <strong>{{ $todayData['install'] ?? 0 }}</strong>
                                </div>
                                <div class="detail-metric">
                                    <span class="text-muted">Tasks</span>
                                    <strong>{{ $todayData['tasks'] ?? 0 }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="vendor-overview-card">
                                <h6 class="text-center mb-3">📊 Overall Stats</h6>
                                <div class="detail-metric">
                                    <span class="text-muted">Total Poles</span>
                                    <strong>{{ $overallData['total_poles'] }}</strong>
                                </div>
                                <div class="detail-metric">
                                    <span class="text-muted">Surveyed</span>
                                    <strong>{{ $overallData['survey'] }}</strong>
                                </div>
                                <div class="detail-metric">
                                    <span class="text-muted">Installed</span>
                                    <strong>{{ $overallData['install'] }}</strong>
                                </div>
                                <div class="detail-metric">
                                    <span class="text-muted">Billed</span>
                                    <strong>{{ $overallData['billed'] ?? 0 }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Vendor Details Modal -->
<div class="modal fade" id="vendorDetailsModal" tabindex="-1" aria-labelledby="vendorDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vendorDetailsModalLabel">Vendor Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="vendorDetailsContent">
                    <!-- Vendor details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Backlog Modal -->
<div class="modal fade" id="backlogModal" tabindex="-1" aria-labelledby="backlogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="backlogModalLabel">Backlog Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="backlogTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>District</th>
                                <th>Block</th>
                                <th>Panchayat</th>
                                <th>Ward</th>
                                <th>Total Poles</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="backlogTableBody">
                            <!-- Backlog data will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Store vendor data for JavaScript access with proper backlog sites conversion
const vendorData = {
    @foreach ($vendorPoleCounts as $vendorId => $overallData)
        {{ $vendorId }}: {
            overall: @json($overallData),
            today: @json($vendorPoleCountsToday[$vendorId] ?? null),
            backlogSites: [
                @if(isset($vendorPoleCountsToday[$vendorId]) && isset($vendorPoleCountsToday[$vendorId]['backlog_sites']))
                    @foreach($vendorPoleCountsToday[$vendorId]['backlog_sites'] as $site)
                        {
                            district: "{{ $site->district }}",
                            block: "{{ $site->block }}",
                            panchayat: "{{ $site->panchayat }}",
                            ward: "{{ $site->ward }}",
                            total_poles: {{ $site->total_poles }}
                        },
                    @endforeach
                @endif
            ]
        },
    @endforeach
};

function showVendorDetails(vendorName, vendorId) {
    document.getElementById('vendorDetailsModalLabel').textContent = vendorName + ' - Vendor Details';
    
    const vendor = vendorData[vendorId];
    const overall = vendor.overall;
    const today = vendor.today;

    let content = `
        <div class="supplier-card">
            <h6 class="mb-3">Vendor Information</h6>
            <div class="detail-metric">
                <span>Vendor Name</span>
                <strong>${vendorName}</strong>
            </div>
            <div class="detail-metric">
                <span>Mobile Number</span>
                <strong>+1-234-567-8901</strong>
            </div>
            <div class="detail-metric">
                <span>Manager</span>
                <strong>Manager Name</strong>
            </div>
        </div>
        
        <div class="supplier-card">
            <h6 class="mb-3">Overall Statistics</h6>
            <div class="detail-metric">
                <span>Total Poles</span>
                <strong>${overall.total_poles}</strong>
            </div>
            <div class="detail-metric">
                <span>Surveyed Poles</span>
                <strong>${overall.survey}</strong>
            </div>
            <div class="detail-metric">
                <span>Installed Sites</span>
                <strong>${overall.install}</strong>
            </div>
            <div class="detail-metric">
                <span>Billed</span>
                <strong>${overall.billed || 0}</strong>
            </div>
        </div>`;

    if (today) {
        content += `
        <div class="supplier-card">
            <h6 class="mb-3">Today's Statistics</h6>
            <div class="detail-metric">
                <span>Total Poles Today</span>
                <strong>${today.total_poles}</strong>
            </div>
            <div class="detail-metric">
                <span>Surveyed Today</span>
                <strong>${today.survey}</strong>
            </div>
            <div class="detail-metric">
                <span>Installed Today</span>
                <strong>${today.install}</strong>
            </div>
            <div class="detail-metric">
                <span>Tasks Today</span>
                <strong>${today.tasks}</strong>
            </div>
            <div class="detail-metric">
                <span>Backlog Today</span>
                <strong>${today.backlog}</strong>
            </div>
        </div>`;
    }

    document.getElementById('vendorDetailsContent').innerHTML = content;

    var modal = new bootstrap.Modal(document.getElementById('vendorDetailsModal'));
    modal.show();
}

function showBacklogModal(vendorName, vendorId) {
    document.getElementById('backlogModalLabel').textContent = vendorName + ' - Backlog Details';
    
    if ($.fn.DataTable.isDataTable('#backlogTable')) {
        $('#backlogTable').DataTable().destroy();
    }
    
    const tableBody = document.getElementById('backlogTableBody');
    tableBody.innerHTML = '';
    
    const vendor = vendorData[vendorId];
    const backlogSites = vendor.backlogSites;
    
    console.log('Vendor ID:', vendorId);
    console.log('Vendor Name:', vendorName);
    console.log('Backlog Sites:', backlogSites);
    
    if (backlogSites && backlogSites.length > 0) {
        backlogSites.forEach(site => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${site.district}</td>
                <td>${site.block}</td>
                <td>${site.panchayat}</td>
                <td>${site.ward}</td>
                <td>${site.total_poles}</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
            `;
            tableBody.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="6" class="text-center">No backlog sites found.</td>';
        tableBody.appendChild(row);
    }

    $('#backlogTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        order: [[0, 'asc']],
        responsive: true,
        destroy: true 
    });

    var modal = new bootstrap.Modal(document.getElementById('backlogModal'));
    modal.show();
}
</script>

@endsection