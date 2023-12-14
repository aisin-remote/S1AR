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
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="col-auto">
                                        <input type="month" id="monthFilter" class="form-control form-control-sm py-1" value="{{ date('yyyy-MM') }}">
                                    </div>
                                    <div class="col-auto">
                                        <button id="filterButton" class="btn btn-primary">Apply Filter</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-success" onclick="exportToExcel()">Export to Excel</button>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered" id="employee-table">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">NPK</th>
                                    <th class="text-center align-middle">Nama</th>
                                    <th class="text-center align-middle">Department</th>
                                    <th class="text-center align-middle">Occupation</th>
                                    <?php
                                    $tahun = date('Y');
                                    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulanSekarang, $tahun);
                                    ?>
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
                                        if (in_array(trim($rsccd), ['HDR', 'TL1', 'TL2', 'TL3'])) {
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
                                        if (in_array(trim($rsccd), ['HDR', 'TL1', 'TL2', 'TL3'])) {
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

                                        <td {!! in_array(trim($rsccd), ['HDR', 'TL1' , 'TL2' , 'TL3' ]) ? 'class="text-success text-center"' : 'text-center' !!}>
                                            {!! in_array(trim($rsccd), ['HDR', 'TL1', 'TL2', 'TL3']) ? '✔️' : '<span class="badge badge-warning">'. $rsccd .'</span>' !!}
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
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
    function exportToExcel() {
        const sheetName = 'Monthly_Attendance';
        const fileName = 'monthly_attendance';

        const tables = document.querySelectorAll('table');
        const wb = XLSX.utils.book_new();

        tables.forEach((table, index) => {
            const ws = XLSX.utils.table_to_sheet(table);

            // Set column width based on the maximum width of the data in each column
            const maxColumnWidths = Array.from({
                    length: ws['!cols'].length
                }, (_, colIndex) =>
                XLSX.utils.decode_col(XLSX.utils.encode_col(colIndex + 1))
            );

            ws['!cols'] = maxColumnWidths.map((width) => ({
                wch: width
            }));

            XLSX.utils.book_append_sheet(wb, ws, sheetName + (index + 1));
        });

        XLSX.writeFile(wb, fileName + '.xlsx');
    }
</script>
<script>
    $(document).ready(function() {
        var table = $('#employee-table').DataTable({
            "dom": '<"top"f>rt<"bottom"lip><"clear">',
            "paging": false,
            "pagingType": "simple_numbers",
            "scrollY": "400px",
            "scrollX": true,
            "columnDefs": [{
                "orderable": false,
                "targets": [1, 2, 3]
            }],
            initComplete: function() {
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

    // Mendapatkan jumlah hari dalam bulan saat ini
    var numberOfDaysInMonth = new Date(currentYear, currentMonth, 0).getDate();

    // Menentukan nama-nama hari untuk memberi warna pada hari-hari akhir pekan
    var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Mendapatkan tabel dengan ID 'employee-table'
    var table = document.getElementById('employee-table');

    // Membuat elemen-elemen <th> dan menambahkannya ke tabel
    for (var day = 1; day <= numberOfDaysInMonth; day++) {
        var th = document.createElement('th');

        // Mengatur kelas CSS berdasarkan hari dalam seminggu (0 untuk Minggu, 6 untuk Sabtu)
        var dayOfWeek = new Date(currentYear, currentMonth - 1, day).getDay();
        var isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        th.className = isWeekend ? 'text-danger text-center align-middle' : 'text-center align-middle';

        // Menambahkan elemen <th> ke dalam baris pertama tabel
        table.rows[0].appendChild(th);

        // Tambahkan kolom note setelah kolom terakhir (30 atau 31)
        if (day === numberOfDaysInMonth) {
            var alpTh = document.createElement('th');
            alpTh.className = 'text-center align-middle';
            alpTh.textContent = 'ALP';
            table.rows[0].appendChild(alpTh);

            var sktTh = document.createElement('th');
            sktTh.className = 'text-center align-middle';
            sktTh.textContent = 'SKT';
            table.rows[0].appendChild(sktTh);

            var cutiTh = document.createElement('th');
            cutiTh.className = 'text-center align-middle';
            cutiTh.textContent = 'Cuti';
            table.rows[0].appendChild(cutiTh);

            var hdrTh = document.createElement('th');
            hdrTh.className = 'text-center align-middle';
            hdrTh.textContent = 'HDR';
            table.rows[0].appendChild(hdrTh);
        }

        // Setel teks untuk semua elemen <th>, termasuk kolom tanggal dan kolom note
        th.textContent = day;
    }
</script>
@endpush
@endsection