@extends('layouts.app', ['title' => 'History'])

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data History Kehadiran Karyawan</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <form id="filter-form">
                        <div class="form-row">
                            <div class="form-group col-auto">
                                <label for="start_date">Filter Month:</label>
                                <input type="month" class="form-control form-control-sm" id="start_date" name="start_date">
                            </div>
                            <div class="form-group col-auto">
                                <label for="filter_button">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-sm form-control form-control-sm" id="filter_button">Apply Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered" id="employee-table">
                            <thead>
                                <tr class="text-center align middle">
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
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ url("/employee/history/datatables") }}',
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
            ]
        });

        $('#filter_button').on('click', function() {
            table.ajax.reload();
        });
    });
</script>
@endpush
@endsection