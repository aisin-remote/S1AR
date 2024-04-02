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
                                        <i class="fas fa-plus"></i> Pengajuan Izin
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-bordered" id="employee-table">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle"
                                            style="text-align: center; vertical-align: middle;">Jenis Izin</th>
                                        {{-- <th class="align-middle" style="text-align: center; vertical-align: middle;">Action --}}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jenisIzin as $izin)
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle;">{{ $izin->jenisizin }}
                                            </td>
                                            {{-- <td style="text-align: center; vertical-align: middle;">
                                                <!-- Contoh Aksi -->
                                                <a href="#" class="btn btn-sm" style="background-color: #007bff; color: #fff; border-color: #007bff; text-align: center; vertical-align: middle; display: inline-block; width: auto; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem;"
                                                data-toggle="modal" data-target="#editModal{{$izin->jenisizin}}">
                                                Edit
                                             </a>
                                                {{-- <form action="#" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form> --}}
                                            {{-- </td> --}}
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

    <!-- Add this modal code at the end of your HTML file, before closing the body tag -->
    <div class="modal fade" id="cuziaizinmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                    <div class="container-fluid">
                        <div class="row" style="display: block">
                            <form action="{{ route('jenisizin.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="jenis_izin">Jenis Izin</label>
                                    <input type="text" class="form-control" id="jenis_izin" name="jenis_izin"
                                        placeholder="Masukkan Jenis Izin">
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
