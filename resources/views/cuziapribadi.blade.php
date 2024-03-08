@extends('layouts.app', ['title' => 'Cuzia Pribadi Attendance'])

@section('content')
@if(session('error'))
<script>
    alert("{{ session('error') }}");
</script>
@endif

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Cuzia Pribadi Karyawan </h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <form id="filter-form">
                        <div class="form-row">
                            <div class="form-group col-auto">
                                <label for="start_date">Start Date:</label>
                                <input type="date" class="form-control form-control-sm" id="start_date" name="start_date">
                            </div>
                            <div class="form-group col-auto">
                                <label for="end_date">End Date:</label>
                                <input type="date" class="form-control form-control-sm" id="end_date" name="end_date">
                            </div>
                            <div class="form-group col-auto">
                                <label for="filter_button">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-sm form-control form-control-sm" id="filter_button">Apply Filter</button>
                            </div>
                            <div class="form-group ml-auto">
                                <label for="pengajuan_button">&nbsp;</label>
                                <button type="button" class="btn btn-success btn-sm form-control form-control-sm" data-toggle="modal" data-target="#cuziapribadimodal" id="pengajuan_button">Pengajuan Cuzia</button>
                            </div>

                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered" id="employee-table">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">NPK</th>
                                    <th class="text-center align-middle">Nama</th>
                                    <th class="align-middle">Jenis</th>
                                    <th class="align-middle">Tanggal Cuzia</th>
                                    <th class="align-middle">Tanggal Pengajuan</th>
                                    <th class="align-middle">Status</th>
                                    <th class="align-middle">Keterangan</th>
                                    <th class="align-middle">Action</th>
                                </tr>
                            </thead>
                            {{-- <tbody>
                                @if (count($data1) > 0)
                                @foreach($data1 as $data)
                                <tr>
                                    <td class="px-4 py-2">{{ $data->empno}}</td>
                                    <td class="px-4 py-2">{{ $data->empnm }}</td>
                                    <td class="px-4 py-2">{{ $data->hirar }}</td>
                                    <td class="px-4 py-2">{{ $data->descr }}</td>
                                    <td class="px-4 py-2">{{ $data->rsccd }}</td>
                                    <td class="px-4 py-2">{{ $data->schdt }}</td>
                                    <td class="px-4 py-2">{{ $data->crtdt }}</td>
                                    <td class="px-4 py-2">{{ $data->stts }}</td>
                                    <td class="px-4 py-2 ">
                                        <a href="{{ route('problem.editData', $data->id) }}" class="text-blue-500 hover:text-blue-700 font-bold">Edit</a>
                                        <a href="{{ route('problem.delete', ['id' => $data->id]) }}" class="text-red-500 hover:text-red-700 font-bold">Hapus</a>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="17" class="px-4 py-2 border text-center">No data available.
                                    </td>
                                </tr>
                                @endif
                            </tbody> --}}
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

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
                                        <option value="3">IMP Mendapatkan Upah</option>
                                        <option value="4">IMP Tidak Mendapatkan Upah</option>
                                        <option value="5">Sakit</option>
                                        <option value="6">Dinas Luar</option>
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
<!-- Add this modal code at the end of your HTML file, before closing the body tag -->
<div class="modal fade" id="cuziaDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModal">Detail Cuzia Pribadi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                            <form id="leaveRequestForm" enctype="multipart/form-data">
                                <div class="form-group ">
                                    <label for="nama">Nama:</label>
                                    <input type="text" class="form-control" id="nama" name="nama">
                                </div>
                                <div class="form-group ">
                                    <label for="npk">NPK:</label>
                                    <input type="text" class="form-control" id="npk" name="npk">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 ">
                                        <label for="tgl_mulai">Tanggal Mulai:</label>
                                        <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai">
                                    </div>
                                    <div class="form-group col-md-6 ">
                                        <label for="tgl_selesai">Tanggal Selesai:</label>
                                        <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai">
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="jenis_cuzia">Jenis Cuzia:</label>
                                    <select class="form-control" id="jenis_cuzia" name="jenis_cuzia">
                                        <option value="" disabled selected>Pilih Jenis Cuzia</option>
                                        <option value="1">Cuti Tahunan</option>
                                        <option value="2">Cuti Istimewa</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                                <div class="form-group " id="lampiranContainer" style="display: none;">
                                    <label for="lampiran">Unggah Dokumen/Lampiran:</label>
                                    <input type="file" class="form-control-file" id="lampiran" name="lampiran">
                                </div>
                                </div>
                    </div>
                </div>
        </div>
    </div>
</div>
@push('scripts')

<script>
//   $(document).ready(function() {
//         $('#jenis_cuzia').change(function() {
//             var selectedValue = $(this).val();
//             if (selectedValue == '1' || selectedValue == '2') {
//                 $('#lampiranContainer').show();
//             } else {
//                 $('#lampiranContainer').hide();
//             }
//         })
//         });
    $(document).ready(function() {
        var table = $('#employee-table').DataTable({
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            processing: true,
            ajax: {
                url: '{{ url("/cuziapribadi/datatables") }}',
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [{
                    data: 'empno',
                    name: 'empno'
                },
                {
                    data: 'empnm',
                    name: 'empnm',
                    orderable: false
                },

                {
                    data: 'rsccd',
                    name: 'rsccd'
                },
                {
                    data: 'schdt',
                    name: 'schdt'
                },
                {
                    data: 'crtdt',
                    name: 'crtdt'
                },
                {
                    data: 'stts',
                    name: 'stts'
                },
                {
                    data: 'note',
                    name: 'note'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `<button type="button" class="btn btn-primary btn-sm" style="background-color: #005d75;" data-toggle="modal" data-target="#cuziaDetailModal" data-npk="${row.npk}" data-nama="${row.empnm}" data-jenis="${row.jenis_cuzia}" data-tgl_mulai="${row.tgl_mulai}" data-tgl_selesai="${row.tgl_selesai}" data-status="${row.stts}">Detail</button>`;
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
            }
        });

        $('#filter_button').on('click', function() {
            table.ajax.reload();
        });
    });
</script>
@endpush
@endsection
