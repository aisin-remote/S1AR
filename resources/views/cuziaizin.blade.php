@extends('layouts.app', ['title' => 'Cuzia izin Attendance'])

@section('content')
    @if (session('error'))
        <script>
            alert("{{ session('error') }}");
        </script>
    @endif

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data izin Pribadi Karyawan </h1>
            </div>

            <div class="card">
                <div class="row px-3 py-3">
                    <div class="col-lg-12">
                        <form id="filter-form">
                            <div class="form-row">
                                <div class="form-group col-auto">
                                    <label for="start_date">Start Date:</label>
                                    <input type="date" class="form-control form-control-sm" id="start_date"
                                        name="start_date">
                                </div>
                                <div class="form-group col-auto">
                                    <label for="end_date">End Date:</label>
                                    <input type="date" class="form-control form-control-sm" id="end_date"
                                        name="end_date">
                                </div>
                                <div class="form-group col-auto">
                                    <label for="filter_button">&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-sm form-control form-control-sm"
                                        id="filter_button">Apply Filter</button>
                                </div>
                                <div class="form-group ml-auto">
                                    <label for="pengajuan_button">&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-sm form-control form-control-sm"
                                        data-toggle="modal" data-target="#cuziaizinmodal" id="pengajuan_button">
                                        <i class="fas fa-plus"></i> Pengajuan Izin
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-bordered" id="employee-table">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Tanggal Pengajuan</th>
                                        <th class="text-center align-middle">NPK</th>
                                        <th class="text-center align-middle">Nama</th>
                                        <th class="align-middle">Jenis</th>
                                        <th class="align-middle">Tanggal Mulai</th>
                                        <th class="align-middle">Tanggal Selesai</th>
                                        <th class="align-middle">Status</th>
                                        <th class="align-middle">Keterangan</th>
                                        <th class="align-middle">Lampiran</th>
                                        <th class="align-middle">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Add this modal code at the end of your HTML file, before closing the body tag -->
    <div class="modal fade" id="cuziaizinmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModal">Detail Pengajuan Izin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row" style="display: block">
                            <form action="{{ route('cuziaizin.store') }}" method="POST" enctype="multipart/form-data">
                                <!-- Isi formulir di sini -->
                                @csrf
                                <div class="form-group mb-2">
                                    <label for="nama">Nama:</label>
                                    <input type="text" required class="form-control" id="nama" name="nama"
                                        value="{{ Auth::user()->name }}" readonly>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="empno">NPK:</label>
                                    <input type="text" class="form-control" required id="empno" name="empno"
                                        value="{{ Auth::user()->npk }}" readonly>
                                </div>

                                <div class="form-row">
                                    <div class="form-group mb-2 col-md-6 ">
                                        <label for="tgl_mulai">Tanggal Mulai:</label>
                                        <input type="date" required class="form-control" id="tgl_mulai" name="tgl_mulai">
                                    </div>
                                    <div class="form-group mb-2 col-md-6 ">
                                        <label for="tgl_selesai">Tanggal Selesai:</label>
                                        <input type="date" required class="form-control" id="tgl_selesai"
                                            name="tgl_selesai">
                                    </div>
                                </div>
                                <div class="form-group mb-2 ">
                                    <label for="jenis_cuzia">Jenis Izin:</label>
                                    <select required class="form-control" id="jenisizin" name="jenisizin">
                                        <option value="" disabled selected>Pilih Jenis Izin</option>
                                        @foreach ($jenisizin as $cuti)
                                            <option value="{{ $cuti->id }}">{{ $cuti->jenisizin }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="note">Keterangan:</label>
                                    <textarea required class="form-control" id="note" name="note"></textarea>
                                </div>
                                <div class="form-group " id="lampiranContainer">
                                    <label for="lampiran">Unggah Dokumen/Lampiran:</label>
                                    <input type="file" multiple name="data_verifikasi" id="data_verifikasi"
                                        class="w-full border-2 border-gray-300 px-3 py-2 rounded-md">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Add this modal code at the end of your HTML file, before closing the body tag -->
    <div class="modal fade" id="cuziaDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModal">Detail Pengajuan Izin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row" style="display: block">
                            <div class="form-group ">
                                <label for="nama">Nama:</label>
                                <input type="text" required class="form-control" id="nama3" name="nama" disabled>
                            </div>
                            <div class="form-group ">
                                <label for="npk">NPK:</label>
                                <input type="text" required class="form-control" id="npk" name="npk" disabled>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 ">
                                    <label for="tgl_mulai">Tanggal Mulai:</label>
                                    <input type="date"  required class="form-control" id="tgl_mulai3" name="tgl_mulai"
                                        disabled>
                                </div>
                                <div class="form-group col-md-6 ">
                                    <label for="tgl_selesai">Tanggal Selesai:</label>
                                    <input type="date" required class="form-control" id="tgl_selesai4" name="tgl_selesai"
                                        disabled>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="jenis_cuzia">Jenis Cuzia:</label>
                                <select required class="form-control" id="jenisizin2" name="jenisizin" disabled>
                                    <option value="" disabled selected>Pilih Jenis Izin</option>
                                    @foreach ($jenisizin as $cuti)
                                        <option value="{{ $cuti->id }}">{{ $cuti->jenisizin }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label for="note">Keterangan:</label>
                                <textarea required class="form-control" id="note3" name="note" disabled></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    @push('scripts')
        <?php
        $result = DB::connection('mysql2')->select(DB::raw('SELECT date FROM holiday'));

        $holidays = []; // Array to store national holiday dates

        // Process query results
        if (!empty($result)) {
            // Storing data from each row of the query result into the $holidays array
            foreach ($result as $row) {
                $holidays[] = $row->date; // Assuming the result is an object. Use $row['date'] if it's an array.
            }
        } else {
            echo 'Tidak ada data libur nasional.';
        }
        ?>

        <script>
            // Tanggal-tanggal libur nasional dari PHP disalin ke dalam skrip JavaScript
            var holidays = <?php echo json_encode($holidays); ?>;

            document.addEventListener("DOMContentLoaded", function() {
                var inputTanggalMulai = document.getElementById('tgl_mulai');
                var inputTanggalSelesai = document.getElementById('tgl_selesai');

                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();
                today = yyyy + '-' + mm + '-' + dd;

                inputTanggalMulai.setAttribute("min", today);

                inputTanggalMulai.addEventListener('change', function() {
                    var selectedDate = new Date(inputTanggalMulai.value);

                    // Memeriksa apakah tanggal yang dipilih adalah hari libur nasional
                    var isHoliday = holidays.includes(inputTanggalMulai.value);

                    // Memeriksa apakah tanggal yang dipilih jatuh pada hari Sabtu atau Minggu
                    var dayOfWeek = selectedDate.getDay();
                    var isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);

                    if (isHoliday || isWeekend) {
                        inputTanggalMulai.value = '';
                        alert("Maaf, tanggal yang dipilih adalah hari libur nasional atau akhir pekan.");
                        return;
                    }

                    inputTanggalSelesai.setAttribute("min", inputTanggalMulai.value);
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                var table = $('#employee-table').DataTable({
                    destroy: true,
                    dom: '<"top"f>rt<"bottom"lip><"clear">',
                    processing: true,
                    ajax: {
                        url: '{{ url('/cuziaizin/datatables') }}',
                        data: function(d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                        }
                    },
                    columns: [{
                            data: 'tgl_pengajuan',
                            name: 'tgl_pengajuan'
                        }, {
                            data: 'empno',
                            name: 'empno'
                        },
                        {
                            data: 'empnm',
                            name: 'empnm',
                            orderable: false
                        },

                        {
                            data: 'jenisizin',
                            name: 'jenisizin'
                        },
                        {
                            data: 'tgl_mulai',
                            name: 'tgl_mulai'
                        },
                        {
                            data: 'tgl_selesai',
                            name: 'tgl_selesai'
                        },
                        {
                            data: 'approval_status',
                            name: 'approval_status',
                            render: function(data, type, row) {
                                var statusText = '';
                                var userInfoOccupation =
                                    '<?php echo $userInfoOccupation; ?>'; // Asumsikan nilai ini diambil dari server-side
                                switch (data) {
                                    case '1':
                                        if (userInfoOccupation === 'LDR/OPR') {
                                            statusText = 'Disetujui oleh SPV';
                                        } else if (userInfoOccupation === 'SPV') {
                                            statusText = 'Disetujui oleh MGR';
                                        } else if (userInfoOccupation === 'KDP') {
                                            statusText = 'Disetujui oleh GMR';
                                        } else {
                                            statusText = 'Disetujui oleh Atasan 1';
                                        }
                                        break;
                                    case '2':
                                        if (userInfoOccupation === 'LDR/OPR') {
                                            statusText = 'Disetujui oleh MGR';
                                        } else if (userInfoOccupation === 'SPV') {
                                            statusText = 'Disetujui oleh GMR';
                                        } else if (userInfoOccupation === 'KDP') {
                                            statusText = 'Disetujui oleh DIR';
                                        } else {
                                            statusText = 'Disetujui oleh Atasan 2';
                                        }
                                        break;
                                    case '-1':
                                        if (userInfoOccupation === 'LDR/OPR') {
                                            statusText = 'Ditolak oleh SPV';
                                        } else if (userInfoOccupation === 'SPV') {
                                            statusText = 'Ditolak oleh MGR';
                                        } else if (userInfoOccupation === 'KDP') {
                                            statusText = 'Ditolak oleh GMR';
                                        } else {
                                            statusText = 'Ditolak oleh Atasan 1';
                                        }
                                        break;
                                    case '-2':
                                        if (userInfoOccupation === 'LDR/OPR') {
                                            statusText = 'Ditolak oleh MGR';
                                        } else if (userInfoOccupation === 'SPV') {
                                            statusText = 'Ditolak oleh GMR';
                                        } else if (userInfoOccupation === 'KDP') {
                                            statusText = 'Ditolak oleh DIR';
                                        } else {
                                            statusText = 'Ditolak oleh Atasan 2';
                                        }
                                        break;
                                    case '3':
                                        statusText = 'Disetujui oleh HR';
                                        break;
                                    case '-3':
                                        statusText = 'Ditolak oleh HR';
                                        break;
                                    default:
                                        statusText = 'Menunggu Persetujuan';
                                        break;
                                }
                                return statusText;
                            }
                        },
                        {
                            data: 'note',
                            name: 'note'
                        },
                        {
                            data: 'lampiran', // Ganti 'lampiran' dengan nama field di database Anda
                            name: 'lampiran',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                if (data) {
                                    return `<a href="storage/${data.substr(7)}" target="_blank">Lihat Lampiran</a>`;
                                } else {
                                    return 'Tidak ada lampiran';
                                }
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(data, type, row) {
                                return `<button type="button" class="btn btn-primary btn-sm btn-update"  data-toggle="modal" data-target="#cuziaDetailModal" data-empno="${row.empno}" data-uuid="${row.id}" data-nama="${row.empnm}" data-jenis="${row.jenisizin}" data-tgl_mulai="${row.tgl_mulai}" data-tgl_selesai="${row.tgl_selesai}" data-status="${row.approval_status}" data-note="${row.note}">Detail</button>`;
                            }

                        }

                    ],

                    initComplete: function() {
                        var userInfoOccupation = '<?php echo $userInfoOccupation; ?>';
                        var userInfoDept = '<?php echo $userInfoDept; ?>';

                        if (userInfoOccupation == 'GMR' || userInfoDept == 'HRD') {
                            this.api().columns([3]).every(function() {
                                var column = this;

                                // Create a container for the filter and clear button
                                var filterContainer = $(
                                    '<div class="d-flex align-items-center"></div>').appendTo($(
                                    '#employee-table_wrapper .top'));

                                // Create a select element
                                var select = $(
                                        '<select class="form-control form-control-sm col-md-3  mb-md-0" id="departmentSelect"><option value="">-- Select Department --</option><option value="hr">HRD & GA</option><option value="ir">IR & LEGAL</option><option value="enb">ENB</option><option value="enu">ENU</option><option value="mte">MTE</option><option value="qab">QAB</option><option value="msy">MSY</option><option value="qau">QAU</option><option value="itd">ITD</option><option value="PRO BODY">PRO BODY</option><option value="pro unit dc">PRO UNIT DC</option><option value="pro unit ma">PRO UNIT MA</option><option value="psd">PSD</option><option value="ppic">PPIC</option><option value="eqec">EQEC</option><option value="mma">MMA</option><option value="pro ec">PRO EC</option></select>'
                                    )
                                    .appendTo(filterContainer) // Append it to the container

                                    // Add event listeners for keyup and change events
                                    .on('keyup change', function() {
                                        column.search($(this).val()).draw();
                                    });

                                // Add a clear button to reset the filter
                                $('<button class="btn btn-secondary btn-sm ml-2" id="clearFilter">Clear</button>')
                                    .appendTo(filterContainer)
                                    .on('click', function() {
                                        select.val('')
                                            .change(); // Reset the select and trigger change event
                                    });
                            });
                        }
                    }
                });

                $('#filter_button').on('click', function() {
                    table.ajax.reload();

                });
                $('#employee-table').on('click', '.btn-update', function() {
                    console.log($(this).data('jenisizin'));
                    $('#nama3').val($(this).data('nama'));
                    $('#npk').val($(this).data('empno'));
                    $('#tgl_mulai3').val($(this).data('tgl_mulai'));
                    $('#tgl_selesai4').val($(this).data('tgl_selesai'));
                    $('#jenisizin2').val($(this).data('jenis'));
                    $('#note3').val($(this).data('note'));
                });
            });
        </script>
    @endpush
@endsection
