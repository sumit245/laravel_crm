@extends("layouts.main")
@section("content")
<div class="m-5">
    @foreach ($vendorPoleCounts as $vendorId => $count)
        <div class="tab-pane fade show active" id="vendor-content" role="tabpanel" aria-labelledby="vendor-tab">
            <div class="row mt-3">
              <div class="col-md-4 mb-3">
                <div class="performance-card">
                  <div class="d-flex align-items-center mb-3">
                    <img src="https://via.placeholder.com/50" alt="Profile" class="profile-img me-3">
                    <div>
                      <h6 class="mb-0"> <li>{{ $count['vendor_name'] }}</li></h6>
                      <small class="text-muted">{{-- $vendor->name --}}</small>
                    </div>
                  </div>
                  <div class="mt-3 mb-4">
                    <div class="progress" style="height: 6px;">
                      <div class="progress-bar bg-success" style="width: 85%;"></div>
                    </div>
                    <div class="text-end mt-1">
                      <span class="badge badge-performance badge-high">85%</span>
                    </div>
                  </div>
                  <div class="mt-3">Overall Data</div>
                  <div class="metric">🎯 Targets: <strong></strong>{{ $count['total_poles'] }}</div>
                  <div class="metric">✅ Surveyed Poles: <strong></strong>{{ $count['survey'] }}</div>
                  <div class="metric">💡 Installed Sites: <strong></strong>{{ $count['install'] }}</div>

                  <div class="metric">🧾 Billed: <strong></strong></div>
                  <div class="text-end mt-3">
                    <a href="#" class="btn btn-sm btn-primary">See Details</a>
                  </div>
                </div>
              </div>
              <!-- <div class="col-12"><p class="text-muted">No vendors found.</p></div> -->
            </div>
          </div>
    @endforeach
</div>
              <!-- Tab 2: Today -->
            @foreach ($vendorPoleCountsToday as $vendorId => $data)
                <div class="card bg-light">
                    <h4>{{ $data['vendor_name'] }} (Today)</h4>
                    <p>Total Poles Today: {{ $data['total_poles'] }}</p>
                    <p>Surveyed Today: {{ $data['survey'] }}</p>
                    <p>Installed Today: {{ $data['install'] }}</p>
                    <p>Tasks Today: {{ $data['tasks'] }}</p>
                    <p>Target Today: {{ $data['today_target'] }}</p>
                </div>
            @endforeach
@endsection