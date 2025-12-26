<div>
    @if (session('success') || session('error'))
        <div class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }} alert-dismissible fade show"
            role="alert">
            {{ session('success') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stock Summary Cards -->
    @if ($project->project_type == 1)
        <div class="row my-2">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="metric-card-initial">
                    <div class="metric-card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="font-10 text-uppercase mg-b-10 fw-bold text-muted">Initial Stock
                                    Value</label>
                                <h5 class="metric-card-title mb-0">{{ number_format($initialStockValue, 2) }}</h5>
                            </div>
                            <div class="text-primary">
                                <i class="mdi mdi-package-variant" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="metric-card-instore">
                    <div class="metric-card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="font-10 text-uppercase mg-b-10 fw-bold text-muted">In Store Stock
                                    Value</label>
                                <h5 class="metric-card-title mb-0">{{ number_format($inStoreStockValue, 2) }}</h5>
                            </div>
                            <div class="text-success">
                                <i class="mdi mdi-warehouse" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="metric-card-dispatched">
                    <div class="metric-card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="font-10 text-uppercase mg-b-10 fw-bold text-muted">Dispatched Stock
                                    Value</label>
                                <h5 class="metric-card-title mb-0">{{ number_format($dispatchedStockValue, 2) }}</h5>
                            </div>
                            <div class="text-warning">
                                <i class="mdi mdi-truck-delivery" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-end">
                @if (auth()->user()->role === \App\Enums\UserRole::ADMIN->value)
                    <button id="addStoreButton" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2"
                        style="max-height: 2.8rem;">
                        <i class="mdi mdi-plus-circle"></i>
                        <span>Create Store</span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Store Creation Form -->
    <div id="storeFormContainer" class="row my-3" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">Create Store</h6>
                    <form id="storeForm" action="{{ route('store.create', $project->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="name" class="form-label">Store Name</label>
                                <input type="text" class="form-control form-control-sm" id="name" name="name"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control form-control-sm" id="address" name="address" rows="2" required></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="storeIncharge" class="form-label">Store Incharge</label>
                                <select class="form-select form-select-sm" id="storeIncharge" name="storeIncharge"
                                    required>
                                    <option value="">Select Incharge</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->firstName }}
                                            {{ $user->lastName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" id="cancelStoreButton"
                                class="btn btn-secondary btn-sm me-2">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save Store</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stores List -->
    <div id="storeList" class="my-3">
        <h6 class="mb-3">Stores</h6>
        @if ($stores->isEmpty())
            <p class="text-muted">No stores available. @if (auth()->user()->role === \App\Enums\UserRole::ADMIN->value)
                    Click "Create Store" to add one.
                @endif
            </p>
        @else
            <div class="row">
                @foreach ($stores as $store)
                    <div class="col-md-3 mb-3">
                        <div class="card" style="cursor: pointer;"
                            onclick="window.location.href='{{ route('store.show', $store->id) }}'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <h6 class="card-title mb-2 fw-normal" style="font-size: 0.95rem;">{{ $store->store_name }}</h6>
                                        <div class="d-flex align-items-start text-muted mb-2" style="font-size: 0.75rem;">
                                            <i class="mdi mdi-map-marker me-1 mt-1" style="font-size: 0.875rem; flex-shrink: 0;"></i>
                                            <span class="text-truncate" style="line-height: 1.3; min-width: 0; flex: 1 1 auto;">{{ $store->address }}</span>
                                        </div>
                                    </div>
                                    <i class="mdi mdi-chevron-right text-muted ms-2" style="flex-shrink: 0;"></i>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted" style="font-size: 0.7rem;">Incharge: </small>
                                    <small class="fw-normal" style="font-size: 0.75rem;">{{ $store->storeIncharge->firstName ?? 'N/A' }}
                                        {{ $store->storeIncharge->lastName ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('styles')
    <style>
        /* Metric Cards - Enhanced Visual Distinction */
        .row.my-2 .metric-card-initial,
        .row.my-2 .metric-card-instore,
        .row.my-2 .metric-card-dispatched {
            border-radius: 8px;
            border: 2px solid;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* Initial Stock Value Card - Blue Theme */
        .row.my-2 .metric-card-initial {
            border-color: #007bff;
            background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
        }

        .row.my-2 .metric-card-initial:hover {
            border-color: #0056b3;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
            transform: translateY(-2px);
        }

        /* In Store Stock Value Card - Green Theme */
        .row.my-2 .metric-card-instore {
            border-color: #28a745;
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        }

        .row.my-2 .metric-card-instore:hover {
            border-color: #1e7e34;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
            transform: translateY(-2px);
        }

        /* Dispatched Stock Value Card - Orange/Amber Theme */
        .row.my-2 .metric-card-dispatched {
            border-color: #ffc107;
            background: linear-gradient(135deg, #ffffff 0%, #fffbf0 100%);
        }

        .row.my-2 .metric-card-dispatched:hover {
            border-color: #e0a800;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
            transform: translateY(-2px);
        }

        .row.my-2 .metric-card-body {
            padding: 1.25rem 1.5rem;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .row.my-2 .metric-card-body>div {
            width: 100%;
        }

        .row.my-2 .metric-card-body .d-flex {
            align-items: center;
            gap: 1rem;
        }

        .row.my-2 .metric-card-body .d-flex>div:first-child {
            flex: 1 1 auto;
            min-width: 0;
        }

        .row.my-2 .metric-card-body .d-flex>div:last-child {
            flex-shrink: 0;
        }

        .row.my-2 .metric-card-body i {
            display: block;
        }

        .row.my-2 .metric-card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .row.my-2 [class*="col-"] .card .card-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            color: #212529 !important;
            margin-bottom: 0 !important;
            line-height: 1.2 !important;
        }

        .row.my-2 [class*="col-"] .card label {
            font-size: 0.75rem !important;
            letter-spacing: 0.5px !important;
            color: #6c757d !important;
            margin-bottom: 0.5rem !important;
            display: block !important;
            font-weight: 600 !important;
        }

        /* Ensure equal height for metric cards */
        .row.my-2 {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: stretch !important;
        }

        .row.my-2>[class*="col-"] {
            display: flex !important;
            flex-direction: column !important;
        }

        /* Consistent button width for Add buttons */
        #addStoreButton,
        .add-target-btn {
            min-width: 140px;
        }

        /* Store Cards - Higher Specificity */
        #storeList .row {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: stretch !important;
        }

        #storeList .row>[class*="col-"] {
            display: flex !important;
            flex-direction: column !important;
        }

        #storeList .card,
        #storeList .row .card {
            border-radius: 8px !important;
            border: 1px solid #e3e6f0 !important;
            background: #ffffff !important;
            transition: all 0.2s ease !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
        }

        #storeList .card:hover,
        #storeList .row .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            transform: translateY(-2px) !important;
            border-color: #ced4da !important;
        }

        #storeList .card .card-body,
        #storeList .row .card .card-body {
            padding: 1rem 1.25rem !important;
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* Store card title - primary, normal weight */
        #storeList .card .card-title {
            font-weight: 500 !important;
            font-size: 0.95rem !important;
            color: #212529 !important;
            margin-bottom: 0.5rem !important;
        }

        /* Address with icon - truncate on overflow */
        #storeList .card .d-flex .text-truncate {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        /* Incharge text - secondary, normal weight */
        #storeList .card small.fw-normal {
            font-weight: 400 !important;
        }

        /* Form Card - Higher Specificity */
        #storeFormContainer .card {
            border-radius: 8px !important;
            border: 1px solid #e3e6f0 !important;
            background: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }

        #storeFormContainer .card .card-body {
            padding: 1.5rem !important;
        }

        /* Buttons - Higher Specificity */
        #storeFormContainer .btn,
        #storeList .btn,
        .row.my-2 .btn {
            border-radius: 4px !important;
            font-weight: 500 !important;
        }

        #storeFormContainer .btn-sm,
        #storeList .btn-sm,
        .row.my-2 .btn-sm {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
        }

        #storeFormContainer .form-control-sm,
        #storeFormContainer .form-select-sm,
        #storeList .form-control-sm,
        #storeList .form-select-sm {
            border-radius: 4px !important;
            font-size: 0.875rem !important;
        }

        #storeFormContainer .input-group-sm .form-control,
        #storeFormContainer .input-group-sm .btn,
        #storeList .input-group-sm .form-control,
        #storeList .input-group-sm .btn {
            border-radius: 4px !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const addStoreButton = document.getElementById("addStoreButton");
            const cancelStoreButton = document.getElementById("cancelStoreButton");
            const storeFormContainer = document.getElementById("storeFormContainer");

            // Only add event listeners if elements exist
            if (addStoreButton && storeFormContainer) {
                addStoreButton.addEventListener("click", (e) => {
                    e.stopPropagation();
                    storeFormContainer.style.display = "block";
                    addStoreButton.style.display = "none";
                });
            }

            if (cancelStoreButton && storeFormContainer && addStoreButton) {
                cancelStoreButton.addEventListener("click", () => {
                    storeFormContainer.style.display = "none";
                    if (addStoreButton) {
                        addStoreButton.style.display = "block";
                    }
                });
            }
        });
    </script>
@endpush
