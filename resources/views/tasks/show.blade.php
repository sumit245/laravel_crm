@extends("layouts.main")

@section("content")
<div class="container mt-4">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5>Task Details</h5>
      <a href="{{ route("tasks.index") }}" class="btn btn-sm btn-secondary">
        <i class="mdi mdi-arrow-left"></i> Back to Tasks
      </a>
    </div>

    <div class="card-body">

      <!-- ✅ JICR Report Form -->
      <h6 class="mb-3 border-bottom pb-1"><strong>JICR Report</strong></h6>
      <div class="border rounded p-3 mb-4 bg-light">
        <form id="jicrForm" action="{{ route('jicr.generate') }}" method="GET">
          <input type="hidden" name="district" value="{{ $site->district }}">
          <input type="hidden" name="block" value="{{ $site->block }}">
          <input type="hidden" name="panchayat" value="{{ $site->panchayat }}">

          <div class="row">
            <div class="col-md-3 mb-2">
              <label for="fromDate" class="form-label">From Date</label>
              <input type="date" id="fromDate" name="from_date" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
              <label for="toDate" class="form-label">To Date</label>
              <input type="date" id="toDate" name="to_date" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end mb-2">
              <button type="submit" class="btn btn-primary w-100">Generate</button>
            </div>
          </div>
        </form>

        @if (!empty($showReport) && isset($data))
          <div class="mt-3">
            @include("jicr.show", ["data" => $data])
          </div>
        @endif
      </div>

      <!-- ✅ Task Information -->
      <h6 class="mb-3 border-bottom pb-1"><strong>Task Information</strong></h6>
      <div class="row mb-3">
        <div class="col-md-4">
          <strong>Task Name:</strong>
          <p>{{ $task->activity }}</p>
        </div>
        <div class="col-md-4">
          <strong>Assigned To (Engineer):</strong>
          <p>{{ $engineer->firstName }} {{ $engineer->lastName }}</p>
        </div>
        <div class="col-md-4">
          <strong>Assigned To (Vendor):</strong>
          <p>{{ $vendor->name }}</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Start Date:</strong>
          <p>{{ $task->start_date }}</p>
        </div>
        <div class="col-md-6">
          <strong>Due Date:</strong>
          <p>{{ $task->end_date }}</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Status:</strong>
          <p><span class="badge bg-success">{{ $task->status }}</span></p>
        </div>
        <div class="col-md-6">
          <strong>Priority:</strong>
          <p>{{ ucfirst($task->priority) }}</p>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-12">
          <strong>Description:</strong>
          <p>{{ $task->description }}</p>
        </div>
      </div>

      <!-- ✅ Site Information -->
      <h6 class="mb-3 border-bottom pb-1"><strong>Site Information</strong></h6>
      <div class="row mb-3">
        <div class="col-md-4">
          <strong>Site Name:</strong>
          <p>{{ $site->name }}</p>
        </div>
        <div class="col-md-4">
          <strong>State:</strong>
          <p>{{ $site->state }}</p>
        </div>
        <div class="col-md-4">
          <strong>Location:</strong>
          <p>{{ $site->location }}</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Present Capacity:</strong>
          <p>{{ $site->present_capacity }}</p>
        </div>
        <div class="col-md-6">
          <strong>Contact No:</strong>
          <p>{{ $site->contact_no }}</p>
        </div>
      </div>

      <!-- ✅ Material & Rejection -->
      <h6 class="mb-3 border-bottom pb-1"><strong>Material & Rejection</strong></h6>
      <div class="row mb-4">
        <div class="col-md-6">
          <strong>Material Consumed:</strong>
          <p>{{ $task->material_consumed }}</p>
        </div>
        <div class="col-md-6">
          <strong>Rejected At:</strong>
          <p>{{ $task->rejected_at }}</p>
        </div>
      </div>

      <!-- ✅ Location Info -->
      <h6 class="mb-3 border-bottom pb-1"><strong>Location Information</strong></h6>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Survey Location</strong>
          <p>Latitude: {{ $site->survey_latitude }}</p>
          <p>Longitude: {{ $site->survey_longitude }}</p>
        </div>
        <div class="col-md-6">
          <strong>Actual Location</strong>
          <p>Latitude: {{ $site->actual_latitude }}</p>
          <p>Longitude: {{ $site->actual_longitude }}</p>
        </div>
      </div>

      <!-- ✅ File Attachments -->
      <h6 class="mb-3 border-bottom pb-1"><strong>Attached Files</strong></h6>
      <div class="row">
        @forelse ($task->image as $file)
          @php
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          @endphp
          <div class="col-md-3 col-sm-4 col-6 mb-4">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-center align-items-center" style="height: 180px; overflow: hidden; background-color: #f8f9fa;">
                @if ($extension === "pdf")
                  <div class="w-100 text-center pdf-thumbnail" data-pdf-url="{{ $file }}">
                    <span class="text-muted">Loading PDF...</span>
                  </div>
                @else
                  <a href="{{ $file }}" target="_blank" class="d-block">
                    <img src="{{ $file }}" alt="Attachment" class="img-fluid" style="max-height: 160px;">
                  </a>
                @endif
              </div>
              <div class="card-footer text-center bg-white">
                <a href="{{ $file }}" target="_blank" class="text-decoration-none small">
                  {{ $extension === 'pdf' ? 'View PDF' : 'View Image' }}
                </a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12">
            <p class="text-muted">No attachments available.</p>
          </div>
        @endforelse
      </div>

    </div>

    <!-- ✅ Footer with Actions -->
    <div class="card-footer d-flex justify-content-end">
      <a href="{{ route("tasks.edit", $task->id) }}" class="btn btn-sm btn-warning mx-2">
        <i class="mdi mdi-pencil"></i> Edit
      </a>
      <form action="{{ route("tasks.destroy", $task->id) }}" method="POST"
            onsubmit="return confirm('Are you sure you want to delete this task?');">
        @csrf
        @method("DELETE")
        <button type="submit" class="btn btn-sm btn-danger">
          <i class="mdi mdi-delete"></i> Delete
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push("scripts")
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const pdfThumbnails = document.querySelectorAll('.pdf-thumbnail');

    pdfThumbnails.forEach(thumbnail => {
      const pdfUrl = thumbnail.dataset.pdfUrl;
      pdfjsLib.getDocument(pdfUrl).promise.then((pdf) => {
        pdf.getPage(1).then((page) => {
          const viewport = page.getViewport({ scale: 0.5 });
          const canvas = document.createElement('canvas');
          const context = canvas.getContext('2d');
          canvas.width = viewport.width;
          canvas.height = viewport.height;

          page.render({
            canvasContext: context,
            viewport: viewport
          }).promise.then(() => {
            thumbnail.innerHTML = '';
            thumbnail.appendChild(canvas);
          });
        });
      }).catch((error) => {
        console.error('Error loading PDF:', error);
        thumbnail.innerHTML = '<span class="text-danger">Failed to load PDF</span>';
      });
    });
  });
</script>
@endpush
