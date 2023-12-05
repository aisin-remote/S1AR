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
                                <select id="monthFilter" class="form-control form-control-sm py-1 ">
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
                                    @endphp

                                    @for ($hari = 1; $hari <= $jumlah_hari; $hari++) @php $rsccd='' ; $today=date('j'); $month=date('m'); if ($bulanSekarang==$month) { if ($hari <=$today) { foreach ($npkData as $data) { if (!is_null($data->schdt) && date('j', strtotime($data->schdt)) == $hari) {
                                        $rsccd = $data->rsccd;

                                        // Increment ALP count if rsccd is ALP
                                        if (trim($rsccd) == 'ALP') {
                                        $alpCount++;
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

                                        break;
                                        }
                                        }
                                        }
                                        @endphp

                                        <td {!! in_array(trim($rsccd), ['HDR', 'TL1' , 'TL2' , 'TL3' ]) ? 'class="text-success text-center"' : 'text-center' !!}>
                                            {!! in_array(trim($rsccd), ['HDR', 'TL1', 'TL2', 'TL3']) ? '<i class="fas fa-check"></i>' : '<span class="badge badge-warning">'. $rsccd .'</span>' !!}
                                        </td>
                                        @endfor

                                        <!-- Display ALP count in the "Note" column -->
                                        <td>ALP : {{ $alpCount }}</td>
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
            "dom": '<"top"f>rt<"bottom"lip><"clear">',
            "paging": true,
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

                    // Pindahkan elemen pencarian ke sebelah kiri atas tabel
                    var input = $('<input type="text" class="form-control form-control-sm w-25" placeholder="Search Department..."/>')
                        .appendTo($('#employee-table_wrapper .top'))
                        .on('keyup change', function() {
                            column.search($(this).val()).draw();
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
    var selectedMonth = document.getElementById('monthFilter').value;
    var currentDate = new Date();
    var currentMonth = selectedMonth;
    var currentYear = currentDate.getFullYear();

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
            var noteTh = document.createElement('th');
            noteTh.className = 'text-center align-middle';
            noteTh.textContent = 'Note';
            table.rows[0].appendChild(noteTh);
        }

        // Setel teks untuk semua elemen <th>, termasuk kolom tanggal dan kolom note
        th.textContent = day;
    }
</script>
@endpush
@endsection