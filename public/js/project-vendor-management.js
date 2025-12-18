/**
 * Project Vendor Management JavaScript
 * Handles AJAX operations, search, filtering, and UI interactions
 */

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Update bulk action buttons visibility and counts
function updateVendorBulkActions() {
    // Assigned vendors
    const assignedCheckboxes = document.querySelectorAll('.vendor-checkbox-assigned:checked');
    const assignedBulkActions = document.getElementById('assignedVendorBulkActions');
    const assignedSelectedCount = document.getElementById('assignedVendorSelectedCount');
    
    if (assignedCheckboxes.length > 0) {
        assignedBulkActions.classList.add('show');
        assignedSelectedCount.textContent = `${assignedCheckboxes.length} selected`;
    } else {
        assignedBulkActions.classList.remove('show');
    }
    
    // Available vendors
    const availableCheckboxes = document.querySelectorAll('.vendor-checkbox-available:checked');
    const availableBulkActions = document.getElementById('availableVendorBulkActions');
    const availableSelectedCount = document.getElementById('availableVendorSelectedCount');
    
    if (availableCheckboxes.length > 0) {
        availableBulkActions.classList.add('show');
        availableSelectedCount.textContent = `${availableCheckboxes.length} selected`;
    } else {
        availableBulkActions.classList.remove('show');
    }
    
    updateVendorTotalCounts();
}

// Update total counts
function updateVendorTotalCounts() {
    const assignedTotal = document.querySelectorAll('.vendor-checkbox-assigned:not([style*="display: none"])').length;
    const availableTotal = document.querySelectorAll('.vendor-checkbox-available:not([style*="display: none"])').length;
    
    const assignedCountEl = document.getElementById('assignedVendorTotalCount');
    const availableCountEl = document.getElementById('availableVendorTotalCount');
    
    if (assignedCountEl) {
        assignedCountEl.textContent = assignedTotal;
    }
    if (availableCountEl) {
        availableCountEl.textContent = availableTotal;
    }
}

// Filter vendors by search
const filterVendors = debounce(function() {
    const searchTerm = document.getElementById('vendorSearch').value.toLowerCase();
    const vendorItems = document.querySelectorAll('#availableVendorContainer .vendor-item');
    
    let visibleCount = 0;
    
    vendorItems.forEach(item => {
        const vendorName = item.dataset.name || '';
        const matchesSearch = !searchTerm || vendorName.includes(searchTerm);
        
        if (matchesSearch) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    updateVendorTotalCounts();
}, 300);

// Assign vendor to project
function assignVendor(vendorIds, vendorName) {
    if (!Array.isArray(vendorIds)) {
        vendorIds = [vendorIds];
    }
    
    // Get fresh CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken || '';
    
    // Ensure projectId is available
    const currentProjectId = window.projectId;
    
    if (!currentProjectId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Project ID not found. Please refresh the page and try again.'
        });
        return;
    }
    
    if (!token) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'CSRF token not found. Please refresh the page and try again.'
        });
        return;
    }
    
    Swal.fire({
        title: 'Assign Vendor',
        html: `Assign <strong>${vendorName}</strong> to this project?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Assign',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Create FormData for better CSRF token handling
            const formData = new FormData();
            vendorIds.forEach(id => {
                formData.append('user_ids[]', id);
            });
            formData.append('_token', token);
            
            return fetch(`/projects/${currentProjectId}/assign-vendors`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to assign vendor');
                    }).catch(err => {
                        throw new Error(err.message || 'Failed to assign vendor');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to assign vendor');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            showToast('success', result.value.message || 'Vendor assigned successfully');
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    });
}

// Remove vendor from project
function removeVendor(vendorIds, vendorName) {
    if (!Array.isArray(vendorIds)) {
        vendorIds = [vendorIds];
    }
    
    // Get fresh CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken || '';
    
    // Ensure projectId is available
    const currentProjectId = window.projectId;
    
    if (!currentProjectId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Project ID not found. Please refresh the page and try again.'
        });
        return;
    }
    
    if (!token) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'CSRF token not found. Please refresh the page and try again.'
        });
        return;
    }
    
    const vendorText = vendorIds.length === 1 ? vendorName : `${vendorIds.length} vendors`;
    
    Swal.fire({
        title: 'Remove Vendor',
        html: `Remove <strong>${vendorText}</strong> from this project?<br><br>
               <small class="text-muted">Their targets will be reassigned to the Project Manager.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Create FormData for better CSRF token handling
            const formData = new FormData();
            vendorIds.forEach(id => {
                formData.append('user_ids[]', id);
            });
            formData.append('_token', token);
            
            return fetch(`/projects/${currentProjectId}/remove-vendors`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to remove vendor');
                    }).catch(err => {
                        throw new Error(err.message || 'Failed to remove vendor');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to remove vendor');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const message = result.value.message || 'Vendor removed successfully';
            const targetsReassigned = result.value.targets_reassigned || 0;
            
            let finalMessage = message;
            if (targetsReassigned > 0) {
                finalMessage += ` ${targetsReassigned} target(s) reassigned.`;
            }
            
            showToast('success', finalMessage);
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    });
}

// Assign selected vendors
function assignSelectedVendors() {
    const selectedCheckboxes = document.querySelectorAll('.vendor-checkbox-available:checked');
    const vendorIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
    
    if (vendorIds.length === 0) {
        showToast('warning', 'Please select at least one vendor');
        return;
    }
    
    const vendorNames = Array.from(selectedCheckboxes).map(cb => {
        return cb.closest('.vendor-item').querySelector('.vendor-name').textContent;
    }).join(', ');
    
    assignVendor(vendorIds, vendorIds.length === 1 ? vendorNames : `${vendorIds.length} vendors`);
}

// Remove selected vendors
function removeSelectedVendors() {
    const selectedCheckboxes = document.querySelectorAll('.vendor-checkbox-assigned:checked');
    const vendorIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
    
    if (vendorIds.length === 0) {
        showToast('warning', 'Please select at least one vendor');
        return;
    }
    
    const vendorNames = Array.from(selectedCheckboxes).map(cb => {
        return cb.closest('.vendor-item').querySelector('.vendor-name').textContent;
    }).join(', ');
    
    removeVendor(vendorIds, vendorIds.length === 1 ? vendorNames : `${vendorIds.length} vendors`);
}

// Show toast notification
function showToast(icon, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

// Show loading overlay
function showVendorLoading(section) {
    const overlay = document.getElementById(`${section}LoadingOverlay`);
    if (overlay) {
        overlay.classList.add('show');
    }
}

// Hide loading overlay
function hideVendorLoading(section) {
    const overlay = document.getElementById(`${section}LoadingOverlay`);
    if (overlay) {
        overlay.classList.remove('show');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateVendorTotalCounts();
});

// Make functions globally available
window.updateVendorBulkActions = updateVendorBulkActions;
window.filterVendors = filterVendors;
window.assignVendor = assignVendor;
window.removeVendor = removeVendor;
window.assignSelectedVendors = assignSelectedVendors;
window.removeSelectedVendors = removeSelectedVendors;
window.showToast = showToast;

