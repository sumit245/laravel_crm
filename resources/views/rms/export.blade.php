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

        <x-datatable id="rmsLogs"
            title="RMS Push Logs"
            :columns="[
                ['title' => 'Pole Number', 'width' => '12%'],
                ['title' => 'Status', 'width' => '8%'],
                ['title' => 'Message', 'width' => '22%'],
                ['title' => 'District', 'width' => '10%'],
                ['title' => 'Block', 'width' => '10%'],
                ['title' => 'Panchayat', 'width' => '12%'],
                ['title' => 'Pushed By', 'width' => '12%'],
                ['title' => 'Pushed At', 'width' => '14%'],
            ]"
            :exportEnabled="true"
            :importEnabled="false"
            :bulkDeleteEnabled="false"
            :actionsColumnEnabled="false"
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
                    <td>{{ $log->response_data['detail'] ?? $log->message ?? 'N/A' }}</td>
                    <td>{{ $log->district ?? 'N/A' }}</td>
                    <td>{{ $log->block ?? 'N/A' }}</td>
                    <td>{{ $log->panchayat ?? 'N/A' }}</td>
                    <td>{{ $log->pushedBy->name ?? 'N/A' }}</td>
                    <td>{{ $log->pushed_at ? $log->pushed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td></td>
                    <td></td>
                    <td colspan="6" class="text-center">
                        @if(isset($error))
                            <div class="alert alert-danger mb-0">{{ $error }}</div>
                        @else
                            No RMS push logs found.
                        @endif
                    </td>
                </tr>
            @endforelse
        </x-datatable>
    </div>
@endsection
