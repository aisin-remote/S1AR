<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <img src="{{ url('assets/img/S1AR.png') }}" alt="S1AR" class="img-fluid" style="max-width: 100px;">
        </div>

        <div class="sidebar-brand sidebar-brand-sm">
            <img src="{{ url('assets/img/S1AR.png') }}" alt="S1AR" class="img-fluid" style="max-width: 50px;">
        </div>

        @php
            $npk = auth()->user()->npk;

            $userInfo = DB::connection('mysql2')->select(
                DB::raw(
                    "
                    SELECT kehadiran2.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr, users.is_admin
                    FROM kehadiran2
                    LEFT JOIN hirarki ON kehadiran2.empno = hirarki.empno
                    LEFT JOIN users ON kehadiran2.empno = users.npk
                    LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                    WHERE kehadiran2.empno = $npk
                    GROUP BY kehadiran2.empno, hirarki.hirar, hirarkidesc.descr, users.is_admin
                    ORDER BY mutdt DESC LIMIT 1;
                    "
                )
            );

            // Default values
            $jenis = 'Jenis tidak dikenali';
            $userInfoOccupation = '';
            $userInfoDept = '';

            if (!empty($userInfo)) {
                $npkDesc = $userInfo[0]->hirar; // Use array syntax
                $isadmin = $userInfo[0]->is_admin;
                $cleanedString = str_replace(' ', '', $npkDesc);

                // Hitung jumlah karakter
                $jumlahKarakter = strlen($cleanedString);

                // Tentukan jenis berdasarkan jumlah karakter
                if ($jumlahKarakter == 5) {
                    $jenis = 'KDP';
                } elseif ($jumlahKarakter == 7) {
                    $jenis = 'SPV';
                } elseif ($jumlahKarakter == 9) {
                    $jenis = 'LDR/OPR';
                } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                    $jenis = 'GMR';
                }

                $cleanedStringDept = str_replace(' ', '', $userInfo[0]->descr);
                $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
                $userInfoOccupation = $jenis;
                $userInfoDept = $cleanedStringDeptFinal;
            }
        @endphp

        <ul class="sidebar-menu">
            <li>
                <a href="/dashboard" class="nav-link{{ request()->is('dashboard*') ? ' text-primary' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="#" class="nav-link has-dropdown{{ request()->is('dailyattendance*') || request()->is('weeklyattendance*') || request()->is('monthlyattendance*') ? ' text-primary' : '' }}">
                    <i class="fas fa-th"></i>
                    <span>Attendance</span>
                </a>
                <ul class="dropdown-menu">
                    @if (in_array($userInfoOccupation, ['GMR', 'KDP']) || $userInfoDept == 'HRD')
                        <li>
                            <a href="/dailyattendance" class="nav-link{{ request()->is('dailyattendance*') ? ' text-primary' : '' }}">
                                Daily attendance
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="/monthlyattendance" class="nav-link{{ request()->is('monthlyattendance*') ? ' text-primary' : '' }}">
                            Monthly attendance
                        </a>
                    </li>
                    <li>
                        <a href="/historyattendance" class="nav-link{{ request()->is('historyattendance*') ? ' text-primary' : '' }}">
                            History attendance
                        </a>
                    </li>
                </ul>
            </li>

            @if ($userInfoDept == 'HRD')
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
                    @if (in_array($userInfoOccupation, ['KDP', 'GMR', 'SPV', 'LDR/OPR']) || $userInfoDept == 'HRD')
                        <li>
                            <a href="/cuzia" class="nav-link{{ request()->is('cuzia*') ? ' text-primary' : '' }}">
                                Approval Cuti
                            </a>
                        </li>
                    @endif
                    @if ($isadmin == '1')
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
                    @if (in_array($userInfoOccupation, ['KDP', 'GMR', 'SPV', 'LDR/OPR']) || $userInfoDept == 'HRD')
                        <li>
                            <a href="/izin" class="nav-link{{ request()->is('izin*') ? ' text-primary' : '' }}">
                                Approval Izin
                            </a>
                        </li>
                    @endif
                    @if ($isadmin == '1')
                        <li>
                            <a href="/rekapizin" class="nav-link{{ request()->is('rekapizin*') ? ' text-primary' : '' }}">
                                Rekap Izin
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        </ul>
    </aside>
</div>
