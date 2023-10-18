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
                            <tbody>
                                @foreach($groupedData as $npk => $npkData)
                                <tr>
                                    <td>{{ $npk }}</td>
                                    <td>{{ $npkData[0]->empnm }}</td> <!-- Ambil nama dari data pertama karena nama sama dalam satu NPK -->
                                    @for ($hari = 1; $hari <= $jumlah_hari; $hari++) <?php
                                                                                        $hadir = false;
                                                                                        foreach ($npkData as $data) {
                                                                                            if (date('j', strtotime($data->datin)) == $hari) {
                                                                                                $hadir = true;
                                                                                                break;
                                                                                            }
                                                                                        }
                                                                                        ?> <td>{!! $hadir ? '<i class="fas fa-check"></i>' : '' !!}</td>
                                        @endfor
                                </tr>
                                @endforeach
                            </tbody>
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
        "paging": true,
        "pagingType": "simple_numbers",
        "scrollY": "400px",
        "scrollX": true, // Mengaktifkan scrolling horizontal
        "scrollCollapse": true,
        "fixedHeader": true,
        "fixedColumns": { // Menggunakan FixedColumns untuk mengaktifkan fixed header dan kolom
            leftColumns: 2, // Tetapkan 2 kolom pertama (NPK dan Nama)
        }
    });
});
</script>
@endpush
@endsection