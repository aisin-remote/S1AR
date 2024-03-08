@extends('layouts.app', ['title' => 'Weekly Attendance'])
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Kehadiran Karyawan Mingguan</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="employee-table">
                            <thead>
                                <tr class="text-center align middle">
                                    <th>NPK</th>
                                    <th>Nama</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Waktu Masuk</th>
                                    <th>Tanggal Keluar</th>
                                    <th>Waktu Keluar</th>
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
        $('#employee-table').DataTable({
            serverSide: true,
            ajax: '{{ url("/employee/weekly/datatables") }}',
            columns: [{
                    data: 'empno',
                    name: 'empno'
                },
                {
                    data: 'empnm',
                    name: 'empnm'
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
    });
</script>
@endpush
@endsection