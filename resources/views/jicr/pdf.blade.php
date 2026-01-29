<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JICR Report</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 15px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .mt-4 {
            margin-top: 0rem !important;
            font-size: 10px;
        }

        .mt-5 {
            margin-top: 0rem !important;
        }

        .annexure_table table td {
            font-size: 6px;
        }

        .form-container {
            position: relative;
            margin-top: 0;
            max-width: 100%;
            overflow-x: auto;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .intro-text {
            text-align: justify;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .details-line {
            margin-bottom: 10px;
            font-size: 10px;
        }

        /* DomPDF does not support text-decoration-style:dotted; use border-bottom instead */
        .details-line span,
        .dotted-underline {
            border-bottom: 1px dotted #000;
            text-decoration: none;
        }

        .table {
            width: 100%;
            border: 1px solid #000;
            table-layout: fixed;
            word-wrap: break-word;
            border-collapse: collapse;
            font-size: 8px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            word-wrap: break-word;
            font-size: 8px;
        }

        /* Use table for signature row - dompdf flexbox is unreliable, table guarantees single row */
        .signature-section {
            margin-top: 30px;
            width: 100%;
            font-size: 10px;
            border: none;
            border-collapse: collapse;
        }

        .signature-section td {
            width: 50%;
            vertical-align: top;
            padding: 0 20px 0 0;
            border: none;
        }

        .signature-section td:first-child {
            text-align: left;
        }

        .signature-section td:last-child {
            padding: 0 0 0 20px;
            text-align: right;
        }

        .signature-box {
            text-align: center;
            display: inline-block;
            max-width: 240px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
        }

        .page-number {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }

        .annexure_table {
            margin-top: 5vh;
            page-break-before: always;
        }

        .annexure_table table {
            font-size: 6px;
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        .annexure_table table th,
        .annexure_table table td {
            border: 1px solid #000;
            padding: 2px;
            vertical-align: top;
            word-wrap: break-word;
            font-size: 6px;
        }

        @media print {
            .form-container {
                border: none !important;
                margin-top: 0;
                padding: 15px;
                width: 100%;
                max-width: 100%;
                overflow: visible;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table {
                border-collapse: collapse !important;
            }

            .table,
            .table th,
            .table td {
                border: 1px solid #000 !important;
                border-collapse: collapse !important;
                word-wrap: break-word;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 8px !important;
            }

            .signature-line {
                border-top: 1px solid #000 !important;
            }

            .page-break {
                page-break-before: always;
            }

            .annexure_table {
                page-break-before: always !important;
            }

            .annexure_table table {
                page-break-inside: avoid;
                border-collapse: collapse !important;
                font-size: 6px !important;
            }

            .annexure_table table th,
            .annexure_table table td {
                padding: 2px;
                border: 1px solid #000 !important;
                border-collapse: collapse !important;
                font-size: 6px !important;
            }

            .signature-section {
                margin-bottom: 20px;
            }

            .signature-section td {
                width: 50% !important;
                border: none !important;
            }

            .details-line span,
            .dotted-underline {
                border-bottom: 1px dotted #000 !important;
                text-decoration: none !important;
            }
        }
    </style>
</head>

<body>
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
            <span class="dotted-underline"> {{ $data['district'] ?? '' }}
                /
                {{ $data['block'] ?? '' }} /
                {{ $data['panchayat'] ?? '' }}</span>
        </div>
        <div class="details-line">Name of Executing Agency
            <span class="dotted-underline"> Sugs Lloyd Limited
            </span>
        </div>
        <div class="details-line">Work order no. and date<span class="dotted-underline">
                {{ $data['work_order_number'] }}
                {{ $data['project_start_date'] }}</span></div>

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
                    <td> {{ $data['agreement_number'] }} {{ $data['agreement_date'] }}</td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>Capacity of System:</td>
                    <td>{{ $data['project_capacity'] ?? '' }}W</td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>Name of Executing Agency with full address:<br>
                        Contact person name and mobile no –Mandatory</td>
                    <td style="white-space: normal; word-wrap: break-word; max-width: 30vw;">M/s Sugs Lloyd Limited,
                        Office No-
                        8B, CSC-I, Mandawali, Fazalpur, Behind Narwana Appartments,
                        Delhi-110092,</td>
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
                    <td style="white-space: normal; word-wrap: break-word; max-width: 20vw;">Exact location on
                        installation
                        Latitude and longitude <br>
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
                        a)Mono Crystalline<br>
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
                    <td colspan="3"><strong>INSTALLATION OF New POLE / MOUNTING STRUCTURE AND OTHER HARDWARE</strong>
                    </td>
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
                        length in meter.<span style="text-decoration-line: underline; text-decoration-style: dotted;">11
                            M</span><br>
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
                    <td width="50%">Yes</td>
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

        <div class="mt-4" style="font-size: 10px;">
            It is Certified that the system is installed as per technical specifications laid down in the agreement, if
            any
            shortcoming is found in future, will be repaired/replaced/rectify immediately.
        </div>

        <table class="signature-section mt-5" cellpadding="0" cellspacing="0" style="width:100%; border:none;">
            <tr>
                <td style="width:50%; vertical-align:top; border:none; padding-right:20px;">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div>Signature of Agency with Seal</div>
                    </div>
                </td>
                <td style="width:50%; vertical-align:top; border:none; padding-left:20px;">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div>Signature of PRD Representative</div>
                    </div>
                </td>
            </tr>
        </table>
        <div class="annexure_table">
            <table>
                <thead>
                    <tr>
                        <td>Sl. No.</td>
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
                    @foreach ($data['poles'] as $index => $pole)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $pole['district'] }}</td>
                            <td>{{ $pole['block'] }}</td>
                            <td>{{ $pole['panchayat'] }}</td>
                            <td>{{ $pole['solar_panel_no'] }}</td>
                            <td>{{ $pole['battery_no'] }}</td>
                            <td>{{ $pole['luminary_no'] }}</td>
                            <td>{{ $pole['sim_no'] }}</td>
                            <td>{{ $pole['complete_pole_number'] }}</td>
                            <td>{{ $pole['ward_no'] }}</td>
                            <td>{{ $pole['beneficiary'] }}</td>
                            <td>{{ $pole['latitude'] }}</td>
                            <td>{{ $pole['longitude'] }}</td>
                            <td>{{ $pole['date_of_installation'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>
