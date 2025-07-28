@extends('layouts.main')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header p-4 bg-white text-white">
                    <h5 class="card-title mb-0 fw-bold text-black"><i class="bi bi-database me-2"></i> Database Backup</h5>
                </div>
                <div class="card-body p-4">
                    <div id="alertContainer"></div>

                    <div class="row">
                        <!-- Left sidebar -->
                        <div class="col-md-3 border-end pe-4">
                            <form id="backupForm">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100 mb-4 backup-btn">
                                    Take Backup
                                </button>

                                <h6 class="fw-bold mb-3">Select Projects</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll" checked>
                                    <label class="form-check-label fw-bold" for="selectAll">Select All</label>
                                </div>
                                <hr class="my-2">
                                
                                @foreach(['Project Alpha', 'Project Beta', 'Project Gamma'] as $i => $project)
                                <div class="form-check">
                                    <input class="form-check-input project-cb" type="checkbox" name="projects[]" 
                                           id="project{{$i}}" value="{{$i+1}}" checked>
                                    <label class="form-check-label" for="project{{$i}}">{{$project}}</label>
                                </div>
                                @endforeach

                                <h6 class="fw-bold mb-3 mt-4">Select Users</h6>
                                @foreach([['all', 'All Users', 'fw-bold'], ['staff', 'Staff Only'], ['vendors', 'Vendors Only']] as $i => $user)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_type" 
                                           id="{{$user[0]}}" value="{{$user[0]}}" {{$i==0?'checked':''}}>
                                    <label class="form-check-label {{$user[2]??''}}" for="{{$user[0]}}">{{$user[1]}}</label>
                                </div>
                                @endforeach

                                <label for="format" class="form-label fw-bold mt-4">Export Format</label>
                                <select class="form-select" name="format">
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="csv">CSV (.csv)</option>
                                    <option value="sql">SQL (.sql)</option>
                                </select>
                            </form>
                        </div>

                        <!-- Right content -->
                        <div class="col-md-9">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold mb-0">Previous Backups</h6>
                                <select class="form-select form-select-sm" id="sortSelect" style="width: 200px;">
                                    <option value="date_desc">Latest First</option>
                                    <option value="date_asc">Oldest First</option>
                                    <option value="name_asc">Name A-Z</option>
                                    <option value="size_desc">Size (Large to Small)</option>
                                </select>
                            </div>

                            <div class="row" id="backupsList">
                                @php
                                $backups = [
                                    ['name' => 'Full_Backup_2024_01_15.xlsx', 'size' => '2.5 MB', 'date' => '2024-01-15', 'type' => 'excel', 'sizeBytes' => 2621440],
                                    ['name' => 'Staff_Data_2024_01_14.csv', 'size' => '1.2 MB', 'date' => '2024-01-14', 'type' => 'csv', 'sizeBytes' => 1258291],
                                    ['name' => 'Vendors_Backup_2024_01_13.xlsx', 'size' => '3.1 MB', 'date' => '2024-01-13', 'type' => 'excel', 'sizeBytes' => 3250176],
                                    ['name' => 'Project_Alpha_2024_01_12.csv', 'size' => '890 KB', 'date' => '2024-01-12', 'type' => 'csv', 'sizeBytes' => 911360],
                                    ['name' => 'Complete_DB_2024_01_11.sql', 'size' => '15.7 MB', 'date' => '2024-01-11', 'type' => 'sql', 'sizeBytes' => 16459776],
                                ];
                                @endphp

                                @foreach($backups as $backup)
                                <div class="col-md-4 mb-4 backup-item" 
                                     data-date="{{$backup['date']}}" 
                                     data-name="{{$backup['name']}}" 
                                     data-size="{{$backup['sizeBytes']}}">
                                    <div class="card backup-card">
                                        <div class="card-body text-center">
                                            @if($backup['type'] == 'excel')
                                                <i class="mdi mdi-file-excel text-success" style="font-size: 3rem;"></i>
                                            @elseif($backup['type'] == 'csv')
                                                <i class="mdi mdi-file-delimited text-info" style="font-size: 3rem;"></i>
                                            @else
                                                <i class="mdi mdi-database text-warning" style="font-size: 3rem;"></i>
                                            @endif
                                            
                                            <h6 class="card-title text-truncate mt-2" title="{{$backup['name']}}">
                                                {{$backup['name']}}
                                            </h6>
                                            
                                            <small class="text-muted d-block">{{$backup['date']}}</small>
                                            <small class="text-muted d-block">{{$backup['size']}}</small>
                                            
                                            <div class="d-flex justify-content-center gap-2 mt-3">
                                                <button class="btn btn-icon btn-info download-btn" 
                                                        data-file="{{$backup['name']}}" 
                                                        data-toggle="tooltip" title="Download">
                                                    <i class="mdi mdi-download"></i>
                                                </button>
                                                <button class="btn btn-icon btn-danger delete-btn" 
                                                        data-file="{{$backup['name']}}" 
                                                        data-toggle="tooltip" title="Delete">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.project-cb').prop('checked', $(this).is(':checked'));
    });

    $('.project-cb').on('change', function() {
        const total = $('.project-cb').length;
        const checked = $('.project-cb:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });

    // Backup form submission
    $('#backupForm').on('submit', function(e) {
        e.preventDefault();
        if ($('.project-cb:checked').length === 0) {
            showAlert('Please select at least one project.', 'danger');
            return;
        }
        
        const btn = $('.backup-btn');
        btn.html('<i class="mdi mdi-loading mdi-spin me-1"></i> Creating...').prop('disabled', true);
        
        setTimeout(() => {
            btn.html('Take Backup').prop('disabled', false);
            showAlert('Backup created successfully!', 'success');
        }, 2000);
    });

    // Download functionality
    $('.download-btn').on('click', function() {
        const fileName = $(this).data('file');
        showAlert(`Download started for ${fileName}`, 'info');
    });

    // Delete functionality
    $('.delete-btn').on('click', function() {
        const fileName = $(this).data('file');
        if (confirm(`Are you sure you want to delete ${fileName}?`)) {
            $(this).closest('.backup-item').fadeOut(300, function() {
                $(this).remove();
            });
            showAlert(`${fileName} deleted successfully`, 'success');
        }
    });

    // Sorting functionality
    $('#sortSelect').on('change', function() {
        const sortBy = $(this).val();
        const $container = $('#backupsList');
        const $items = $('.backup-item').detach();
        
        $items.sort(function(a, b) {
            let aVal, bVal;
            
            switch(sortBy) {
                case 'date_desc':
                    aVal = new Date($(a).data('date'));
                    bVal = new Date($(b).data('date'));
                    return bVal - aVal;
                case 'date_asc':
                    aVal = new Date($(a).data('date'));
                    bVal = new Date($(b).data('date'));
                    return aVal - bVal;
                case 'name_asc':
                    aVal = $(a).data('name').toLowerCase();
                    bVal = $(b).data('name').toLowerCase();
                    return aVal.localeCompare(bVal);
                case 'size_desc':
                    aVal = parseInt($(a).data('size'));
                    bVal = parseInt($(b).data('size'));
                    return bVal - aVal;
                default:
                    return 0;
            }
        });
        
        $container.append($items);
    });

    // Alert function
    function showAlert(message, type) {
        const alert = `<div class="alert alert-${type} alert-dismissible fade show">
            <i class="mdi mdi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'alert-circle' : 'information'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        $('#alertContainer').html(alert);
        setTimeout(() => $('.alert').alert('close'), 3000);
    }
});
</script>
@endpush

@push('styles')
<style>

.card-title{
    font-size:25px !important;
}



/* Take Backup button - outline with bluish hover */
.backup-btn {
    border: 1px solid #6c757d;
    color: #fff;
    background-color: #1f3bb3;
    transition: all 0.3s ease;
}

.backup-btn:hover {
    background: #1f3bb3;
    border-color: #007bff;
    color: white;
    box-shadow: 0 2px 4px rgba(0,123,255,0.2);
}

.backup-btn:active {
    background-color: #0056b3;
    border-color: #0056b3;
    transform: translateY(0);
}

.backup-btn:disabled {
    background-color: transparent;
    border-color: #6c757d;
    color: #000;
    opacity: 0.6;
    transform: none;
    box-shadow: none;
}

/* Icon buttons from your index.blade.php */
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
}

/* Download button - permanent blue */
.download-btn {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.download-btn:hover {
    background-color: #138496;
    border-color: #117a8b;
    color: white;
}

/* Delete button - permanent red */
.delete-btn {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.delete-btn:hover {
    background-color: #c82333;
    border-color: #bd2130;
    color: white;
}

/* Backup card styling */
.backup-card {
    transition: transform 0.2s;
    height: 100%;
}

.backup-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.border-end {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Loading animation */
.mdi-spin {
    animation: mdi-spin 1s infinite linear;
}

@keyframes mdi-spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}.form-check .form-check-input {
    float: left;
    margin-left: 0;
}
</style>
@endpush