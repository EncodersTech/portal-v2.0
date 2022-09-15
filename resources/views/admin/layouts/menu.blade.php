<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ Request::is('admin/dashboard*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-fw fa-calendar-plus"></i>
                <p>Meets
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.meets') }}"
                        class="nav-link {{ Request::is('admin/meets*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-fw fa-calendar-plus"></i>
                        <p>Meets</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.featured.meets') }}"
                        class="nav-link {{ Request::is('admin/featured-meets*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-fw fa-certificate"></i>
                        <p>Featured Meets</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.users') }}" class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Users</p>
            </a>
        </li>
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link {{ Request::is('admin/usag*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-receipt"></i>
                <p>USAG
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.usag.reservations') }}"
                        class="nav-link {{ Request::is('admin/usag-reservations*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-registered"></i>
                        <p>USAG Reservations</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.usag.sanctions') }}"
                        class="nav-link {{ Request::is('admin/usag-sanctions*') ? 'active' : '' }}">
                        <i class="nav-icon fab fa-stripe-s"></i>
                        <p>USAG Sanctions</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-treeview">
            <a href="#" class="nav-link {{ Request::is('admin/reports*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>Reports
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.gym.balance.reports') }}"
                        class="nav-link {{ Request::is('admin/reports-gym-balance*') ? 'active' : '' }}">
                        <i class="fas fa-info-circle nav-icon"></i>
                        <p>Gym Balance</p>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.transfer') }}"
                class="nav-link {{ Request::is('admin/transfer*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-hand-holding-usd"></i>
                <p>Transfer</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.settings') }}"
                class="nav-link {{ Request::is('admin/settings*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cog"></i>
                <p>Settings</p>
            </a>
        </li>
    </ul>
</nav>