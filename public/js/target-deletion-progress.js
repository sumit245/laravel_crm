/**
 * Target Deletion Progress Tracker
 * Handles progress tracking, polling, and resumability for target deletion operations
 */

class TargetDeletionProgress {
    constructor() {
        this.pollInterval = null;
        this.currentJobId = null;
        this.pollIntervalMs = window.TARGET_DELETION_POLL_INTERVAL || 2000;
        this.currentModalInstance = null;
        this.initialized = false;
    }

    /**
     * Initialize - check for active jobs on page load
     */
    init() {
        // Prevent duplicate initialization
        if (this.initialized) {
            return;
        }
        this.initialized = true;
        
        // Check localStorage for active job
        const savedJobId = localStorage.getItem('target_deletion_job_id');
        if (savedJobId) {
            this.checkJobStatus(savedJobId).then(active => {
                if (active) {
                    this.startProgressTracking(savedJobId);
                } else {
                    localStorage.removeItem('target_deletion_job_id');
                    // After checking localStorage, also check server
                    this.checkActiveJobs();
                }
            }).catch(() => {
                // If check fails, still check server
                this.checkActiveJobs();
            });
        } else {
            // Also check server for active jobs
            this.checkActiveJobs();
        }
    }

    /**
     * Check for active deletion jobs from server
     */
    async checkActiveJobs() {
        try {
            const response = await fetch('/projects/targets/active-deletion-jobs');
            if (!response.ok) {
                console.error('Failed to fetch active jobs:', response.status);
                return;
            }
            const data = await response.json();
            
            if (data.jobs && data.jobs.length > 0) {
                // Use the most recent job
                const latestJob = data.jobs[0];
                // Only start tracking if we don't already have a job being tracked
                if (!this.currentJobId || this.currentJobId !== latestJob.job_id) {
                    this.startProgressTracking(latestJob.job_id);
                }
            }
        } catch (error) {
            console.error('Error checking active jobs:', error);
        }
    }

    /**
     * Check if a job is still active
     */
    async checkJobStatus(jobId) {
        try {
            const response = await fetch(`/projects/targets/deletion-progress/${jobId}`);
            if (!response.ok) {
                return false;
            }
            const data = await response.json();
            const jobStatus = data.job_status || data.status;
            return jobStatus === 'pending' || jobStatus === 'processing';
        } catch (error) {
            return false;
        }
    }

    /**
     * Start progress tracking for a deletion job
     */
    startProgressTracking(jobId) {
        this.currentJobId = jobId;
        localStorage.setItem('target_deletion_job_id', jobId);
        
        // Show progress modal
        this.showProgressModal(jobId);
        
        // Start polling
        this.startPolling(jobId);
    }

    /**
     * Show progress modal
     */
    showProgressModal(jobId) {
        // Prevent duplicate modals
        if (this.currentModalInstance) {
            return; // Modal already showing
        }
        
        // Remove existing modal if any
        const existingModal = document.getElementById('targetDeletionProgressModal');
        if (existingModal) {
            const bsModal = bootstrap.Modal.getInstance(existingModal);
            if (bsModal) {
                bsModal.dispose();
            }
            existingModal.remove();
        }
        
        const modalHtml = `
            <div class="modal fade" id="targetDeletionProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="mdi mdi-delete"></i> Deleting Targets
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <p class="mb-2" id="deletionStatusMessage">Please wait while we process your request...</p>
                                <p class="text-muted small mb-3">
                                    <i class="mdi mdi-information-outline"></i> 
                                    Please do not press back or close the app
                                </p>
                            </div>
                            
                            <!-- Classic Windows-style Progress Bar -->
                            <div class="progress" style="height: 25px; border: 2px solid #ddd; border-radius: 4px;">
                                <div id="deletionProgressBar" 
                                     class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" 
                                     style="width: 0%;"
                                     aria-valuenow="0" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <span id="deletionProgressText">0%</span>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <small class="text-muted" id="deletionProgressDetails">
                                    Processing...
                                </small>
                            </div>
                            
                            <div id="deletionError" class="alert alert-danger mt-3" style="display: none;">
                                <strong>Error:</strong> <span id="deletionErrorMessage"></span>
                            </div>
                        </div>
                        <div class="modal-footer" id="deletionModalFooter" style="display: none;">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Append to body
        const body = document.body || document.getElementsByTagName('body')[0];
        if (!body) {
            console.error('Body element not found');
            return;
        }
        
        // Use a temporary container to ensure proper DOM insertion
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = modalHtml.trim();
        const modalElement = tempDiv.firstElementChild;
        
        // Append to body
        body.appendChild(modalElement);
        
        // Wait for DOM to be ready and Bootstrap to be loaded
        const showModal = () => {
            const modal = document.getElementById('targetDeletionProgressModal');
            if (!modal) {
                console.error('Modal element not found after insertion');
                return;
            }
            
            // Ensure modal is in the DOM
            if (!document.body.contains(modal)) {
                console.error('Modal not in DOM');
                return;
            }
            
            // Check if Bootstrap is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    // Remove any existing backdrop first
                    const existingBackdrop = document.querySelector('.modal-backdrop');
                    if (existingBackdrop) {
                        existingBackdrop.remove();
                    }
                    
                    // Dispose any existing instance first
                    const existingInstance = bootstrap.Modal.getInstance(modal);
                    if (existingInstance) {
                        try {
                            existingInstance.dispose();
                        } catch (e) {
                            // Ignore disposal errors
                        }
                    }
                    
                    // Wait a bit for DOM to settle
                    setTimeout(() => {
                        try {
                            // Create new modal instance
                            const bsModal = new bootstrap.Modal(modal, {
                                backdrop: 'static',
                                keyboard: false
                            });
                            
                            // Store instance to prevent duplicates
                            this.currentModalInstance = bsModal;
                            
                            // Show modal
                            bsModal.show();
                            console.log('Progress modal shown successfully');
                        } catch (error) {
                            console.error('Error creating/showing Bootstrap modal:', error);
                            // Try fallback with jQuery
                            if (typeof $ !== 'undefined' && $.fn.modal) {
                                try {
                                    $('#targetDeletionProgressModal').modal({backdrop: 'static', keyboard: false});
                                    $('#targetDeletionProgressModal').modal('show');
                                } catch (jqError) {
                                    console.error('jQuery modal fallback also failed:', jqError);
                                }
                            }
                        }
                    }, 100);
                } catch (error) {
                    console.error('Error initializing Bootstrap modal:', error);
                    // Fallback: use jQuery to show modal
                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        try {
                            $('#targetDeletionProgressModal').modal({backdrop: 'static', keyboard: false});
                            $('#targetDeletionProgressModal').modal('show');
                        } catch (jqError) {
                            console.error('jQuery modal fallback failed:', jqError);
                        }
                    }
                }
            } else {
                // Fallback: use jQuery/bootstrap3 modal if available
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    try {
                        $('#targetDeletionProgressModal').modal({backdrop: 'static', keyboard: false});
                        $('#targetDeletionProgressModal').modal('show');
                    } catch (jqError) {
                        console.error('jQuery modal failed:', jqError);
                    }
                } else {
                    console.error('Bootstrap Modal not available');
                }
            }
        };
        
        // Wait for DOM to be ready
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            // Use multiple frames to ensure DOM is fully rendered
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    showModal();
                });
            });
        } else {
            $(document).ready(() => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        showModal();
                    });
                });
            });
        }
    }

    /**
     * Start polling for progress updates
     */
    startPolling(jobId) {
        // Clear any existing interval
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }

        // Poll immediately
        this.pollProgress(jobId);

        // Then poll at intervals
        this.pollInterval = setInterval(() => {
            this.pollProgress(jobId);
        }, this.pollIntervalMs);
    }

    /**
     * Poll for progress updates
     */
    async pollProgress(jobId) {
        try {
            const response = await fetch(`/projects/targets/deletion-progress/${jobId}`);
            
            if (!response.ok) {
                console.error('Progress endpoint returned error:', response.status);
                return; // Don't stop polling, might be temporary
            }
            
            const data = await response.json();
            console.log('Poll progress response:', data);
            
            // The backend returns status: 'success' and job_status: 'pending'|'processing'|'completed'|'failed'
            // Always update progress if we have valid data (check for status or progress_percentage)
            if (data && (data.status === 'success' || data.job_status !== undefined || data.progress_percentage !== undefined)) {
                const jobStatus = data.job_status || (data.status === 'success' ? 'processing' : data.status);
                
                // Always update progress bar with the data, even if it's 0%
                this.updateProgress(data);

                // Check if completed or failed
                if (jobStatus === 'completed') {
                    this.handleCompletion(data);
                } else if (jobStatus === 'failed') {
                    this.handleFailure(data);
                }
            } else {
                console.warn('Unexpected response format:', data);
            }
        } catch (error) {
            console.error('Error polling progress:', error);
            // Don't stop polling on network errors - user might reconnect
            // This allows the progress bar to resume when connection is restored
        }
    }

    /**
     * Update progress bar and status
     */
    updateProgress(data) {
        console.log('Updating progress with data:', data);
        
        const progressBar = document.getElementById('deletionProgressBar');
        const progressText = document.getElementById('deletionProgressText');
        const statusMessage = document.getElementById('deletionStatusMessage');
        const progressDetails = document.getElementById('deletionProgressDetails');

        if (progressBar) {
            const percentage = Math.round(data.progress_percentage || 0);
            console.log('Setting progress percentage to:', percentage);
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
            
            if (progressText) {
                progressText.textContent = percentage + '%';
            }
        } else {
            console.warn('Progress bar element not found');
        }

        if (statusMessage) {
            statusMessage.textContent = data.message || 'Processing...';
        } else {
            console.warn('Status message element not found');
        }

        if (progressDetails) {
            progressDetails.textContent = 
                `Deleted ${data.processed_tasks || 0} out of ${data.total_tasks || 0} targets`;
            if (data.total_poles > 0) {
                progressDetails.textContent += ` (${data.processed_poles || 0} poles processed)`;
            }
        } else {
            console.warn('Progress details element not found');
        }
    }

    /**
     * Update progress modal with data (wrapper for updateProgress with error handling)
     */
    updateProgressModal(data) {
        // Handle error state
        if (data.status === 'failed' || data.error_message) {
            const errorDiv = document.getElementById('deletionError');
            const errorMessage = document.getElementById('deletionErrorMessage');
            
            if (errorDiv && errorMessage) {
                errorMessage.textContent = data.error_message || 'An error occurred during deletion.';
                errorDiv.style.display = 'block';
            }
            
            // Update status message
            const statusMessage = document.getElementById('deletionStatusMessage');
            if (statusMessage) {
                statusMessage.textContent = 'Deletion failed!';
                statusMessage.className = 'mb-2 text-danger';
            }
            
            // Stop animation
            const progressBar = document.getElementById('deletionProgressBar');
            if (progressBar) {
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-danger');
            }
            
            // Show close button
            const footer = document.getElementById('deletionModalFooter');
            if (footer) {
                footer.style.display = 'block';
            }
            
            // Stop polling if active
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
            
            return;
        }
        
        // Handle completion state
        if (data.status === 'completed') {
            this.handleCompletion(data);
            return;
        }
        
        // Normal progress update
        this.updateProgress(data);
    }

    /**
     * Handle completion
     */
    handleCompletion(data) {
        // Stop polling
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }

        // Update UI
        const progressBar = document.getElementById('deletionProgressBar');
        if (progressBar) {
            progressBar.classList.remove('progress-bar-animated');
            progressBar.classList.add('bg-success');
        }

        const statusMessage = document.getElementById('deletionStatusMessage');
        if (statusMessage) {
            statusMessage.textContent = 'Deletion completed successfully!';
            statusMessage.className = 'mb-2 text-success';
        }

        // Show close button
        const footer = document.getElementById('deletionModalFooter');
        if (footer) {
            footer.style.display = 'block';
        }

        // Clear localStorage
        localStorage.removeItem('target_deletion_job_id');
        this.currentJobId = null;

        // Auto-close after 3 seconds
        setTimeout(() => {
            const modalElement = document.getElementById('targetDeletionProgressModal');
            if (modalElement) {
                if (this.currentModalInstance) {
                    this.currentModalInstance.hide();
                    this.currentModalInstance = null;
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
            }
            // Reload page to refresh data
            window.location.reload();
        }, 3000);
    }

    /**
     * Handle failure
     */
    handleFailure(data) {
        // Stop polling
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }

        // Update UI
        const progressBar = document.getElementById('deletionProgressBar');
        if (progressBar) {
            progressBar.classList.remove('progress-bar-animated');
            progressBar.classList.add('bg-danger');
        }

        const statusMessage = document.getElementById('deletionStatusMessage');
        if (statusMessage) {
            statusMessage.textContent = 'Deletion failed!';
            statusMessage.className = 'mb-2 text-danger';
        }

        // Show error message
        const errorDiv = document.getElementById('deletionError');
        const errorMessage = document.getElementById('deletionErrorMessage');
        if (errorDiv && errorMessage) {
            errorMessage.textContent = data.error_message || 'An unknown error occurred';
            errorDiv.style.display = 'block';
        }

        // Show close button
        const footer = document.getElementById('deletionModalFooter');
        if (footer) {
            footer.style.display = 'block';
        }

        // Clear localStorage
        localStorage.removeItem('target_deletion_job_id');
        this.currentJobId = null;
        this.currentModalInstance = null;
    }

    /**
     * Stop polling (cleanup)
     */
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
}

// Make class available globally
window.TargetDeletionProgress = TargetDeletionProgress;

// Initialize on page load with error handling
$(document).ready(function() {
    try {
        window.targetDeletionProgress = new TargetDeletionProgress();
        window.targetDeletionProgress.init();
    } catch (error) {
        console.error('Error initializing TargetDeletionProgress:', error);
    }
});

// Global error handler for modal-related errors
window.addEventListener('error', function(event) {
    if (event.message && event.message.includes('modal') || 
        event.message && event.message.includes('bootstrap') ||
        event.filename && event.filename.includes('target-deletion-progress')) {
        console.error('Target deletion modal error:', {
            message: event.message,
            filename: event.filename,
            line: event.lineno,
            col: event.colno,
            error: event.error
        });
    }
}, true);

