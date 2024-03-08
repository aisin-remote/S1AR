@extends('layouts.app', ['title' => 'CUZIA KARYAWAN'])
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data CUZIA Karyawan</h1>
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
                                    <th class="text-center align-middle">Jenis Cuzia</th>
                                    <th class="text-center align-middle">Tanggal Cuzia</th>
                                    <th class="text-center align-middle">Status</th>
                                    <th class="text-center align-middle">Keterangan</th>
                                    <th class="text-center align-middle">Action</th>
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
                                    <td>{{ $npkData[0]->rsccd }}</td>
                                    <td>{{ $npkData[0]->schdt }}</td>
                                    <td>{{ $npkData[0]->stts }}</td>
                                    <td>{{ $npkData[0]->note }}</td>
                                    <td class="px-4 py-2 ">
                                        <div class="d-flex">
                                        <button type="button" class="btn btn-success btn-sm form-control form-control-sm" data-toggle="modal" >Detail</button>
                                        <button type="button" class="btn btn-primary btn-sm form-control form-control-sm">Setujui</button>
                                        <button type="button" class="btn btn-danger btn-sm form-control form-control-sm" >Tolak</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<!-- Add this modal code at the end of your HTML file, before closing the body tag -->
<div class="modal fade" id="cuziapribadimodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 80%;"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModal">Pengajuan Cuti</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Form Section -->
                        <div class="col-md-6">
                            <form id="leaveRequestForm" enctype="multipart/form-data">
                                <div class="form-group mb-2">
                                    <label for="nama">Nama:</label>
                                    <input type="text" class="form-control" id="nama" name="nama">
                                </div>
                                <div class="form-group mb-2">
                                    <label for="npk">NPK:</label>
                                    <input type="text" class="form-control" id="npk" name="npk">
                                </div>
                                <div class="form-group mb-2">
                                    <label for="bagian">Bagian:</label>
                                    <input type="text" class="form-control" id="bagian" name="bagian">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-2">
                                        <label for="tgl_mulai">Tanggal Mulai:</label>
                                        <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai">
                                    </div>
                                    <div class="form-group col-md-6 mb-2">
                                        <label for="tgl_selesai">Tanggal Selesai:</label>
                                        <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai">
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="jenis_cuzia">Jenis Cuzia:</label>
                                    <select class="form-control" id="jenis_cuzia" name="jenis_cuzia">
                                        <option value="" disabled selected>Pilih Jenis Cuzia</option>
                                        <option value="1">Cuti Tahunan</option>
                                        <option value="2">Cuti Istimewa</option>

                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                                <div class="form-group mb-2" id="lampiranContainer" style="display: none;">
                                    <label for="lampiran">Unggah Dokumen/Lampiran:</label>
                                    <input type="file" class="form-control-file" id="lampiran" name="lampiran">
                                </div>
                                <button type="submit" class="btn btn-success btn-sm">Submit</button>
                            </form>

                        </div>
                        <!-- Table Section -->
                        <div class="col-md-6 table responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Hak Cuti</th>
                                        <th>Telah Diambil</th>
                                        <th>Saldo Cuti</th>
                                        <th>Paraf Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>CT</td>
                                        <!-- Add data for "Hak Cuti", "Telah Diambil", "Saldo Cuti", "Paraf Tanggal" here -->
                                    </tr>
                                    <tr>
                                        <td>CI</td>
                                        <!-- Add data for "Hak Cuti", "Telah Diambil", "Saldo Cuti", "Paraf Tanggal" here -->
                                    </tr>
                                    <!-- Add more rows as needed -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#jenis_cuzia').change(function() {
            var selectedValue = $(this).val();
            if (selectedValue == '1' || selectedValue == '2') {
                $('#lampiranContainer').show();
            } else {
                $('#lampiranContainer').hide();
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#employee-table').DataTable({
            dom: '<"top"f>Brt<"bottom"lip><"clear">',
            "ordering": false,
            paging: true,
            pagingType: "simple_numbers",
            scrollY: "400px",
            scrollX: false,
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
        window.location.href = '{{ route("cuziafilter") }}/' + selectedMonth;
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
