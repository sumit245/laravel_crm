{{-- resources/views/review-meetings/show.blade.php --}}
{{-- ... existing code ... --}}
@extends('layouts.main')
@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Whiteboard</h4>
        <div id="whiteboard-container" style="width: 100%; height: 600px;"></div>
    </div>
</div>
@endsection

{{-- ... existing code ... --}}

@push('scripts')
    <script src="https://unpkg.com/tldraw@3.15.4/files/dist-cjs/index.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const whiteboardContainer = document.getElementById('whiteboard-container');
            const meetingId = {{ $meet->id }};

            if (whiteboardContainer) {
                // Create a new Tldraw instance and tell it to render in our container
                const editor = new Tldraw.Tldraw({
                    host: whiteboardContainer,
                    persistenceKey: `whiteboard_${meetingId}`,
                    onMount: async (editor) => {
                        try {
                            const response = await fetch(`/review-meetings/${meetingId}/whiteboard`);
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            const data = await response.json();
                            if (data.data) {
                                editor.store.loadSnapshot(JSON.parse(data.data));
                            }
                        } catch (error) {
                            console.error('Error loading whiteboard data:', error);
                        }
                    },
                });

                // Autosave logic
                let isSaving = false;
                editor.store.listen(() => {
                    if (isSaving) return;
                    isSaving = true;
                    setTimeout(() => {
                        const snapshot = editor.store.getSnapshot();
                        const data = JSON.stringify(snapshot);

                        fetch(`/review-meetings/${meetingId}/whiteboard`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    data
                                })
                            }).catch(error => console.error('Error saving whiteboard data:', error))
                            .finally(() => {
                                isSaving = false;
                            });
                    }, 2000); // Save 2 seconds after the last change
                });
            }
        });
    </script>
@endpush
