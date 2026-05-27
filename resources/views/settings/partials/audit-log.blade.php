<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">Settings Audit Log</h4>
        <p class="text-muted mb-0">Latest 100 settings changes.</p>
    </div>
    <div class="settings-card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Changed At</th><th>Group</th><th>Key</th><th>User</th><th>Old</th><th>New</th></tr></thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td>{{ optional($log->changed_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->setting_group }}</td>
                            <td>{{ $log->setting_key }}</td>
                            <td>{{ $log->changedBy?->email ?? $log->changed_by }}</td>
                            <td><code>{{ \Illuminate\Support\Str::limit(json_encode($log->old_value), 80) }}</code></td>
                            <td><code>{{ \Illuminate\Support\Str::limit(json_encode($log->new_value), 80) }}</code></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No settings changes recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
