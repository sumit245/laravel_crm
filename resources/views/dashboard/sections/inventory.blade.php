@php
    $inv = $inventory_analytics ?? [];
    $totalStock      = $inv['totalStock'] ?? 0;
    $totalDispatched = $inv['totalDispatched'] ?? 0;
    $totalConsumed   = $inv['totalConsumed'] ?? 0;
    $recentDispatches = $inv['recentDispatches'] ?? collect();
@endphp

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="p-3 rounded border text-center" style="background:#f0faf2;">
            <div class="h3 fw-bold mb-1 text-success">{{ number_format($totalStock) }}</div>
            <div class="small text-muted text-uppercase fw-semibold">In Stock</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="p-3 rounded border text-center" style="background:#fff8e1;">
            <div class="h3 fw-bold mb-1 text-warning">{{ number_format($totalDispatched) }}</div>
            <div class="small text-muted text-uppercase fw-semibold">Dispatched (With Vendor)</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="p-3 rounded border text-center" style="background:#e8f5e9;">
            <div class="h3 fw-bold mb-1" style="color:#1565c0;">{{ number_format($totalConsumed) }}</div>
            <div class="small text-muted text-uppercase fw-semibold">Consumed (Installed)</div>
        </div>
    </div>
</div>

@if($recentDispatches->isNotEmpty())
<div class="table-responsive">
    <table class="table table-sm table-hover mb-0" style="font-size:0.87rem;">
        <thead class="table-light">
            <tr>
                <th>Item</th>
                <th>Serial No.</th>
                <th>Vendor</th>
                <th>Project</th>
                <th>Dispatch Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentDispatches as $dispatch)
            <tr>
                <td>{{ $dispatch->item ?? '—' }}</td>
                <td><code>{{ $dispatch->serial_number }}</code></td>
                <td>{{ $dispatch->vendor->name ?? '—' }}</td>
                <td>{{ $dispatch->project->project_name ?? '—' }}</td>
                <td>{{ $dispatch->dispatch_date ? \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted mb-0" style="font-size:0.9rem;">No dispatches recorded yet.</p>
@endif
