@php
  $awardIcons = ["🥇", "🥈", "🥉"]; // Gold, Silver, Bronze
@endphp

<div class="bg-light mt-4 p-4">
  <!-- Header Section -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Performance Overview</h3>
    <select class="form-select w-auto" id="dateFilter">
      <option value="today">Today</option>
      <option value="week">This Week</option>
      <option value="month">This Month</option>
      <option value="custom">Custom Range</option>
    </select>
  </div>

  <!-- Lists Section -->
  <div class="row">
    @foreach ($performanceData as $category => $items)
      <div class="col">
        <div class="list-header header-{{ $items["color"] }}">{{ $items["title"] }}</div>
        <div class="list-container bg-white p-3">
          @if (count($items["data"]) > 0)
            @foreach ($items["data"] as $item)
              <div class="user-card" onclick="toggleDropdown(this)">
                <div class="d-flex align-items-center">
                  <img src="{{ $item->image }}" alt="User" class="user-avatar">
                  <div>
                    <div class="fw-bold">{{ $item->name }}</div>
                    <div class="position-text">{{ $item->role }}</div>
                    <div class="status-badge">{{ $item->performance }}</div>
                  </div>
                </div>

                @if (!empty($item->siteEngineers))
                  <div class="nested-list">
                    <div class="row">
                      <div class="col">
                        @foreach ($item->siteEngineers as $sub)
                          <div class="user-card" onclick="toggleDropdown(this, event)">
                            <div class="d-flex align-items-center">
                              <img src="{{ $sub->image }}" alt="User" class="user-avatar">
                              <div>
                                <div class="fw-bold">{{ $sub->name }}</div>
                                <div class="position-text">{{ $sub->role }}</div>
                                <div class="status-badge">{{ $sub->performance }}</div>
                              </div>
                            </div>

                            @if (!empty($sub->vendors))
                              <div class="nested-list">
                                <h6 class="text-danger fw-bold mt-2">Weak Vendors</h6>
                                @foreach ($sub->vendors as $vendor)
                                  @if ($vendor->performancePercentage <= 1)
                                    <div class="user-card">
                                      <div class="d-flex align-items-center">
                                        <img src="{{ $vendor->image }}" alt="User" class="user-avatar">
                                        <div>
                                          <div class="fw-bold">{{ $vendor->name }}</div>
                                          <div class="position-text">{{ $vendor->role }}</div>
                                          <div class="status-badge">{{ $vendor->performance }}</div>
                                        </div>
                                      </div>
                                    </div>
                                  @endif
                                @endforeach
                              </div>
                            @endif
                          </div>
                        @endforeach
                      </div>
                      <div class="col">
                        <!-- Top Performers (Site Engineers with >50% tasks completed) -->
                        <h6 class="text-success fw-bold mt-2">Top Site Engineers</h6>
                        @foreach ($item->siteEngineers as $index => $sub)
                          @if ($sub->performancePercentage > 1)
                            <div class="user-card" onclick="toggleDropdown(this, event)">
                              <div class="d-flex align-items-center">
                                @if ($index < 3)
                                  <span class="award-icon">{{ $awardIcons[$index] }}</span>
                                @endif
                                {{-- Show first three award in golden, bronze and silver --}}
                                <img src="{{ $sub->image }}" alt="User" class="user-avatar">
                                <div>
                                  <div class="fw-bold">{{ $sub->name }}</div>
                                  <div class="position-text">{{ $sub->role }}</div>
                                  <div class="status-badge-container">
                                    <div class="status-badge-fill bg-primary" style="width: {{ $sub->performance }}%;">
                                    </div>
                                    <div class="status-badge-text">{{ $sub->performance }}</div>
                                  </div>
                                </div>
                              </div>

                              @if (!empty($sub->vendors))
                                <div class="nested-list">
                                  <h6 class="text-success fw-bold mt-2">Top Vendors</h6>
                                  @foreach ($sub->vendors as $vendor)
                                    @if ($vendor->performancePercentage > 1)
                                      <div class="user-card">
                                        <div class="d-flex align-items-center">
                                          <img src="{{ $vendor->image }}" alt="User" class="user-avatar">
                                          <div>
                                            <div class="fw-bold">{{ $vendor->name }}</div>
                                            <div class="position-text">{{ $vendor->role }}</div>
                                            <div class="status-badge">{{ $vendor->performance }}</div>
                                          </div>
                                        </div>
                                      </div>
                                    @endif
                                  @endforeach
                                </div>
                              @endif
                            </div>
                          @endif
                        @endforeach
                      </div>
                      <div class="col">
                        <!-- Weak Performers (Site Engineers with <50% tasks completed OR 0/0 tasks) -->
                        <h6 class="text-danger fw-bold mt-2">Weak Site Engineers</h6>
                        @foreach ($item->siteEngineers as $sub)
                          @if ($sub->performancePercentage <= 1)
                            <div class="user-card" onclick="toggleDropdown(this, event)">
                              <div class="d-flex align-items-center">
                                <img src="{{ $sub->image }}" alt="User" class="user-avatar">
                                <div>
                                  <div class="fw-bold">{{ $sub->name }}</div>
                                  <div class="position-text">{{ $sub->role }}</div>
                                  <div class="status-badge">{{ $sub->performance }}</div>
                                </div>
                              </div>

                              @if (!empty($sub->vendors))
                                <div class="nested-list">
                                  <h6 class="text-danger fw-bold mt-2">Weak Vendors</h6>
                                  @foreach ($sub->vendors as $vendor)
                                    @if ($vendor->performancePercentage <= 1)
                                      <div class="user-card">
                                        <div class="d-flex align-items-center">
                                          <img src="{{ $vendor->image }}" alt="User" class="user-avatar">
                                          <div>
                                            <div class="fw-bold">{{ $vendor->name }}</div>
                                            <div class="position-text">{{ $vendor->role }}</div>
                                            <div class="status-badge">{{ $vendor->performance }}</div>
                                          </div>
                                        </div>
                                      </div>
                                    @endif
                                  @endforeach
                                </div>
                              @endif
                            </div>
                          @endif
                        @endforeach
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            @endforeach
          @else
            <div>No data for now</div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>

@push("styles")
  <style>
    .list-container {
      max-height: 400px;
      overflow-y: auto;
    }

    .list-header {
      padding: 10px;
      color: white;
      border-radius: 4px 4px 0 0;
    }

    .header-green {
      background-color: #28a745;
    }

    .header-red {
      background-color: #dc3545;
    }

    .header-yellow {
      background-color: #ffc107;
      color: black;
    }

    .user-card {
      border: 1px solid #dee2e6;
      margin-bottom: 8px;
      padding: 10px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .user-card:hover {
      background-color: #f8f9fa;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }

    .nested-list {
      margin-left: 20px;
      display: none;
    }

    .status-badge {
      background-color: #e9ecef;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
    }

    .status-badge-container {
      position: relative;
      width: 100px;
      height: 20px;
      border-radius: 12px;
      background-color: #e9ecef;
      overflow: hidden;
      text-align: center;
    }

    .status-badge-fill {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      border-radius: 12px;
    }

    .status-badge-text {
      position: absolute;
      width: 100%;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 0.8rem;
      font-weight: bold;
      color: black;
    }

    .position-text {
      color: #6c757d;
      font-size: 0.9rem;
    }
  </style>
@endpush

@push("scripts")
  <script>
    document.getElementById('dateFilter').addEventListener('change', function(e) {
      const customRange = document.getElementById('customDateRange');
      if (e.target.value === 'custom') {
        customRange.style.display = 'flex';
      } else {
        customRange.style.display = 'none';
      }
    });

    function applyDateRange() {
      const fromDate = document.getElementById('dateFrom').value;
      const toDate = document.getElementById('dateTo').value;
      console.log('Date range selected:', fromDate, 'to', toDate);

    }

    function toggleDropdown(element, event) {
      if (event) {
        event.stopPropagation();
      }

      const nestedList = element.querySelector('.nested-list');
      if (nestedList) {
        const isVisible = nestedList.style.display === 'block';
        nestedList.style.display = isVisible ? 'none' : 'block';
      }
    }
  </script>
@endpush
