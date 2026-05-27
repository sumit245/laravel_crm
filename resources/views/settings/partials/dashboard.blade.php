<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">Dashboard Defaults</h4>
        <p class="text-muted mb-0">Default filters and visible dashboard sections.</p>
    </div>
    <form class="settings-card-body" action="{{ route('settings.dashboard.update') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Default Date Filter</label>
                <select name="default_date_filter" class="form-select">
                    @foreach(['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'all_time' => 'All Time'] as $value => $label)
                        <option value="{{ $value }}" @selected($dashboardSetting->default_date_filter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Default Project Behavior</label>
                <select name="default_project_behavior" class="form-select">
                    @foreach(['first_assigned' => 'First Assigned', 'all_projects' => 'All Projects', 'none' => 'No Default'] as $value => $label)
                        <option value="{{ $value }}" @selected($dashboardSetting->default_project_behavior === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label d-block">Visible Widgets</label>
                @foreach($dashboardWidgets as $widget)
                    <label class="d-block">
                        <input type="checkbox" name="visible_widgets[]" value="{{ $widget }}" @checked(in_array($widget, $dashboardSetting->visible_widgets ?? []))>
                        {{ ucwords(str_replace('_', ' ', $widget)) }}
                    </label>
                @endforeach
            </div>
        </div>
        <button class="btn btn-primary mt-4">Save Dashboard Settings</button>
    </form>
</div>
