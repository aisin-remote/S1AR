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
                <h1>Data Jenis Izin</h1>
            </div>

            <div class="card">
                <div class="row px-3 py-3">
                    <div class="col-lg-12">
                        <form id="filter-form">
                            <div class="form-row">
                                <div class="form-group ml-auto">
                                    <label for="pengajuan_button">&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-sm form-control form-control-sm"
                                        data-toggle="modal" data-target="#cuziaizinmodal" id="pengajuan_button">
                                        <i class="fas fa-plus"></i> Jenis Izin
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="col-lg-12 mt-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm table-bordered text-center align-middle table-bor"
                                    id="jenisizin-table">
                                    <thead class="thead-custom">
                                        <tr>
                                            <th class="text-center align-middle">No</th>
                                            <th class="text-center align-middle">Jenis Izin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables will populate the body -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="cuziaizinmodal" tabindex="-1" role="dialog" aria-labelledby="editModal"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModal">Detail Jenis Izin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('jenisizin.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="jenis_izin">Jenis Izin</label>
                            <input type="text" required class="form-control" id="jenis_izin" name="jenis_izin"
                                placeholder="Masukkan Jenis Izin">
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                var table = $('#jenisizin-table').DataTable({
                    processing: true,
                    ajax: {
                        url: '{{ url('/jenisizin/datatables') }}',
                        data: function(d) {}
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                // Mengembalikan nomor urut berdasarkan nomor baris (index + 1)
                                return meta.row + 1;
                            }
                        },
                        {
                            data: 'jenisizin',
                            name: 'jenisizin'
                        }
                    ],
                    responsive: true,
                });

                $('#filter_button').on('click', function() {
                    table.ajax.reload();
                });
            });
        </script>
    @endpush

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

        /* Table Styling */
        .table.table-bor,
        .table.table-bor th,
        .table.table-bor td {
            border: 1px solid #1e1d1d;
        }
    </style>
@endsection
