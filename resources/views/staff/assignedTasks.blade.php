<div class="home-tab">
  <!--
    <div class="d-flex align-items-center flex-row">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab"
                   aria-controls="overview" aria-selected="true">Assigned Panchayats</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#audiences" role="tab"
                   aria-controls="audiences" aria-selected="false">Surveyed Poles</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#demographics" role="tab"
                   aria-controls="demographics" aria-selected="false">Installed Poles</a>
            </li>
        </ul>
    </div>
-->
    <div class="tab-content tab-content-basic">
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="home-tab">
            <label>
                <input type="search" class="form-control form-control-sm mb-4" placeholder="Search Panchayats..."
                       aria-controls="panchayatTable">
            </label>
            <table id="panchayatTable" class="display table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Panchayat</th>
                    <th>Block</th>
                    <th>District</th>
                    <th>Engineer</th>
                    <th>Vendor</th>
                    <th>Wards</th>
                    <th>Number of poles</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                {{-- Example Static Rows, Replace with @foreach --}}
                <tr>
                    <td>1</td>
                    <td>Example Panchayat</td>
                    <td>Example Block</td>
                    <td>Example District</td>
                    <td>John Doe</td>
                    <td>ABC Vendor</td>
                    <td>5</td>
                    <td>20</td>
                    <td>
                      <a href="" class="btn btn-icon btn-info">
                        <i class="mdi mdi-eye"></i>
                      </a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Panchayat</td>
                    <td>Block</td>
                    <td>District</td>
                    <td>Doe</td>
                    <td>Vendor</td>
                    <td>15</td>
                    <td>120</td>
                    <td>
                      <a href="" class="btn btn-icon btn-info">
                        <i class="mdi mdi-eye"></i>
                      </a>
                    </td>
                </tr>
                {{-- End Example Rows --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
    <!-- Include jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <script>
        $(document).ready(function () {
            $('#panchayatTable').DataTable({
                "paging": false,      // Disable pagination for now
                "searching": true,    // Enable search filter
                "ordering": true,     // Enable sorting
                "info": true,         // Show table info
                "lengthMenu": [10, 25, 50, 100], // Pagination options (not yet enabled)
                "order": []           // No default sorting
            });
        });
    </script>
@endsection
