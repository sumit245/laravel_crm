@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Task Details</h5>
        <a href="{{ route("tasks.index") }}" class="btn btn-sm btn-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Tasks
        </a>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <h6><strong>Task Name:</strong></h6>
            <p>{{ $task->activity }}</p>
          </div>
          <div class="col-md-6">
            <h6><strong>Assigned To (Engineer):</strong></h6>
            <p>{{ $engineer->firstName }} {{ $engineer->lastName }}</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <h6><strong>Start Date:</strong></h6>
            <p>{{ $task->start_date }}</p>
          </div>
          <div class="col-md-6">
            <h6><strong>Due Date:</strong></h6>
            <p>{{ $task->end_date }}</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <h6><strong>Status:</strong></h6>
            <p>
              <span class="badge bg-success">{{ $task->status }}</span>
            </p>
          </div>
          <div class="col-md-6">
            <h6><strong>Priority:</strong></h6>
            <p>{{ ucfirst($task->priority) }}</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <h6><strong>Description:</strong></h6>
            <p>{{ $task->description }}</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <h6><strong>View Photos</strong></h6>
            <div class="d-flex flex-wrap gap-2">
              @foreach ($task->image as $file)
                {{-- <p>{{ $file }}</p> --}}
                @php
                  $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                @endphp
                @if ($extension === "pdf")
                  <!-- Thumbnail for PDF -->
                  <a href="{{ $file }}" target="_blank" class="d-block">
                    <div class="pdf-thumbnail" data-pdf-url="{{ $file }}"
                      style="width: 100px; height: auto; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                      <span class="text-muted">PDF</span>
                    </div>
                  </a>
                @else
                  <!-- Thumbnail for Image -->
                  <a href="{{ $file }}" target="_blank" class="d-block">
                    <img src="{{ $file }}" alt="Image" class="img-thumbnail"
                      style="width: 100px; height: auto;">
                  </a>
                @endif
              @endforeach
            </div>
          </div>
        </div>

      </div>
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
    document.addEventListener("DOMContentLoaded", function() {
      const pdfThumbnails = document.querySelectorAll('.pdf-thumbnail');

      pdfThumbnails.forEach(thumbnail => {
        const pdfUrl = thumbnail.dataset.pdfUrl;

        // Load PDF and render first page as thumbnail
        const loadingTask = pdfjsLib.getDocument(pdfUrl);
        loadingTask.promise.then(function(pdf) {
          pdf.getPage(1).then(function(page) {
            const viewport = page.getViewport({
              scale: 0.5
            });
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = viewport.width;
            canvas.height = viewport.height;

            thumbnail.innerHTML = ''; // Clear placeholder
            thumbnail.appendChild(canvas);

            const renderTask = page.render({
              canvasContext: context,
              viewport: viewport
            });
            return renderTask.promise;
          });
        }).catch(function(error) {
          console.error("Error loading PDF: ", error);
          thumbnail.innerHTML = '<span class="text-danger">PDF Error</span>';
        });
      });
    });
  </script>
@endpush
