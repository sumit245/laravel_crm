{{-- resources/views/meets/notes.blade.php --}}
@extends('layouts.main')

@section('content')
<div class="content-wrapper p-2">
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">Meeting Notes & Whiteboard</h4>

      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('meets.updateNotes', $meet->id) }}">
        @csrf
        @method('PUT')

        {{-- CKEditor WYSIWYG --}}
        <div class="mb-3">
          <label class="form-label">Notes</label>
          <textarea id="notes" name="notes" class="form-control" rows="10">{!! old('notes', $meet->notes) !!}</textarea>
        </div>

        {{-- Whiteboard controls --}}
        <div class="mb-2 d-flex gap-2 flex-wrap">
          <button type="button" class="btn btn-secondary" id="wb-pen">Pen</button>
          <button type="button" class="btn btn-secondary" id="wb-eraser">Eraser</button>
          <button type="button" class="btn btn-secondary" id="wb-undo">Undo</button>
          <button type="button" class="btn btn-secondary" id="wb-redo">Redo</button>
          <button type="button" class="btn btn-warning" id="wb-clear">Clear</button>
          <button type="button" class="btn btn-info" id="wb-save">Save Drawing</button>
        </div>

        <div class="border rounded p-2 mb-3" style="max-width:100%; overflow:auto;">
          <canvas id="whiteboard" width="1000" height="600" style="border:1px dashed #ccc;"></canvas>
        </div>

        <input type="hidden" name="whiteboard_dataurl" id="whiteboard_dataurl">
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" value="1" id="insert_whiteboard" name="insert_whiteboard_into_notes">
          <label class="form-check-label" for="insert_whiteboard">Insert saved drawing into Notes</label>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save Notes</button>
          <a href="{{ route('meets.exportPdf', $meet->id) }}" class="btn btn-outline-primary">Export PDF</a>
          {{-- optional --}}
          {{-- <a href="{{ route('meets.exportExcel', $meet->id) }}" class="btn btn-outline-success">Export Excel</a> --}}
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  {{-- CKEditor 5 Classic --}}
  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

  {{-- Fabric.js --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js" integrity="sha512-hlXlV0b4P2u7kqOeX9o0jKWj7v2wxg6x08Q5o8qGFE5G7xw3Uck9XAbwVQqk8K1TbxLJxMoPfvX6lA5S9wX8Dw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <script>
    // Init CKEditor
    let ck;
    ClassicEditor.create(document.querySelector('#notes')).then(editor => { ck = editor; }).catch(console.error);

    // Init Fabric whiteboard
    const canvas = new fabric.Canvas('whiteboard', { isDrawingMode: true });
    canvas.freeDrawingBrush.width = 2;

    const history = [];
    let redoStack = [];

    function saveState() {
      redoStack = [];
      history.push(JSON.stringify(canvas));
      if (history.length > 50) history.shift();
    }

    // Initial state
    saveState();
    canvas.on('object:added', saveState);
    canvas.on('object:modified', saveState);
    canvas.on('object:removed', saveState);
    canvas.on('path:created', saveState);

    document.getElementById('wb-pen').addEventListener('click', () => {
      canvas.isDrawingMode = true;
      canvas.freeDrawingBrush.color = '#111';
      canvas.freeDrawingBrush.width = 2;
    });

    document.getElementById('wb-eraser').addEventListener('click', () => {
      canvas.isDrawingMode = true;
      canvas.freeDrawingBrush.color = '#ffffff'; // simple eraser (white paint)
      canvas.freeDrawingBrush.width = 14;
    });

    document.getElementById('wb-clear').addEventListener('click', () => {
      canvas.clear();
      canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
      saveState();
    });

    document.getElementById('wb-undo').addEventListener('click', () => {
      if (history.length > 1) {
        redoStack.push(history.pop());
        canvas.loadFromJSON(history[history.length - 1], () => canvas.renderAll());
      }
    });

    document.getElementById('wb-redo').addEventListener('click', () => {
      if (redoStack.length) {
        const state = redoStack.pop();
        history.push(state);
        canvas.loadFromJSON(state, () => canvas.renderAll());
      }
    });

    // Save drawing to hidden input
    document.getElementById('wb-save').addEventListener('click', () => {
      const dataUrl = canvas.toDataURL({ format: 'png', quality: 1.0 });
      document.getElementById('whiteboard_dataurl').value = dataUrl;
      alert('Drawing captured. Click "Save Notes" to persist.');
    });

    // Background to white so PDF looks clean
    canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
  </script>
@endpush
