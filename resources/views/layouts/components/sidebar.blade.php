<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="/">S1AR</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="/">S1AR</a>
        </div>
        <ul class="sidebar-menu">
            <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ request()->is('dailyattendance*')||request()->is('weeklyattendance*')||request()->is('monthlyattendance*') ? 'text-primary' : '' }}"><i class="fas fa-th"></i>
                    <span>History Attendance</span></a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="nav-link {{ request()->is('dailyattendance*') ? 'text-primary' : '' }}" href="/dailyattendance">Daily attendance</a>
                    </li>
                    <li>
                        <a class="nav-link {{ request()->is('monthlyattendance') ? 'text-primary' : '' }}" href="/monthlyattendance">Monthly attendance</a>
                    </li>
                    <li>
                        <a class="nav-link {{ request()->is('historyattendance*') ? 'text-primary' : '' }}" href="/historyattendance">History attendance</a>
                    </li>
            </li>
        </ul>
        <a class="sidebar-menu" href="#"><i class="fas fa-edit"></i><span>Cuzia<span class="text-sm badge bg-secondary" style="width: 100px;">Coming Soon</span></span></a>
    </aside>
</div>