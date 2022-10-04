<nav class="sidebar animate-collapse">
    <div class="sidebar-brand-and-collapse">
        <a class="sidebar-brand" href="#">
            <img src="{{ asset('img/logos/red_and_white_transparent.png') }}" class="logo">
        </a>

        <button class="sidebar-collapse-button" title="Toggle Sidebar">
            <span class="fa-stack">
                <i class="far fa-square fa-stack-2x"></i>
                <i class="fas fa-bars fa-stack-1x"></i>
            </span>
        </button>
    </div>

    <div class="sidebar-collapsible" id="main-sidebar-collapsible">
        <div class="sidebar-item {{ $current_page == 'dashboard' ? 'active' : '' }}">
            <a class="sidebar-link" href="{{ route('dashboard') }}" title="Dashboard">
                <span class="fas fa-fw fa-tachometer-alt"></span>
                <span class="sidebar-item-text">Dashboard </span>
                @if($_managed->gyms()->where('is_archived', false)->count() > 0)
                @if($_managed->countGymUsag_rs($_managed->gyms()->where('is_archived', false)))
                <span class="alert-danger notif">
                    {{ $_managed->countGymUsag_rs($_managed->gyms()->where('is_archived', false)) }}
                </span>
                @endif
                @endif
                </span>
            </a>
        </div>

        <div class="sidebar-item {{ $current_page == 'browse-meets' ? 'active' : '' }}">
            <a class="sidebar-link" href="{{ route('meets.browse') }}">
                <span class="fas fa-fw fa-calendar-alt" title="Browse Meets"></span>
                <span class="sidebar-item-text">Browse Meets</span>
            </a>
        </div>

        @if ($_managed->isCurrentUser() || $_managed->pivot->can_manage_gyms)
        <div class="sidebar-item {{ $current_page == 'my-gyms' ? 'active' : '' }}">
            <a class="sidebar-link" href="{{ route('gyms.index') }}">
                <span class="fas fa-fw fa-list-ul"
                    title="{{ $_managed->isNotCurrentUser() ? $_managed->first_name . '\'s' : 'My'}} Gyms"></span>
                <span class="sidebar-item-text">
                    {{ $_managed->isNotCurrentUser() ? $_managed->first_name . '\'s' : 'My'}} Gyms
                </span>
            </a>
        </div>
        @endif

        @if(session('impersonated_by'))
        <div class="sidebar-item">
            <a class="sidebar-link" href="{{ route('impersonate.leave') }}">
                <span class="fas fa-fw fa-user-check" title="Back to my user"></span>
                <span class="sidebar-item-text text-warning">Return to admin</span>
            </a>
        </div>
        @endif


        @if ($_managed->isCurrentUser() || $_managed->pivot->shouldShowSidebarGymSection())
        @if ($_managed->gyms()->where('is_archived', false)->count() > 0)
        <div class="mt-2 mb-2 pt-2 pb-2 border-top border-bottom border-dark accordion" id="sidebar-gym-list-accordion"
            data-expand-default="{{ !Str::startsWith($current_page, 'gym-') }}">
            @foreach ($_managed->gyms as $gym)
            @if (!$gym->is_archived)
            <div class="sidebar-item sidebar-dropdown {{ $current_page == 'gym-' . $gym->id ? 'active' : '' }}">
                <a href="#sidebar-gym-{{ $gym->id }}" data-gym="{{ $gym->id }}"
                    class="sidebar-gym-menu sidebar-link sidebar-dropdown-toggle {{ $current_page == 'gym-' . $gym->id ? '' : 'collapsed' }}"
                    data-toggle="collapse" role="button">
                    <img src="{{ $gym->profile_picture }}" alt="Gym Picture" class="profile-picture rounded-circle"
                        title="{{ $gym->short_name }}">
                    <span class="sidebar-item-text">
                        {{ $gym->short_name }}
                    </span>
                    <span class="fas fa-fw sidebar-dropdown-caret sidebar-collapse-hidden"></span>
                </a>
                <div class="collapse {{ $current_page == 'gym-' . $gym->id ? 'show' : '' }}"
                    id="sidebar-gym-{{ $gym->id }}" data-parent="#sidebar-gym-list-accordion">
                    <div class="sidebar-submenu sidebar-collapse-hidden">
                        @if ($_managed->isCurrentUser() || $_managed->pivot->can_manage_gyms)
                        <div class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('gyms.show' , ['gym' => $gym])}}">
                                <span class="fas fa-fw fa-info-circle" title="Gym Details"></span>
                                <span class="sidebar-item-text">Gym Details</span>
                            </a>
                        </div>
                        @endif

                        @if ($_managed->isCurrentUser() || $_managed->pivot->can_manage_rosters)
                        <div class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('gyms.athletes.index', ['gym' => $gym]) }}">
                                <span class="fas fa-fw fa-running" title="Manage Athletes"></span>
                                <span class="sidebar-item-text">Manage Athletes</span>
                            </a>
                        </div>

                        <div class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('gyms.coaches.index', ['gym' => $gym]) }}">
                                <span class="fas fa-fw fa-chalkboard-teacher" title="Manage Coaches"></span>
                                <span class="sidebar-item-text">Manage Coaches</span>
                            </a>
                        </div>
                        @endif

                        @if ($_managed->isCurrentUser() || $_managed->pivot->can_register_in_meet)
                        <div class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('gyms.meets.joined' , ['gym' => $gym])}}">
                                <span class="far fa-fw fa-calendar-check" title="Entered Meets"></span>
                                <span class="sidebar-item-text">Entered Meets</span>
                            </a>
                        </div>
                        @endif

                        @if ($_managed->isCurrentUser() || $_managed->pivot->can_create_meet ||
                        $_managed->pivot->can_edit_meet)
                        <div class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('gyms.meets.index' , ['gym' => $gym])}}">
                                <span class="fas fa-fw fa-calendar-check" title="My Hosted Meets"></span>
                                <span class="sidebar-item-text">My Hosted Meets</span>
                            </a>
                        </div>
                        @endif

                        @if ($_managed->isCurrentUser() || $_managed->pivot->can_create_meet)
                        <div class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('gyms.meets.create' , ['gym' => $gym])}}">
                                <span class="fas fa-fw fa-calendar-plus" title="Add Hosted Meet"></span>
                                <span class="sidebar-item-text">Add Hosted Meet</span>
                            </a>
                        </div>
                        @endif
                        <div class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('gyms.conversation' , ['gym' => $gym])}}">
                                <span class="fas fa-comments" title="Create Meet"></span>
                                <span class="sidebar-item-text">Conversations</span>
                                <span class="badge badge-pill badge-light small unread-count d-none"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
        @endif


        <div class="sidebar-item sidebar-dropdown {{ $current_page == 'profile' ? 'active' : '' }}">
            <a href="#sidebar-profile" class="sidebar-link sidebar-dropdown-toggle" data-toggle="collapse"
                role="button">
                <img src="{{ Auth::user()->profile_picture }}" alt="Profile Picture"
                    class="profile-picture rounded-circle" title="You">
                <span class="sidebar-item-text font-weight-bold">
                    {{ Auth::user()->fullName() }}
                </span>
                <span class="fas fa-fw sidebar-dropdown-caret sidebar-collapse-hidden"></span>
            </a>
            <div class="collapse show" id="sidebar-profile">
                <div class="sidebar-submenu sidebar-collapse-hidden">
                    <div class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('account.payment.options') }}">
                            <span class="fas fa-fw fa-dollar-sign" title="Available Funds"></span>
                            <span class="sidebar-item-text">
                                Available Funds :
                                <strong>${{ Auth::user()->availableFunds() }}</strong>
                            </span>
                        </a>
                    </div>
                    <div class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('account.balance.transactions') }}">
                            <span class="fas fa-fw fa-hand-holding-usd" title="Available Funds"></span>
                            <span class="sidebar-item-text">
                                Withdraw
                            </span>
                        </a>
                    </div>
                    <div class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('account.balance.schedule_withdraw') }}">
                            <span class="fas fa-fw fa-hand-holding-usd" title="Available Funds"></span>
                            <span class="sidebar-item-text">
                                Automated Withdrawals
                            </span>
                        </a>
                    </div>

                    <div class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('account.profile') }}">
                            <span class="fas fa-fw fa-user" title="Visit Account"></span>
                            <span class="sidebar-item-text">Profile</span>
                        </a>
                    </div>

                    @if(!session('impersonated_by'))
                    <div class="sidebar-item">
                        <a class="sidebar-link sidebar-logout-button" href="{{ route('logout') }}">
                            <span class="fas fa-fw fa-sign-out-alt" title="Sign Out"></span>
                            <span class="sidebar-item-text">Sign Out</span>
                        </a>

                        <form class="d-none" id="sidebar-logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                        </form>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        <div class="sidebar-item sidebar-pushdown">
            <a class="sidebar-link" href="#modal-contact-us" data-hide-email="true" data-toggle="modal"
                data-backdrop="static" data-keyboard="false">
                <span class="fas fa-fw fa-question-circle" title="Have a question ?"></span>
                <span class="sidebar-item-text">Have a question ?</span>
            </a>
        </div>
    </div>
</nav>