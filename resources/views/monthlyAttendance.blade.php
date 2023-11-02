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
                                    // $today = date('j');
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
</script>
@endpush
@endsection