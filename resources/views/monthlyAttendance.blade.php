@extends('layouts.app', ['title' => 'Monthly Attendance'])
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Kehadiran Karyawan Bulanan</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="employee-table">
                            <thead>
                                <tr class="text-center align-middle">
                                    <th>NPK</th>
                                    <th>Nama</th>  
                                    <?php
                                    $bulan = date('m'); // Mengambil bulan saat ini (format: MM)
                                    $tahun = date('Y'); // Mengambil tahun saat ini (format: YYYY)
                                    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun); // Mendapatkan jumlah hari dalam bulan ini

                                    // Menambahkan kolom tanggal dari 1 hingga 31
                                    for ($hari = 1; $hari <= $jumlah_hari; $hari++) {
                                        echo "<th>$hari</th>";
                                    }
                                    ?>
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

@endpush
@endsection