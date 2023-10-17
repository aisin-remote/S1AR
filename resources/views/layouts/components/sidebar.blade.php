<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="/">SIAR</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="/">SIAR</a>
        </div>
        <ul class="sidebar-menu">
            <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ request()->is('dailyattendance*')||request()->is('monthlyattendance*') ? 'text-primary' : '' }}"><i class="fas fa-th"></i>
                    <span>History Attendance</span></a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="nav-link {{ request()->is('dailyattendance*') ? 'text-primary' : '' }}" href="/dailyattendance">Daily attendance</a>
                    </li>
                    <li>
                        <a class="nav-link" href="/monthlyattendance">Monthly attendance</a>
                    </li>
            </li>
        </ul>
    </aside>
</div>