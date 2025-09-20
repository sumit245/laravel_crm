@extends('layouts.main')

@push('styles')
    {{-- Add Font Awesome if not already in your main layout --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        .whiteboard-container {
            display: flex;
            gap: 15px;
        }

        .fabric-toolbar-vertical {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 10px;
            background-color: #f4f5f7;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            width: 60px;
            /* Fixed width for the toolbar */
        }

        .tool-btn {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
            font-size: 18px;
            color: #333;
        }

        .tool-btn:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
        }

        .tool-btn.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .toolbar-separator {
            height: 1px;
            background-color: #ccc;
            margin: 5px 0;
        }

        .color-picker-wrapper {
            position: relative;
            width: 40px;
            height: 40px;
        }

        #wb-color {
            width: 100%;
            height: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            padding: 0;
            background-color: transparent;
        }

        .canvas-wrapper {
            flex-grow: 1;
            border: 1px dashed #ccc;
            border-radius: 5px;
            overflow: hidden;
            /* Ensures canvas stays within borders */
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper p-2">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">{{ $meet->title }}</h4>
                    <div class="font-weight-bold">{{ $meet->meet_date->format('F d, Y') }}</div>
                </div>

                {{-- Before starting of the form we also need to display last meetings --}}

                <form method="POST" action="{{ route('meets.updateNotes', $meet->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Add a whiteboard button here that will open whiteboard inside a popup --}}
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="10">{!! old('notes', $meet->notes) !!}</textarea>
                    </div>

                    {{-- NEW WHITEBOARD LAYOUT --}}
                    <div class="whiteboard-container mb-3">
                        <!-- Vertical Toolbar -->
                        <div class="fabric-toolbar-vertical">
                            <button type="button" class="tool-btn" id="wb-select" title="Select Tool"><i
                                    class="fa-solid fa-arrow-pointer"></i></button>
                            <button type="button" class="tool-btn active" id="wb-pen" title="Pen Tool"><i
                                    class="fa-solid fa-pencil"></i></button>
                            <div class="toolbar-separator"></div>
                            <button type="button" class="tool-btn" id="wb-add-rect" title="Add Rectangle"><i
                                    class="fa-regular fa-square"></i></button>
                            <button type="button" class="tool-btn" id="wb-add-circle" title="Add Circle"><i
                                    class="fa-regular fa-circle"></i></button>
                            <button type="button" class="tool-btn" id="wb-add-line" title="Add Line"><i
                                    class="fa-solid fa-minus"></i></button>
                            <button type="button" class="tool-btn" id="wb-add-text" title="Add Text"><i
                                    class="fa-solid fa-font"></i></button>
                            <div class="toolbar-separator"></div>
                            <div class="color-picker-wrapper" title="Select Color">
                                <input type="color" id="wb-color" value="#000000">
                            </div>
                            <div class="toolbar-separator"></div>
                            <button type="button" class="tool-btn" id="wb-undo" title="Undo"><i
                                    class="fa-solid fa-rotate-left"></i></button>
                            <button type="button" class="tool-btn" id="wb-redo" title="Redo"><i
                                    class="fa-solid fa-rotate-right"></i></button>
                            <button type="button" class="tool-btn" id="wb-clear" title="Clear Canvas"><i
                                    class="fa-solid fa-trash"></i></button>
                        </div>

                        <!-- Canvas Wrapper -->
                        <div class="canvas-wrapper">
                            <canvas id="whiteboard" width="1000" height="600"></canvas>
                        </div>
                    </div>

                    {{-- Hidden input and controls --}}
                    <button type="button" class="btn btn-info mb-3" id="wb-save">Capture Drawing</button>
                    <input type="hidden" name="whiteboard_dataurl" id="whiteboard_dataurl">

                    {{-- Not needed this checkbox as everything is captured on ckeditor --}}
                    <div class="form-check mb-3 mx-4">
                        <input class="form-check-input" type="checkbox" value="1" id="insert_whiteboard"
                            name="insert_whiteboard_into_notes">
                        <label class="form-check-label" for="insert_whiteboard">Insert captured drawing into Notes</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Notes</button>
                        <a href="{{ route('meets.exportPdf', $meet->id) }}" class="btn btn-outline-primary">Export PDF</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <script>
        // Init CKEditor
        let ck;
        ClassicEditor.create(document.querySelector('#notes')).then(editor => ck = editor).catch(console.error);

        // Init Fabric whiteboard
        const canvas = new fabric.Canvas('whiteboard', {
            isDrawingMode: true,
            backgroundColor: '#ffffff'
        });
        canvas.freeDrawingBrush.width = 2;
        canvas.freeDrawingBrush.color = '#000000';

        const history = [];
        let redoStack = [];
        let activeTool = document.getElementById('wb-pen');

        function setActiveTool(toolBtn) {
            if (activeTool) activeTool.classList.remove('active');
            activeTool = toolBtn;
            activeTool.classList.add('active');
        }

        function saveState() {
            redoStack = [];
            history.push(JSON.stringify(canvas));
            if (history.length > 50) history.shift();
        }

        // --- Event Listeners ---
        canvas.on('object:added', saveState);
        canvas.on('object:modified', saveState);
        canvas.on('object:removed', saveState);
        canvas.on('path:created', saveState);
        saveState(); // Initial state

        // --- Toolbar Button Actions ---
        document.getElementById('wb-select').addEventListener('click', (e) => {
            canvas.isDrawingMode = false;
            setActiveTool(e.currentTarget);
        });

        document.getElementById('wb-pen').addEventListener('click', (e) => {
            canvas.isDrawingMode = true;
            setActiveTool(e.currentTarget);
        });

        // --- Shape Insertion ---
        function addShapeAndSelect(shape) {
            canvas.add(shape);
            canvas.isDrawingMode = false; // Switch to select mode after adding a shape
            setActiveTool(document.getElementById('wb-select'));
        }

        document.getElementById('wb-add-rect').addEventListener('click', () => {
            const rect = new fabric.Rect({
                left: 100,
                top: 100,
                width: 150,
                height: 100,
                fill: 'transparent',
                stroke: canvas.freeDrawingBrush.color,
                strokeWidth: canvas.freeDrawingBrush.width
            });
            addShapeAndSelect(rect);
        });

        document.getElementById('wb-add-circle').addEventListener('click', () => {
            const circle = new fabric.Circle({
                left: 100,
                top: 100,
                radius: 50,
                fill: 'transparent',
                stroke: canvas.freeDrawingBrush.color,
                strokeWidth: canvas.freeDrawingBrush.width
            });
            addShapeAndSelect(circle);
        });

        document.getElementById('wb-add-line').addEventListener('click', () => {
            const line = new fabric.Line([50, 100, 250, 100], {
                stroke: canvas.freeDrawingBrush.color,
                strokeWidth: canvas.freeDrawingBrush.width
            });
            addShapeAndSelect(line);
        });

        document.getElementById('wb-add-text').addEventListener('click', () => {
            const text = new fabric.IText('Your Text Here', {
                left: 100,
                top: 100,
                fontFamily: 'Arial',
                fontSize: 24,
                fill: canvas.freeDrawingBrush.color
            });
            addShapeAndSelect(text);
        });

        // --- Color and Width ---
        document.getElementById('wb-color').addEventListener('input', (e) => {
            const color = e.target.value;
            canvas.freeDrawingBrush.color = color;
            const activeObject = canvas.getActiveObject();
            if (activeObject) {
                // For shapes and text
                if (activeObject.get('fill')) activeObject.set('fill', color);
                if (activeObject.get('stroke')) activeObject.set('stroke', color);
                canvas.renderAll();
            }
        });

        // --- History and Clear Actions ---
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

        document.getElementById('wb-clear').addEventListener('click', () => {
            if (confirm('Are you sure you want to clear the entire canvas?')) {
                canvas.clear();
                canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
                saveState();
            }
        });

        // --- MODIFIED: "Insert Drawing" Button Functionality ---
        document.getElementById('wb-save').addEventListener('click', () => {
            if (!ck) {
                alert('Editor is not ready yet. Please wait a moment.');
                return;
            }

            // 1. Generate the Data URL from the canvas
            const dataUrl = canvas.toDataURL({
                format: 'png',
                quality: 1.0
            });

            // 2. Use the CKEditor model to insert the image at the current cursor position
            ck.model.change(writer => {
                const imageElement = writer.createElement('imageBlock', {
                    src: dataUrl
                });
                // Insert the image into the editor
                ck.model.insertContent(imageElement, ck.model.document.selection);
            });
        });
    </script>
@endpush
