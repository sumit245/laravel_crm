<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Onboarding - Sugs Lloyd Ltd</title>

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
            overflow-x: hidden;
        }
        
        /* Main Layout Styles */
        .container-scroller {
            padding: 0;
        }
        
        .page-body-wrapper {
            padding: 0;
        }
        
        .main-panel {
            width: 100%;
        }
        
        /* Fixed sidebar styles */
        .side-nav {
            background-color: #fff;
            min-height: 100vh;
            border-right: 1px solid #e0e0e0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 16.666667%;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding-top: 20px;
            z-index: 1000;
        }
        
        /* Adjust main content to accommodate fixed sidebar */
        .main-content {
            padding: 0;
            background-color: #f0f0f0;
            margin-left: 16.666667%;
        }
        
        @media (max-width: 767.98px) {
            .side-nav {
                position: relative;
                width: 100%;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Form Section Styles */
        .form-section {
            display: none;
            margin-bottom: 24px;
        }
        
        .form-section.active {
            display: block;
        }
        
        /* Card Styles - Google Forms inspired */
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
        
        /* Navigation Tabs Styles */
        .form-tabs {
            padding-left: 0;
        }
        
        .form-tabs .nav-item {
            width: 100%;
        }
        
        .form-tabs .nav-link {
            color: #5f6368;
            padding: 12px 16px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            border-radius: 0 24px 24px 0;
            margin-bottom: 4px;
        }
        
        .form-tabs .nav-link:hover {
            background-color: #f1f3f4;
        }
        
        .form-tabs .nav-link.active {
            color: #673ab7;
            background-color: #f0ebf8;
            border-left: 3px solid #673ab7;
            font-weight: 500;
        }
        
        .form-tabs .nav-link.disabled {
            color: #bdc1c6;
            cursor: not-allowed;
        }
        
        .form-tabs .nav-link.completed {
            color: #1a73e8;
            border-left: 3px solid #1a73e8;
        }
        
        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #e0e0e0;
            margin-right: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .nav-link.active .section-number {
            background-color: #673ab7;
            color: white;
        }
        
        .nav-link.completed .section-number {
            background-color: #1a73e8;
            color: white;
        }
        
        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: #5f6368;
            margin-bottom: 8px;
        }
        
        .form-label.required::after {
            content: " *";
            color: #d93025;
        }
        
        .form-control {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 8px 12px;
            height: auto;
            font-size: 14px;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: #673ab7;
            box-shadow: 0 1px 2px 0 rgba(103, 58, 183, 0.3);
        }
        
        .form-control.is-invalid {
            border-color: #d93025;
            background-image: none;
        }
        
        .invalid-feedback {
            color: #d93025;
            font-size: 12px;
            margin-top: 4px;
        }
        
        /* Enhanced Select Styles */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23673ab7' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 30px;
            cursor: pointer;
        }
        
        /* Select2 Custom Styling */
        .select2-container--bootstrap4 .select2-selection {
            border: 1px solid #dadce0;
            border-radius: 4px;
            height: auto;
            min-height: 38px;
            padding: 4px 8px;
            font-size: 14px;
        }
        
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #673ab7;
            box-shadow: 0 1px 2px 0 rgba(103, 58, 183, 0.3);
        }
        
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: #673ab7;
        }
        
        .select2-container--bootstrap4 .select2-dropdown {
            border-color: #dadce0;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 36px;
        }
        
        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 28px;
        }
        
        /* Radio and Checkbox Styles */
        .form-check {
            padding-left: 0;
            margin-bottom: 8px;
        }
        
        .form-check-input {
            margin-left: 0;
            margin-right: 8px;
            width: 18px;
            height: 18px;
        }
        
        .form-check-input:checked {
            background-color: #673ab7;
            border-color: #673ab7;
        }
        
        .form-check-label {
            margin-left: 8px;
            font-weight: 400;
        }
        
        /* Button Styles */
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
        
        .btn-danger {
            background-color: #d93025;
            border-color: #d93025;
        }
        
        /* Section Headers */
        h5.fw-semibold {
            color: #202124;
            margin-bottom: 16px;
            font-weight: 500;
        }
        
        /* Progress Bar */
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e0e0e0;
            margin-top: 16px;
        }
        
        .progress-bar {
            background-color: #673ab7;
        }
        
        /* Document Upload */
        .document-upload {
            border: 1px dashed #dadce0;
            border-radius: 4px;
            padding: 16px;
            text-align: center;
            transition: all 0.2s;
        }
        
        .document-upload:hover {
            border-color: #673ab7;
            background-color: #f8f9fa;
        }
        
        /* Photo Upload */
        .photo-upload-container {
            text-align: center;
            margin-bottom: 24px;
        }
        
        #passportPhotoPreview {
            border-radius: 4px;
            border: 1px solid #dadce0;
            padding: 4px;
            max-height: 150px;
        }
        
        #removePhotoButton {
            border-radius: 50%;
            width: 24px;
            height: 24px;
            line-height: 16px;
            padding: 0;
            font-size: 16px;
            text-align: center;
            background-color: #d93025;
            border-color: #d93025;
            color: white;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            .side-nav {
                min-height: auto;
                position: relative;
            }
            
            .form-tabs {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .form-tabs .nav-item {
                flex: 0 0 auto;
            }
            
            .card-header {
                padding: 12px 16px;
            }
            
            .card-body {
                padding: 16px;
            }
            
            .card-footer {
                padding: 12px 16px;
            }
        }
        
        /* Form Watermark */
        .form-watermark {
            position: relative;
        }
        
        .form-watermark::before {
            content: "";
            background: url('{{ asset('images/logo.png') }}') no-repeat center center;
            background-size: 300px;
            opacity: 0.03;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        
        /* Alert Styles */
        .alert {
            border-radius: 4px;
            padding: 16px;
        }
        
        .alert-info {
            background-color: #e8f0fe;
            border-color: #d2e3fc;
            color: #1967d2;
        }
        
        /* Section Dividers */
        hr {
            margin: 24px 0;
            border-color: #e0e0e0;
        }
        
        /* Logo Container */
        .logo-container {
            text-align: center;
            padding: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        /* Progress Indicator */
        .progress-indicator {
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }
        
        .progress-text {
            font-size: 14px;
            color: #5f6368;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper">
            <div class="main-panel">
                <div class="container-fluid p-0">
                    <div class="row g-0">
                        <!-- Side Navigation Tabs -->
                        <div class="col-md-3 col-lg-2 side-nav">
                            <div class="logo-container">
                                <img src="{{ asset('images/logo.png') }}" alt="Sugs Lloyd Ltd Logo" class="img-fluid" style="max-height: 80px;">
                            </div>
                            
                            <!-- Progress Indicator -->
                            <div class="progress-indicator mx-3">
                                <div class="progress-text">Form Completion</div>
                                <div class="progress">
                                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="text-end mt-2">
                                    <small id="progress-percentage">0%</small>
                                </div>
                            </div>
                            
                            <ul class="nav flex-column form-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-section="personal-info">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">1</span>
                                            <span class="section-title">Personal Information</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-section="contact-info">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">2</span>
                                            <span class="section-title">Contact Information</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-section="education">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">3</span>
                                            <span class="section-title">Educational Background</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-section="employment">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">4</span>
                                            <span class="section-title">Employment Details</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-section="documents">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">5</span>
                                            <span class="section-title">Document Uploads</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-section="additional-info">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">6</span>
                                            <span class="section-title">Additional Information</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-section="photo">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">7</span>
                                            <span class="section-title">Passport Size Photo</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-section="declaration">
                                        <div class="d-flex align-items-center">
                                            <span class="section-number">8</span>
                                            <span class="section-title">Declaration</span>
                                            <span class="section-status ms-auto"><i class="fas fa-circle"></i></span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Main Form Content -->
                        <div class="col-md-9 col-lg-10 main-content form-watermark">
                            <div class="p-4">
                                <div class="mb-4 pb-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <h3 class="fw-bold mb-2">Employee Onboarding – Sugs Lloyd Ltd</h3>
                                            <p class="text-muted mb-0">Please fill out all the details below carefully. All fields marked with * are mandatory.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <form id="onboarding-form" method="POST" action="{{ route('hrm.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <!-- Personal Information Section -->
                                    <div class="form-section active" id="personal-info">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">1. Personal Information</span>
                                                <span class="section-indicator">Section 1 of 8</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="name">Full Name</label>
                                                        <input type="text" id="name" name="name" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid name (2-50 characters, letters only)</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="email">Email Address</label>
                                                        <input type="email" id="email" name="email" class="form-control" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
                                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="phone">Phone Number</label>
                                                        <input type="text" id="phone" name="phone" class="form-control" required pattern="^[0-9+\s()-]{10,15}$" title="Please enter a valid phone number (10-15 digits)">
                                                        <div class="invalid-feedback">Please enter a valid phone number (10-15 digits)</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="dob">Date of Birth</label>
                                                        <input type="date" id="dob" name="dob" class="form-control" required max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                                                        <div class="invalid-feedback">You must be at least 18 years old</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required">Gender</label>
                                                        <div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="gender" id="gender-male" value="Male" required>
                                                                <label class="form-check-label" for="gender-male">Male</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="gender" id="gender-female" value="Female" required>
                                                                <label class="form-check-label" for="gender-female">Female</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="gender" id="gender-other" value="Other" required>
                                                                <label class="form-check-label" for="gender-other">Other</label>
                                                            </div>
                                                            <div class="invalid-feedback">Please select your gender</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required">Marital Status</label>
                                                        <div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="marital_status" id="marital-single" value="Single" required>
                                                                <label class="form-check-label" for="marital-single">Single</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="marital_status" id="marital-married" value="Married" required>
                                                                <label class="form-check-label" for="marital-married">Married</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="marital_status" id="marital-other" value="Other" required>
                                                                <label class="form-check-label" for="marital-other">Other</label>
                                                            </div>
                                                            <div class="invalid-feedback">Please select your marital status</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="nationality">Nationality</label>
                                                        <input type="text" id="nationality" name="nationality" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid nationality (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid nationality (2-50 characters, letters only)</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="language">Preferred Language</label>
                                                        <select id="language" name="language" class="form-select custom-select" required>
                                                            <option value="">Select Language</option>
                                                            <option value="English">English</option>
                                                            <option value="Hindi">Hindi</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select your preferred language</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary" disabled>Previous</button>
                                                    <button type="button" class="btn btn-primary next-section" data-next="contact-info">Next Section</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Contact Information Section -->
                                    <div class="form-section" id="contact-info">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">2. Contact Information</span>
                                                <span class="section-indicator">Section 2 of 8</span>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="fw-semibold mb-3">Permanent Address</h5>
                                                <div class="row g-3 mb-4 border-bottom pb-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="perm_house_no">House/Flat No.</label>
                                                        <input type="text" id="perm_house_no" name="perm_house_no" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter your house/flat number</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="perm_street">Street/Road</label>
                                                        <input type="text" id="perm_street" name="perm_street" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter your street/road</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="perm_city">City</label>
                                                        <input type="text" id="perm_city" name="perm_city" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid city name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid city name</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="perm_state">State</label>
                                                        <input type="text" id="perm_state" name="perm_state" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid state name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid state name</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="perm_country">Country</label>
                                                        <input type="text" id="perm_country" name="perm_country" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid country name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid country name</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="perm_zip">PIN / ZIP Code</label>
                                                        <input type="text" id="perm_zip" name="perm_zip" class="form-control" required pattern="^[0-9]{5,10}$" title="Please enter a valid ZIP code (5-10 digits)">
                                                        <div class="invalid-feedback">Please enter a valid ZIP code (5-10 digits)</div>
                                                    </div>
                                                </div>

                                                <!-- Same as Permanent Checkbox -->
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="sameAsPermanent">
                                                    <label class="form-check-label" for="sameAsPermanent">Same as Permanent Address</label>
                                                </div>

                                                <h5 class="fw-semibold mb-3">Current Address</h5>
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="curr_house_no">House/Flat No.</label>
                                                        <input type="text" id="curr_house_no" name="curr_house_no" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter your house/flat number</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="curr_street">Street/Road</label>
                                                        <input type="text" id="curr_street" name="curr_street" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter your street/road</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="curr_city">City</label>
                                                        <input type="text" id="curr_city" name="curr_city" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid city name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid city name</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="curr_state">State</label>
                                                        <input type="text" id="curr_state" name="curr_state" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid state name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid state name</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="curr_country">Country</label>
                                                        <input type="text" id="curr_country" name="curr_country" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid country name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid country name</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label required" for="curr_zip">PIN / ZIP Code</label>
                                                        <input type="text" id="curr_zip" name="curr_zip" class="form-control" required pattern="^[0-9]{5,10}$" title="Please enter a valid ZIP code (5-10 digits)">
                                                        <div class="invalid-feedback">Please enter a valid ZIP code (5-10 digits)</div>
                                                    </div>
                                                </div>

                                                <hr>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label required" for="emergency_contact_name">Emergency Contact Name</label>
                                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" required pattern="^[a-zA-Z\s]{2,50}$" title="Please enter a valid name (2-50 characters, letters only)">
                                                        <div class="invalid-feedback">Please enter a valid emergency contact name</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label required" for="emergency_contact_phone">Emergency Contact Phone Number</label>
                                                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" required pattern="^[0-9+\s()-]{10,15}$" title="Please enter a valid phone number (10-15 digits)">
                                                        <div class="invalid-feedback">Please enter a valid emergency contact phone number</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="personal-info">Previous</button>
                                                    <button type="button" class="btn btn-primary next-section" data-next="education">Next Section</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Educational Background Section -->
                                    <div class="form-section" id="education">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">3. Educational Background</span>
                                                <span class="section-indicator">Section 3 of 8</span>
                                            </div>

                                            <div id="education-entries">
                                                <!-- Education entry will be added here dynamically -->
                                            </div>

                                            <div class="card-body">
                                                <button type="button" class="btn btn-success" onclick="addEducationEntry()">
                                                    <i class="fas fa-plus-circle me-2"></i> Add More Education
                                                </button>
                                            </div>

                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="contact-info">Previous</button>
                                                    <button type="button" class="btn btn-primary next-section" data-next="employment">Next Section</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Employment Details Section -->
                                    <div class="form-section" id="employment">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">4. Employment Details</span>
                                                <span class="section-indicator">Section 4 of 8</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="position_applied_for">Position Applied For</label>
                                                        <input type="text" id="position_applied_for" name="position_applied_for" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter the position you are applying for</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="department">Department</label>
                                                        <input type="text" id="department" name="department" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter the department</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="date_of_joining">Date of Joining</label>
                                                        <input type="date" id="date_of_joining" name="date_of_joining" class="form-control" required min="{{ date('Y-m-d') }}">
                                                        <div class="invalid-feedback">Please select a valid joining date (must be today or in the future)</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="previous_employer">Previous Employer</label>
                                                        <input type="text" id="previous_employer" name="previous_employer" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter your previous employer</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="experience">Total Years of Experience</label>
                                                        <input type="number" id="experience" name="experience" step="0.1" min="0" max="50" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter a valid experience (0-50 years)</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label required" for="notice_period">Notice Period</label>
                                                        <input type="text" id="notice_period" name="notice_period" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter your notice period</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="education">Previous</button>
                                                    <button type="button" class="btn btn-primary next-section" data-next="documents">Next Section</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Document Uploads Section -->
                                    <div class="form-section" id="documents">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">5. Document Uploads</span>
                                                <span class="section-indicator">Section 5 of 8</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info mb-4">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Upload documents (PDF, JPG, PNG, max 5MB each). You can add more documents below.
                                                </div>
                                                <div id="documentUploads">
                                                    <div class="row g-3 align-items-end mb-3 p-3 border rounded bg-light">
                                                        <div class="col-md-5">
                                                            <label class="form-label required" for="document_name_1">Document Name</label>
                                                            <input type="text" id="document_name_1" name="document_name[]" class="form-control" placeholder="e.g. Resume" required>
                                                            <div class="invalid-feedback">Please enter a document name</div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="form-label required" for="document_file_1">Upload File</label>
                                                            <input type="file" id="document_file_1" name="document_file[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                                            <div class="invalid-feedback">Please upload a valid document (PDF, JPG, PNG, max 5MB)</div>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button" class="btn btn-outline-danger remove-document d-none">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" id="addMoreDocuments" class="btn btn-outline-secondary mt-2">
                                                    <i class="fas fa-plus-circle me-2"></i> Add More Documents
                                                </button>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="employment">Previous</button>
                                                    <button type="button" class="btn btn-primary next-section" data-next="additional-info">Next Section</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Additional Information Section -->
                                    <div class="form-section" id="additional-info">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">6. Additional Information</span>
                                                <span class="section-indicator">Section 6 of 8</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label required">Do you have any disabilities?</label>
                                                        <div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="disabilities" id="disabilities-yes" value="Yes" required>
                                                                <label class="form-check-label" for="disabilities-yes">Yes</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="disabilities" id="disabilities-no" value="No" required>
                                                                <label class="form-check-label" for="disabilities-no">No</label>
                                                            </div>
                                                            <div class="invalid-feedback">Please select an option</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label required">Are you currently employed?</label>
                                                        <div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="currently_employed" id="employed-yes" value="Yes" required>
                                                                <label class="form-check-label" for="employed-yes">Yes</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" class="form-check-input" name="currently_employed" id="employed-no" value="No" required>
                                                                <label class="form-check-label" for="employed-no">No</label>
                                                            </div>
                                                            <div class="invalid-feedback">Please select an option</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12" id="reason-for-leaving-container" style="display: none;">
                                                        <label class="form-label required" for="reason_for_leaving">Reason for leaving current employment:</label>
                                                        <input type="text" id="reason_for_leaving" name="reason_for_leaving" class="form-control">
                                                        <div class="invalid-feedback">Please provide a reason for leaving</div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label" for="other_info">Any other information you'd like to provide:</label>
                                                        <textarea id="other_info" name="other_info" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="documents">Previous</button>
                                                    <button type="button" class="btn btn-primary next-section" data-next="photo">Next Section</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Passport Size Photo Section -->
                                    <div class="form-section" id="photo">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">7. Passport Size Photo</span>
                                                <span class="section-indicator">Section 7 of 8</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info mb-4">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Upload a recent passport size photo (JPG, PNG, max 2MB).
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label required" for="passportPhotoInput">Upload Photo</label>
                                                        <input type="file" id="passportPhotoInput" name="passport_photo" accept="image/jpeg,image/png" class="form-control" required>
                                                        <div class="invalid-feedback">Please upload a valid passport photo (JPG, PNG, max 2MB)</div>
                                                    </div>
                                                    <div class="col-md-6 d-flex align-items-end">
                                                        <div class="photo-upload-container">
                                                            <label class="form-label d-block">Preview</label>
                                                            <div class="position-relative d-inline-block">
                                                                <img id="passportPhotoPreview" src="{{ asset('images/default-avatar.png') }}" alt="Passport Preview" class="img-thumbnail" style="max-height: 150px;">
                                                                <button type="button" id="removePhotoButton" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="display:none;">×</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="additional-info">Previous</button>
                                                    <button type="button" class="btn btn-primary next-section" data-next="declaration">Next Section</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Declaration Section -->
                                    <div class="form-section" id="declaration">
                                        <div class="card mb-4">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">8. Declaration</span>
                                                <span class="section-indicator">Section 8 of 8</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info mb-4">
                                                    <p class="mb-0">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        I hereby declare that the information provided above is true to the best of my knowledge and belief. I understand that any false information may lead to disqualification from the recruitment process.
                                                    </p>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label required" for="signature">Signature</label>
                                                        <input type="text" id="signature" name="signature" class="form-control" required>
                                                        <div class="invalid-feedback">Please enter your signature</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label required" for="declaration_date">Date</label>
                                                        <input type="date" id="declaration_date" name="date" class="form-control" required value="{{ date('Y-m-d') }}" readonly>
                                                        <div class="invalid-feedback">Please select today's date</div>
                                                    </div>
                                                    <div class="col-md-12 mt-4">
                                                        <div class="form-check">
                                                            <input type="checkbox" id="agree_terms" name="agree_terms" class="form-check-input" required>
                                                            <label class="form-check-label" for="agree_terms">
                                                                I agree to the terms and conditions and confirm that all information provided is accurate.
                                                            </label>
                                                            <div class="invalid-feedback">You must agree to the terms and conditions</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="photo">Previous</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-check-circle me-2"></i> Submit & Preview
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset("vendors/select2/select2.min.js") }}"></script>
    <script src="{{ asset("js/off-canvas.js") }}"></script>
    <script src="{{ asset("js/hoverable-collapse.js") }}"></script>
    <script src="{{ asset("js/template.js") }}"></script>
    <script src="{{ asset("js/dashboard.js") }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize variables
        const sections = ['personal-info', 'contact-info', 'education', 'employment', 'documents', 'additional-info', 'photo', 'declaration'];
        let completedSections = [];

        
        // Function to update progress bar
        function updateProgress() {
            const totalSections = sections.length;
            const completedCount = completedSections.length;
            const progressPercentage = Math.round((completedCount / totalSections) * 100);
            
            document.getElementById('progress-bar').style.width = progressPercentage + '%';
            document.getElementById('progress-percentage').textContent = progressPercentage + '%';
        }
        
        // Function to mark section as completed
        function markSectionCompleted(sectionId) {
            if (!completedSections.includes(sectionId)) {
                completedSections.push(sectionId);
                
                // Update tab status
                const tabLink = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
                tabLink.classList.add('completed');
                
                updateProgress();
            }
        }
        
        // Function to validate section
        function validateSection(sectionId) {
            const section = document.getElementById(sectionId);
            const inputs = section.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (input.type === 'radio') {
                    // For radio buttons, check if any in the group is checked
                    const name = input.name;
                    const radioGroup = section.querySelectorAll(`input[name="${name}"]`);
                    const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                    
                    if (!isChecked) {
                        isValid = false;
                        // Find the parent div that contains the invalid-feedback
                        const parentDiv = input.closest('div.col-md-4, div.col-md-6, div.col-md-12');
                        if (parentDiv) {
                            const feedback = parentDiv.querySelector('.invalid-feedback');
                            if (feedback) {
                                feedback.style.display = 'block';
                            }
                        }
                    }
                } else if (input.type === 'checkbox') {
                    if (!input.checked && input.required) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                } else if (input.type === 'file') {
                    // Skip file validation for now as it's handled separately
                } else {
                    // For other inputs, check if they have a value and match their pattern if any
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else if (input.pattern && !new RegExp(input.pattern).test(input.value)) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                }
            });
            
            return isValid;
        }
        
        // Function to switch sections
        function switchSection(fromSection, toSection) {
            // Hide current section
            document.getElementById(fromSection).classList.remove('active');
            
            // Show target section
            document.getElementById(toSection).classList.add('active');
            
            // Update active tab
            document.querySelector(`.nav-link[data-section="${fromSection}"]`).classList.remove('active');
            document.querySelector(`.nav-link[data-section="${toSection}"]`).classList.add('active');
            document.querySelector(`.nav-link[data-section="${toSection}"]`).classList.remove('disabled');
            
            // Scroll to top
            window.scrollTo(0, 0);
        }
        
        // Next section button click handler
        document.querySelectorAll('.next-section').forEach(button => {
            button.addEventListener('click', function() {
                const currentSection = this.closest('.form-section').id;
                const nextSection = this.getAttribute('data-next');
                
                if (validateSection(currentSection)) {
                    markSectionCompleted(currentSection);
                    switchSection(currentSection, nextSection);
                } else {
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please fill in all required fields correctly before proceeding.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#673ab7'
                    });
                }
            });
        });
        
        // Previous section button click handler
        document.querySelectorAll('.prev-section').forEach(button => {
            button.addEventListener('click', function() {
                const currentSection = this.closest('.form-section').id;
                const prevSection = this.getAttribute('data-prev');
                
                switchSection(currentSection, prevSection);
            });
        });
        
        // Tab click handler
        document.querySelectorAll('.form-tabs .nav-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                if (!this.classList.contains('disabled')) {
                    const targetSection = this.getAttribute('data-section');
                    const currentSection = document.querySelector('.form-section.active').id;
                    
                    switchSection(currentSection, targetSection);
                }
            });
        });
        
        // Same as permanent address checkbox
        document.getElementById('sameAsPermanent').addEventListener('change', function() {
            const fields = ['house_no', 'street', 'city', 'state', 'country', 'zip'];
            fields.forEach(field => {
                const perm = document.getElementById(`perm_${field}`);
                const curr = document.getElementById(`curr_${field}`);
                if (this.checked) {
                    curr.value = perm.value;
                    curr.readOnly = true;
                    curr.classList.add('bg-light');
                } else {
                    curr.readOnly = false;
                    curr.classList.remove('bg-light');
                }
            });
        });
        
        // Add more documents button
        document.getElementById('addMoreDocuments').addEventListener('click', function() {
            const wrapper = document.getElementById('documentUploads');
            const documentCount = wrapper.children.length + 1;
            const row = document.createElement('div');
            row.className = 'row g-3 align-items-end mb-3 p-3 border rounded bg-light';
            row.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label required" for="document_name_${documentCount}">Document Name</label>
                    <input type="text" id="document_name_${documentCount}" name="document_name[]" class="form-control" placeholder="Document Name" required>
                    <div class="invalid-feedback">Please enter a document name</div>
                </div>
                <div class="col-md-5">
                    <label class="form-label required" for="document_file_${documentCount}">Upload File</label>
                    <input type="file" id="document_file_${documentCount}" name="document_file[]" class="form-control" accept=".pdf,.jpg,.  id="document_file_${documentCount}" name="document_file[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    <div class="invalid-feedback">Please upload a valid document (PDF, JPG, PNG, max 5MB)</div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger remove-document">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            wrapper.appendChild(row);
            
            // Show all remove buttons if there's more than one document
            if (wrapper.children.length > 1) {
                wrapper.querySelectorAll('.remove-document').forEach(btn => {
                    btn.classList.remove('d-none');
                });
            }
        });
        
        // Remove document field
        document.getElementById('documentUploads').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-document') || e.target.closest('.remove-document')) {
                const button = e.target.classList.contains('remove-document') ? e.target : e.target.closest('.remove-document');
                const row = button.closest('.row');
                row.remove();
                
                // Hide remove button if only one document remains
                const wrapper = document.getElementById('documentUploads');
                if (wrapper.children.length === 1) {
                    wrapper.querySelector('.remove-document').classList.add('d-none');
                }
            }
        });
        
        // Passport photo preview
        const photoInput = document.getElementById('passportPhotoInput');
        const photoPreview = document.getElementById('passportPhotoPreview');
        const removeButton = document.getElementById('removePhotoButton');
        
        photoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        title: 'File Too Large',
                        text: 'The photo must be less than 2MB in size.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#673ab7'
                    });
                    photoInput.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                    removeButton.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            } else {
                resetPassportPhoto();
            }
        });
        
        removeButton.addEventListener('click', function() {
            resetPassportPhoto();
        });
        
        function resetPassportPhoto() {
            photoPreview.src = '{{ asset('images/default-avatar.png') }}';
            photoInput.value = '';
            removeButton.style.display = 'none';
        }
        
        // Show/hide reason for leaving based on employment status
        document.querySelectorAll('input[name="currently_employed"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const reasonContainer = document.getElementById('reason-for-leaving-container');
                const reasonInput = document.getElementById('reason_for_leaving');
                
                if (this.value === 'Yes') {
                    reasonContainer.style.display = 'block';
                    reasonInput.required = true;
                } else {
                    reasonContainer.style.display = 'none';
                    reasonInput.required = false;
                    reasonInput.value = '';
                }
            });
        });
        
        // Form submission
        // document.getElementById('onboarding-form').addEventListener('submit', function(e) {
        //     e.preventDefault();
            
        //     // Validate all sections
        //     let allValid = true;
        //     sections.forEach(section => {
        //         if (!validateSection(section)) {
        //             allValid = false;
        //         }
        //     });
            
        //     if (allValid) {
        //         // Submit the form
        //         this.submit();
        //     } else {
        //         Swal.fire({
        //             title: 'Validation Error',
        //             text: 'Please fill in all required fields in all sections before submitting.',
        //             icon: 'error',
        //             confirmButtonText: 'OK',
        //             confirmButtonColor: '#673ab7'
        //         });
        //     }
        // });
        
        // Initialize Select2 for enhanced select boxes
        if ($.fn.select2) {
            $('.custom-select').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#onboarding-form'),
                minimumResultsForSearch: 6
            });
        }
    });

    let educationCount = 0;

    function addEducationEntry() {
        educationCount++;
        const container = document.getElementById('education-entries');
        const entryHTML = `
            <div class="card-body row g-3 mb-3 border p-3 bg-light rounded" id="education-entry-${educationCount}">
                <div class="col-md-4">
                    <label class="form-label required" for="education_qualification_${educationCount}">Highest Qualification</label>
                    <select id="education_qualification_${educationCount}" name="education[${educationCount}][qualification]" class="form-select custom-select" required>
                        <option value="">Select Qualification</option>
                        <option value="High School">High School</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Bachelors">Bachelors</option>
                        <option value="Masters">Masters</option>
                        <option value="PhD">PhD</option>
                        <option value="Other">Other</option>
                    </select>
                    <div class="invalid-feedback">Please select a qualification</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label required" for="education_institution_${educationCount}">Institution Name</label>
                    <input type="text" id="education_institution_${educationCount}" name="education[${educationCount}][institution]" class="form-control" required>
                    <div class="invalid-feedback">Please enter the institution name</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label required" for="education_year_${educationCount}">Year of Graduation</label>
                    <input type="number" id="education_year_${educationCount}" name="education[${educationCount}][year]" class="form-control" min="1950" max="${new Date().getFullYear()}" required>
                    <div class="invalid-feedback">Please enter a valid graduation year</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label required" for="education_specialization_${educationCount}">Specialization</label>
                    <input type="text" id="education_specialization_${educationCount}" name="education[${educationCount}][specialization]" class="form-control" required>
                    <div class="invalid-feedback">Please enter your specialization</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="education_certifications_${educationCount}">Additional Certifications</label>
                    <input type="text" id="education_certifications_${educationCount}" name="education[${educationCount}][certifications]" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-danger" onclick="removeEducationEntry(${educationCount})">
                        <i class="fas fa-trash-alt me-2"></i> Remove
                    </button>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', entryHTML);
        
        // Initialize Select2 for the newly added select
        if ($.fn.select2) {
            $(`#education_qualification_${educationCount}`).select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $(`#education-entry-${educationCount}`),
                minimumResultsForSearch: 6
            });
        }
    }

    function removeEducationEntry(id) {
        const entry = document.getElementById(`education-entry-${id}`);
        if (entry) {
            // Use SweetAlert2 for confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to remove this education entry.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    entry.remove();
                }
            });
        }
    }

    // Add initial entry by default
    window.onload = function() {
        addEducationEntry();
    };
</script>

<script>
    // Add this at the end of your existing script
    document.addEventListener('DOMContentLoaded', function() {
        // Function to save form data to sessionStorage
        function saveFormData() {
            const formData = {};
            const form = document.getElementById('onboarding-form');
            
            // Get all input elements
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                // Skip file inputs
                if (input.type === 'file') return;
                
                // Handle radio buttons and checkboxes
                if (input.type === 'radio' || input.type === 'checkbox') {
                    if (input.checked) {
                        formData[input.name] = input.value;
                    }
                } else {
                    formData[input.name] = input.value;
                }
            });
            
            // Save to sessionStorage
            sessionStorage.setItem('employeeFormData', JSON.stringify(formData));
        }
        
        // Function to load form data from sessionStorage
        function loadFormData() {
            const savedData = sessionStorage.getItem('employeeFormData');
            
            if (savedData) {
                const formData = JSON.parse(savedData);
                const form = document.getElementById('onboarding-form');
                
                // Set values for all inputs
                Object.keys(formData).forEach(name => {
                    const input = form.querySelector(`[name="${name}"]`);
                    
                    if (input) {
                        if (input.type === 'radio' || input.type === 'checkbox') {
                            const radioOrCheckbox = form.querySelector(`[name="${name}"][value="${formData[name]}"]`);
                            if (radioOrCheckbox) {
                                radioOrCheckbox.checked = true;
                            }
                        } else {
                            input.value = formData[name];
                        }
                    }
                });
            }
        }
        
        // Save form data when moving between sections
        document.querySelectorAll('.next-section, .prev-section').forEach(button => {
            button.addEventListener('click', saveFormData);
        });
        
        // Save form data periodically (every 30 seconds)
        setInterval(saveFormData, 30000);
        
        // Load form data when page loads
        window.addEventListener('DOMContentLoaded', loadFormData);
        
        // Save form data before submitting
        document.getElementById('onboarding-form').addEventListener('submit', saveFormData);
    });
</script>

</body>
</html>