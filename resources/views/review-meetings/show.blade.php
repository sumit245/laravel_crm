@extends('layouts.main')

@push('styles')
    {{-- Basic styling for the Kanban board and layout --}}
    <style>
        .kanban-board {
            display: flex;
            justify-content: space-between;
            background-color: #f4f5f7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .kanban-stage {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            border-radius: 5px;
            color: #495057;
            font-weight: 500;
            position: relative;
            margin: 0 5px;
        }

        .kanban-stage:not(.active)::after {
            content: '→';
            position: absolute;
            right: -15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 1.5rem;
        }

        .kanban-stage:last-child::after {
            content: '';
        }

        .kanban-stage.active {
            background-color: #0d6efd;
            color: #fff;
            font-weight: bold;
        }

        .kanban-stage.completed {
            background-color: #198754;
            color: white;
        }

        .meeting-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            align-items: flex-start;
        }

        #editor-container .ck-editor__editable {
            min-height: 550px;
        }

        #whiteboard-container {
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .action-buttons {
            margin-top: 1rem;
            display: flex;
            gap: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper p-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">{{ $meet->title }}</h4>
                    <div class="font-weight-bold">{{ $meet->meet_date->format('F d, Y') }}</div>
                </div>

                {{-- 1. Kanban Status Board --}}
                <div class="kanban-board">
                    @php
                        $statuses = ['Not Started', 'In Progress', 'Completed'];
                        $currentStatus = $meet->status ?? 'Not Started';
                    @endphp
                    @foreach ($statuses as $status)
                        <div
                            class="kanban-stage {{ $currentStatus === $status ? 'active' : '' }} {{ $currentStatus === 'Completed' && $status === 'Completed' ? 'completed' : '' }}">
                            {{ $status }}
                        </div>
                    @endforeach
                </div>

                <div class="meeting-layout">
                    {{-- 2. Rich Text Editor & Action Buttons --}}
                    <div id="editor-wrapper">
                        <h5>Minutes of Meeting</h5>
                        <div id="editor-container">
                            <div id="editor">{!! $meet->minutes_of_meeting ?? '' !!}</div>
                        </div>
                        <div class="action-buttons">
                            <button id="save-notes-btn" class="btn btn-info">Save Notes</button>
                            <button id="export-pdf-btn" class="btn btn-danger">Export as PDF</button>
                            @if ($meet->status === 'Completed')
                                <form action="{{ route('meets.share', $meet->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to email these notes to all participants?');">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Share Notes via Email</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- 3. Whiteboard with "Insert" Button --}}
                    <div id="whiteboard-wrapper">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Whiteboard</h5>
                            <button id="insert-drawing-btn" class="btn btn-primary btn-sm mb-2">Insert Drawing to Notes
                                ↓</button>
                        </div>
                        <div id="whiteboard-container" style="width: 100%; height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Required CDNs for CKEditor, tldraw, jspdf, and html2canvas --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script src="https://unpkg.com/tldraw@2.1.4/dist/tldraw.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const meetingId = {{ $meet->id }};
            let editor; // To hold CKEditor instance
            let tldrawEditor; // To hold tldraw instance

            // 1. Initialize CKEditor 5
            ClassicEditor
                .create(document.querySelector('#editor'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList',
                        'blockQuote', 'insertTable', 'undo', 'redo'
                    ],
                })
                .then(newEditor => {
                    editor = newEditor;
                })
                .catch(error => {
                    console.error('CKEditor initialization error:', error);
                });

            // 2. Initialize tldraw Whiteboard
            const whiteboardContainer = document.getElementById('whiteboard-container');
            if (whiteboardContainer) {
                tldrawEditor = Tldraw.createEditor({
                    // You can add initial shapes, etc. here
                });

                const TldrawComponent = Tldraw.Tldraw({
                    editor: tldrawEditor
                });
                // The component returns a function that you can use to mount the component
                // to a DOM element.
                TldrawComponent(whiteboardContainer);

                // Load existing whiteboard data if available
                // (You would need to save/load this similar to the notes)
            }

            // 3. Handle "Insert Drawing" Button Click
            document.getElementById('insert-drawing-btn').addEventListener('click', async () => {
                if (!tldrawEditor || !editor) {
                    alert('Editor or whiteboard not initialized.');
                    return;
                }

                const svg = await tldrawEditor.getSvg(tldrawEditor.getShapeIds());
                if (!svg) {
                    alert('Whiteboard is empty. Please draw something first.');
                    return;
                }

                // Convert SVG to a Base64 data URL to embed in the editor
                const blob = new Blob([svg.outerHTML], {
                    type: 'image/svg+xml'
                });
                const reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = () => {
                    const base64data = reader.result;
                    // Insert the image into CKEditor
                    editor.model.change(writer => {
                        const imageElement = writer.createElement('image', {
                            src: base64data
                        });
                        editor.model.insertContent(imageElement, editor.model.document
                            .selection);
                    });
                };
            });

            // 4. Handle "Save Notes" Button Click
            document.getElementById('save-notes-btn').addEventListener('click', function() {
                const btn = this;
                btn.textContent = 'Saving...';
                btn.disabled = true;

                const notesContent = editor.getData();

                fetch(`/meets/${meetingId}/save-notes`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            notes: notesContent
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Notes saved successfully!');
                        } else {
                            alert('Failed to save notes.');
                        }
                    })
                    .catch(error => {
                        console.error('Error saving notes:', error);
                        alert('An error occurred while saving.');
                    })
                    .finally(() => {
                        btn.textContent = 'Save Notes';
                        btn.disabled = false;
                    });
            });

            // 5. Handle "Export as PDF" Button Click
            document.getElementById('export-pdf-btn').addEventListener('click', function() {
                const btn = this;
                btn.textContent = 'Generating...';
                btn.disabled = true;

                const {
                    jsPDF
                } = window.jspdf;
                const editorContent = document.querySelector('#editor-container .ck-editor__editable');

                html2canvas(editorContent, {
                    scale: 2, // Improve resolution
                    useCORS: true
                }).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF({
                        orientation: 'portrait',
                        unit: 'pt',
                        format: 'a4'
                    });

                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = pdf.internal.pageSize.getHeight();
                    const canvasWidth = canvas.width;
                    const canvasHeight = canvas.height;
                    const ratio = canvasWidth / canvasHeight;
                    const imgWidth = pdfWidth - 40; // with some margin
                    const imgHeight = imgWidth / ratio;

                    let heightLeft = imgHeight;
                    let position = 20; // top margin

                    pdf.addImage(imgData, 'PNG', 20, position, imgWidth, imgHeight);
                    heightLeft -= pdfHeight;

                    while (heightLeft >= 0) {
                        position = heightLeft - imgHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', 20, position, imgWidth, imgHeight);
                        heightLeft -= pdfHeight;
                    }
                    pdf.save(`Meeting-Notes-{{ $meet->title }}.pdf`);
                }).catch(error => {
                    console.error("PDF Generation Error:", error);
                    alert("Could not generate PDF.");
                }).finally(() => {
                    btn.textContent = 'Export as PDF';
                    btn.disabled = false;
                });
            });
        });
    </script>
@endpush
