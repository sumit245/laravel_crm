@extends("layouts.main")

@section("content")
<div class="container mx-auto p-6 max-w-5xl bg-white shadow-lg rounded-lg relative">

{{-- Logo + Heading --}}
    <div class="flex flex-col items-center justify-center text-center mb-6">
        <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="h-20 w-auto mb-2">
        <h1 class="text-xl font-semibold text-emerald-700">Review & Confirm Your Details</h1>
        <p class="mt-2 text-gray-600 max-w-2xl">
            Please review your information. Use the <span class="font-semibold">Edit</span> buttons to make changes before final submission.
        </p>
    </div>

    <!-- @php
        $data = $data['dataInfo'] ?? [];
        $contact = $data['contactInfo'] ?? [];
        $employment = $data['employment'] ?? [];
        $additional = $data['additionalInfo'] ?? [];
        $declaration = $data['declaration'] ?? [];
    @endphp -->

    {{-- data Information --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">data Information</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p><strong>Full Name:</strong> {{ $data['name'] ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $data['email'] ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $data['phone'] ?? 'N/A' }}</p>
            <p><strong>DOB:</strong> {{ $data['dob'] ?? 'N/A' }}</p>
            <p><strong>Gender:</strong> {{ $data['gender'] ?? 'N/A' }}</p>
            <p><strong>Marital Status:</strong> {{ $data['marital_status'] ?? 'N/A' }}</p>
            <p><strong>Nationality:</strong> {{ $data['nationality'] ?? 'N/A' }}</p>
            <p><strong>Language:</strong> {{ $data['language'] ?? 'N/A' }}</p>
        </div>
        <div class="edit-btn flex justify-start w-full">
            <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
                Edit
            </a>
        </div>
    </div>

    {{-- Contact Information --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Contact Information</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <p><strong>Permanent Address:</strong> {{ isset($contact['permanentAddress']) ? implode(', ', array_filter($contact['permanentAddress'])) : 'N/A' }}</p>
            <p><strong>Current Address:</strong> {{ isset($contact['currentAddress']) ? implode(', ', array_filter($contact['currentAddress'])) : 'N/A' }}</p>
            <p><strong>Emergency Contact:</strong> {{ $contact['emergencyContact']['name'] ?? 'N/A' }} ({{ $contact['emergencyContact']['phone'] ?? 'N/A' }})</p>
        </div>
        <div class="edit-btn flex justify-start w-full">
            <a href="#" class="text-sm text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded-md transition duration-200 ease-in-out border border-emerald-600 hover:text-white hover:border-emerald-600">
                Edit
            </a>
        </div>
    </div>

    {{-- Education --}}
    <div class="bg-gray-50 p-5 rounded-lg shadow-md mb-6 border border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-800">Educational Background</h2>
        </div>
        @forelse ($data['education'] ?? [] as $edu)
            <div class="mb-4 text-gray-700">
                <p><strong>Qualification:</strong> {{ $edu['qualification'] ?? 'N/A' }}</p>
                <p><strong>Institution:</strong> {{ $edu['institution'] ?? 'N/A' }} ({{ $edu['year'] ?? 'N/A' }})</p>
                <p><strong>Specialization:</strong> {{ $edu['specialization'] ?? 'N/A' }}</p>
                <p><strong>Certifications:</strong> {{ $edu['certifications'] ?? 'N/A' }}</p>
                <hr class="my-2">
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
                        <span class="text-gray-500">Not uploaded</span>
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