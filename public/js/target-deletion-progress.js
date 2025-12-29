/**
 * Target Deletion Progress Tracker
 * Handles progress tracking, polling, and resumability for target deletion operations
 */

class TargetDeletionProgress {
    constructor() {
        this.pollInterval = null;
        this.currentJobId = null;
        this.pollIntervalMs = window.TARGET_DELETION_POLL_INTERVAL || 2000;
    }

    /**
     * Initialize - check for active jobs on page load
     */
    init() {
        // Check localStorage for active job
        const savedJobId = localStorage.getItem('target_deletion_job_id');
        if (savedJobId) {
            this.checkJobStatus(savedJobId).then(active => {
                if (active) {
                    this.startProgressTracking(savedJobId);
                } else {
                    localStorage.removeItem('target_deletion_job_id');
                }
            });
        }

        // Also check server for active jobs
        this.checkActiveJobs();
    }

    /**
     * Check for active deletion jobs from server
     */
    async checkActiveJobs() {
        try {
            const response = await fetch('/projects/targets/active-deletion-jobs');
            const data = await response.json();
            
            if (data.jobs && data.jobs.length > 0) {
                // Use the most recent job
                const latestJob = data.jobs[0];
                this.startProgressTracking(latestJob.job_id);
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
            const data = await response.json();
            return data.status === 'pending' || data.status === 'processing';
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
        // Remove existing modal if any
        $('#targetDeletionProgressModal').remove();
        
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
                                    Please do not refresh or close the page
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
        
        $('body').append(modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('targetDeletionProgressModal'));
        modal.show();
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
            const data = await response.json();

            // Update progress bar
            this.updateProgress(data);

            // Check if completed or failed
            if (data.status === 'completed') {
                this.handleCompletion(data);
            } else if (data.status === 'failed') {
                this.handleFailure(data);
            }
        } catch (error) {
            console.error('Error polling progress:', error);
            // Don't stop polling on network errors - user might reconnect
        }
    }

    /**
     * Update progress bar and status
     */
    updateProgress(data) {
        const progressBar = document.getElementById('deletionProgressBar');
        const progressText = document.getElementById('deletionProgressText');
        const statusMessage = document.getElementById('deletionStatusMessage');
        const progressDetails = document.getElementById('deletionProgressDetails');

        if (progressBar) {
            const percentage = Math.round(data.progress_percentage || 0);
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
            
            if (progressText) {
                progressText.textContent = percentage + '%';
            }
        }

        if (statusMessage) {
            statusMessage.textContent = data.message || 'Processing...';
        }

        if (progressDetails) {
            progressDetails.textContent = 
                `Deleted ${data.processed_tasks || 0} out of ${data.total_tasks || 0} targets`;
            if (data.total_poles > 0) {
                progressDetails.textContent += ` (${data.processed_poles || 0} poles processed)`;
            }
        }
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
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
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

// Initialize on page load
$(document).ready(function() {
    window.targetDeletionProgress = new TargetDeletionProgress();
    window.targetDeletionProgress.init();
});

