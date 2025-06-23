@extends("layouts.main")

@section("content")
  <div class="container relative mx-auto max-w-5xl rounded-lg bg-white p-6 shadow-lg">
    {{-- Logo + Heading --}}
    <div class="mb-6 flex flex-col items-center justify-center text-center">
      <img src="https://sugs-assets.s3.ap-south-1.amazonaws.com/logo.png" alt="Sugs LLoyd Limited" class="img-fluid mb-2">
      <h1 class="text-xl font-semibold text-emerald-700">Application Details</h1>
      <p class="mt-2 max-w-2xl text-gray-600">
        Below are the details submitted by the applicant.
      </p>
    </div>

    @php
      $data = session("submittedData");
      $documents = [
          "Resume" => "bills/sample.pdf",
          "Profile Picture" => "bills/sample.pdf",
          "Contract" => "bills/sample.pdf",
      ];
    @endphp

    {{-- Personal Information --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 shadow-md">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Personal Information</h2>
      </div>
      <div class="grid grid-cols-1 gap-4 text-gray-700 md:grid-cols-2">
        <p><strong>Full Name:</strong> {{ $data["personalInfo"]["name"] ?? "N/A" }}</p>
        <p><strong>Email:</strong> {{ $data["personalInfo"]["email"] ?? "N/A" }}</p>
        <p><strong>Phone:</strong> {{ $data["personalInfo"]["phone"] ?? "N/A" }}</p>
        <p><strong>DOB:</strong> {{ $data["personalInfo"]["dob"] ?? "N/A" }}</p>
        <p><strong>Gender:</strong> {{ $data["personalInfo"]["gender"] ?? "N/A" }}</p>
        <p><strong>Marital Status:</strong> {{ $data["personalInfo"]["maritalStatus"] ?? "N/A" }}</p>
        <p><strong>Nationality:</strong> {{ $data["personalInfo"]["nationality"] ?? "N/A" }}</p>
        <p><strong>Language:</strong> {{ $data["personalInfo"]["language"] ?? "N/A" }}</p>
      </div>
    </div>

    {{-- Contact Information --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 shadow-md">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Contact Information</h2>
      </div>
      <div class="grid grid-cols-1 gap-4 text-gray-700 md:grid-cols-2">
        <p><strong>Permanent Address:</strong>
          {{ isset($data["contactInfo"]["permanentAddress"]) ? implode(", ", array_filter($data["contactInfo"]["permanentAddress"])) : "N/A" }}
        </p>
        <p><strong>Current Address:</strong>
          {{ isset($data["contactInfo"]["currentAddress"]) ? implode(", ", array_filter($data["contactInfo"]["currentAddress"])) : "N/A" }}
        </p>
        <p><strong>Emergency Contact:</strong> {{ $data["contactInfo"]["emergencyContact"]["name"] ?? "N/A" }}
          ({{ $data["contactInfo"]["emergencyContact"]["phone"] ?? "N/A" }})</p>
      </div>
    </div>

    {{-- Education --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 shadow-md">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Educational Background</h2>
      </div>
      @forelse ($data['education'] ?? [] as $edu)
        <div class="mb-4 text-gray-700">
          <p><strong>Qualification:</strong> {{ $edu["qualification"] ?? "N/A" }}</p>
          <p><strong>Institution:</strong> {{ $edu["institution"] ?? "N/A" }} ({{ $edu["year"] ?? "N/A" }})</p>
          <p><strong>Specialization:</strong> {{ $edu["specialization"] ?? "N/A" }}</p>
          <p><strong>Certifications:</strong> {{ $edu["certifications"] ?? "N/A" }}</p>
          <hr class="my-2">
        </div>
      @empty
        <p class="text-gray-500">No education records provided.</p>
      @endforelse
    </div>

    {{-- Documents --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 shadow-md">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Uploaded Documents</h2>
      </div>
      @if (!empty($documents))
        <ul class="list-inside list-disc space-y-2 text-gray-700">
          @foreach ($documents as $docName => $docPath)
            <li>
              <span class="font-semibold">{{ ucfirst(str_replace("_", " ", $docName)) }}:</span>
              @if ($docPath)
                <div class="mt-2">
                  @php $fileExtension = pathinfo($docPath, PATHINFO_EXTENSION); @endphp

                  @if (in_array(strtolower($fileExtension), ["jpg", "jpeg", "png", "gif", "bmp", "webp"]))
                    <img src="{{ asset($docPath) }}" alt="{{ $docName }}"
                      class="h-32 w-32 rounded object-cover shadow-md">
                  @elseif (strtolower($fileExtension) === "pdf")
                    <a href="{{ asset($docPath) }}" target="_blank" class="ml-2 text-emerald-600 hover:underline">
                      <i class="fas fa-file-pdf"></i> View PDF
                    </a>
                  @elseif (in_array(strtolower($fileExtension), ["doc", "docx", "xls", "xlsx", "ppt", "pptx"]))
                    <a href="{{ asset($docPath) }}" target="_blank" class="ml-2 text-emerald-600 hover:underline">
                      <i class="fas fa-file-word"></i> View Document
                    </a>
                  @else
                    <a href="{{ asset($docPath) }}" target="_blank" class="ml-2 text-emerald-600 hover:underline">
                      <i class="fas fa-file"></i> View Document
                    </a>
                  @endif
                </div>
              @else
                <span class="text-gray-500">Not uploaded</span>
              @endif
            </li>
          @endforeach
        </ul>
      @else
        <p class="text-gray-500">No documents uploaded.</p>
      @endif
    </div>

    {{-- Additional Information --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 shadow-md">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Additional Information</h2>
      </div>
      <div class="grid grid-cols-1 gap-4 text-gray-700 md:grid-cols-2">
        <p><strong>Disabilities:</strong> {{ $data["additionalInfo"]["disabilities"] ?? "N/A" }}</p>
        <p><strong>Currently Employed:</strong> {{ $data["additionalInfo"]["currentlyEmployed"] ?? "N/A" }}</p>
        <p><strong>Reason for Leaving:</strong> {{ $data["additionalInfo"]["reasonForLeaving"] ?? "N/A" }}</p>
        <p><strong>Other Info:</strong> {{ $data["additionalInfo"]["otherInfo"] ?? "N/A" }}</p>
      </div>
    </div>

    {{-- Passport Size Photo --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 shadow-md">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Passport Size Photo</h2>
      </div>
      @if (!empty($data["photo"]))
        <img src="{{ asset($data["photo"]) }}" alt="Passport Photo"
          class="h-40 w-32 rounded border object-cover shadow-md">
      @else
        <p class="text-gray-500">No photo uploaded.</p>
      @endif
    </div>

    {{-- Declaration --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 shadow-md">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Declaration</h2>
      </div>
      <div class="grid grid-cols-1 gap-4 text-gray-700 md:grid-cols-2">
        <p>I declare the above information is true.</p>
        <p><strong>Signature:</strong> {{ $data["declaration"]["signature"] ?? "N/A" }}</p>
        <p><strong>Date:</strong> {{ $data["declaration"]["date"] ?? "N/A" }}</p>
      </div>
    </div>

    {{-- Approve and Reject Buttons --}}
    <div class="sticky bottom-0 left-0 mt-6 border-t border-gray-200 bg-white py-4 shadow-inner">
      <div class="flex justify-end gap-4 px-6">
        {{-- Approve Form --}}
        @csrf
        <button type="submit"
          class="badge bg-danger rounded-md border border-green-500 px-6 py-2 font-semibold text-green-800 shadow-md transition duration-200 hover:border-green-600 hover:bg-green-200">
          Approve
        </button>
        </form>

        {{-- Reject Form --}}
        @csrf
        <button type="submit" class="badge bg-success border px-6 py-2">
          Reject
        </button>
        </form>
      </div>
    </div>
  </div>
@endsection
