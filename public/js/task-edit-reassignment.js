/**
 * Task Edit Reassignment JavaScript
 * Handles ward conflict checking, inventory warnings, and form validation
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.taskEditData === 'undefined') {
            console.warn('Task edit data not found. Skipping reassignment checks.');
            return;
        }

        const data = window.taskEditData;
        const vendorSelect = document.getElementById('vendor_id');
        const wardConflictWarning = document.getElementById('wardConflictWarning');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('taskEditForm');

        // If task is completed, disable form modifications
        if (data.taskStatus === 'Completed') {
            // Disable all assignment fields (already done in blade, but ensure)
            const assignmentFields = ['manager_id', 'engineer_id', 'vendor_id'];
            assignmentFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.disabled = true;
                }
            });
            return; // Exit early for completed tasks
        }

        // Ward conflict checking when vendor changes
        if (vendorSelect && data.wardInfo && data.checkWardConflictUrl) {
            vendorSelect.addEventListener('change', function() {
                const selectedVendorId = this.value;
                
                // Hide warning initially
                if (wardConflictWarning) {
                    wardConflictWarning.style.display = 'none';
                }

                // Skip check if no vendor selected or same vendor
                if (!selectedVendorId || selectedVendorId == data.currentVendorId) {
                    return;
                }

                // Show loading state
                const originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Checking...';
                }

                // Make AJAX call to check ward conflicts
                fetch(data.checkWardConflictUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        vendor_id: selectedVendorId,
                        project_id: data.projectId,
                        site_id: (data.wardInfo && data.wardInfo.site_id) ? data.wardInfo.site_id : null,
                        district: (data.wardInfo && data.wardInfo.district) ? data.wardInfo.district : null,
                        panchayat: (data.wardInfo && data.wardInfo.panchayat) ? data.wardInfo.panchayat : null,
                        ward: (data.wardInfo && data.wardInfo.ward) ? data.wardInfo.ward : null
                    })
                })
                .then(response => response.json())
                .then(result => {
                    // Restore submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }

                    // Show warning if conflict exists
                    if (result.has_conflict && wardConflictWarning) {
                        wardConflictWarning.style.display = 'block';
                        
                        // Update warning message with details
                        if (result.conflict_details) {
                            const details = result.conflict_details;
                            let message = '<i class="mdi mdi-alert"></i> <strong>Warning:</strong> ';
                            message += 'This vendor has completed installations in the same wards. ';
                            
                            if (details.completed_poles_count) {
                                message += `Found ${details.completed_poles_count} completed pole(s) in conflicting wards. `;
                            }
                            
                            message += 'Consider selecting a different vendor to avoid conflicts.';
                            wardConflictWarning.innerHTML = message;
                        }
                    } else if (wardConflictWarning) {
                        wardConflictWarning.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error checking ward conflicts:', error);
                    
                    // Restore submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                    
                    // Show error message
                    if (wardConflictWarning) {
                        wardConflictWarning.className = 'alert alert-danger mt-2';
                        wardConflictWarning.innerHTML = '<i class="mdi mdi-alert-circle"></i> Error checking ward conflicts. Please try again.';
                        wardConflictWarning.style.display = 'block';
                    }
                });
            });
        }

        // Form submission validation
        if (form) {
            form.addEventListener('submit', function(e) {
                // If task is completed, prevent submission of assignment changes
                if (data.taskStatus === 'Completed') {
                    e.preventDefault();
                    alert('Cannot modify assignments for completed tasks. Historical performance data must be preserved.');
                    return false;
                }

                // Validate required fields
                const managerId = document.getElementById('manager_id');
                const engineerId = document.getElementById('engineer_id');
                const vendorId = document.getElementById('vendor_id');

                let isValid = true;
                let errorMessage = '';

                if (managerId && !managerId.value) {
                    isValid = false;
                    errorMessage += 'Project Manager is required.\n';
                }

                if (engineerId && !engineerId.value) {
                    isValid = false;
                    errorMessage += 'Site Engineer is required.\n';
                }

                if (vendorId && !vendorId.value) {
                    isValid = false;
                    errorMessage += 'Vendor is required.\n';
                }

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields:\n' + errorMessage);
                    return false;
                }

                // Show loading state on submit
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Updating...';
                }

                return true;
            });
        }

        // Real-time validation feedback
        const requiredFields = ['manager_id', 'engineer_id', 'vendor_id'];
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
            }
        });
    });
})();
