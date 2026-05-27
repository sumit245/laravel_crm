<style>
    .project-poles-filter-card .form-label {
        font-size: 0.875rem;
        font-weight: 600;
    }

    .project-pole-active-filters-label {
        color: #212529;
        font-weight: 600;
    }

    .project-pole-active-badge {
        background-color: #0b5ed7 !important;
        color: #fff !important;
        border: 1px solid #0a58ca !important;
        font-weight: 600 !important;
        font-size: 0.8125rem !important;
    }

    .rms-status-indicator {
        position: relative;
    }

    .rms-progress-bar {
        width: 100%;
        height: 20px;
        background-color: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
        display: flex;
    }

    .rms-success-bar {
        background-color: #28a745;
        height: 100%;
        transition: width 0.3s;
    }

    .rms-error-bar {
        background-color: #dc3545;
        height: 100%;
        transition: width 0.3s;
    }

    .rms-status-text {
        display: block;
        margin-top: 4px;
        font-size: 0.85em;
    }

    .badge.badge-readable {
        background-color: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #adb5bd !important;
        font-weight: 600 !important;
        padding: 0.35em 0.65em !important;
        display: inline-block !important;
    }

    .badge.badge-readable.badge-no,
    .badge.badge-readable.badge-not-pushed {
        background-color: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #adb5bd !important;
    }

    #installed-poles .table-responsive,
    #surveyed-poles .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
