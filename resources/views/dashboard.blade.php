@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard</h1>
            </div>
            <div class="card">
                <div id="content">
                    <div class="container-fluid">
                        <div class="row">
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
                                    ",
                                    ),
                                );

                                if (!empty($userInfo)) {
                                    $npkDesc = $userInfo[0]->hirar;
                                    $cleanedString = str_replace(' ', '', $npkDesc);
                                    $jumlahKarakter = strlen($cleanedString);

                                    if ($jumlahKarakter == 5) {
                                        $jenis = 'KDP';
                                    } elseif ($jumlahKarakter == 7) {
                                        $jenis = 'SPV';
                                    } elseif ($jumlahKarakter == 9) {
                                        $jenis = 'LDR/OPR';
                                    } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                                        $jenis = 'GMR';
                                    } else {
                                        $jenis = 'Jenis tidak dikenali';
                                    }
                                } else {
                                    $jenis = 'Jenis tidak dikenali';
                                }

                                $isadmin = $userInfo[0]->is_admin;
                                $cleanedStringDept = trim($userInfo[0]->descr);
                                $userInfoOccupation = $jenis;
                                $userInfoDept = $cleanedStringDept;

                            @endphp
                            <div class="col-xl-12 col-md-12 mb-4">
                                @if (in_array(session('selected_role'), ['HRD Admin', 'Supervisor', 'Kepala Department','GMR']))
                                    <div class="card w-100" id="panelReminder" style="padding: 10px;">
                                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center"
                                            style="font-weight: bold; padding: 10px;">
                                            <div>
                                                <i class="fa fa-exclamation-triangle text-white"></i> SISTEM PENGINGAT
                                            </div>
                                            <a data-toggle="collapse" style="color: white;" href="#collapseReminder"
                                                aria-expanded="false" class="collapsed">
                                                <i class="fa fa-caret-up"></i>
                                            </a>
                                        </div>
                                        <div id="collapseReminder" class="panel-collapse collapse" aria-expanded="false">
                                            <div class="card-block">
                                                @if (in_array(session('selected_role'), ['HRD Admin']))
                                                    @if ($jumlahpengajuancuti1 > 0 || $jumlahpengajuanizin1 > 0)
                                                        @if ($jumlahpengajuancuti1 > 0)
                                                            <p style="color: #000000; font-weight: bold; font-size: 15px;">
                                                                Terdapat {{ $jumlahpengajuancuti1 }} pengajuan cuti yang
                                                                harus segera diproses dari:
                                                            </p>
                                                            <table style="width:100%; border: none;">
                                                                @foreach (explode(',', $nama_karyawan) as $index => $karyawan)
                                                                    <tr style="border: none;">
                                                                        <td style="border: none;">
                                                                            {{ $index + 1 }}. <a
                                                                                href="{{ route('cuzia') }}">{{ $karyawan }}</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @endif
                                                        @if ($jumlahpengajuanizin1 > 0)
                                                            <p style="color: #000000; font-weight: bold; font-size: 15px;">
                                                                Terdapat {{ $jumlahpengajuanizin1 }} pengajuan izin yang
                                                                harus segera diproses dari:
                                                            </p>
                                                            <table style="width:100%; border: none;">
                                                                @foreach (explode(',', $nama_karyawan1) as $index => $karyawan)
                                                                    <tr style="border: none;">
                                                                        <td style="border: none;">
                                                                            {{ $index + 1 }}. <a
                                                                                href="{{ route('izinview') }}">{{ $karyawan }}</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @endif
                                                    @else
                                                        <p style="color: #000000; font-weight: bold; font-size: 15px;">Saat
                                                            ini tidak ada informasi pengingat yang tersedia.</p>
                                                    @endif
                                                @endif

                                                @if (in_array(session('selected_role'), ['Supervisor']))
                                                    @if ($jumlahpengajuancuti2 > 0 || $jumlahpengajuanizin2 > 0)
                                                        @if ($jumlahpengajuancuti2 > 0)
                                                            <p style="color: #000000; font-weight: bold; font-size: 15px;">
                                                                Terdapat {{ $jumlahpengajuancuti2 }} pengajuan cuti yang
                                                                harus segera diproses dari:
                                                            </p>
                                                            <table style="width:100%; border: none;">
                                                                @foreach (explode(',', $pengajuanCutiList) as $index => $cuti)
                                                                    <tr style="border: none;">
                                                                        <td style="border: none;">
                                                                            {{ $index + 1 }}. <a
                                                                                href="{{ route('cuzia') }}">{{ $cuti }}</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @endif
                                                        @if ($jumlahpengajuanizin2 > 0)
                                                            <p style="color: #000000; font-weight: bold; font-size: 15px;">
                                                                Terdapat {{ $jumlahpengajuanizin2 }} pengajuan izin yang
                                                                harus segera diproses dari:
                                                            </p>
                                                            <table style="width:100%; border: none;">
                                                                @foreach (explode(',', $pengajuanIzinList) as $index => $izin)
                                                                    <tr style="border: none;">
                                                                        <td style="border: none;">
                                                                            {{ $index + 1 }}. <a
                                                                                href="{{ route('izinview') }}">{{ $izin }}</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @endif
                                                    @else
                                                        <p style="color: #000000; font-weight: bold; font-size: 15px;">Saat
                                                            ini tidak ada informasi pengingat yang tersedia.</p>
                                                    @endif
                                                @endif

                                                @if (in_array(session('selected_role'), ['Kepala Department']))
                                                    @if ($jumlahpengajuancuti3 > 0 || $jumlahpengajuanizin3 > 0)
                                                        @if ($jumlahpengajuancuti3 > 0)
                                                            <p style="color: #000000; font-weight: bold; font-size: 15px;">
                                                                Terdapat {{ $jumlahpengajuancuti3 }} pengajuan cuti yang
                                                                harus segera diproses dari:
                                                            </p>
                                                            <table style="width:100%; border: none;">
                                                                @foreach (explode(',', $pengajuanCutiList1) as $index => $cuti)
                                                                    <tr style="border: none;">
                                                                        <td style="border: none;">
                                                                            {{ $index + 1 }}. <a
                                                                                href="{{ route('cuzia') }}">{{ $cuti }}</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @endif
                                                        @if ($jumlahpengajuanizin3 > 0)
                                                            <p style="color: #000000; font-weight: bold; font-size: 15px;">
                                                                Terdapat {{ $jumlahpengajuanizin3 }} pengajuan izin yang
                                                                harus segera diproses dari:
                                                            </p>
                                                            <table style="width:100%; border: none;">
                                                                @foreach (explode(',', $pengajuanIzinList1) as $index => $izin)
                                                                    <tr style="border: none;">
                                                                        <td style="border: none;">
                                                                            {{ $index + 1 }}. <a
                                                                                href="{{ route('izinview') }}">{{ $izin }}</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @endif
                                                    @else
                                                        <p style="color: #000000; font-weight: bold; font-size: 15px;">Saat
                                                            ini tidak ada informasi pengingat yang tersedia.</p>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if (in_array(session('selected_role'), ['Karyawan']))
                            <!-- Saldo Cuti Istimewa Card Example -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card shadow h-100 py-2" style="background-color: #ffa163; color: white;">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Saldo Cuti
                                                    Istimewa</div>
                                                <div class="h5 mb-0 font-weight-bold">{{ $saldocutiistimewa }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Saldo Cuti Tahunan Card Example -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card shadow h-100 py-2" style="background-color: #6f42c1; color: white;">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Saldo Cuti Tahunan
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold">{{ $saldocutitahunan }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
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
                                        GROUP BY kehadiran2.empno, hirarki.hirar, hirarkidesc.descr,users.is_admin
                                        ORDER BY mutdt DESC LIMIT 1;
                                    ",
                                    ),
                                );
                                $isadmin = $userInfo[0]->is_admin;
                                if (!empty($userInfo)) {
                                    $npkDesc = $userInfo[0]->hirar; // Use array syntax
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
                                    } else {
                                        $jenis = 'Jenis tidak dikenali'; // Atur jenis untuk kondisi lainnya
                                    }
                                } else {
                                    $jenis = 'Jenis tidak dikenali';
                                }
                                $cleanedStringDept = trim($userInfo[0]->descr);
                                $userInfoOccupation = $jenis;
                                $userInfoDept = $cleanedStringDept;
                            @endphp
                             @if (in_array(session('selected_role'), ['HRD Admin']))
                                <!-- Pengajuan Cuti Card Example -->
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #076270; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Pengajuan Cuti
                                                    </div>
                                                    <div class="h5 mb-0 font-weight-bold">{{ $jumlahpengajuancuti1 }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (in_array(session('selected_role'), ['HRD Admin']))
                                <!-- Pengajuan Izin Card Example -->
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #9966ff; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Pengajuan Izin
                                                    </div>
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col-auto">
                                                            <div class="h5 mb-0 font-weight-bold">
                                                                {{ $jumlahpengajuanizin1 }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (in_array(session('selected_role'), ['Supervisor']))
                                <!-- Pengajuan Cuti Card Example -->
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #076270; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Pengajuan Cuti
                                                    </div>
                                                    <div class="h5 mb-0 font-weight-bold">{{ $jumlahpengajuancuti2 }}</div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (in_array(session('selected_role'), ['Supervisor']))
                                <!-- Pengajuan Cuti Card Example -->
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #36b9cc; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Pengajuan
                                                        Izin
                                                    </div>
                                                    <div class="h5 mb-0 font-weight-bold">{{ $jumlahpengajuanizin2 }}
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (in_array(session('selected_role'), ['Kepala Department']))
                                <!-- Pengajuan Cuti Card Example -->
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #076270; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Pengajuan
                                                        Cuti
                                                    </div>
                                                    <div class="h5 mb-0 font-weight-bold">{{ $jumlahpengajuancuti3 }}
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (in_array(session('selected_role'), ['Kepala Department']))
                                <!-- Pengajuan Cuti Card Example -->
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #36b9cc; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Pengajuan
                                                        Izin
                                                    </div>
                                                    <div class="h5 mb-0 font-weight-bold">{{ $jumlahpengajuanizin3 }}
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
        </section>
    </div>
@endsection
