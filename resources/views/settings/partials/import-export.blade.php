<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">Import / Export Defaults</h4>
        <p class="text-muted mb-0">File rules and sample format paths by module.</p>
    </div>
    <form class="settings-card-body" action="{{ route('settings.import-export.update') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table table-sm settings-table">
                <thead><tr><th>Module</th><th>Allowed Types</th><th>Max Size KB</th><th>Sample Format Path</th></tr></thead>
                <tbody>
                    @foreach($importExportSettings as $setting)
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $setting->module_key)) }}</td>
                            <td>
                                @foreach(['xlsx', 'xls', 'csv'] as $type)
                                    <label class="me-2">
                                        <input type="checkbox" name="settings[{{ $setting->module_key }}][allowed_file_types][]" value="{{ $type }}" @checked(in_array($type, $setting->allowed_file_types ?? []))>
                                        {{ strtoupper($type) }}
                                    </label>
                                @endforeach
                            </td>
                            <td><input type="number" min="1" max="51200" name="settings[{{ $setting->module_key }}][max_file_size_kb]" class="form-control form-control-sm" value="{{ $setting->max_file_size_kb }}" required></td>
                            <td><input name="settings[{{ $setting->module_key }}][sample_format_path]" class="form-control form-control-sm" value="{{ $setting->sample_format_path }}"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary mt-3">Save Import / Export Settings</button>
    </form>
</div>
