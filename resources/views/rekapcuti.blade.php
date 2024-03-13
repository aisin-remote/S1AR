@extends('layouts.app', ['title' => 'Cuzia Attendance'])

@section('content')
@if(session('error'))
<script>
    alert("{{ session('error') }}");
</script>
@endif

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Rekap Cuti Karyawan </h1>
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
                                    <th class="align-middle">Tanggal Cuti</th>
                                    <th class="align-middle">Status</th>
                                    <th class="align-middle">Keterangan</th>
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
<div class="modal fade" id="cuziaDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModal">Detail Pengajuan Cuti</h5>
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
                                    <label for="jenis_cuzia">Jenis Cuzia:</label>
                                    <select class="form-control" id="jenis_cuzia" name="jenis_cuzia" disabled>
                                        <option value="" disabled selected>Pilih Jenis Cuzia</option>
                                        <option value="CT">Cuti Tahunan</option>
                                        <option value="CL">Cuti Istimewa</option>
                                        <!-- Add more options as needed -->
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

@push('scripts')

<script>

    $(document).ready(function() {
        var table = $('#employee-table').DataTable({
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            processing: true,
            ajax: {
                url: '{{ url("/rekapcuti/datatables") }}',
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                {
                    data: 'tgl_pengajuan',
                    name: 'tgl_pengajuan'
                },{
                    data: 'empno',
                    name: 'empno'
                },
                {
                    data: 'empnm',
                    name: 'empnm',
                    orderable: false
                },

                {
                    data: 'jeniscuti',
                    name: 'jeniscuti'
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
                    switch(data) {
                        case '1':
                            statusText = 'Disetujui oleh Atasan 1';
                            break;
                        case '2':
                            statusText = 'Disetujui oleh Atasan 2';
                            break;
                        case '-1':
                            statusText = 'Ditolak';
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
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `<button type="button" class="btn btn-primary btn-sm btn-update"  data-toggle="modal" data-target="#cuziaDetailModal" data-empno="${row.empno}" data-uuid="${row.id}" data-nama="${row.empnm}" data-jenis="${row.jeniscuti}" data-tgl_mulai="${row.tgl_mulai}" data-tgl_selesai="${row.tgl_selesai}" data-status="${row.approval_status}" data-note="${row.note}">Detail</button>`;
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

        $('#employee-table').on('click', '.btn-update', function(){
            console.log($(this).data('tgl_mulai'));
            $('#nama').val($(this).data('nama'));
            $('#npk').val($(this).data('empno'));
            $('#tgl_mulai').val($(this).data('tgl_mulai'));
            $('#tgl_selesai').val($(this).data('tgl_selesai'));
            $('#jenis_cuzia').val($(this).data('jenis'));
            $('#note').val($(this).data('note'));
            $('#btn-tolak').attr('data-id', $(this).data('uuid'));
            $('#btn-setuju').attr('data-id', $(this).data('uuid'));
        });

        $('#btn-tolak').on('click', function() {
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "{{ route('cuzia.approve') }}";
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
            input.value = '0';
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });

        $('#btn-setuju').on('click', function() {
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "{{ route('cuzia.approve') }}";
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
        });

    });
</script>
@endpush
@endsection
