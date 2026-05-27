<script>
document.addEventListener('DOMContentLoaded', function () {
    const billingModalMap = {
        add_vehicle: 'addVehicleModal',
        edit_vehicle: 'editVehicleModal',
        add_category: 'addCategoryModal',
        edit_category: 'editCategoryModal',
        edit_user_category: 'editUserCategoryModal',
        edit_city: 'editCityModal',
    };

    function parseVehicleIds(raw) {
        if (!raw) {
            return [];
        }
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed.map(function (id) { return parseInt(id, 10); }) : [];
        } catch (error) {
            return [];
        }
    }

    function syncBillingActionField(form) {
        const actionField = form.querySelector('input[name="_billing_action"]');
        if (actionField && form.action) {
            actionField.value = form.action;
        }
    }

    function restoreEditFormActionsFromOldInput() {
        document.querySelectorAll('.billing-modal-form').forEach(function (form) {
            const actionField = form.querySelector('input[name="_billing_action"]');
            if (actionField && actionField.value) {
                form.action = actionField.value;
            }
        });
    }

    function bindModalFormSubmitGuard() {
        document.querySelectorAll('.billing-modal-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                syncBillingActionField(form);
                const submitButtons = form.querySelectorAll('.billing-submit-btn');
                submitButtons.forEach(function (button) {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                    if (!button.dataset.originalLabel) {
                        button.dataset.originalLabel = button.textContent.trim();
                    }
                    button.textContent = 'Saving…';
                });
            });
        });

        document.querySelectorAll('.billing-delete-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const submitButton = form.querySelector('button[type="submit"]');
                if (!submitButton) {
                    return;
                }
                submitButton.disabled = true;
                submitButton.setAttribute('aria-busy', 'true');
            });
        });
    }

    function openBillingModalFromValidation() {
        @if (!empty($billingModalToOpen))
            const modalEl = document.getElementById(@json($billingModalToOpen));
            if (modalEl && window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        @endif
    }

    const editVehicleModal = document.getElementById('editVehicleModal');
    if (editVehicleModal) {
        editVehicleModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }
            const form = document.getElementById('editVehicleForm');
            form.action = button.getAttribute('data-action') || '';
            syncBillingActionField(form);
            document.getElementById('edit_vehicle_name').value = button.getAttribute('data-vehicle-name') || '';
            document.getElementById('edit_vehicle_category').value = button.getAttribute('data-category') || '';
            document.getElementById('edit_vehicle_sub_category').value = button.getAttribute('data-sub-category') || '';
            document.getElementById('edit_vehicle_rate').value = button.getAttribute('data-rate') || '';
        });
    }

    const editCategoryModal = document.getElementById('editCategoryModal');
    if (editCategoryModal) {
        editCategoryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }
            const form = document.getElementById('editCategoryForm');
            form.action = button.getAttribute('data-action') || '';
            syncBillingActionField(form);
            document.getElementById('edit_category_code').value = button.getAttribute('data-category-code') || '';
            document.getElementById('edit_category_name').value = button.getAttribute('data-name') || '';
            document.getElementById('edit_category_description').value = button.getAttribute('data-description') || '';
            document.getElementById('edit_category_city').value = button.getAttribute('data-city-category') || '0';
            document.getElementById('edit_category_daily').value = button.getAttribute('data-daily-amount') || '';

            const vehicleIds = parseVehicleIds(button.getAttribute('data-vehicle-ids'));
            const select = document.getElementById('edit_category_vehicles');
            if (select) {
                Array.from(select.options).forEach(function (option) {
                    option.selected = vehicleIds.includes(parseInt(option.value, 10));
                });
            }
        });
    }

    const editUserCategoryModal = document.getElementById('editUserCategoryModal');
    if (editUserCategoryModal) {
        editUserCategoryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }
            const form = document.getElementById('editUserCategoryForm');
            form.action = button.getAttribute('data-action') || '';
            syncBillingActionField(form);
            document.getElementById('edit_user_category_label').textContent = button.getAttribute('data-user-label') || '';
            const categorySelect = document.getElementById('edit_user_category_id');
            if (categorySelect) {
                categorySelect.value = button.getAttribute('data-category-id') || '';
            }
        });
    }

    const editCityModal = document.getElementById('editCityModal');
    if (editCityModal) {
        editCityModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }
            const form = document.getElementById('editCityForm');
            form.action = button.getAttribute('data-action') || '';
            syncBillingActionField(form);
            document.getElementById('edit_city_name').value = button.getAttribute('data-city-name') || '';
            document.getElementById('edit_city_category').value = button.getAttribute('data-city-category') || '0';
        });
    }

    restoreEditFormActionsFromOldInput();
    bindModalFormSubmitGuard();
    openBillingModalFromValidation();
});
</script>
