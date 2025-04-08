@extends('layouts.main')
<div class="row">
          <div class="col-12">
              <!-- Date filter -->
              <div class="d-flex justify-content-between align-items-center mb-3 p-2">
                <h3 class="fw-bold"></h3>
                <select class="form-select" style="width:9.375rem;" name="date_filter" id="taskFilter" onchange="filterTasks()">
                  <option value="today" {{ request("date_filter") == "today" ? "selected" : "" }}>Today</option>
                  <option value="this_week" {{ request("date_filter") == "this_week" ? "selected" : "" }}>This Week</option>
                  <option value="this_month" {{ request("date_filter") == "this_month" ? "selected" : "" }}>This Month</option>
                  <option value="all_time" {{ request("date_filter") == "all_time" ? "selected" : "" }}>All Time</option>
                  <option value="custom" {{ request("date_filter") == "custom" ? "selected" : "" }}>Custom Range</option>
                </select>
              </div>
              <!-- Custom Date Range Modal -->
  <div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="customDateModalLabel">Select Custom Date Range</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="customDateForm" action="{{ route("dashboard") }}" method="GET"
            onsubmit="return validateDateRange()">
            <input type="hidden" name="date_filter" value="custom">
            <div class="mb-3">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="start_date" name="start_date"
                value="{{ request("start_date", date("Y-m-d", strtotime("-30 days"))) }}" onchange="updateEndDateMin()">
            </div>
            <div class="mb-3">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" class="form-control" id="end_date" name="end_date"
                value="{{ request("end_date", date("Y-m-d")) }}">
              <div id="dateError" class="invalid-feedback"></div>
            </div>
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
              
  <div class="p-2">
  <x-data-table id="installedPolesTable" class="table-striped table-bordered table-sm table">
    <x-slot:thead>
      <tr>
        <th>#</th>
        <th>Complete Pole Numbers</th>
        <th>Location</th>
        <th>Sim Numbers</th>
        <th>Luminary QR</th>
        <th>Battery QR</th>
        <th>Panel QR</th>
        <th>RMS status</th>
        <th>Actions</th>
      </tr>
    </x-slot:thead>
    <x-slot:tbody>
      @foreach ($installedPoles as $survey)
        <tr>
          <td>{{ $survey->id }}</td>
          <td>{{ $survey->complete_pole_number ?? "N/A" }}</td>
          <td>{{ $survey->lat && $survey->lng ? $survey->lat .', '. $survey->lng : "N/A" }}</td>
          <td>{{ $survey->sim_number ?? "N/A" }}</td>
          <td>{{ $survey->luminary_qr ?? "N/A" }}</td>
          <td>{{ $survey->battery_qr ?? "N/A" }}</td>
          <td>{{ $survey->panel_qr ?? "N/A" }}</td>
          <td>{{ $survey->be ?? "N/A" }}</td>
          <td>
            <!-- View Button -->
            <a href="{{-- route("inventory.show", $member->id) --}}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
              <i class="mdi mdi-eye"></i>
            </a>

            <!-- Delete Button -->

          </td>
        </tr>
      @endforeach
    </x-slot:tbody>
  </x-data-table>
  </div>
</div>
