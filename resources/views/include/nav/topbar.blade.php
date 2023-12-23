@if ((Auth::user()->memberOf()->count() > 0) && ($current_page != 'profile'))
    <div class="top-bar">   
        <nav class="navbar navbar-light bg-light justify-content-between">
            <a class="navbar-brand small">
                <span class="far fa-fw fa-user-circle"></span> Currently managing
            </a>
            <form id="managed-account-selector-form" class="form-inline"
                    action="{{ route('managed.switch') }}" method="POST">
                @csrf
                @method('PATCH')
    
                <select class="form-control" id="managed-account-selector" name="id">
                    <option value="{{ Auth::user()->id }}"
                        {{ $_managed->isCurrentUser() ? 'selected' : '' }}>
                        My Own Account
                    </option>
                    @foreach (Auth::user()->memberOf as $managed)
                        <option value="{{ $managed->id }}"
                            {{ $_managed->id == $managed->id ? 'selected' : ''}}>
                            {{ $managed->fullName() }}'s Account
                        </option>
                    @endforeach
                </select>
            </form>
        </nav>
    </div>    
@endif
