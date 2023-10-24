@extends('layouts.app', ['title' => 'Daily Attendance'])
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Kehadiran Karyawan Harian</h1>
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
                                    <th>Sub Section</th> <!-- Tambahkan kolom ini -->
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
            processing: true,
            serverSide: true,
            ajax: '{{ url("/employee/datatables") }}',
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
                },
                {
                    data: 'sub_section', // Sesuaikan dengan nama kolom yang digunakan
                    name: 'sub_section'
                }
            ]
        });
    });
</script>
@endpush
@endsection
