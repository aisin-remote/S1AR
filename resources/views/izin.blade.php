@extends('layouts.app', ['title' => 'Cuzia Attendance'])

@section('content')
    @if (session('error'))
        <script>
            alert("{{ session('error') }}");
        </script>
    @endif

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Izin Karyawan </h1>
            </div>

            <div class="card">
                <div class="row px-3 py-3">
                    <div class="col-lg-12">
                        <form id="filter-form">
                            <div class="form-row">
                                {{-- <div class="form-group col-auto">
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
                                </div> --}}
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table-bor table-striped table-sm table-bordered w-100" id="employee-table">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="text-center align-middle">No</th>
                                        <th class="align-middle">Tanggal Pengajuan</th>
                                        <th class="text-center align-middle">NPK</th>
                                        <th class="text-center align-middle">Nama</th>
                                        <th class="align-middle">Jenis</th>
                                        <th class="align-middle">Tanggal Izin</th>
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
    <div class="modal fade" id="izinDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                                <input type="text" class="form-control" id="nama" name="nama" disabled>
                            </div>
                            <div class="form-group ">
                                <label for="npk">NPK:</label>
                                <input type="text" class="form-control" id="npk" name="npk" disabled>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 ">
                                    <label for="tgl_mulai">Tanggal Mulai:</label>
                                    <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai" disabled>
                                </div>
                                <div class="form-group col-md-6 ">
                                    <label for="tgl_selesai">Tanggal Selesai:</label>
                                    <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai" disabled>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="jenis_izin">Jenis Cuzia:</label>
                                <select required class="form-control" id="jenisizin" name="jenis_izin"disabled>
                                    <option value="" disabled selected>Pilih Jenis Izin</option>
                                    @foreach ($jenisIzin as $izin)
                                        <option value="{{ $izin->jenisizin }}">{{ $izin->jenisizin }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label for="note">Keterangan:</label>
                                <textarea class="form-control" id="note" name="note" disabled></textarea>
                            </div>
                            <button id="btn-tolak" class="btn btn-danger btn-sm m-1 btn-tolak" style="width: 100px;">
                                Tolak
                            </button>
                            <button id="btn-setuju" class="btn btn-primary btn-sm m-1 btn-setuju" style="width: 100px;">
                                Setuju
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rejectionReasonModal" tabindex="-1" role="dialog" aria-labelledby="rejectionReasonLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionReasonLabel">Masukkan Alasan Penolakan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row" style="display: block">
                            <div class="form-group mb-2">
                                <label for="rejectionReason">Alasan Penolakan:</label>
                                <textarea id="rejectionReason" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmRejection">Oke</button>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                var table = $('#employee-table').DataTable({
                    dom: '<"top"f>rt<"bottom"lip><"clear">',
                    processing: true,
                    ajax: {
                        url: '{{ url('/izin/datatables') }}',
                        data: function(d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                        }
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                // Mengembalikan nomor urut berdasarkan nomor baris (index + 1)
                                return meta.row + 1;
                            }
                        },
                        {
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
                                return `<button type="button" class="btn btn-primary btn-sm btn-update"  data-toggle="modal" data-target="#izinDetailModal" data-empno="${row.empno}" data-uuid="${row.id}" data-nama="${row.empnm}" data-jenis="${row.jenisizin}" data-tgl_mulai="${row.tgl_mulai}" data-tgl_selesai="${row.tgl_selesai}" data-status="${row.approval_status}" data-note="${row.note}">Detail</button>`;
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
                                        '<select class="form-control form-control-sm col-md-3 mb-2 mb-md-0" id="departmentSelect"><option value="">-- Select Department --</option><option value="hr">HRD & GA</option><option value="ir">IR & LEGAL</option><option value="enb">ENB</option><option value="enu">ENU</option><option value="mte">MTE</option><option value="qab">QAB</option><option value="msy">MSY</option><option value="qau">QAU</option><option value="itd">ITD</option><option value="PRO BODY">PRO BODY</option><option value="pro unit dc">PRO UNIT DC</option><option value="pro unit ma">PRO UNIT MA</option><option value="psd">PSD</option><option value="ppic">PPIC</option><option value="eqec">EQEC</option><option value="mma">MMA</option><option value="pro ec">PRO EC</option></select>'
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
                    console.log($(this).data('tgl_mulai'));
                    $('#nama').val($(this).data('nama'));
                    $('#npk').val($(this).data('empno'));
                    $('#tgl_mulai').val($(this).data('tgl_mulai'));
                    $('#tgl_selesai').val($(this).data('tgl_selesai'));
                    $('#jenisizin').val($(this).data('jenis'));
                    $('#note').val($(this).data('note'));
                    $('#btn-tolak').attr('data-id', $(this).data('uuid'));
                    $('#btn-setuju').attr('data-id', $(this).data('uuid'));
                });

                // $('#btn-tolak').on('click', function() {
                //     var reason = prompt("Masukkan alasan penolakan:");
                //     if (reason !== null && reason !== "") {
                //         var form = document.createElement("form");
                //         form.method = "POST";
                //         form.action =
                //             "{{ route('izin.approve') }}"; // Your existing route for handling approval/rejection

                //         // CSRF Token
                //         var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                //             'content');
                //         var hiddenToken = document.createElement('input');
                //         hiddenToken.type = 'hidden';
                //         hiddenToken.name = '_token';
                //         hiddenToken.value = csrfToken;
                //         form.appendChild(hiddenToken);

                //         // ID of the request
                //         var inputId = document.createElement("input");
                //         inputId.type = 'hidden';
                //         inputId.name = 'id';
                //         inputId.value = $(this).data(
                //             'id'); // Ensure your button has a data-id attribute with the request ID
                //         form.appendChild(inputId);

                //         // Approval status (0 for rejection)
                //         var inputStatus = document.createElement("input");
                //         inputStatus.type = 'hidden';
                //         inputStatus.name = 'status';
                //         inputStatus.value = '0'; // '0' signifies rejection
                //         form.appendChild(inputStatus);

                //         // Reason for rejection
                //         var inputReason = document.createElement("input");
                //         inputReason.type = 'hidden';
                //         inputReason.name = 'reason';
                //         inputReason.value = reason; // The reason collected from the prompt
                //         form.appendChild(inputReason);

                //         document.body.appendChild(form);
                //         form.submit();
                //     }
                // });


                $('#btn-setuju').on('click', function() {
                    var form = document.createElement("form");
                    form.method = "POST";
                    form.action = "{{ route('izin.approve') }}";
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var hiddenToken = document.createElement('input');
                    hiddenToken.type = 'hidden';
                    hiddenToken.name = '_token';
                    hiddenToken.value = csrfToken;
                    form.appendChild(hiddenToken);

                    var input = document.createElement("input");
                    input.name = 'id';
                    input.value = $(this).data('id');
                    form.appendChild(input);

                    var input = document.createElement("input");
                    input.name = 'status';
                    input.value = '1';
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                     // Tampilkan SweetAlert dengan konfigurasi ukuran card dan tunda penutupan otomatis
                     Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Data telah berhasil diapprove.',
                        customClass: {
                            popup: 'swal2-small' // Menggunakan kelas khusus untuk menentukan ukuran card (swal2-large)
                        },
                        allowOutsideClick: false, // Mencegah penutupan dengan klik di luar alert
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                });

            });
        </script>
        <script>
            document.getElementById('btn-tolak').addEventListener('click', function() {
                $('#rejectionReasonModal').modal('show');
            });

            document.getElementById('confirmRejection').addEventListener('click', function() {
                var reason = document.getElementById('rejectionReason').value;
                if (reason !== "") {
                    var form = document.createElement("form");
                    form.method = "POST";
                    form.action = "{{ route('izin.approve') }}"; // Your existing route for handling approval/rejection

                    // CSRF Token
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var hiddenToken = document.createElement('input');
                    hiddenToken.type = 'hidden';
                    hiddenToken.name = '_token';
                    hiddenToken.value = csrfToken;
                    form.appendChild(hiddenToken);

                    // ID of the request
                    var inputId = document.createElement("input");
                    inputId.type = 'hidden';
                    inputId.name = 'id';
                    inputId.value = document.getElementById('btn-tolak').dataset
                    .id; // Ensure your button has a data-id attribute with the request ID
                    form.appendChild(inputId);

                    // Approval status (0 for rejection)
                    var inputStatus = document.createElement("input");
                    inputStatus.type = 'hidden';
                    inputStatus.name = 'status';
                    inputStatus.value = '0'; // '0' signifies rejection
                    form.appendChild(inputStatus);

                    // Reason for rejection
                    var inputReason = document.createElement("input");
                    inputReason.type = 'hidden';
                    inputReason.name = 'reason';
                    inputReason.value = reason; // The reason collected from the prompt
                    form.appendChild(inputReason);

                    document.body.appendChild(form);
                    form.submit();
                     // Tampilkan SweetAlert dengan konfigurasi ukuran card dan tunda penutupan otomatis
                     Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Data telah berhasil ditolak.',
                            customClass: {
                                popup: 'swal2-small' // Menggunakan kelas khusus untuk menentukan ukuran card (swal2-large)
                            },
                            allowOutsideClick: false, // Mencegah penutupan dengan klik di luar alert
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                } else {
                    alert("Alasan penolakan harus diisi!");
                }
            });
        </script>
        <style>
            /* Header Styling */
            .thead-custom {
                background-color: #054483;
                color: white;
            }

            /* Table Styling */
            .table-bor {
                width: 80%;
                border-collapse: collapse;
            }

            .table-bor th,
            .table-bor td {
                border: 1px solid #1e1d1d;
            }
        </style>
    @endpush
@endsection
