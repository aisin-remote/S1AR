@extends('layouts.app', ['title' => 'Monthly Attendance Department'])
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Kehadiran Karyawan Bulanan</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <div class="container">
                        <div class="row">
                            <div class="col-auto">
                                <select id="monthFilter" class="form-control py-2">
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button id="filterButton" class="btn btn-primary">Apply Filter</button>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="employee-table">
                            <thead>
                                <tr class="text-center align-middle">
                                    <th>NPK</th>
                                    <th>Nama</th>
                                    <th>Department</th>
                                    <th>Occupation</th>
                                    <?php
                                    $bulan = date('m');
                                    $tahun = date('Y');
                                    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
                                    for ($hari = 1; $hari <= $jumlah_hari; $hari++) {
                                        $class = (date('N', strtotime("$tahun-$bulan-$hari")) >= 6) ? 'class="text-danger"' : '';
                                        echo "<th $class>$hari</th>";
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedData as $npk => $npkData)
                                <tr>
                                    <td>{{ $npk }}</td>
                                    <td>{{ $npkData[0]->empnm }}</td>
                                    <td>{{ $npkData[0]->department }}</td>
                                    <td>{{ $npkData[0]->occupation }}</td>
                                    @for ($hari = 1; $hari <= $jumlah_hari; $hari++) <?php
                                                                                        $rsccd = '';
                                                                                        $today = date('j');
                                                                                        if ($hari <= $today) {
                                                                                            foreach ($npkData as $data) {
                                                                                                if (!is_null($data->schdt) && date('j', strtotime($data->schdt)) == $hari) {
                                                                                                    $rsccd = $data->rsccd;
                                                                                                    break;
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                        ?> <td {!! in_array(TRIM($rsccd), ['HDR', 'TL1' , 'TL2' , 'TL3' ]) ? 'class="text-success"' : '' !!}>
                                        {!! in_array(TRIM($rsccd), ['HDR', 'TL1', 'TL2', 'TL3']) ? '<i class="fas fa-check"></i>' : '<span class="badge badge-warning">'. $rsccd .'</span>' !!}
                                        </td>
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
            "scrollX": true,
            "scrollCollapse": true,
            "fixedHeader": true,
            "fixedColumns": {
                leftColumns: 4,
            }
        });
    });

    // Mendapatkan bulan saat ini (0-11, dimulai dari Januari)
    var currentMonth = new Date().getMonth() + 1;

    // Mengatur nilai seleksi pada elemen select dengan ID 'monthFilter' ke bulan saat ini
    document.getElementById('monthFilter').value = currentMonth;

    // Mendapatkan bulan yang dipilih dari URL (jika ada)
    var selectedMonthFromUrl = window.location.pathname.split('/').pop();

    // Jika bulan yang dipilih dari URL valid (antara 1 hingga 12), atur nilai seleksi pada elemen select
    if (selectedMonthFromUrl >= 1 && selectedMonthFromUrl <= 12) {
        document.getElementById('monthFilter').value = selectedMonthFromUrl;
    }

    // Menambahkan event listener untuk tombol filter
    document.getElementById('filterButton').addEventListener('click', function() {
        var selectedMonth = document.getElementById('monthFilter').value;
        window.location.href = '{{ route("monthlyattendance") }}/' + selectedMonth;
    });
</script>
@endpush
@endsection