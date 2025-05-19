@extends("layouts.main")

@section("content")
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Side Navigation Tabs -->
        <div class="col-md-3 col-lg-2 side-nav">
            
            
            <ul class="nav flex-column form-tabs">
            <div class="text-center p-3 mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Sugs Lloyd Ltd Logo" class="img-fluid" style="max-height: 80px;">
            </div>
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
                <div class="mb-4 border-bottom pb-2">
                    <h3 class="fw-bold">Employee Onboarding – Sugs Lloyd Ltd</h3>
                    <p class="text-muted">Please fill out all the details below carefully. All fields are mandatory.</p>
                </div>
                
                <form id="onboarding-form" method="POST" action="#" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Personal Information Section -->
                    <div class="form-section active" id="personal-info">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold">1. Personal Information</span>
                                <span class="section-indicator">Section 1 of 8</span>
                            </div>
                            <div class="card-body row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
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
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Marital Status</label>
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
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Preferred Language</label>
                                    <select name="language" class="form-control" required>
                                        <option value="">Select Language</option>
                                        <option value="English">English</option>
                                        <option value="Hindi">Hindi</option>
                                        <option value="Other">Other</option>
                                    </select>
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
                                        <label class="form-label">House/Flat No.</label>
                                        <input type="text" id="perm_house_no" name="perm_house_no" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Street/Road</label>
                                        <input type="text" id="perm_street" name="perm_street" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">City</label>
                                        <input type="text" id="perm_city" name="perm_city" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">State</label>
                                        <input type="text" id="perm_state" name="perm_state" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Country</label>
                                        <input type="text" id="perm_country" name="perm_country" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">PIN / ZIP Code</label>
                                        <input type="text" id="perm_zip" name="perm_zip" class="form-control" required>
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
                                        <label class="form-label">House/Flat No.</label>
                                        <input type="text" id="curr_house_no" name="curr_house_no" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Street/Road</label>
                                        <input type="text" id="curr_street" name="curr_street" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">City</label>
                                        <input type="text" id="curr_city" name="curr_city" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">State</label>
                                        <input type="text" id="curr_state" name="curr_state" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Country</label>
                                        <input type="text" id="curr_country" name="curr_country" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">PIN / ZIP Code</label>
                                        <input type="text" id="curr_zip" name="curr_zip" class="form-control" required>
                                    </div>
                                </div>

                                <hr>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <input type="text" name="emergency_contact_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact Phone Number</label>
                                        <input type="text" name="emergency_contact_phone" class="form-control" required>
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
            <button type="button" class="btn btn-success" onclick="addEducationEntry()">+ Add More Education</button>
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
                            <div class="card-body row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Position Applied For</label>
                                    <input type="text" name="position_applied_for" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Joining</label>
                                    <input type="date" name="date_of_joining" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Previous Employer</label>
                                    <input type="text" name="previous_employer" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Years of Experience</label>
                                    <input type="number" name="experience" step="0.1" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Notice Period</label>
                                    <input type="text" name="notice_period" class="form-control" required>
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
                                <p class="text-muted mb-4">
                                    Upload documents (PDF, JPG, PNG, max 5MB each). You can add more documents below.
                                </p>
                                <div id="documentUploads">
                                    <div class="row g-3 align-items-end mb-2">
                                        <div class="col-md-5">
                                            <label class="form-label">Document Name</label>
                                            <input type="text" name="document_name[]" class="form-control" placeholder="e.g. Resume" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Upload File</label>
                                            <input type="file" name="document_file[]" class="form-control" required>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger remove-document d-none">×</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="addMoreDocuments" class="btn btn-outline-secondary mt-2">+ Add More Documents</button>
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
                                        <label class="form-label">Do you have any disabilities?</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" class="form-check-input" name="disabilities" id="disabilities-yes" value="Yes" required>
                                                <label class="form-check-label" for="disabilities-yes">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" class="form-check-input" name="disabilities" id="disabilities-no" value="No" required>
                                                <label class="form-check-label" for="disabilities-no">No</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Are you currently employed?</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" class="form-check-input" name="currently_employed" id="employed-yes" value="Yes" required>
                                                <label class="form-check-label" for="employed-yes">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" class="form-check-input" name="currently_employed" id="employed-no" value="No" required>
                                                <label class="form-check-label" for="employed-no">No</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">If yes, reason for leaving current employment:</label>
                                        <input type="text" name="reason_for_leaving" class="form-control">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Any other information you'd like to provide:</label>
                                        <textarea name="other_info" class="form-control"></textarea>
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
                                <p class="text-muted mb-3">Upload a recent passport size photo (JPG, PNG, max 2MB).</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Upload Photo</label>
                                        <input type="file" id="passportPhotoInput" name="passport_photo" accept="image/jpeg,image/png" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="position-relative d-inline-block">
                                            <label class="form-label d-block">Preview</label>
                                            <img id="passportPhotoPreview" src="{{ asset('images/default-avatar.png') }}" alt="Passport Preview" class="img-thumbnail" style="max-height: 150px;">
                                            <button type="button" id="removePhotoButton" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="display:none;">×</button>
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
                                <div class="alert alert-info">
                                    <p class="mb-0">
                                        I hereby declare that the information provided above is true to the best of my knowledge and belief. I understand that any false information may lead to disqualification from the recruitment process.
                                    </p>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Signature</label>
                                        <input type="text" name="signature" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary prev-section" data-prev="photo">Previous</button>
                                        <i class="fas fa-check-circle"></i> Submit & Preview
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
@endsection

@push('styles')
<style>
    /* Main Layout Styles */
    .side-nav {
        background-color: #f8f9fa;
        min-height: 100vh;
        border-right: 1px solid #dee2e6;
        position: sticky;
        top: 0;
    }
    .form-check .form-check-input {
    margin-left: 0;
}
    .main-content {
        padding: 0;
        background-color: #fff;
    }
    
    /* Form Section Styles */
    .form-section {
        display: none;
    }
    
    .form-section.active {
        display: block;
    }
    
    /* Navigation Tabs Styles */
    .nav-section-title {
        background-color: #e9ecef;
        font-weight: bold;
    }
    
    .form-tabs .nav-link {
        color: #6c757d;
        padding: 0.75rem 1rem;
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    .form-tabs .nav-link.active {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
        border-left: 3px solid #0d6efd;
    }
    
    .form-tabs .nav-link.disabled {
        color: #adb5bd;
        cursor: not-allowed;
    }
    
    .form-tabs .nav-link.completed {
        color: #198754;
        border-left: 3px solid #198754;
    }
    
    .section-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #e9ecef;
        margin-right: 10px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .nav-link.active .section-number {
        background-color: #0d6efd;
        color: white;
    }
    
    .nav-link.completed .section-number {
        background-color: #198754;
        color: white;
    }
    
    .section-status .fa-circle {
        font-size: 10px;
        color: #adb5bd;
    }
    
    .nav-link.active .section-status .fa-circle {
        color: #0d6efd;
    }
    
    .nav-link.completed .section-status .fa-circle:before {
        content: "\f00c";
        font-size: 14px;
        color: #198754;
    }
    
    /* Form Watermark */
    .form-watermark {
        position: relative;
    }
    
    .form-watermark::before {
        content: "";
        background: url('{{ asset('images/logo.png') }}') no-repeat center center;
        background-size: 300px;
        opacity: 0.07;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }
    
    /* Form Elements */
    .form-label {
        font-weight: 500;
    }
    
    .form-label::after {
        content: " *";
        color: red;
    }
    
    .card-header {
        background-color: #f5f7fa;
    }
    
    .section-indicator {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    /* Document Upload */
    .remove-document {
        font-size: 24px;
        line-height: 1;
        padding: 0 10px;
        height: 38px;
    }
    
    /* Photo Upload */
    #removePhotoButton {
        border-radius: 50%;
        width: 24px;
        height: 24px;
        line-height: 16px;
        padding: 0;
        font-size: 16px;
        text-align: center;
    }
    
    /* Progress Bar */
    .form-progress {
        padding: 15px 0;
    }
    
    .progress {
        height: 8px;
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
    }
</style>
@endpush

@push('scripts')
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
                const tabLink = document.querySelector(.nav-link[data-section="${sectionId}"]);
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
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
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
            document.querySelector(.nav-link[data-section="${fromSection}"]).classList.remove('active');
            document.querySelector(.nav-link[data-section="${toSection}"]).classList.add('active');
            document.querySelector(.nav-link[data-section="${toSection}"]).classList.remove('disabled');
            
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
                    alert('Please fill in all required fields before proceeding.');
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
                const perm = document.getElementById(perm_${field});
                const curr = document.getElementById(curr_${field});
                if (this.checked) {
                    curr.value = perm.value;
                    curr.readOnly = true;
                } else {
                    curr.readOnly = false;
                }
            });
        });
        
        // Add more documents button
        document.getElementById('addMoreDocuments').addEventListener('click', function() {
            const wrapper = document.getElementById('documentUploads');
            const row = document.createElement('div');
            row.className = 'row g-3 align-items-end mb-2';
            row.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label">Document Name</label>
                    <input type="text" name="document_name[]" class="form-control" placeholder="Document Name" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Upload File</label>
                    <input type="file" name="document_file[]" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger remove-document">×</button>
                </div>
            `;
            wrapper.appendChild(row);
        });
        
        // Remove document field
        document.getElementById('documentUploads').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-document')) {
                const row = e.target.closest('.row');
                row.remove();
            }
        });
        
        // Passport photo preview
        const photoInput = document.getElementById('passportPhotoInput');
        const photoPreview = document.getElementById('passportPhotoPreview');
        const removeButton = document.getElementById('removePhotoButton');
        
        photoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
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
        
        // Form submission
        document.getElementById('onboarding-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all sections
            let allValid = true;
            sections.forEach(section => {
                if (!validateSection(section)) {
                    allValid = false;
                }
            });
            
            if (allValid) {
                // Submit the form
                this.submit();
            } else {
                alert('Please fill in all required fields in all sections before submitting.');
            }
        });
    });

    let educationCount = 0;

function addEducationEntry() {
    educationCount++;
    const container = document.getElementById('education-entries');
    const entryHTML = `
        <div class="card-body row g-3 mb-3 border p-3" id="education-entry-${educationCount}">
            <div class="col-md-4">
                <label class="form-label">Highest Qualification</label>
                <select name="education[${educationCount}][qualification]" class="form-control" required>
                    <option value="">Select Qualification</option>
                    <option value="Bachelors">Bachelors</option>
                    <option value="Masters">Masters</option>
                    <option value="PhD">PhD</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Institution Name</label>
                <input type="text" name="education[${educationCount}][institution]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Year of Graduation</label>
                <input type="text" name="education[${educationCount}][year]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Specialization</label>
                <input type="text" name="education[${educationCount}][specialization]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Additional Certifications</label>
                <input type="text" name="education[${educationCount}][certifications]" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-danger" onclick="removeEducationEntry(${educationCount})">Remove</button>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', entryHTML);
}

function removeEducationEntry(id) {
    const entry = document.getElementById(education-entry-${id});
    if (entry) entry.remove();
}

// Add initial entry by default
window.onload = addEducationEntry;
</script>
@endpush