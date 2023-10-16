@extends('layouts.app', ['title' => 'Dashboard'])
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Data Karyawan</h1>
        </div>

        <div class="card">
            <div class="row px-3 py-3">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <tr class="text-center align middle">
                                <th>NPK</th>
                                <th>Nama</th>
                                <th>Tanggal Masuk</th>
                                <th>Waktu Masuk</th>
                                <th>Tanggal Keluar</th>
                                <th>Waktu Keluar</th>
                            </tr>
                            @foreach($data as $row)
                            <tr>
                                <td>{{ $row->empno }}</td>
                                <td>{{ $row->empnm }}</td>
                                <td>{{ $row->datin }}</td>
                                <td>{{ $row->timin }}</td>
                                <td>{{ $row->datot }}</td>
                                <td>{{ $row->timot }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection