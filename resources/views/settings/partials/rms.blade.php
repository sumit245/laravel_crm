<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">RMS Integration</h4>
        <p class="text-muted mb-0">Remote Monitoring System endpoint and retry behavior.</p>
    </div>
    <form class="settings-card-body" action="{{ route('settings.rms.update') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Endpoint</label>
                <input type="url" name="endpoint" class="form-control" value="{{ old('endpoint', $rmsSetting->endpoint) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Auth Mode</label>
                <select name="auth_mode" class="form-select" required>
                    @foreach(['none', 'token', 'basic'] as $mode)
                        <option value="{{ $mode }}" @selected($rmsSetting->auth_mode === $mode)>{{ ucfirst($mode) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Timeout Seconds</label>
                <input type="number" min="1" max="300" name="timeout_seconds" class="form-control" value="{{ $rmsSetting->timeout_seconds }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Retry Count</label>
                <input type="number" min="0" max="10" name="retry_count" class="form-control" value="{{ $rmsSetting->retry_count }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="hidden" name="enabled" value="0">
                    <input class="form-check-input" type="checkbox" name="enabled" value="1" id="rmsEnabled" @checked($rmsSetting->enabled)>
                    <label class="form-check-label" for="rmsEnabled">RMS enabled</label>
                </div>
            </div>
        </div>
        <button class="btn btn-primary mt-4">Save RMS Settings</button>
    </form>
</div>
