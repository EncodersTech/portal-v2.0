<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
</ul>

<!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
    <li class="dropdown user user-menu open mr-2">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
            <img src="{{ url(Auth::user()->profile_picture) }}" class="user-image elevation-3" alt="User Image">
            <span class="hidden-xs">Hi, {{ Auth::user()->first_name }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item" onclick="
            alert('Work in progress')">
                <i class="fas fa-user mr-2"></i> Profile
            </a>
            <div class="dropdown-divider"></div>
            <a href="{{ url('/logout') }}" class="dropdown-item text-danger"
               onclick="event.preventDefault(); localStorage.clear(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
            <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="display-none">
                {{ csrf_field() }}
            </form>
        </div>
    </li>
</ul>
