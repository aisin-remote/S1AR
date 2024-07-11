@extends('layouts.app1')

@section('sidebar')
    <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
            <div class="sidebar-brand">
                <img src="{{ url('assets/img/S1AR.png') }}" alt="S1AR" class="img-fluid" style="max-width: 100px;">
            </div>

            <div class="sidebar-brand sidebar-brand-sm">
                <img src="{{ url('assets/img/S1AR.png') }}" alt="S1AR" class="img-fluid" style="max-width: 50px;">
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="/select-role" class="nav-link{{ request()->is('select-role*') ? ' text-primary' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="/logout"  class="nav-link{{ request()->is('logout*') ? ' text-primary' : '' }}">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>

                </li>
                {{-- <li>
                    <a href="/logout"  class="nav-link{{ request()->is('logout*') ? ' text-primary' : '' }}">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li> --}}
            </ul>
        </aside>
    </div>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>S1AR (Sistem 1 Atap HR) </h1>
            </div>

            <div class="card">
                <div class="row px-3 py-3">
                    <div class="col-lg-12">
                        <div class="card-header">Pilih Peran</div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('setRole') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="role">Peran:</label>
                                    <select class="form-control" id="role" name="role">
                                        @foreach (session('roles') as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Lanjutkan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@endsection
