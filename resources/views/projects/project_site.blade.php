@php
    // Determine columns and filters based on project type
    $isRooftop = $project->project_type == 0;

    if ($isRooftop) {
        $columns = [
            ['title' => 'Breda Sl No'],
            ['title' => 'Site Name'],
            ['title' => 'Address'],
            ['title' => 'Vendor'],
            ['title' => 'Engineer'],
        ];
        $filters = [
            [
                'type' => 'text',
                'name' => 'site_name',
                'label' => 'Site Name',
                'column' => 1,
                'width' => 3,
            ],
            [
                'type' => 'text',
                'name' => 'vendor',
                'label' => 'Vendor',
                'column' => 3,
                'width' => 3,
            ],
            [
                'type' => 'text',
                'name' => 'engineer',
                'label' => 'Engineer',
                'column' => 4,
                'width' => 3,
            ],
        ];
        $importFormatUrl = 'https://sugslloyd.s3.ap-south-1.amazonaws.com/formats/sites_format.xlsx';
    } else {
        $columns = [
            ['title' => 'Site Code'],
            ['title' => 'State'],
            ['title' => 'District'],
            ['title' => 'Block'],
            ['title' => 'Panchayat'],
            ['title' => 'Ward'],
            ['title' => 'Scope'],
            ['title' => 'Surveyed Poles'],
            ['title' => 'Installed Poles'],
        ];
        $filters = [
            [
                'type' => 'text',
                'name' => 'state',
                'label' => 'State',
                'column' => 2,
                'width' => 3,
            ],
            [
                'type' => 'text',
                'name' => 'district',
                'label' => 'District',
                'column' => 3,
                'width' => 3,
            ],
            [
                'type' => 'text',
                'name' => 'block',
                'label' => 'Block',
                'column' => 4,
                'width' => 3,
            ],
            [
                'type' => 'text',
                'name' => 'panchayat',
                'label' => 'Panchayat',
                'column' => 5,
                'width' => 3,
            ],
        ];
        $importFormatUrl = route('sites.importFormat', $project->id);
    }
@endphp

<div class="row my-2 mx-1">
    <x-datatable id="sitesTable" title="Sites" :columns="$columns" :addRoute="route('sites.create', ['project_id' => $project->id])" addButtonText="Add Site" :exportEnabled="true" :importEnabled="true"
        :importRoute="route('sites.import', $project->id)" :importFormatUrl="$importFormatUrl" :bulkDeleteEnabled="true"
        :bulkDeleteRoute="route('sites.bulkDelete') . '?project_id=' . $project->id"
        :deleteRoute="route('sites.destroy', ':id') . '?project_id=' . $project->id" :editRoute="route('sites.edit', ':id') . '?project_id=' . $project->id" :viewRoute="route('sites.show', ':id') . '?project_type=' . $project->project_type" pageLength="50" searchPlaceholder="Search Sites..." :filters="$filters">
        @foreach ($sites as $site)
            <tr>
                <td>
                    <input type="checkbox" class="row-checkbox" value="{{ $site->id }}">
                </td>
                @if ($isRooftop)
                    <td>{{ $site->breda_sl_no }}</td>
                    <td>{{ $site->site_name }}</td>
                    <td>
                        {{ $site->location }},
                        {{ optional($site->districtRelation)->name ?? 'Unknown District' }},
                        {{ optional($site->stateRelation)->name ?? 'Unknown State' }}
                    </td>
                    <td>{{ $site->vendorRelation->name ?? '' }}</td>
                    <td>{{ $site->engineerRelation->firstName ?? '' }} {{ $site->engineerRelation->lastName ?? '' }}</td>
                @else
                    <td>{{ $site->task_id }}</td>
                    <td>{{ $site->state }}</td>
                    <td>{{ $site->district }}</td>
                    <td>{{ $site->block }}</td>
                    <td>{{ $site->panchayat }}</td>
                    <td>{{ $site->ward ?? '-' }}</td>
                    <td>{{ $site->total_poles ?? 0 }}</td>
                    <td>{{ $site->number_of_surveyed_poles ?? 0 }}</td>
                    <td>{{ $site->number_of_installed_poles ?? 0 }}</td>
                @endif
                <td class="text-center">
                    <a href="{{ route('sites.show', $site->id) }}?project_type={{ $project->project_type }}"
                        class="btn btn-sm-icon btn-info" data-toggle="tooltip" title="View Details">
                        <i class="mdi mdi-eye"></i>
                    </a>
                    <a href="{{ route('sites.edit', $site->id) }}?project_id={{ $project->id }}"
                        class="btn btn-sm-icon btn-warning" data-toggle="tooltip" title="Edit Site">
                        <i class="mdi mdi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm-icon btn-danger delete-row" data-toggle="tooltip"
                        title="Delete Site" data-id="{{ $site->id }}" data-name="{{ $site->site_name ?? $site->task_id }}"
                        data-url="{{ route('sites.destroy', $site->id) }}?project_id={{ $project->id }}">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </x-datatable>
</div>