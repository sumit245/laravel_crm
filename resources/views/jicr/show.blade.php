<div class="container p-5">
  <button class="btn btn-primary print-btn" onclick="printDocument()">
    <i class="bi bi-printer"></i> Print
  </button>

  <div class="form-container">

    <div class="form-header">
      Format 12<br>
      JOINT INSPECTION-CUM-COMMISSIONING REPORT (JICR)
    </div>

    <div class="intro-text">
      It is hereby certified that following Solar Street Light System installed & commissioned with
      Remote Monitoring System (RMS) on the identified existing electric pole under 'Mukhyamantri Gramin
      Solar Street Light Yojana' in Bihar State" with following details has been installed and
      commissioned at site: -
    </div>

    <div class="details-line">Name of District/ Block/ Panchayat
      <span style="text-decoration-line: underline; text-decoration-style: dotted;"> {{ $data["district"] ?? "" }} /
        {{ $data["block"] ?? "" }} /
        {{ $data["panchayat"] ?? "" }}</span>
    </div>
    <div class="details-line">Name of Executing Agency
      <span style="text-decoration-line: underline; text-decoration-style: dotted;"> Sugs Lloyd Limited
      </span>
    </div>
    <div class="details-line">Work order no. and date<span
        style="text-decoration-line: underline;text-decoration-style: dotted;font-size:0.96rem;">
        {{ $data["work_order_number"] }}
        {{ $data["project_start_date"] }}</span></div>

    <table class="table-bordered mt-4 table">
      <tbody>
        <tr>
          <td width="5%">Sl.</td>
          <td width="45%">Items</td>
          <td width="50%">Details</td>
        </tr>
        <tr>
          <td>1.</td>
          <td>Name of system: (Solar Street Light)</td>
          <td>SSL20W</td>
        </tr>
        <tr>
          <td>2.</td>
          <td>Agreement number & Date:</td>
          <td> {{ $data["agreement_number"] }} {{ $data["agreement_date"] }}</td>
        </tr>
        <tr>
          <td>3.</td>
          <td>Capacity of System:</td>
          <td>{{ $data["project_capacity"] ?? "" }}W</td>
        </tr>
        <tr>
          <td>4.</td>
          <td>Name of Executing Agency with full address:<br>
            Contact person name and mobile no –Mandatory</td>
          <td style="white-space: normal; word-wrap: break-word; max-width: 30vw;">M/s Sugs Lloyd Limited, Office No-
            8B, CSC-I, Mandawali, Fazalpur, Behind Narwana Appartments,
            Delhi-110092,</td>
          {{-- TODO: take from setting later on --}}
        </tr>
        <tr>
          <td>5.</td>
          <td>Name of Beneficiary with full address:<br>
            District:<br>
            Pin code:</td>
          <td>As Per Annexure</td>
        </tr>
        <tr>
          <td>6.</td>
          <td>Exact location on installation Latitude and longitude <br>
            exactly One photo of system showing longitude and <br>
            latitude in photo</td>
          <td>As Per Annexure</td>
        </tr>
        <tr>
          <td>7.</td>
          <td>Date of installation:</td>
          <td>As Per Annexure</td>
        </tr>
        <tr>
          <td>8.</td>
          <td>Warrantee expire on:</td>
          <td>5 Years</td>
          {{-- TODO: Take from setting later on --}}
        </tr>
        <tr>
          <td>9.</td>
          <td>Name & Address of the local service Centre:<br>
            Contact person name with mobile no - <br>
            Mandatory Email address:</td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3"><strong>DETAILS OF INSTALLATION OF SOLAR MODULES</strong></td>
        </tr>
        <tr>
          <td>10.</td>
          <td>
            a) SPV Modules Type (Poly/Mono crystalline)<br>
            b) Sr. No of module:<br>
            c) Make of module:<br>
            d) Model No:<br>
            e) Module wattage
          </td>
          <td>
            a)Poly Crystalline<br>
            b)As per Annexure<br>
            c)Alpex<br>
            d)120wp<br>
            e)120W
          </td>
        </tr>
      </tbody>
    </table>

    <table class="table-bordered mt-4 table">
      <tbody>
        <tr>
          <td width="5%"></td>
          <td width="45%">
            f) Is the SSLS installed at shadow free (Yes/No)<br>
            g) Tilt angle of module to true south<br>
            h) Module is fixed properly to withstand wind <br>
            loading of 150 km/hr. of wind velocity.
          </td>
          <td width="50%">
            f)Yes<br>
            g)Yes<br>
            h)yes
          </td>
        </tr>
        <tr>
          <td colspan="3"><strong>DETAILS OF INSTALLATION OF BATTERY</strong></td>
        </tr>
        <tr>
          <td>11.</td>
          <td>
            a) Details of Battery<br>
            b) Type of Battery<br>
            c) Sr. no of Battery<br>
            d) Make of Battery :<br>
            e) Model No:<br>
            f) Battery Voltage at the time of installation :<br>
            {{-- g) Cell Voltage<br> --}}
            g) Year of Manufacturing :
          </td>
          <td>
            a)Ecosis<br>
            b)LiFePO454Ah<br>
            c)As per Annexure<br>
            d)Ecosis<br>
            e)LiFePO454Ah<br>
            f)12.8V<br>
            {{-- g)As per A<br> --}}
            g)2025
          </td>
        </tr>
        <tr>
          <td colspan="3"><strong>INSTALLATION OF LUMINAIRE</strong></td>
        </tr>
        <tr>
          <td>12.</td>
          <td>
            a) Name of Manufacturer<br>
            b) Casing of Luminaire:<br>
            c) Sr. no of Luminaire<br>
            d) Model No :<br>
            e) Height of installed Luminaire from ground<br>
            f) Number of LED in Luminaire<br>
            g) Wattage of Luminaire<br>
            h) Lux Measured
          </td>
          <td>
            a)Ecosis<br>
            b)Aluminium<br>
            c)As Per Annexure<br>
            d)SSL20W<br>
            e)As Per Condition b/w 5 to 5.5 meter<br>
            f)36<br>
            g)20W<br>
            h)45
          </td>
        </tr>
        <tr>
          <td colspan="3"><strong>INSTALLATION OF New POLE / MOUNTING STRUCTURE AND OTHER HARDWARE</strong></td>
        </tr>
        <tr>
          <td>13.</td>
          <td>
            a) Specify the Name of Manufacture of POLE / <br>
            STRUCTURE / HARDWARE
          </td>
          <td></td>
        </tr>
        <tr>
          <td></td>
          <td>
            b) Length and Size of installed Pole
          </td>
          <td>
            length in meter.------------<br>
            and dia in mm -------------
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            c) Grouting of Pole (300 x 300 x 1000 mm below <br>
            the ground).
          </td>
          <td>Yes</td>
        </tr>
        <tr>
          <td></td>
          <td>d) Fixing of Module and LED light as per</td>
          <td>Yes</td>
        </tr>
        <tr>
          <td></td>
          <td>e) UID no. is Properly embossed / Punched on the <br>
            system / Pole</td>
          <td>Yes</td>
        </tr>
      </tbody>
    </table>

    <table class="table-bordered mt-4 table">
      <tbody>
        <tr>
          <td width="5%"></td>
          <td width="45%">f) Toll Free no Properly embossed / Punched on the <br>
            system / Pole</td>
          <td width="50%">Yes or No</td>
        </tr>
        <tr>
          <td></td>
          <td>g) Sign board installed at pole properly</td>
          <td>Yes</td>
        </tr>
        <tr>
          <td></td>
          <td>h) System is installed with anti-theft locking nut <br>
            and Bolt</td>
          <td>Yes</td>
        </tr>
        <tr>
          <td></td>
          <td>i) All the cable should be multi strand copper <br>
            conductor properly insulated and sheathed.</td>
          <td>Yes</td>
        </tr>
        <tr>
          <td></td>
          <td>j) Training for operation & maintenance of the <br>
            system</td>
          <td>Yes</td>
        </tr>
        <tr>
          <td></td>
          <td>k) Technical literature, operation & <br>
            maintenance manual with in English/Hindi</td>
          <td>Yes</td>
        </tr>
        <tr>
          <td></td>
          <td>l) Take photograph of system along with</td>
          <td>Enclose</td>
        </tr>
        <tr>
          <td></td>
          <td>m) Remarks</td>
          <td>Separate sheet added/not added</td>
        </tr>
      </tbody>
    </table>

    <div class="mt-4">
      It is Certified that the system is installed as per technical specifications laid down in the agreement, if any
      shortcoming is found in future, will be repaired/replaced/rectify immediately.
    </div>

    <div class="signature-section mt-5">
      <div class="signature-box">
        <div class="signature-line"></div>
        <div>Signature of Agency with Seal</div>
      </div>
      <div class="signature-box">
        <div class="signature-line"></div>
        <div>Signature of PRD Representative</div>
      </div>
    </div>
    <div class="annexure_table">
      <table border="1" cellpadding="6" cellspacing="0"
        style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr>
            <th>District</th>
            <th>Block</th>
            <th>Panchayat</th>
            <th>Solar Panel No.</th>
            <th>Battery No.</th>
            <th>Luminary No.</th>
            <th>SIM No.</th>
            <th>Complete Pole No.</th>
            <th>Ward No.</th>
            <th>Beneficiary</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Date of Installation</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data["poles"] as $pole)
            <tr>
              <td>{{ $pole["district"] }}</td>
              <td>{{ $pole["block"] }}</td>
              <td>{{ $pole["panchayat"] }}</td>
              <td>{{ $pole["solar_panel_no"] }}</td>
              <td>{{ $pole["battery_no"] }}</td>
              <td>{{ $pole["luminary_no"] }}</td>
              <td>{{ $pole["sim_no"] }}</td>
              <td>{{ $pole["complete_pole_number"] }}</td>
              <td>{{ $pole["ward_no"] }}</td>
              <td>{{ $pole["beneficiary"] }}</td>
              <td>{{ $pole["latitude"] }}</td>
              <td>{{ $pole["longitude"] }}</td>
              <td>{{ $pole["date_of_installation"] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
</div>
@push("scripts")
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function printDocument() {
      const originalContents = document.body.innerHTML;
      const formContents = document.querySelector('.form-container').outerHTML;

      document.body.innerHTML = formContents;

      window.print();

      document.body.innerHTML = originalContents;
      location.reload();
    }
  </script>
@endpush

@push("styles")
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    .container {
      max-width: 100%;
      overflow-x: auto;
    }

    .form-container {
      border: 1px solid #000;
      padding: 20px;
      position: relative;
      margin-top: 50px;
      max-width: 100%;
      overflow-x: auto;
    }

    .print-btn {
      position: absolute;
      right: 40px;
    }

    .form-header {
      text-align: center;
      font-weight: bold;
      margin-bottom: 15px;
      font-size: 1.2rem;
    }

    .intro-text {
      text-align: justify;
      margin-bottom: 15px;
    }

    .details-line {
      margin-bottom: 10px;
    }

    .table {
      width: 100%;
      border: 1px solid #000;
      table-layout: fixed;
      word-wrap: break-word;
    }

    .table th,
    .table td {
      border: 1px solid #000;
      padding: 8px;
      vertical-align: top;
      word-wrap: break-word;
    }

    .signature-section {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
    }

    .signature-box {
      text-align: center;
      width: 200px;
    }

    .signature-line {
      border-top: 1px solid #000;
      margin-top: 50px;
    }

    .page-number {
      text-align: center;
      margin-top: 20px;
      font-size: 12px;
    }

    @media screen and (max-width: 768px) {
      .print-btn {
        position: static;
        margin-bottom: 20px;
      }

      .form-container {
        padding: 15px;
      }

      .table,
      .table th,
      .table td {
        font-size: 0.85rem;
      }

      .signature-section {
        flex-direction: column;
        align-items: center;
      }

      .signature-box {
        width: 100%;
      }
    }

    @media print {
      .print-btn {
        display: none !important;
      }

      .form-container {
        border: 2px solid #000 !important;
        margin-top: 0;
        padding: 15px;
        width: 100%;
        max-width: 100%;
        overflow: visible;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .table,
      .table th,
      .table td {
        border: 1px solid #000 !important;
        word-wrap: break-word;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .signature-line {
        border-top: 1px solid #000 !important;
      }

      .page-break {
        page-break-before: always;
      }
    }
  </style>
@endpush
