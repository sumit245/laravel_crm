/**
 * Project Staff Management JavaScript
 * Handles AJAX operations, search, filtering, and UI interactions
 */

// These will be set from the blade template as window variables
// Access via window.projectId and window.csrfToken

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

// Toggle role group collapse/expand
function toggleRoleGroup(element) {
    const roleGroup = element.closest('.role-group');
    roleGroup.classList.toggle('collapsed');
}

// Toggle all staff in a role group
function toggleRoleSelection(checkbox) {
    const roleValue = checkbox.dataset.role;
    const isChecked = checkbox.checked;
    const roleGroup = checkbox.closest('.role-group');
    const staffCheckboxes = roleGroup.querySelectorAll('.staff-checkbox');
    
    staffCheckboxes.forEach(cb => {
        cb.checked = isChecked;
    });
    
    updateBulkActions();
}

// Update bulk action buttons visibility and counts
function updateBulkActions() {
    // Assigned staff
    const assignedCheckboxes = document.querySelectorAll('.staff-checkbox-assigned:checked');
    const assignedBulkActions = document.getElementById('assignedBulkActions');
    const assignedSelectedCount = document.getElementById('assignedSelectedCount');
    
    if (assignedCheckboxes.length > 0) {
        assignedBulkActions.classList.add('show');
        assignedSelectedCount.textContent = `${assignedCheckboxes.length} selected`;
    } else {
        assignedBulkActions.classList.remove('show');
    }
    
    // Available staff
    const availableCheckboxes = document.querySelectorAll('.staff-checkbox-available:checked');
    const availableBulkActions = document.getElementById('availableBulkActions');
    const availableSelectedCount = document.getElementById('availableSelectedCount');
    
    if (availableCheckboxes.length > 0) {
        availableBulkActions.classList.add('show');
        availableSelectedCount.textContent = `${availableCheckboxes.length} selected`;
    } else {
        availableBulkActions.classList.remove('show');
    }
    
    updateTotalCounts();
}

// Update total counts
function updateTotalCounts() {
    const assignedTotal = document.querySelectorAll('.staff-checkbox-assigned').length;
    const availableTotal = document.querySelectorAll('.staff-checkbox-available:not([style*="display: none"])').length;
    
    document.getElementById('assignedTotalCount').textContent = assignedTotal;
    document.getElementById('availableTotalCount').textContent = availableTotal;
}

// Filter staff by search and role
const filterStaff = debounce(function() {
    const searchTerm = document.getElementById('staffSearch').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const staffItems = document.querySelectorAll('#availableStaffContainer .staff-item');
    
    let visibleCount = 0;
    
    staffItems.forEach(item => {
        const staffName = item.dataset.name || '';
        const staffRole = item.dataset.role || '';
        const matchesSearch = !searchTerm || staffName.includes(searchTerm);
        const matchesRole = !roleFilter || staffRole === roleFilter;
        
        if (matchesSearch && matchesRole) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Hide/show role groups based on visible items
    document.querySelectorAll('#availableStaffContainer .role-group').forEach(group => {
        const roleValue = group.dataset.role;
        const visibleInGroup = Array.from(group.querySelectorAll('.staff-item')).some(item => {
            return item.style.display !== 'none';
        });
        
        if (visibleInGroup) {
            group.style.display = '';
        } else {
            group.style.display = roleFilter && roleFilter !== roleValue ? 'none' : '';
        }
    });
    
    updateTotalCounts();
}, 300);

// Assign staff to project
function assignStaff(userIds, staffName) {
    if (!Array.isArray(userIds)) {
        userIds = [userIds];
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
        title: 'Assign Staff',
        html: `Assign <strong>${staffName}</strong> to this project?`,
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
            userIds.forEach(id => {
                formData.append('user_ids[]', id);
            });
            formData.append('_token', token);
            
            return fetch(`/projects/${currentProjectId}/assign-users`, {
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
                        throw new Error(data.message || 'Failed to assign staff');
                    }).catch(err => {
                        throw new Error(err.message || 'Failed to assign staff');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to assign staff');
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
            showToast('success', result.value.message || 'Staff assigned successfully');
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    });
}

// Remove staff from project
function removeStaff(userIds, staffName) {
    if (!Array.isArray(userIds)) {
        userIds = [userIds];
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
    
    const staffText = userIds.length === 1 ? staffName : `${userIds.length} staff members`;
    
    Swal.fire({
        title: 'Remove Staff',
        html: `Remove <strong>${staffText}</strong> from this project?<br><br>
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
            userIds.forEach(id => {
                formData.append('user_ids[]', id);
            });
            formData.append('_token', token);
            
            return fetch(`/projects/${currentProjectId}/remove-staff`, {
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
                        throw new Error(data.message || 'Failed to remove staff');
                    }).catch(err => {
                        throw new Error(err.message || 'Failed to remove staff');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to remove staff');
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
            const message = result.value.message || 'Staff removed successfully';
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

// Assign selected staff
function assignSelectedStaff() {
    const selectedCheckboxes = document.querySelectorAll('.staff-checkbox-available:checked');
    const userIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
    
    if (userIds.length === 0) {
        showToast('warning', 'Please select at least one staff member');
        return;
    }
    
    const staffNames = Array.from(selectedCheckboxes).map(cb => {
        return cb.closest('.staff-item').querySelector('.staff-name').textContent;
    }).join(', ');
    
    assignStaff(userIds, userIds.length === 1 ? staffNames : `${userIds.length} staff members`);
}

// Remove selected staff
function removeSelectedStaff() {
    const selectedCheckboxes = document.querySelectorAll('.staff-checkbox-assigned:checked');
    const userIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
    
    if (userIds.length === 0) {
        showToast('warning', 'Please select at least one staff member');
        return;
    }
    
    const staffNames = Array.from(selectedCheckboxes).map(cb => {
        return cb.closest('.staff-item').querySelector('.staff-name').textContent;
    }).join(', ');
    
    removeStaff(userIds, userIds.length === 1 ? staffNames : `${userIds.length} staff members`);
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
function showLoading(section) {
    const overlay = document.getElementById(`${section}LoadingOverlay`);
    if (overlay) {
        overlay.classList.add('show');
    }
}

// Hide loading overlay
function hideLoading(section) {
    const overlay = document.getElementById(`${section}LoadingOverlay`);
    if (overlay) {
        overlay.classList.remove('show');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotalCounts();
});

// Make functions globally available
window.toggleRoleGroup = toggleRoleGroup;
window.toggleRoleSelection = toggleRoleSelection;
window.updateBulkActions = updateBulkActions;
window.filterStaff = filterStaff;
window.assignStaff = assignStaff;
window.removeStaff = removeStaff;
window.assignSelectedStaff = assignSelectedStaff;
window.removeSelectedStaff = removeSelectedStaff;

