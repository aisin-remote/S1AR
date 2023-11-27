@extends('layouts.app', ['title' => 'Monthly Attendance Department'])
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Kehadiran Karyawan Bulanan per Department</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <div class="container">
                        <div class="row">
                            <div class="col-auto">
                                <select id="departmentFilter" class="form-control py-2">
                                    <option value="" disabled selected>Select Department</option>
                                    <option value="PRODUCTION SYSTEM & DEVELOPMEN">PRODUCTION SYSTEM & DEVELOPMENT</option>
                                    <option value="QA ENGINE COMPONENT">QA ENGINE COMPONENT</option>
                                    <option value="IT DEVELOPMENT">IT DEVELOPMENT</option>
                                    <option value="PRODUCTION ELECTRIC">PRODUCTION ELECTRIC</option>
                                    <option value="ENGINEERING & QUALITY ELECTRICAL COMPONENT">ENGINEERING & QUALITY ELECTRICAL COMPONENT</option>
                                    <option value="ENGINERING BODY">ENGINERING BODY</option>
                                    <option value="QA BODY COMPONENT">QA BODY COMPONENT</option>
                                    <option value="BODY COMPONENT">BODY COMPONENT</option>
                                    <option value="MAINTENANCE ELECTRIC">MAINTENANCE ELECTRIC</option>
                                    <option value="PPIC">PPIC</option>
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
                                                                                        $hadir = false;
                                                                                        $rsccd = '';
                                                                                        foreach ($npkData as $data) {
                                                                                            if (date('j', strtotime($data->datin)) == $hari) {
                                                                                                $hadir = true;
                                                                                                if (is_null($data->rsccd) && !is_null($data->schdt)) {
                                                                                                    $rsccd = $data->rsccd;
                                                                                                }
                                                                                                break;
                                                                                            } elseif (!is_null($data->schdt) && date('j', strtotime($data->schdt)) == $hari) {
                                                                                                $rsccd = $data->rsccd;
                                                                                                break;
                                                                                            }
                                                                                        }
                                                                                        ?> <td {!! $hadir ? 'class="text-success"' : '' !!}>
                                        {!! $hadir ? '<i class="fas fa-check"></i>' : '' !!}
                                        <span class="badge badge-warning">{{ $rsccd }}</span>
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

    // Menambahkan event listener untuk tombol filter
    document.getElementById('filterButton').addEventListener('click', function() {
        var selectedMonth = document.getElementById('departmentFilter').value;
        window.location.href = '{{ route("departmentattendance") }}/' + selectedMonth;
    });
</script>
@endpush
@endsection