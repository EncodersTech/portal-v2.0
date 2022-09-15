<div class="row">
    <div class="col">
        <h5 class="border-bottom"><span class="fas fa-user-friends"></span> Managed Accounts</h5>
        <p>
            Log into accounts you have been granted access to.
        </p>
    </div>
</div>

<div class="row mb-3">   
    <div class="col">
        @if ($managed_accounts == null)
            <div class="alert alert-info">
                You are currently not managing any other accounts.
            </div>
        @else
            <div class="table-responsive-lg">
                <table class="table table-sm table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col" class="member-profile-picture-column text-center align-middle">
                                <span class="fas fa-fw fa-image"></span>
                            </th>
                            <th scope="col" class="align-middle">Full Name</th>
                            <th scope="col" class="align-middle">Email</th>
                            <th scope="col" class="member-permissions-column align-middle">Permissions</th>
                            <th scope="col" class="text-right align-middle"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($managed_accounts as $account)
                            <tr>
                                <td class="member-profile-picture-column align-middle">
                                    <img class="member-profile-picture rounded-circle" alt="Profile Picture"
                                        src="{{ asset($account->profile_picture) }}" title="Profile Picture">
                                </td>
        
                                <td class="align-middle">
                                    {{ $account->fullName() }}
                                </td>

                                <td class="align-middle">
                                    {{ $account->email }}
                                </td>
        
                                <td class="member-permissions-column align-middle">
                                    @foreach ($account->permissions as $id => $val)
                                        <span class="badge badge-info
                                                {{ $val['value'] ? '' : 'd-none' }}">
                                            {{ $val['description'] }}
                                        </span>
                                    @endforeach

                                    @if ($account->activePrmissionCount < 1)
                                        <span class="badge badge-warning">
                                            No permissions
                                        </span>
                                    @endif
                                </td>
        
                                <td class="text-right align-middle">                                    
                                    <div class="mb-1 mr-1 d-inline-block managed-account-remove">
                                        <button class="btn btn-sm btn-danger"
                                                data-account="{{ $account->id }}" title="Remove">
                                            <span class="fas fa-trash"></span>
                                        </button>
                                        <form action="{{ route('account.managed.remove', ['id' => $account->id]) }}"
                                                data-account="{{ $account->id }}" class="d-none" method="post">
                                            @csrf
                                            @method('DELETE')                            
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div> 