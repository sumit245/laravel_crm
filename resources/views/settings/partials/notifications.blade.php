<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">Notification Settings</h4>
        <p class="text-muted mb-0">Control event recipients and enabled channels.</p>
    </div>
    <form class="settings-card-body" action="{{ route('settings.notifications.update') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table table-sm settings-table">
                <thead><tr><th>Event</th><th>Enabled</th><th>Recipients</th><th>Database</th><th>Email</th><th>WhatsApp</th></tr></thead>
                <tbody>
                    @foreach($notificationSettings as $setting)
                        <tr>
                            <td>{{ $setting->event_key }}</td>
                            <td>
                                <input type="hidden" name="settings[{{ $setting->event_key }}][enabled]" value="0">
                                <input type="checkbox" name="settings[{{ $setting->event_key }}][enabled]" value="1" @checked($setting->enabled)>
                            </td>
                            <td>
                                <select name="settings[{{ $setting->event_key }}][recipient_roles][]" class="form-select form-select-sm" multiple>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->value }}" @selected(in_array($role->value, $setting->recipient_roles ?? []))>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                            </td>
                            @foreach(['database_enabled' => 'Database', 'mail_enabled' => 'Email', 'whatsapp_enabled' => 'WhatsApp'] as $field => $label)
                                <td>
                                    <input type="hidden" name="settings[{{ $setting->event_key }}][{{ $field }}]" value="0">
                                    <input type="checkbox" name="settings[{{ $setting->event_key }}][{{ $field }}]" value="1" @checked($setting->{$field})>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary mt-3">Save Notifications</button>
    </form>
</div>
