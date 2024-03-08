@extends('layouts.app', ['title' => 'Master Holiday'])

@section('content')

@if(session('error'))
<script>
    alert("{{ session('error') }}");
</script>
@endif

@if(session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Master Holiday</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-6 mb-2 mb-lg-0">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#uploadModal">
                        Upload Dokumen
                    </button>
                </div>

                <div class="col-lg-6 text-lg-right">
                    <a href="{{ asset('storage/master/import_holiday.xlsx') }}" class="btn btn-success btn-block">Download Template</a>
                </div>

                <div class="col-lg-12 mt-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered text-center align-middle" id="holiday-table">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">Date Holiday</th>
                                    <th class="text-center align-middle">Note Holiday</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('upload-holiday') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="document">Pilih Dokumen (.xlsx, .xls)</label>
                        <input type="file" class="form-control" name="document" accept=".xlsx, .xls" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#holiday-table').DataTable({
            processing: true,
            ajax: {
                url: '{{ url("/holiday/datatables") }}',
                data: function(d) {
                }
            },
            columns: [{
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'note',
                    name: 'note',
                    orderable: false
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
@endsection
