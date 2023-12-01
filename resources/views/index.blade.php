@extends('layouts.app', ['title' => 'Daily Attendance'])

@section('content')
@if(session('error'))
    <script>
        alert("{{ session('error') }}");
    </script>
@endif

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Kehadiran Karyawan Harian</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <form id="filter-form">
                        <div class="form-row">
                            <div class="form-group col-auto">
                                <label for="start_date">Start Date:</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                            <div class="form-group col-auto">
                                <label for="start_date">End Date:</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            <div class="form-group col-auto">
                                <label for="filter_button">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-sm form-control" id="filter_button">Apply Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="employee-table">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">NPK</th>
                                    <th class="text-center align-middle">Nama</th>
                                    <th class="text-center align-middle">Occupation</th>
                                    <th class="text-center align-middle">Department</th>
                                    <th class="align-middle">Tanggal Masuk</th>
                                    <th class="align-middle">Waktu Masuk</th>
                                    <th class="align-middle">Tanggal Keluar</th>
                                    <th class="align-middle">Waktu Keluar</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#employee-table').DataTable({
            processing: true,
            ajax: {
                url: '{{ url("/employee/datatables") }}',
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [{
                    data: 'empno',
                    name: 'empno'
                },
                {
                    data: 'empnm',
                    name: 'empnm',
                    orderable: false
                },
                {
                    data: 'hirar',
                    name: 'occupation',
                    orderable: false
                },
                {
                    data: 'descr',
                    name: 'department',
                    orderable: false
                },
                {
                    data: 'datin',
                    name: 'datin'
                },
                {
                    data: 'timin',
                    name: 'timin'
                },
                {
                    data: 'datot',
                    name: 'datot'
                },
                {
                    data: 'timot',
                    name: 'timot'
                }
            ],
            initComplete: function() {
                this.api().columns([3]).every(function() {
                    var column = this;

                    var input = $('<input type="text" class="form-control form-control-sm mt-2" placeholder="Search..."/>')
                        .appendTo($(column.header()))
                        .on('keyup change', function() {
                            column.search($(this).val()).draw();
                        });
                });
            }
        });

        $('#filter_button').on('click', function() {
            table.ajax.reload();
        });
    });
</script>
@endpush
@endsection