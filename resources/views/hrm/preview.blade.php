<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Application - Sugs Lloyd Ltd</title>

    {{-- datatables css --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    {{-- base css --}}
    <link rel="stylesheet" href="{{ asset("css/vertical-layout-light/style.css") }}">

    {{-- select2 css --}}
    <link rel="stylesheet" href="{{ asset("vendors/select2/select2.min.css") }}">
    <link rel="shortcut icon" href="{{ asset("images/favicon.png") }}">
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Google Forms inspired styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f0f0;
            color: #202124;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
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
        
        .btn {
            font-weight: 500;
            padding: 8px 24px;
            border-radius: 4px;
            transition: all 0.2s;
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
        
        .btn-outline-secondary {
            color: #5f6368;
            border-color: #dadce0;
        }
        
        .btn-outline-secondary:hover {
            background-color: #f1f3f4;
            color: #202124;
        }
        
        .section-title {
            font-weight: 500;
            color: #202124;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 500;
            width: 200px;
            color: #5f6368;
        }
        
        .info-value {
            flex: 1;
        }
        
        .edit-btn {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #673ab7;
            padding: 5px 10px;
            border: 1px solid #673ab7;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .edit-btn:hover {
            background-color: #f0ebf8;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-container img {
            max-height: 80px;
        }
        
        .preview-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .preview-header h1 {
            color: #673ab7;
            font-weight: 500;
        }
        
        .preview-header p {
            color: #5f6368;
        }
        
        .photo-preview {
            max-width: 150px;
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 4px;
        }
        
        @media (max-width: 768px) {
            .info-label {
                width: 100%;
                margin-bottom: 4px;
            }
            
            .info-row {
                flex-direction: column;
                margin-bottom: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Sugs Lloyd Ltd Logo">
        </div>
        
        <div class="preview-header">
            <h1>Review Your Application</h1>
            <p>Please review the information below before final submission. Use the Edit button to make changes.</p>
        </div>
        
        <form action="{{ route('hrm.submit') }}" method="POST">
            @csrf
            
            <!-- Personal Information -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Personal Information</span>
                    <a href="{{ route('hrm.apply') }}#personal-info" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Full Name:</div>
                                <div class="info-value">{{ $data['name'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value">{{ $data['email'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone:</div>
                                <div class="info-value">{{ $data['phone'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Date of Birth:</div>
                                <div class="info-value">{{ $data['dob'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Gender:</div>
                                <div class="info-value">{{ $data['gender'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Marital Status:</div>
                                <div class="info-value">{{ $data['marital_status'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Nationality:</div>
                                <div class="info-value">{{ $data['nationality'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Preferred Language:</div>
                                <div class="info-value">{{ $data['language'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Contact Information</span>
                    <a href="{{ route('hrm.apply') }}#contact-info" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="section-title">Permanent Address</h6>
                            <div class="info-row">
                                <div class="info-value">{{ $data['permanent_address'] ?? 'N/A' }}</div>
                            </div>
                            
                            <h6 class="section-title mt-4">Current Address</h6>
                            <div class="info-row">
                                <div class="info-value">{{ $data['current_address'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="section-title">Emergency Contact</h6>
                            <div class="info-row">
                                <div class="info-label">Name:</div>
                                <div class="info-value">{{ $data['emergency_contact_name'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Phone:</div>
                                <div class="info-value">{{ $data['emergency_contact_phone'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Educational Background -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Educational Background</span>
                    <a href="{{ route('hrm.apply') }}#education" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    @if(!empty($data['education']))
                        @foreach($data['education'] as $index => $education)
                            <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <h6 class="section-title">Education #{{ $index }}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">Qualification:</div>
                                            <div class="info-value">{{ $education['qualification'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Institution:</div>
                                            <div class="info-value">{{ $education['institution'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">Year of Graduation:</div>
                                            <div class="info-value">{{ $education['year'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Specialization:</div>
                                            <div class="info-value">{{ $education['specialization'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Certifications:</div>
                                            <div class="info-value">{{ $education['certifications'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>No educational information provided.</p>
                    @endif
                </div>
            </div>
            
            <!-- Employment Details -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Employment Details</span>
                    <a href="{{ route('hrm.apply') }}#employment" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Position Applied For:</div>
                                <div class="info-value">{{ $data['position_applied_for'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Department:</div>
                                <div class="info-value">{{ $data['department'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Date of Joining:</div>
                                <div class="info-value">{{ $data['date_of_joining'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Previous Employer:</div>
                                <div class="info-value">{{ $data['previous_employer'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Total Experience:</div>
                                <div class="info-value">{{ $data['experience'] ?? 'N/A' }} years</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Notice Period:</div>
                                <div class="info-value">{{ $data['notice_period'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Document Uploads -->
            <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Document Uploads</span>
        <a href="{{ route('hrm.apply') }}#documents" class="edit-btn">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
    <div class="card-body">
        @if(!empty($data['documents']))
            <div class="row">
                @foreach($data['documents'] as $document)
                    <div class="col-md-6 mb-3">
                        <div class="info-row">
                            <div class="info-label">{{ $document['name'] }}:</div>
                            <div class="info-value">
                                <a href="{{ config('filesystems.disks.s3.url') . '/' . $document['s3_path'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-alt me-1"></i> View Document
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p>No documents uploaded.</p>
        @endif
    </div>
</div>
            
            <!-- Additional Information -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Additional Information</span>
                    <a href="{{ route('hrm.apply') }}#additional-info" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Disabilities:</div>
                                <div class="info-value">{{ $data['disabilities'] ?? 'N/A' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Currently Employed:</div>
                                <div class="info-value">{{ $data['currently_employed'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if(($data['currently_employed'] ?? '') == 'Yes')
                                <div class="info-row">
                                    <div class="info-label">Reason for Leaving:</div>
                                    <div class="info-value">{{ $data['reason_for_leaving'] ?? 'N/A' }}</div>
                                </div>
                            @endif
                            <div class="info-row">
                                <div class="info-label">Other Information:</div>
                                <div class="info-value">{{ $data['other_info'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Passport Size Photo -->
            <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Passport Size Photo</span>
        <a href="{{ route('hrm.apply') }}#photo" class="edit-btn">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
    <div class="card-body text-center">
        @if(!empty($data['passport_photo_s3_path']))
            <img src="{{ config('filesystems.disks.s3.url') . '/' . $data['passport_photo_s3_path'] }}" alt="Passport Photo" class="photo-preview">
        @else
            <p>No passport photo uploaded.</p>
        @endif
    </div>
</div>
            
            <!-- Declaration -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Declaration</span>
                    <a href="{{ route('hrm.apply') }}#declaration" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            I hereby declare that the information provided above is true to the best of my knowledge and belief. I understand that any false information may lead to disqualification from the recruitment process.
                        </p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Signature:</div>
                                <div class="info-value">{{ $data['signature'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Date:</div>
                                <div class="info-value">{{ $data['date'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between mt-4 mb-5">
                <a href="{{ route('hrm.apply') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Form
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle me-2"></i> Confirm & Submit Application
                </button>
            </div>
        </form>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>