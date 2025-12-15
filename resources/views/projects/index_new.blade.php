@extends('layouts.main')

@section('content')
    <div class="container p-2">
        <x-datatable id="projectsTable" title="Projects" :columns="[
            ['title' => '#', 'width' => '5%'],
            ['title' => 'Project Name'],
            ['title' => 'Work Order Number'],
            ['title' => 'Start Date'],
            ['title' => 'Order Value'],
        ]" :addRoute="route('projects.create')" addButtonText="Add New Project"
            :exportEnabled="true" :bulkDeleteEnabled="true" :bulkDeleteRoute="route('projects.bulkDelete')" :deleteRoute="route('projects.destroy', ':id')" :editRoute="route('projects.edit', ':id')" :viewRoute="route('projects.show', ':id')"
            pageLength="50" searchPlaceholder="Search Projects...">
            @foreach ($projects as $project)
                <tr>
                    @if (true)
                        {{-- bulkDeleteEnabled --}}
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $project->id }}">
                        </td>
                    @endif
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $project->project_name }}</td>
                    <td>{{ $project->work_order_number }}</td>
                    <td>{{ $project->start_date }}</td>
                    <td>{{ $project->rate }}</td>
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
