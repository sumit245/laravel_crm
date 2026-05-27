<script>
    function projectPoleLocateOnMap(lat, lng) {
        if (lat && lng) {
            window.open('https://www.google.com/maps?q=' + lat + ',' + lng, '_blank');
        } else {
            alert('Location coordinates not available.');
        }
    }

    (function () {
        const projectShowBaseUrl = @json(route('projects.show', $project));
        const projectId = @json($project->id);
        const poleGeoFilters = @json($poleGeoFilters ?? []);
        const poleDeepLinkParams = @json($poleDeepLinkParams ?? []);
        const preserveQueryKeys = ['vendor', 'ward', 'project_manager', 'site_engineer'];

        function jicrQuerySuffix() {
            return '?project_id=' + encodeURIComponent(projectId);
        }

        function resetBlockSelect(tab) {
            $('#poleBlock-' + tab).prop('disabled', true).empty().append('<option value="">All blocks</option>');
        }

        function resetPanchayatSelect(tab) {
            $('#polePanchayat-' + tab).prop('disabled', true).empty().append('<option value="">All panchayats</option>');
        }

        function loadBlocks(tab, district, selectedBlock) {
            return $.ajax({
                url: '/jicr/blocks/' + encodeURIComponent(district) + jicrQuerySuffix(),
                type: 'GET',
                dataType: 'json',
            }).then(function (response) {
                resetBlockSelect(tab);
                $('#poleBlock-' + tab).prop('disabled', false);
                $.each(response.blocks || [], function (index, item) {
                    const val = item.block;
                    const selected = selectedBlock && selectedBlock === val ? ' selected' : '';
                    $('#poleBlock-' + tab).append('<option value="' + val + '"' + selected + '>' + val + '</option>');
                });
            });
        }

        function loadPanchayats(tab, block, district, selectedPanchayat) {
            let suffix = jicrQuerySuffix();
            if (district) {
                suffix += (suffix.indexOf('?') >= 0 ? '&' : '?') + 'district=' + encodeURIComponent(district);
            }

            return $.ajax({
                url: '/jicr/panchayats/' + encodeURIComponent(block) + suffix,
                type: 'GET',
                dataType: 'json',
            }).then(function (response) {
                resetPanchayatSelect(tab);
                $('#polePanchayat-' + tab).prop('disabled', false);
                $.each(response.panchayats || [], function (index, item) {
                    const val = item.panchayat;
                    const selected = selectedPanchayat && selectedPanchayat === val ? ' selected' : '';
                    $('#polePanchayat-' + tab).append('<option value="' + val + '"' + selected + '>' + val + '</option>');
                });
            });
        }

        function buildProjectPolePageUrl(tab, filterParams) {
            const params = new URLSearchParams();

            preserveQueryKeys.forEach(function (key) {
                if (poleDeepLinkParams[key]) {
                    params.set(key, poleDeepLinkParams[key]);
                }
            });

            if (filterParams) {
                filterParams.forEach(function (value, key) {
                    if (value) {
                        params.set(key, value);
                    }
                });
            }

            const qs = params.toString();

            return projectShowBaseUrl + (qs ? '?' + qs : '') + '#' + tab + '-poles';
        }

        function clearProjectPoleFilters(tab) {
            window.location.assign(buildProjectPolePageUrl(tab, null));
        }

        function applyProjectPoleFilters(tab) {
            const params = new URLSearchParams();

            const district = $('#poleDistrict-' + tab).val();
            const block = $('#poleBlock-' + tab).val();
            const panchayat = $('#polePanchayat-' + tab).val();

            ['district', 'block', 'panchayat'].forEach(function (key) {
                params.delete(key);
            });

            if (district) {
                params.set('district', district);
            }
            if (block) {
                params.set('block', block);
            }
            if (panchayat) {
                params.set('panchayat', panchayat);
            }

            $('#' + tab + '-poles .project-pole-status-filter').each(function () {
                const param = $(this).data('param');
                const val = $(this).val();
                if (param) {
                    params.delete(param);
                    if (val) {
                        params.set(param, val);
                    }
                }
            });

            if (tab === 'installed') {
                params.delete('filter_installed');
            }

            window.location.assign(buildProjectPolePageUrl(tab, params));
        }

        function bindTabFilters(tab) {
            $('#poleDistrict-' + tab).on('change', function () {
                const district = $(this).val();
                resetBlockSelect(tab);
                resetPanchayatSelect(tab);
                if (district) {
                    loadBlocks(tab, district, null);
                }
            });

            $('#poleBlock-' + tab).on('change', function () {
                const block = $(this).val();
                const district = $('#poleDistrict-' + tab).val();
                resetPanchayatSelect(tab);
                if (block) {
                    loadPanchayats(tab, block, district, null);
                }
            });

            $('.project-pole-apply-btn[data-tab="' + tab + '"]').on('click', function () {
                applyProjectPoleFilters(tab);
            });
        }

        function bindClearButtons() {
            $(document).on('click', '.project-pole-clear-btn', function (e) {
                e.preventDefault();
                clearProjectPoleFilters($(this).data('tab'));
            });
        }

        function restoreGeoCascade(tab) {
            if (!poleGeoFilters.district) {
                return;
            }

            loadBlocks(tab, poleGeoFilters.district, poleGeoFilters.block || null).then(function () {
                if (poleGeoFilters.block) {
                    return loadPanchayats(
                        tab,
                        poleGeoFilters.block,
                        poleGeoFilters.district,
                        poleGeoFilters.panchayat || null
                    );
                }
            });
        }

        function initDeleteButtons(tableId) {
            $(document).off('click.projectPoleDelete', '#' + tableId + ' .delete-pole-btn');
            $(document).on('click.projectPoleDelete', '#' + tableId + ' .delete-pole-btn', function () {
                const poleName = $(this).data('name');
                const deleteUrl = $(this).data('url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You are about to delete pole "' + poleName + '". This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: @json(csrf_token()),
                        },
                        success: function () {
                            Swal.fire('Deleted!', 'Pole "' + poleName + '" has been deleted.', 'success')
                                .then(function () {
                                    window.location.reload();
                                });
                        },
                        error: function () {
                            Swal.fire('Error!', 'There was an error deleting the pole. Please try again.', 'error');
                        },
                    });
                });
            });
        }

        function adjustPoleTable(tableId) {
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().columns.adjust().draw(false);
            }
        }

        $(document).ready(function () {
            bindClearButtons();
            ['installed', 'surveyed'].forEach(bindTabFilters);
            restoreGeoCascade('installed');
            restoreGeoCascade('surveyed');

            initDeleteButtons('projectInstalledPoles');
            initDeleteButtons('projectSurveyedPoles');

            $(document).on('click', '.rms-status-indicator', function () {
                const poleId = $(this).data('pole-id');
                if (poleId) {
                    window.location.href = @json(route('rms.export')) + '?pole_id=' + poleId;
                }
            });

            $('button[data-bs-target="#installed-poles"]').on('shown.bs.tab', function () {
                adjustPoleTable('projectInstalledPoles');
            });
            $('button[data-bs-target="#surveyed-poles"]').on('shown.bs.tab', function () {
                adjustPoleTable('projectSurveyedPoles');
            });
        });
    })();
</script>
