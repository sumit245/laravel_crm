@extends('layouts.main')

@section('content')
    <div class="container p-2">
        <x-datatable id="projectsTable" title="Projects" :columns="[
            ['title' => '#'],
            ['title' => 'Project Name'],
            ['title' => 'Work Order Number'],
            ['title' => 'Start Date'],
            ['title' => 'Order Value'],
            ['title' => 'Project Type'],
        ]" :addRoute="route('projects.create')" addButtonText="Add New Project"
            :exportEnabled="true" :importEnabled="true" :importRoute="route('projects.import')" :importFormatUrl="route('projects.importFormat')" :bulkDeleteEnabled="true" :bulkDeleteRoute="route('projects.bulkDelete')"
            :deleteRoute="route('projects.destroy', ':id')" :editRoute="route('projects.edit', ':id')" :viewRoute="route('projects.show', ':id')" pageLength="50" searchPlaceholder="Search Projects..."
            :filters="[
                [
                    'type' => 'select',
                    'name' => 'project_type',
                    'label' => 'Project Type',
                    'column' => 6,
                    'width' => 3,
                    'options' => ['Rooftop' => 'Rooftop', 'Streetlight' => 'Streetlight'],
                ],
                [
                    'type' => 'date',
                    'name' => 'start_date_from',
                    'label' => 'Start Date From',
                    'column' => 4,
                    'width' => 3,
                ],
                ['type' => 'date', 'name' => 'start_date_to', 'label' => 'Start Date To', 'column' => 4, 'width' => 3],
                [
                    'type' => 'text',
                    'name' => 'order_value_min',
                    'label' => 'Min Order Value',
                    'column' => 5,
                    'width' => 3,
                ],
            ]">
            @foreach ($projects as $project)
                <tr>
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $project->id }}">
                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $project->project_name }}</td>
                    <td>{{ $project->work_order_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</td>
                    <td>{{ $project->rate }}</td>
                    <td>{{ $project->project_type == 0 ? 'Rooftop' : 'Streetlight' }}</td>
                    <td class="text-center">
                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-icon btn-info"
                            data-toggle="tooltip" title="View Details">
                            <i class="mdi mdi-eye"></i>
                        </a>
                        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-icon btn-warning"
                            data-toggle="tooltip" title="Edit Project">
                            <i class="mdi mdi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-danger delete-row" data-toggle="tooltip"
                            title="Delete Project" data-id="{{ $project->id }}" data-name="{{ $project->project_name }}"
                            data-url="{{ route('projects.destroy', $project->id) }}">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-datatable>
    </div>
@endsection
