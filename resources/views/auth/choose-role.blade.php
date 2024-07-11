<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuzia Attendance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Add custom styles for the sidebar */
        .main-sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .sidebar-brand img {
            display: block;
            margin: 0 auto;
        }
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        .sidebar-menu li {
            margin: 10px 0;
        }
        .sidebar-menu a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #333;
            display: block;
        }
        .sidebar-menu a:hover {
            background-color: #ddd;
        }
        .main-content {
            margin-left: 260px; /* Same width as the sidebar + some margin */
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
            <div class="sidebar-brand">
                <img src="{{ url('assets/img/S1AR.png') }}" alt="S1AR" class="img-fluid" style="max-width: 100px;">
            </div>
            <div class="sidebar-brand sidebar-brand-sm">
                <img src="{{ url('assets/img/S1AR.png') }}" alt="S1AR" class="img-fluid" style="max-width: 50px;">
            </div>

            @php
                $role = session('role', 'employee'); // Default role is employee
            @endphp

            <ul class="sidebar-menu">
                <li>
                    <a href="/" class="nav-link{{ request()->is('dashboard*') ? ' text-primary' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if ($role == 'admin')
                    <li>
                        <a href="/holiday" class="nav-link{{ request()->is('holiday*') ? ' text-primary' : '' }}">
                            <i class="fas fa-calendar"></i>
                            <span>Master Holiday</span>
                        </a>
                    </li>
                    <li>
                        <a href="/jenisizin" class="nav-link{{ request()->is('jenisizin*') ? ' text-primary' : '' }}">
                            <i class="fas fa-calendar"></i>
                            <span>Master Jenis Izin</span>
                        </a>
                    </li>
                @endif

                <li class="dropdown{{ request()->is('cuziacuti*') || request()->is('cuzia*') ? ' active' : '' }}">
                    <a href="#" class="nav-link has-dropdown{{ request()->is('cuziacuti*') || request()->is('cuzia*') ? ' text-primary' : '' }}">
                        <i class="fas fa-umbrella-beach"></i>
                        <span>Cuti</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/cuziacuti" class="nav-link{{ request()->is('cuziacuti*') ? ' text-primary' : '' }}">
                                Pengajuan Cuti
                            </a>
                        </li>
                        @if ($role == 'admin')
                            <li>
                                <a href="/cuzia" class="nav-link{{ request()->is('cuzia*') ? ' text-primary' : '' }}">
                                    Approval Cuti
                                </a>
                            </li>
                            <li>
                                <a href="/rekapcuti" class="nav-link{{ request()->is('rekapcuti*') ? ' text-primary' : '' }}">
                                    Rekap Cuti
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <li class="dropdown{{ request()->is('cuziaizin*') || request()->is('izin*') ? ' active' : '' }}">
                    <a href="#" class="nav-link has-dropdown{{ request()->is('cuziaizin*') || request()->is('izin*') ? ' text-primary' : '' }}">
                        <i class="fas fa-check-circle"></i>
                        <span>Izin</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/cuziaizin" class="nav-link{{ request()->is('cuziaizin*') ? ' text-primary' : '' }}">
                                Pengajuan Izin
                            </a>
                        </li>
                        @if ($role == 'admin')
                            <li>
                                <a href="/izin" class="nav-link{{ request()->is('izin*') ? ' text-primary' : '' }}">
                                    Approval Izin
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            </ul>
        </aside>
    </div>

    <div class="main-content">
        @if (session('error'))
            <script>
                alert("{{ session('error') }}");
            </script>
        @endif

        <section class="section">
            <div class="section-header">
                <h1>Data Cuti Karyawan</h1>
            </div>
            <div class="card">
                <div class="row px-3 py-3">
                    <div class="col-lg-12">
                        <div class="card-body">
                            <form method="POST" action="{{ route('set-role') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="role">Select your role:</label>
                                    <select id="role" name="role" class="form-control" required>
                                        <option value="employee">Employee</option>
                                        <option value="admin">HR Admin</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Continue</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
