@extends('docs.layout')

@section('content')
<div class="page-title">📚 Code Documentation</div>
<div class="page-subtitle">Complete code-level documentation for the Laravel CRM application — auto-generated via reflection.</div>

{{-- Stat Cards --}}
<div class="card-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['totalFiles'] }}</div>
        <div class="stat-label">Total Files</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['totalMethods'] }}</div>
        <div class="stat-label">Total Methods</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ count($stats['categories']) }}</div>
        <div class="stat-label">Categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ count($routeSummary) }}</div>
        <div class="stat-label">Routes</div>
    </div>
</div>

{{-- Category Breakdown --}}
<div class="card">
    <h2 class="section-header">📁 Categories</h2>
    <div class="file-grid">
        @foreach($stats['categories'] as $cat)
        <a href="{{ route('docs.code.show', $cat['slug']) }}" class="file-card">
            <div class="file-icon">
                @switch($cat['slug'])
                    @case('Http-Controllers') 🎛️ @break
                    @case('Http-Controllers-API') 🔌 @break
                    @case('Http-Controllers-Auth') 🔐 @break
                    @case('Http-Middleware') 🛡️ @break
                    @case('Http-Requests') ✅ @break
                    @case('Models') 🗄️ @break
                    @case('Services') ⚙️ @break
                    @case('Repositories') 📦 @break
                    @case('Contracts') 📋 @break
                    @case('Imports') 📥 @break
                    @case('Exports') 📤 @break
                    @case('Helpers') 🔧 @break
                    @case('Enums') 🏷️ @break
                    @case('Jobs') ⏳ @break
                    @case('Mail') ✉️ @break
                    @case('Policies') 🔒 @break
                    @case('Traits') 🧬 @break
                    @case('Providers') 🔗 @break
                    @case('Console') 💻 @break
                    @case('Exceptions') ⚠️ @break
                    @default 📄
                @endswitch
            </div>
            <div class="file-info">
                <div class="file-name">{{ $cat['label'] }}</div>
                <div class="file-meta">{{ $cat['fileCount'] }} files · {{ $cat['methodCount'] }} methods</div>
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- Route Summary --}}
<div class="card">
    <h2 class="section-header">🛣️ Route Summary</h2>
    <input type="text" class="table-filter" id="routeFilter" placeholder="Filter routes by name, URI, or action...">
    <div style="overflow-x:auto; max-height: 500px; overflow-y: auto;">
        <table class="doc-table" id="routeTable">
            <thead>
                <tr>
                    <th>Methods</th>
                    <th>URI</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routeSummary as $route)
                <tr>
                    <td>
                        @foreach(explode('|', $route['methods']) as $method)
                            @php
                                $badgeClass = match($method) {
                                    'GET'          => 'badge-public',
                                    'POST'         => 'badge-protected',
                                    'PUT', 'PATCH' => 'badge-static',
                                    'DELETE'       => 'badge-private',
                                    default        => 'badge-protected',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}"
                                  style="margin-right:2px;">{{ $method }}</span>
                        @endforeach
                    </td>
                    <td><code>{{ $route['uri'] }}</code></td>
                    <td style="color:var(--text-muted)">{{ $route['name'] }}</td>
                    <td style="color:var(--accent)">{{ $route['action'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('routeFilter').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#routeTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
