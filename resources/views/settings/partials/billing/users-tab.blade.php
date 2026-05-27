<div id="billing-tab-users" class="billing-tab-pane" role="tabpanel" aria-labelledby="billing-tab-users-label">
    <div class="billing-section-toolbar">
        <div>
            <h5 id="billing-tab-users-label" class="billing-section-title mb-1">User category assignment</h5>
            <p class="text-muted small mb-0">Link staff to a TA/DA category (vendors excluded).</p>
        </div>
    </div>

    @if ($categories->isEmpty())
        <div class="alert alert-warning" role="alert">
            Create staff categories before assigning users.
        </div>
    @endif

    @if ($users->isEmpty())
        <div class="card billing-data-card border shadow-sm">
            <div class="card-body">
                @include('settings.partials.billing._empty-state', [
                    'title' => 'No assignable users',
                    'message' => 'Staff accounts will appear here when they are available for category assignment.',
                ])
            </div>
        </div>
    @else
        <div class="card billing-data-card border shadow-sm">
            <div class="card-body">
                <x-datatable
                    id="billingUserTable"
                    title="Users"
                    :columns="[
                        ['title' => 'Name', 'width' => '25%'],
                        ['title' => 'Role', 'width' => '20%'],
                        ['title' => 'Email', 'width' => '30%'],
                        ['title' => 'Category', 'width' => '15%'],
                    ]"
                    :exportEnabled="true"
                    :importEnabled="false"
                    :bulkDeleteEnabled="false"
                    pageLength="25"
                    searchPlaceholder="Search users…">
                    @foreach ($users as $user)
                        @php
                            $displayName = trim(($user->firstName ?? '').' '.($user->lastName ?? '')) ?: ($user->email ?? '—');
                        @endphp
                        <tr>
                            <td class="billing-cell-truncate" title="{{ $displayName }}">{{ $displayName }}</td>
                            <td>{{ \App\Enums\UserRole::tryFrom((int) $user->role)?->label() ?? $user->role }}</td>
                            <td class="billing-cell-truncate" title="{{ $user->email }}">{{ $user->email }}</td>
                            <td>{{ $user->usercategory?->category_code ?? '—' }}</td>
                            <td class="text-center billing-row-actions">
                                <button type="button"
                                    class="btn btn-icon btn-warning btn-edit-user-category"
                                    title="Assign category"
                                    aria-label="Assign category for {{ $displayName }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editUserCategoryModal"
                                    data-action="{{ route('settings.billing.users.category.update', $user) }}"
                                    data-user-label="{{ $displayName }}"
                                    data-category-id="{{ $user->category }}"
                                    @disabled($categories->isEmpty())>
                                    <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </x-datatable>
            </div>
        </div>
    @endif
</div>
