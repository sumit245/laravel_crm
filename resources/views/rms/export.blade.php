@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <div class="row mb-3">
            <div class="col-12">
                <h3 class="fw-bold">RMS Push Report</h3>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Success</h5>
                        <h2>{{ $successLogs->count() }}</h2>
                        <p class="mb-0">Poles successfully pushed to RMS</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Errors</h5>
                        <h2>{{ $errorLogs->count() }}</h2>
                        <p class="mb-0">Poles with errors</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <h2>{{ $logs->count() }}</h2>
                        <p class="mb-0">Total push attempts</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs for Success and Error Reports --}}
        <ul class="nav nav-tabs mb-3" id="rmsReportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                    All ({{ $logs->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="success-tab" data-bs-toggle="tab" data-bs-target="#success" type="button" role="tab" aria-controls="success" aria-selected="false">
                    Success ({{ $successLogs->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="error-tab" data-bs-toggle="tab" data-bs-target="#error" type="button" role="tab" aria-controls="error" aria-selected="false">
                    Errors ({{ $errorLogs->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="rmsReportTabContent">
            {{-- All Logs Tab --}}
            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <x-datatable id="rmsAllLogs" 
                    title="All RMS Push Logs" 
                    :columns="[
                        ['title' => 'Pole Number', 'width' => '12%'],
                        ['title' => 'Status', 'width' => '8%'],
                        ['title' => 'Message', 'width' => '20%'],
                        ['title' => 'District', 'width' => '10%'],
                        ['title' => 'Block', 'width' => '10%'],
                        ['title' => 'Panchayat', 'width' => '12%'],
                        ['title' => 'Pushed By', 'width' => '12%'],
                        ['title' => 'Pushed At', 'width' => '16%'],
                    ]" 
                    :exportEnabled="true" 
                    :importEnabled="false" 
                    :bulkDeleteEnabled="false"
                    pageLength="50" 
                    searchPlaceholder="Search logs...">
                    @forelse($logs as $log)
                        <tr data-status="{{ $log->status }}">
                            <td>{{ $log->pole->complete_pole_number ?? 'N/A' }}</td>
                            <td>
                                @if($log->status === 'success')
                                    <span class="badge badge-success">Success</span>
                                @else
                                    <span class="badge badge-danger">Error</span>
                                @endif
                            </td>
                            <td>{{ $log->message ?? 'N/A' }}</td>
                            <td>{{ $log->district ?? 'N/A' }}</td>
                            <td>{{ $log->block ?? 'N/A' }}</td>
                            <td>{{ $log->panchayat ?? 'N/A' }}</td>
                            <td>{{ $log->pushedBy->name ?? 'N/A' }}</td>
                            <td>{{ $log->pushed_at ? $log->pushed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                @if(isset($error))
                                    <div class="alert alert-danger">{{ $error }}</div>
                                @else
                                    No RMS push logs found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </x-datatable>
            </div>

            {{-- Success Logs Tab --}}
            <div class="tab-pane fade" id="success" role="tabpanel" aria-labelledby="success-tab">
                <x-datatable id="rmsSuccessLogs" 
                    title="Successful RMS Pushes" 
                    :columns="[
                        ['title' => 'Pole Number', 'width' => '12%'],
                        ['title' => 'Message', 'width' => '20%'],
                        ['title' => 'District', 'width' => '12%'],
                        ['title' => 'Block', 'width' => '12%'],
                        ['title' => 'Panchayat', 'width' => '14%'],
                        ['title' => 'Pushed By', 'width' => '14%'],
                        ['title' => 'Pushed At', 'width' => '16%'],
                    ]" 
                    :exportEnabled="true" 
                    :importEnabled="false" 
                    :bulkDeleteEnabled="false"
                    pageLength="50" 
                    searchPlaceholder="Search successful logs...">
                    @forelse($successLogs as $log)
                        <tr>
                            <td>{{ $log->pole->complete_pole_number ?? 'N/A' }}</td>
                            <td>{{ $log->message ?? 'N/A' }}</td>
                            <td>{{ $log->district ?? 'N/A' }}</td>
                            <td>{{ $log->block ?? 'N/A' }}</td>
                            <td>{{ $log->panchayat ?? 'N/A' }}</td>
                            <td>{{ $log->pushedBy->name ?? 'N/A' }}</td>
                            <td>{{ $log->pushed_at ? $log->pushed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No successful pushes found.</td>
                        </tr>
                    @endforelse
                </x-datatable>
            </div>

            {{-- Error Logs Tab --}}
            <div class="tab-pane fade" id="error" role="tabpanel" aria-labelledby="error-tab">
                <x-datatable id="rmsErrorLogs" 
                    title="RMS Push Errors" 
                    :columns="[
                        ['title' => 'Pole Number', 'width' => '12%'],
                        ['title' => 'Error Message', 'width' => '25%'],
                        ['title' => 'District', 'width' => '12%'],
                        ['title' => 'Block', 'width' => '12%'],
                        ['title' => 'Panchayat', 'width' => '14%'],
                        ['title' => 'Pushed By', 'width' => '12%'],
                        ['title' => 'Pushed At', 'width' => '13%'],
                    ]" 
                    :exportEnabled="true" 
                    :importEnabled="false" 
                    :bulkDeleteEnabled="false"
                    pageLength="50" 
                    searchPlaceholder="Search error logs...">
                    @forelse($errorLogs as $log)
                        <tr>
                            <td>{{ $log->pole->complete_pole_number ?? 'N/A' }}</td>
                            <td><span class="text-danger">{{ $log->message ?? 'N/A' }}</span></td>
                            <td>{{ $log->district ?? 'N/A' }}</td>
                            <td>{{ $log->block ?? 'N/A' }}</td>
                            <td>{{ $log->panchayat ?? 'N/A' }}</td>
                            <td>{{ $log->pushedBy->name ?? 'N/A' }}</td>
                            <td>{{ $log->pushed_at ? $log->pushed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No errors found.</td>
                        </tr>
                    @endforelse
                </x-datatable>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize datatables when tabs are shown
        $('#rmsReportTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).data('bs-target');
            let tableId = '';
            
            if (target === '#all') {
                tableId = '#rmsAllLogs';
            } else if (target === '#success') {
                tableId = '#rmsSuccessLogs';
            } else if (target === '#error') {
                tableId = '#rmsErrorLogs';
            }
            
            if (tableId) {
                // Wait a bit for the tab content to be visible
                setTimeout(function() {
                    const table = $(tableId).DataTable();
                    if (table) {
                        table.columns.adjust().draw();
                    }
                }, 100);
            }
        });
    });
</script>
@endpush
