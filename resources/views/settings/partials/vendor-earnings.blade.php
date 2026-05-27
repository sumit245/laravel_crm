<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">Vendor Earnings</h4>
        <p class="text-muted mb-0">Streetlight vendor earnings are calculated from installed poles and this configured rate.</p>
    </div>
    <div class="settings-card-body">
        <form action="{{ route('settings.vendor-earnings.update') }}" method="POST" class="mb-4">
            @csrf
            <label class="form-label" for="vendorPoleRate">Global Pole Rate (₹)</label>
            <div class="d-flex align-items-center flex-wrap gap-3">
                <input type="number" step="0.01" min="0" name="pole_rate" id="vendorPoleRate"
                       class="form-control" style="max-width: 220px;"
                       value="{{ $vendorEarning->pole_rate }}" required>
                <div class="form-check form-switch mb-0" style="padding-left: 2.5em;">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="is_active" value="1" id="vendorGlobalActive"
                           style="margin-left: -2.5em;"
                           @checked($vendorEarning->is_active)>
                    <label class="form-check-label text-body" for="vendorGlobalActive">Active</label>
                </div>
                <button class="btn btn-primary">Save Global Rate</button>
            </div>
        </form>

        <h5>Project Override</h5>
        <form action="{{ route('settings.vendor-earnings.projects.update') }}" method="POST" class="row g-2 mb-3">
            @csrf
            <div class="col-md-5">
                <select name="project_id" class="form-select" required>
                    <option value="">Select project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input type="number" step="0.01" min="0" name="pole_rate" class="form-control" placeholder="Pole rate" required></div>
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Save</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Project</th><th>Pole Rate</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($vendorProjectEarnings as $setting)
                        <tr>
                            <td>{{ $setting->project?->project_name ?? 'Project #' . $setting->project_id }}</td>
                            <td>{{ number_format((float) $setting->pole_rate, 2) }}</td>
                            <td>{{ $setting->is_active ? 'Active' : 'Inactive' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No project-specific earning rates configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
