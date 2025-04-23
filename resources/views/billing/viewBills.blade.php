@extends('layouts.main')

@section('content')
<div class="container mt-4">
    <!-- Summary Alert -->
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span><i class="bi bi-graph-up-arrow me-2"></i> You have uploaded <strong>4 bills</strong> this month.</span>
        <span>Total Estimated: <strong>₹12,800</strong></span>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-6">
            <input type="text" class="form-control" placeholder="Search by title or file name...">
        </div>
        <div class="col-md-4">
            <select class="form-select">
                <option selected>Filter by Category</option>
                <option value="Train Ticket">Train</option>
                <option value="Hotel Receipt">Hotel</option>
                <option value="Food">Food</option>
            </select>
        </div>
    </div>

    <!-- Bill Cards -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-receipt-cutoff me-2"></i> Uploaded Bills</h5>
        </div>
        <div class="card-body py-4 px-4">
            <div class="row g-3">
                @php
                    use Illuminate\Support\Str;

                    $bills = [
                        ['title' => 'Train Ticket', 'file' => 'bills/sample.pdf', 'category' => 'Train', 'date' => '2025-04-10'],
                        ['title' => 'Hotel Receipt', 'file' => 'bills/sample.pdf', 'category' => 'Hotel', 'date' => '2025-04-11'],
                        ['title' => 'Food', 'file' => 'bills/sample.pdf', 'category' => 'Food', 'date' => '2025-04-15'],
                        ['title' => 'Food', 'file' => 'bills/sample.pdf', 'category' => 'Food', 'date' => '2025-04-16']
                    ];
                @endphp

                @forelse ($bills as $bill)
                @php
                    $filePath = Str::startsWith($bill['file'], 'bills/') 
                        ? asset($bill['file']) 
                        : asset('storage/bills/' . $bill['file']);
                @endphp
                <div class="col-md-4">
                    <div class="bill-card border rounded shadow-sm p-3 h-100 d-flex flex-column justify-content-between">
                        <div class="mb-2">
                            <div class="d-flex align-items-start">
                                <div class="file-icon me-3">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-1 fw-semibold">{{ $bill['title'] }}</h6>
                                        <span class="badge {{ 
                                            $bill['category'] === 'Train' ? 'bg-primary' : 
                                            ($bill['category'] === 'Hotel' ? 'bg-warning text-dark' : 'bg-success') 
                                        }}">{{ $bill['category'] }}</span>
                                    </div>
                                    <small class="text-muted">{{ $bill['file'] }}</small><br>
                                    <small class="text-muted">Uploaded on: {{ \Carbon\Carbon::parse($bill['date'])->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-auto gap-2">
                            <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center my-5">
                    <img src="{{ asset('images/empty-box.svg') }}" alt="No bills" height="150">
                    <h5 class="mt-3">No bills uploaded yet</h5>
                    <p class="text-muted">Start by uploading your travel or food receipts.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bill-card {
        transition: all 0.2s ease-in-out;
        background-color: #fff;
    }

    .bill-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .file-icon i {
        font-size: 2rem;
        color: #dc3545;
    }

    .bill-card h6 {
        font-size: 1rem;
    }

    .bill-card small {
        font-size: 0.75rem;
    }

    .alert-info {
        background-color: #fff;
        border: 1px solid #b6e0fe;
        color: black;
    }
</style>
@endpush
