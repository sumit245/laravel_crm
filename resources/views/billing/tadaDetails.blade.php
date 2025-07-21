@extends('layouts.main')
@section('content')
<div class="container border rounded p-4 shadow-sm bg-white" id="printable-content">
    <!-- Print Button -->
    <div class="d-flex justify-content-end mb-3 no-print">
        <button type="button" class="btn btn-outline-primary" onclick="printContent()">
            <i class="fas fa-print me-2"></i>Print Statement
        </button>
    </div>

    <!-- {{-- Header --}}
    <div class="row mb-4 align-items-center">
        <div class="col-6">
            <img src="{{ asset('images/logo.png') }}" alt="Company Logo" height="80">
        </div>
    </div>
    {{-- Title --}}
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">SUGS LLOYD LIMITED</h4>
        <h5 class="text-decoration-underline">TOUR / TRAVELLING EXPENSE STATEMENT</h5>
    </div> -->

    {{-- Header --}}
    <div class="row mb-4 align-items-center">
        <!-- <div class="col-2 text-end">
            <img src="{{ asset('images/logo.png') }}" alt="Company Logo" height="80">
        </div> -->
        <div class="col-12 text-center">
            <h4 class="text-uppercase mb-1" style="font-weight: 900; font-size: 1.8rem; letter-spacing: 1px; color: #2c3e50; font-family: 'Poppins', 'Segoe UI', sans-serif;">
                SUGS LLOYD LIMITED
            </h4>
            <h4 class="fw-bold text-uppercase mb-1">NOIDA, UTTAR PRADESH</h4>
            <h5 class="text-decoration-underline fw-semibold">Tour / Travelling Expense Statement</h5>
        </div>
        <div class="col-2"></div> {{-- Empty column for spacing --}}
    </div>

    {{-- Employee Info Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label" aria-readonly="true"><strong>Name:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="{{ $tadas->user->firstName ?? "N/A" }} {{ $tadas->user->lastName ?? "N/A" }}" name="name" readonly>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Vertical/Deptt.:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="ACCOUNTS" name="deptt" readonly>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Category:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="category" value="{{ $tadas->user->usercategory->category_code ?? 'Define it first' }}" readonly>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Designation:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="{{ $tadas->user->designation ?? "Employee" }}" name="designation" readonly>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label"><strong>Grade:</strong></label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="ACCOUNTS" name="grade" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex">
                    <label class="form-label"><strong>Date:</strong></label>
                    <div class="d-flex justify-content-end gap-1 mt-1">
                        @php
                            // Format the date as dd-mm-yy
                            $formattedDate = '';
                            if ($tadas->created_at) {
                                $date = new DateTime($tadas->created_at);
                                $formattedDate = $date->format('d-m-y');
                            }
                        @endphp
                        @foreach(str_split($formattedDate) as $digit)
                            <input type="text" class="form-control text-center p-0" value="{{ $digit }}" readonly style="width: 30px; height: 40px; display: inline-block; margin-right: 2px;">
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Journey Info --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label"><strong>Journey To:</strong></label>
                <div class="col-md-9">
                    <input type="text" class="form-control" value="{{ $tadas->visiting_to ?? "N/A" }}" name="journey_to" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label"><strong>Visit Purpose:</strong></label>
                <div class="col-md-9">
                    <input type="text" class="form-control" value="{{ $tadas->purpose_of_visit ??"N/A" }}" name="purpose" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">
                    <label class="form-label me-2 mb-0"><strong>Departure On:</strong></label>
                    <input class="form-control w-auto" value="{{ $tadas->date_of_departure ?? 'na' }}" name="departure_date" readonly>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <label class="form-label me-2 mb-0"><strong>Returned On:</strong></label>
                    <input class="form-control w-auto" value="{{ $tadas->date_of_return }}" name="return_date" readonly>
                </div>
            </div>
        </div>
    </div>

    {{-- Place Visited --}}
    <div class="card mb-4">
        <div class="card-body">
            {{-- Travelling Fare Table --}}
            <div class="table-responsive">
                <h6 class="fw-bold mb-2">1. Travelling Fare:</h6>
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th rowspan="2">From</th>
                            <th rowspan="2">To</th>
                            <th rowspan="2">Mode of Travel</th>
                            <th rowspan="2">Date of Journey</th>
                            <th rowspan="2">Amount Rs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($travelfares as $travel )
                        <tr>
                            <td>{{ $travel->from ?? "N/A"}}</td>
                            <td>{{ $travel->to ?? "N/A" }}</td>
                            <td>{{ $travel->mode_of_transport ?? "N/A" }}</td>
                            <td>{{ $travel->date_of_journey ?? "N/A" }}</td>
                            <td>{{ $travel->amount ?? "N/A" }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Travel Expense Table --}}
    <form>
        {{-- Summary Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">2. Daily Allowance & Hotel Bills</h6>
                <table class="table table-bordered text-center align-middle mb-3">
                    <thead class="table-secondary">
                        <tr>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Hotel Bill Number</th>
                            <th>Other Charges</th>
                            <th>Hotel Charges (Rs.)</th>
                            <th>Total(incl. Taxes)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailyfares as $daily)
                        <tr>
                            <td>{{ $daily->check_in_date ?? "N/A" }}</td>
                            <td>{{ $daily->check_out_date?? "N/A" }}</td>
                            <td>{{ $daily->hotel_bill_no }}</td>
                            <td>{{ $daily->other_charges ?? 0 }}</td>
                            <td>{{ $daily->amount ?? 0 }}</td>
                            <td>{{ ($daily->other_charges ?? 0) + ($daily->amount ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <h6 class="fw-bold mb-3">3. Travel Expense</h6>
                <input type="text" class="form-control mb-3" value="{{ $travelfare }}" readonly>
                <h6 class="fw-bold mb-3">4. Hotel Expense</h6>
                <input type="text" class="form-control mb-3" value="{{ $hotelExpense }}" readonly>
            </div>
        </div>
        <hr class="my-4">

        {{-- Conveyance & Telephone Expenses --}}
        <div class="card mb-4 mt-4">
            <div class="card-header fw-bold">Miscellaneous Expenses</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(json_decode($tadas->miscellaneous, true) as $extra)
                        <tr>
                            <td>{{ $extra['description'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($extra['date_of_expense'])->format('d M Y, h:i A') }}</td>
                            <td>₹{{ number_format($extra['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row"></div>
        <h6 class="fw-bold mb-3">5. Miscellaneous Expenses</h6>
        <input type="text" class="form-control mb-3" value="₹{{ $otherExpense }}" readonly>
        <div class="col-md-4">
            <label class="form-label"><strong>Bill Amount (Rs):</strong></label>
            <input type="text" class="form-control" value="₹{{ $totalamount }}" readonly>
        </div>
    </div>

    {{-- Signature Section --}}
    <div class="row m-3 mb-4">
        <div class="col-md-6">
            <label class="form-label"><strong>Checked By:</strong></label>
            <input type="text" class="form-control" placeholder="Enter name/signature">
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong>Signature of Dept. Head:</strong></label>
            <input type="text" class="form-control" placeholder="Enter name/signature">
        </div>
    </div>

    {{-- Notes --}}
    <div class="card">
        <div class="card-header fw-bold">Note</div>
        <div class="card-body">
            <ol class="ps-4">
                <li>The Bill must be submitted within 5 days after completion of the tour.</li>
                <li>All supporting vouchers must be attached with this bill.</li>
                <li>Any mistake in the Hotel Bill, if found, will be individual's responsibility.</li>
                <li>Hotel Bills shall be taken in the name of SUGS Lloyd Ltd. and GSTIN must be mentioned on the Bills.</li>
                <li>Balance, if any, must be deposited to the A/c Team along with the bill.</li>
                <li>Bill must be complete in all respect.</li>
            </ol>
        </div>
    </div>

    <div class="text-end mt-4 no-print">
        <button type="submit" class="btn btn-primary px-4">Submit Statement</button>
    </div>
    </form>
</div>

<script>
    function printContent() {
        // Get the content to print
        const printContent = document.getElementById('printable-content').innerHTML;
        
        // Store original content
        const originalContent = document.body.innerHTML;
        
        // Store original title
        const originalTitle = document.title;
        
        // Set new title for print
        document.title = 'TADA Statement - {{ $tadas->user->firstName ?? "Employee" }} {{ $tadas->user->lastName ?? "" }}';
        
        // Create print styles
        const printStyles = `
            <style>
  body {
    margin: 0;
    padding: 20px;
    font-family: Arial, sans-serif;
    font-size: 12px;
    line-height: 1.4;
  }

  .container {
    max-width: 100%;
    margin: 0;
    padding: 0;
    box-shadow: none;
    border: none;
  }

  .no-print {
    display: none !important;
  }

  .table {
    font-size: 11px;
    width: 100%;
    border-collapse: collapse;
  }

  .table th,
  .table td {
    padding: 4px;
    border: 1px solid #aaa;
    text-align: center;
  }

  .table-secondary th {
    background-color: #f8f9fa;
  }

  .form-control {
    border: none;
    box-shadow: none;
    background: transparent;
    padding: 2px;
    font-size: 11px;
    width: 100%;
  }

  .card {
    border: 1px solid #ccc;
    box-shadow: none;
    margin-bottom: 12px;
    page-break-inside: avoid;
  }

  .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #ccc;
    padding: 6px 10px;
    font-weight: bold;
  }

  .card-body {
    padding: 10px;
  }

  h4, h5, h6 {
    margin-bottom: 6px;
    color: #000;
  }

  .row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -5px;
  }

  .col-md-3 { width: 25%; }
  .col-md-4 { width: 33.333%; }
  .col-md-6 { width: 50%; }
  .col-md-8 { width: 66.666%; }
  .col-md-9 { width: 75%; }
  .col-md-12 { width: 100%; }

  .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-md-9, .col-md-12 {
    padding: 0 5px;
    margin-bottom: 6px;
  }

  .text-center { text-align: center; }
  .text-end { text-align: right; }
  .fw-bold { font-weight: bold; }
  .text-uppercase { text-transform: uppercase; }
  .text-decoration-underline { text-decoration: underline; }

  ol {
    padding-left: 18px;
  }

  li {
    margin-bottom: 3px;
  }

  .mb-1 { margin-bottom: 4px; }
  .mb-2 { margin-bottom: 8px; }
  .mb-3 { margin-bottom: 12px; }
  .mb-4 { margin-bottom: 16px; }

  .d-flex { display: flex; }
  .align-items-center { align-items: center; }
  .justify-content-end { justify-content: flex-end; }

  input[style*="width: 30px"] {
    width: 20px !important;
    height: 25px;
    border: 1px solid #000;
    text-align: center;
    margin-right: 2px;
  }
</style>

        `;
        
        // Replace body content with print content
        document.body.innerHTML = printStyles + '<div class="container">' + printContent + '</div>';
        
        // Print
        window.print();
        
        // Restore original content and title
        document.body.innerHTML = originalContent;
        document.title = originalTitle;
        
        // Re-attach event listeners (since we replaced the DOM)
        location.reload();
    }
    
    // Optional: Add keyboard shortcut for printing (Ctrl+P)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printContent();
        }
    });
</script>
@endsection