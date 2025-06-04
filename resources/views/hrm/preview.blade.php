<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review & Confirm Your Details - Sugs Lloyd Ltd</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/select2/select2.min.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f0f0;
            color: #202124;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .card-header {
            background-color: #673ab7;
            color: white;
            padding: 16px 24px;
            border-bottom: none;
            font-weight: 500;
        }
        .card-body {
            padding: 24px;
            background-color: white;
        }
        .card-footer {
            background-color: white;
            border-top: 1px solid #e0e0e0;
            padding: 16px 24px;
        }
        .btn-primary {
            background-color: #673ab7;
            border-color: #673ab7;
        }
        .btn-primary:hover {
            background-color: #5e35b1;
            border-color: #5e35b1;
        }
        .btn-success {
            background-color: #1a73e8;
            border-color: #1a73e8;
        }
        .btn-success:hover {
            background-color: #1765cc;
            border-color: #1765cc;
        }
        .edit-btn {
            display: flex;
            justify-content: start;
            margin-top: 15px;
        }
        .edit-btn a {
            text-decoration: none;
            color: #673ab7;
            padding: 5px 15px;
            border-radius: 5px;
            border: 1px solid #673ab7;
            transition: all 0.3s ease;
        }
        .edit-btn a:hover {
            background-color: #673ab7;
            color: white;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-container img {
            max-height: 80px;
        }
        .section-title {
            font-weight: 600;
            color: #202124;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .data-row {
            margin-bottom: 8px;
        }
        .data-label {
            font-weight: 500;
            color: #5f6368;
        }
        .data-value {
            color: #202124;
        }
        .submit-container {
            position: sticky;
            bottom: 0;
            background-color: white;
            padding: 15px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            border-top: 1px solid #e0e0e0;
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Sugs Lloyd Ltd Logo" class="img-fluid">
            <h1 class="mt-3">Review & Confirm Your Details</h1>
            <p class="text-muted">Please review your information carefully before final submission.</p>
        </div>
        
        <form action="{{ route('hrm.submit') }}" method="POST">
            @csrf
            
            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Personal Information</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="data-row">
                                <span class="data-label">Full Name:</span>
                                <span class="data-value">{{ $data['name'] ?? 'N/A' }}</span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Email:</span>
                                <span class="data-value">{{ $data['email'] ?? 'N/A' }}</span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Phone:</span>
                                <span class="data-value">{{ $data['phone'] ?? 'N/A' }}</span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Date of Birth:</span>
                                <span class="data-value">{{ $data['dob'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-row">
                                <span class="data-label">Gender:</span>
                                <span class="data-value">{{ $data['gender'] ?? 'N/A' }}</span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Marital Status:</span>
                                <span class="data-value">{{ $data['marital_status'] ?? 'N/A' }}</span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Nationality:</span>
                                <span class="data-value">{{ $data['nationality'] ?? 'N/A' }}</span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Preferred Language:</span>
                                <span class="data-value">{{ $data['language'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="edit-btn">
                        <a href="{{ route('hrm.apply') }}#personal-info">
                            <i class="fas fa-edit me-2"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No education records provided.</p>
        @endforelse
        <div class="edit-btn flex justify-start w-full">
            <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
                Edit
            </a>
        </div>
    </div>

    {{-- Documents --}}
<div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-xl font-semibold text-gray-800">Uploaded Documents</h2>
    </div>

    @php
        $documents = $data['documents'] ?? [];
    @endphp

    @if (!empty($documents))
        <ul class="list-disc list-inside space-y-2 text-gray-700">
            @foreach ($documents as $docName => $docPath)
                <li>
                    <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $docName)) }}:</span>
                    @if ($docPath)
                        <a href="{{ asset($docPath) }}" target="_blank" class="text-emerald-600 hover:underline ml-2">
                            View Document
                        </a>
                    @else
                        <p>No educational information provided.</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-gray-500">No documents uploaded.</p>
    @endif

    <div class="edit-btn flex justify-start w-full mt-4">
        <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
            Edit
        </a>
    </div>
</div>


    {{-- Employment Details --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Employment Details</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p><strong>Position:</strong> {{ $employment['positionAppliedFor'] ?? 'N/A' }}</p>
            <p><strong>Department:</strong> {{ $employment['department'] ?? 'N/A' }}</p>
            <p><strong>Joining Date:</strong> {{ $employment['dateOfJoining'] ?? 'N/A' }}</p>
            <p><strong>Previous Employer:</strong> {{ $employment['previousEmployer'] ?? 'N/A' }}</p>
            <p><strong>Experience:</strong> {{ $employment['experience'] ?? 'N/A' }} years</p>
            <p><strong>Notice Period:</strong> {{ $employment['noticePeriod'] ?? 'N/A' }}</p>
        </div>
        <div class="edit-btn flex justify-start w-full">
            <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
                Edit
            </a>
        </div>
    </div>

    {{-- Additional Information --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Additional Information</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p><strong>Disabilities:</strong> {{ $additional['disabilities'] ?? 'N/A' }}</p>
            <p><strong>Currently Employed:</strong> {{ $additional['currentlyEmployed'] ?? 'N/A' }}</p>
            <p><strong>Reason for Leaving:</strong> {{ $additional['reasonForLeaving'] ?? 'N/A' }}</p>
            <p><strong>Other Info:</strong> {{ $additional['otherInfo'] ?? 'N/A' }}</p>
        </div>
        <div class="edit-btn flex justify-start w-full">
            <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
                Edit
            </a>
        </div>
    </div>

    {{-- Photo Upload --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-8 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Passport Size Photo</h2>
        </div>
        @if (!empty($data['photo']))
            <img src="{{ asset($data['photo']) }}" alt="Passport Photo" class="w-32 h-40 object-cover border rounded shadow-md">
        @else
            <p class="text-gray-500">No photo uploaded.</p>
        @endif

        <div class="edit-btn flex justify-end w-full">
            <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
                Edit
            </a>
        </div>
    </div>

    {{-- Declaration --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Declaration</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p>I declare the above information is true.</p>
            <p><strong>Signature:</strong> {{ $declaration['signature'] ?? 'N/A' }}</p>
            <p><strong>Date:</strong> {{ $declaration['date'] ?? 'N/A' }}</p>
        </div>
        <div class="edit-btn flex justify-start w-full">
            <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
                Edit
            </a>
        </div>
    </div>

   

    {{-- Submit Form --}}
    @csrf
    <div class="sticky bottom-0 left-0 bg-white py-4 mt-6">
        <div class="edit-btn flex justify-end">
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-black font-semibold rounded hover:bg-emerald-700 transition duration-200 shadow">
                Confirm & Submit
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .edit-btn {
        display: flex;
        justify-content: end;
    }
    .edit-btn a {
        text-decoration: none;
        color: black;
        border-radius: 5px;
        background-color: #f4f5f7;
    }
    .logo{
        display:flex;
    }
</style>
@endpush