@extends('layouts.main')

@section('content')
    <div class="container p-2">
        <x-datatable id="staffTable" title="Staff Management" :columns="[
            ['title' => '#', 'width' => '5%'],
            ['title' => 'Name'],
            ['title' => 'Email'],
            ['title' => 'Address'],
            ['title' => 'Role'],
            ['title' => 'Phone'],
        ]" :addRoute="route('staff.create')" addButtonText="Add New Staff"
            :exportEnabled="true" :importEnabled="true" :importRoute="route('import.staff')" :importFormatUrl="null" :bulkDeleteEnabled="true" :bulkDeleteRoute="route('staff.bulkDelete')"
            :deleteRoute="route('staff.destroy', ':id')" :editRoute="route('staff.edit', ':id')" :viewRoute="route('staff.show', ':id')" pageLength="50" searchPlaceholder="Search Staff..."
            :filters="[
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'Name',
                    'column' => 1,
                    'width' => 3,
                ],
                [
                    'type' => 'text',
                    'name' => 'email',
                    'label' => 'Email',
                    'column' => 2,
                    'width' => 3,
                ],
                [
                    'type' => 'select',
                    'name' => 'role',
                    'label' => 'Role',
                    'column' => 4,
                    'width' => 3,
                    'options' => [
                        '1' => 'Site Engineer',
                        '2' => 'Project Manager',
                        '4' => 'Store Incharge',
                        '5' => 'Coordinator',
                    ],
                ],
            ]">
            @foreach ($staff as $member)
                <tr>
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $member->id }}">
                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $member->firstName }} {{ $member->lastName }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->address }}</td>
                    @php
                        $roles = [
                            1 => 'Site Engineer',
                            2 => 'Project Manager',
                            4 => 'Store Incharge',
                        ];
                    @endphp
                    <td>{{ $roles[$member->role] ?? 'Coordinator' }}</td>
                    <td>{{ $member->contactNo }}</td>
                    <td class="text-center">
                        <a href="{{ route('staff.show', $member->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                            title="View Details">
                            <i class="mdi mdi-eye"></i>
                        </a>
                        <a href="{{ route('staff.edit', $member->id) }}" class="btn btn-icon btn-warning"
                            data-toggle="tooltip" title="Edit Staff">
                            <i class="mdi mdi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-danger delete-row" data-toggle="tooltip"
                            title="Delete Staff" data-id="{{ $member->id }}"
                            data-name="{{ $member->firstName }} {{ $member->lastName }}"
                            data-url="{{ route('staff.destroy', $member->id) }}">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-datatable>
    </div>

    @if (session()->has('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: {!! json_encode(session('success')) !!},
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if (session()->has('error'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: {!! json_encode(session('error')) !!},
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if (session()->has('import_errors'))
        <script>
            const importErrors = {!! json_encode(session('import_errors')) !!};
            const maxShow = 10;
            const shortList = Array.isArray(importErrors) ? importErrors.slice(0, maxShow) : [importErrors];
            Swal.fire({
                title: 'Import completed with errors',
                icon: 'warning',
                html: shortList.join('<br>') + (importErrors.length > maxShow ? '<br><em>...more errors omitted</em>' :
                    ''),
                confirmButtonText: 'OK',
                width: '600px'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            const validationErrors = {!! json_encode($errors->all()) !!};
            Swal.fire({
                title: 'Validation errors',
                icon: 'error',
                html: validationErrors.join('<br>'),
                confirmButtonText: 'OK',
                width: '600px'
            });
        </script>
    @endif
@endsection
