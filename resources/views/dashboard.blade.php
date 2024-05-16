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
                            <div class="col-xl-3 col-md-6 mb-4">
                                <!-- Saldo Cuti Card Example -->
                                <div class="card shadow h-100 py-2" style="background-color: #ffa163; color: white;">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                    Saldo Cuti Istimewa</div>
                                                <div class="h5 mb-0 font-weight-bold">{{ $saldocutiistimewa }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar-alt fa-2x"></i>
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
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                    Saldo Cuti Tahunan</div>
                                                <div class="h5 mb-0 font-weight-bold">{{ $saldocutitahunan }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                GROUP BY kehadiran2.empno, hirarki.hirar, hirarkidesc.descr,users.is_admin
                                ORDER BY mutdt DESC LIMIT 1;
                                ",
                                    ),
                                );
                                $isadmin = $userInfo[0]->is_admin;
                            @endphp
                            {{-- @if ($isadmin == '1')
                                <!-- Pengajuan Cuti Card Example -->
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #36b9cc; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                        Pengajuan Cuti</div>
                                                    <div class="h5 mb-0 font-weight-bold">{{ $jumlahpengajuancuti }}</div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- @endif --}}
                                {{-- @if ($isadmin == '1') --}}
                                <!-- Pengajuan Izin Card Example -->
                                {{-- <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card shadow h-100 py-2" style="background-color: #9966ff; color: white;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                        Pengajuan Izin</div>
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col-auto">
                                                            <div class="h5 mb-0 font-weight-bold">{{ $jumlahpengajuanizin }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif --}}
                        </div>
                    </div>
                </div>
        </section>
    </div>
    </div>
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('cutiChart').getContext('2d');
        var cutiChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'], // Labels for each bar
                datasets: [{
                        label: 'Cuti Requests',
                        data: [
                            {{ $dashboardData['approvedCutiRequests'] }},
                            {{ $dashboardData['pendingCutiRequests'] }},
                            {{ $dashboardData['rejectedCutiRequests'] }}
                        ], // Data for Cuti Requests
                        backgroundColor: [
                            '#36b9cc', // Blue for Approved
                            '#36b9cc', // Blue for Pending
                            '#36b9cc' // Blue for Rejected
                        ],
                        borderColor: [
                            '#36b9cc', // Blue for Approved
                            '#36b9cc', // Blue for Pending
                            '#36b9cc' // Blue for Rejected
                        ],
                        borderWidth: 1
                    },
                    {
                        label: 'Izin Requests',
                        data: [
                            {{ $dashboardData['approvedIzinRequests'] }},
                            {{ $dashboardData['pendingIzinRequests'] }},
                            {{ $dashboardData['rejectedIzinRequests'] }}
                        ], // Data for Izin Requests
                        backgroundColor: [
                            '#9966ff', // Purple for Approved
                            '#9966ff', // Purple for Pending
                            '#9966ff' // Purple for Rejected
                        ],
                        borderColor: [
                            '#9966ff', // Purple for Approved
                            '#9966ff', // Purple for Pending
                            '#9966ff' // Purple for Rejected
                        ],
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script> --}}
@endsection
