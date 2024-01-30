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
                    <form id="filter-form">
                        <div class="form-row">
                            <div class="form-group col-auto">
                                <label for="start_date">Filter Month:</label>
                                <input type="month" id="monthFilter" class="form-control form-control-sm py-1">
                            </div>
                            <div class="form-group col-auto">
                                <label for="filter_button">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-sm form-control form-control-sm" id="filterButton">Apply Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered" id="employee-table">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">NPK</th>
                                    <th class="text-center align-middle">Nama</th>
                                    <th class="text-center align-middle">Department</th>
                                    <th class="text-center align-middle">Occupation</th>
                                    <?php
                                    // $tahun = date('Y');
                                    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulanSekarang, $tahunSekarang);
                                    ?>
                                    @for ($day = 1; $day <= $jumlah_hari; $day++) <th>
                                        <?php
                                        // Mengatur kelas CSS berdasarkan hari dalam seminggu (0 untuk Minggu, 6 untuk Sabtu)
                                        $date = sprintf("%04d-%02d-%02d", $tahunSekarang, $bulanSekarang, $day);
                                        $dayOfWeek = (new DateTime("$tahunSekarang-$bulanSekarang-$day"))->format('w');
                                        $isWeekend = $dayOfWeek == 0 || $dayOfWeek == 6;
                                        $hasHolidayData = $holidays->where('date', $date)->isNotEmpty();
                                        $thClass = $isWeekend || $hasHolidayData ? 'text-danger text-center align-middle' : 'text-center align-middle';
                                        ?>
                                        <div class="<?= $thClass ?>">
                                            {{ $day }}
                                        </div>
                                        </th>
                                        @endfor
                                        <!-- Tambahkan kolom note setelah kolom terakhir (30 atau 31) -->
                                        <th class="text-center align-middle">ALP</th>
                                        <th class="text-center align-middle">SKT</th>
                                        <th class="text-center align-middle">Cuti</th>
                                        <th class="text-center align-middle">HDR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedData as $npk => $npkData)
                                <tr>
                                    <td>{{ $npk }}</td>
                                    <td>{{ $npkData[0]->empnm }}</td>
                                    <td>{{ $npkData[0]->descr }}</td>
                                    @php
                                    $npkDesc = $npkData[0]->hirar; // Ambil nilai dari $npkData[0]->hirar

                                    $cleanedString = str_replace(' ', '', $npkDesc);

                                    // Hitung jumlah karakter
                                    $jumlahKarakter = strlen($cleanedString);

                                    // Tentukan jenis berdasarkan jumlah karakter
                                    if ($jumlahKarakter == 5) {
                                    $jenis = 'KDP';
                                    } elseif ($jumlahKarakter == 7) {
                                    $jenis = 'SPV';
                                    } elseif ($jumlahKarakter == 9) {
                                    $jenis = 'LDR/OPR';
                                    } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                                    $jenis = 'GMR';
                                    } else {
                                    $jenis = 'Jenis tidak dikenali'; // Atur jenis untuk kondisi lainnya
                                    }

                                    echo "<td>{$jenis}</td>";
                                    @endphp

                                    @php
                                    $alpCount = 0; // Initialize ALP counter
                                    $sktCount = 0; // Initialize SKT counter
                                    $hdrCount = 0; // Initialize HDR counter
                                    $cutiCount = 0; // Initialize Cuti counter
                                    @endphp

                                    @for ($hari = 1; $hari <= $jumlah_hari; $hari++) @php $rsccd='' ; $today=date('j'); $month=date('m'); if ($bulanSekarang==$month) { if ($hari <=$today) { foreach ($npkData as $data) { if (!is_null($data->schdt) && date('j', strtotime($data->schdt)) == $hari) {
                                        $rsccd = $data->rsccd;

                                        // Increment ALP count if rsccd is ALP
                                        if (trim($rsccd) == 'ALP') {
                                        $alpCount++;
                                        }

                                        // Increment SKT count if rsccd is SKT
                                        if (trim($rsccd) == 'SKT') {
                                        $sktCount++;
                                        }

                                        // Increment HDR count if rsccd is HDR
                                        if (in_array(trim($rsccd), ['HDR', 'TL1', 'TL2', 'TL3', 'HHO', 'BKT', 'DTA', 'HHL', 'OT>03', 'OT>11', 'PCT', 'PTA', 'SF2', 'SF3', 'SLP', 'TL1', 'TL2', 'TL3', 'OVDAY', 'OVHOL', 'OVSPD', 'DLU', 'TRNT', 'TRT'])) {
                                        $hdrCount++;
                                        }

                                        // Increment Cuti count if rsccd is Cuti
                                        if (in_array(trim($rsccd), ['CBS', 'CHD', 'CK', 'CM', 'CML', 'CTH'])) {
                                        $cutiCount++;
                                        }

                                        break;
                                        }
                                        }
                                        }
                                        } else {
                                        foreach ($npkData as $data) {
                                        if (!is_null($data->schdt) && date('j', strtotime($data->schdt)) == $hari) {
                                        $rsccd = $data->rsccd;

                                        // Increment ALP count if rsccd is ALP
                                        if (trim($rsccd) == 'ALP') {
                                        $alpCount++;
                                        }

                                        // Increment SKT count if rsccd is SKT
                                        if (trim($rsccd) == 'SKT') {
                                        $sktCount++;
                                        }

                                        // Increment HDR count if rsccd is HDR
                                        if (in_array(trim($rsccd), ['HDR', 'TL1', 'TL2', 'TL3', 'HHO', 'BKT', 'DTA', 'HHL', 'OT>03', 'OT>11', 'PCT', 'PTA', 'SF2', 'SF3', 'SLP', 'TL1', 'TL2', 'TL3', 'OVDAY', 'OVHOL', 'OVSPD', 'DLU', 'TRNT', 'TRT'])) {
                                        $hdrCount++;
                                        }

                                        // Increment Cuti count if rsccd is Cuti
                                        if (in_array(trim($rsccd), ['CBS', 'CHD', 'CK', 'CM', 'CML', 'CTH'])) {
                                        $cutiCount++;
                                        }

                                        break;
                                        }
                                        }
                                        }
                                        @endphp

                                        <td {!! in_array(trim($rsccd), ['HDR', 'TL1' , 'TL2' , 'TL3', 'SF2', 'SF3' ]) ? 'class="text-success text-center"' : 'text-center' !!}>
                                            @if(in_array(trim($rsccd), ['HDR', 'TL1', 'TL2', 'TL3', 'SF2', 'SF3']))
                                            ✔️
                                            @elseif(in_array(trim($rsccd), ['HHO', 'HHL']))
                                            {!! '<span class="badge badge-success">'. $rsccd .'</span>' !!}
                                            @else
                                            {!! in_array(trim($rsccd), ['ALP']) ? '<span class="badge badge-danger">'. $rsccd .'</span>' : '<span class="badge badge-warning">'. $rsccd .'</span>' !!}
                                            @endif

                                        </td>
                                        @endfor

                                        <!-- Display ALP count in the "Note" column -->
                                        <td>{{ $alpCount }}</td>
                                        <td>{{ $sktCount }}</td>
                                        <td>{{ $cutiCount }}</td>
                                        <td>{{ $hdrCount }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <h6 class="mb-2">Legend:</h6>
                    <div class="row">
                        <div class="col-3 mb-2">
                            <span class="badge badge-danger">ALP</span> Mangkir
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-warning">SKT</span> Sakit
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-warning">PTA</span> Pulang Tidak Absen
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-warning">DTA</span> Datang Tidak Absen
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-warning">PCT</span> Pulang Cepat
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-warning">HOF</span> Hari OFF
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-warning">LBN</span> Libur Nasional
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-success">HHL</span> Hadir Hari Libur
                        </div>
                        <div class="col-3 mb-2">
                            <span class="badge badge-success">HHO</span> Hadir Hari OFF
                        </div>
                    </div>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
<script>
    // function exportToExcel() {
    //     const sheetName = 'Monthly_Attendance';
    //     const fileName = 'monthly_attendance';

    //     // Mengambil elemen tabel dengan ID employee-table
    //     const table = document.getElementById('employee-table');

    //     // Memastikan tabel ditemukan sebelum melanjutkan
    //     if (!table) {
    //         console.error('Tabel dengan ID employee-table tidak ditemukan.');
    //         return;
    //     }

    //     const wb = XLSX.utils.book_new();
    //     const ws = XLSX.utils.table_to_sheet(table);

    //     XLSX.utils.book_append_sheet(wb, ws, sheetName);

    //     XLSX.writeFile(wb, fileName + '.xlsx');
    // }
</script>
<script>
    $(document).ready(function() {
        var table = $('#employee-table').DataTable({
            dom: '<"top"f>Brt<"bottom"lip><"clear">',
            "ordering": false,
            paging: true,
            pagingType: "simple_numbers",
            scrollY: "400px",
            scrollX: true,
            buttons: [{
                extend: 'excelHtml5',
                autoFilter: true,
                text: 'Export to Excel', // customize button text
                className: 'btn btn-success btn-sm float-right', // apply Bootstrap button classes
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    $('row c', sheet).each(function() {
                        $(this).attr('s', '50');
                    });
                }
            }],
            initComplete: function() {
                var userInfoOccupation = '<?php echo $userInfoOccupation; ?>';
                var userInfoDept = <?php echo (strpos($userInfoDept, 'HRD') === 0) ? 'true' : 'false'; ?>;

                if (userInfoOccupation == 'GMR' || userInfoDept == true) {
                    this.api().columns([2]).every(function() {
                        var column = this;

                        // Create a container for the filter and clear button
                        var filterContainer = $('<div class="d-flex align-items-center"></div>').appendTo($('#employee-table_wrapper .top'));

                        // Create a select element
                        var select = $('<select class="form-control form-control-sm col-md-3 mb-2 mb-md-0" id="departmentSelect"><option value="">-- Select Department --</option><option value="hr">HRD & GA</option><option value="ir">IR & LEGAL</option><option value="enb">ENB</option><option value="enu">ENU</option><option value="mte">MTE</option><option value="qab">QAB</option><option value="msy">MSY</option><option value="qau">QAU</option><option value="itd">ITD</option><option value="PRO BODY">PRO BODY</option><option value="pro unit dc">PRO UNIT DC</option><option value="pro unit ma">PRO UNIT MA</option><option value="psd">PSD</option><option value="ppic">PPIC</option><option value="eqec">EQEC</option><option value="mma">MMA</option><option value="pro ec">PRO EC</option></select>')
                            .appendTo(filterContainer) // Append it to the container

                            // Add event listeners for keyup and change events
                            .on('keyup change', function() {
                                column.search($(this).val()).draw();
                            });

                        // Add a clear button to reset the filter
                        $('<button class="btn btn-secondary btn-sm ml-2" id="clearFilter">Clear</button>')
                            .appendTo(filterContainer)
                            .on('click', function() {
                                select.val('').change(); // Reset the select and trigger change event
                            });
                    });
                } else {
                    var column = this;

                    // Create a container for the filter and clear button
                    var filterContainer = $('<div class="d-flex align-items-center"></div>').appendTo($('#employee-table_wrapper .top'));

                    // Create a select element
                    var select = $('<p style="color:white;"></p>').text(userInfoDept + ' Dept')
                        .appendTo(filterContainer) // Append it to the container

                        // Add event listeners for keyup and change events
                        .on('keyup change', function() {
                            column.search($(this).val()).draw();
                        });
                }
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

    // Mendapatkan bulan saat ini (0-11, dimulai dari Januari) dan tahun saat ini
    var currentDate = selectedMonthFromUrl ? selectedMonthFromUrl.split('-') : null;

    // Jika currentDate tidak ada atau kosong, gunakan tanggal hari ini
    if (!currentDate || currentDate.length !== 2) {
        var today = new Date();
        var currentMonth = today.getMonth() + 1; // Adding 1 to get the correct month (1-12)
        var currentYear = today.getFullYear();
    } else {
        var currentMonth = currentDate[1];
        var currentYear = currentDate[0];
    }
</script>
@endpush
@endsection