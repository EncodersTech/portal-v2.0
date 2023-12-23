<div class="modal fade" id="modal-invite-account-manager" tabindex="-1" role="dialog"
        aria-labelledby="modal-invite-account-manager" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-plus"></span> Invite a Member
                </h5>
                <button type="button" class="close modal-invite-account-manager-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                <p>
                    Please enter the e-mail address of new or existing users to invite to manage your account
                </p>
                
                <form method="POST" action="{{ route('account.member.invite') }}"
                        id='modal-invite-account-manager-form'>
                    @csrf

                    <div class="row mb-3">
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="fas fa-fw fa-envelope"></span></span>
                                </div>
                                <input id="invite_email" type="invite_email" class="form-control" 
                                        name="invite_email" placeholder="Email address" 
                                        required autocomplete="invite_email" autofocus>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer pb-0 pr-0">
                        <div class="text-right">
                            <button class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                        id="modal-invite-account-manager-spinner" style="display: none;">
                                </span>
                                <span class="fas fa-plus"></span> Invite
                            </button>
                        </div>
                    </div>        
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <h5 class="border-bottom"><span class="fas fa-users"></span> Account Managers</h5>
        <p>
            Grant staff access to manage your Club. You may specify access levels and permissions.
        </p>
    </div>
</div>

<div class="row mb-3">   
    <div class="col">
        @if ($account_managers == null)
            <div class="alert alert-info">
                You have not invited any users to manage your account.
            </div>
        @else
            <div class="modal fade" id="modal-edit-account-manager" tabindex="-1" role="dialog"
                    aria-labelledby="modal-edit-account-manager" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title text-primary">
                                <span class="fas fa-edit"></span> Edit Permissions
                            </h5>
                            <button type="button" class="close modal-edit-account-manager-close" aria-label="Close">
                                <span class="fas fa-times" aria-hidden="true"></span>
                            </button>
                        </div>
                        
                        <div class="modal-body">                            
                            <form method="POST" action="{{ route('account.member.permissions.edit') }}"
                                    id='modal-edit-account-manager-form'>
                                @csrf
                                @method('PATCH')

                                <input type="hidden" name="member">

                                @foreach ($account_manager_permission_list as $field => $desc)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                                name="{{ $field }}" id="{{ $field}}">
                    
                                        <label class="form-check-label" for="{{ $field }}">
                                            {{ $desc }}
                                        </label>
                                    </div>
                                @endforeach

                                <div class="modal-footer pb-0 pr-0">
                                    <div class="text-right">
                                        <button class="btn btn-primary">
                                            <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                                    id="modal-edit-account-manager-spinner" style="display: none;">
                                            </span>
                                            <span class="fas fa-save"></span> Save
                                        </button>
                                    </div>
                                </div>        
                            </form>
                        </div>
                    </div>
                </div>
            </div>

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
                        @foreach ($account_managers as $member)
                            <tr>
                                <td class="member-profile-picture-column align-middle">
                                    <img class="member-profile-picture rounded-circle" alt="Profile Picture"
                                        src="{{ asset($member->profile_picture) }}" title="Profile Picture">
                                </td>
        
                                <td class="align-middle">
                                    {{ $member->fullName() }}
                                </td>

                                <td class="align-middle">
                                    {{ $member->email }}
                                </td>
        
                                <td id="member-permissions-{{ $member->id }}"
                                    class="member-permissions-column align-middle">
                                    @foreach ($member->permissions as $id => $val)
                                        <span class="badge badge-info
                                                {{ $val['value'] ? '' : 'd-none' }}"
                                                data-permission='{{ $id }}' data-value="{{ $val['value'] }}">
                                            {{ $val['description'] }}
                                        </span>
                                    @endforeach

                                    @if ($member->activePrmissionCount < 1)
                                        <span class="badge badge-warning">
                                            No permissions
                                        </span>
                                    @endif
                                </td>
        
                                <td class="text-right align-middle">
                                    <div class="mb-1 mr-1 d-inline-block">
                                        <a href="#" data-member="{{ $member->id }}"
                                            class="btn btn-sm btn-success account-member-edit-permissions"
                                            title="Edit Permissions">
                                            <span class="fas fa-edit"></span>
                                        </a>
                                    </div>
                                    
                                    <div class="mb-1 mr-1 d-inline-block account-member-remove">
                                        <button class="btn btn-sm btn-danger"
                                                data-member="{{ $member->id }}" title="Remove">
                                            <span class="fas fa-trash"></span>
                                        </button>
                                        <form action="{{ route('account.member.remove', ['id' => $member->id]) }}"
                                                data-member="{{ $member->id }}" class="d-none" method="post">
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
        <div class="text-right">
            <a href="#modal-invite-account-manager" class="btn btn-sm btn-primary" data-toggle="modal"
                data-backdrop="static" data-keyboard="false">
                <span class="fas fa-plus"></span> Invite a Member
            </a>
        </div>
    </div>
</div> 

@if ($member_invitations != null)
    <div class="row">
        <div class="col">
            <h5 class="border-bottom">
                <span class="fas fa-user-clock"></span> Pending Invites
            </h5>
            <p>
                If any invites you sent are pending, they will show up below.
            </p>
        </div>
    </div>
    <div class="row">   
        <div class="col">
            <div class="table-responsive-lg">
                <table class="table table-sm table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col" class="align-middle">To</th>
                            <th scope="col" class="align-middle">Status</th>
                            <th scope="col" class="align-middle">Sent</th>
                            <th scope="col" class="text-right align-middle"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member_invitations as $invitation)
                            <tr>
                                <td class="align-middle">
                                    {{ $invitation->email }}
                                </td>
        
                                <td class="align-middle">
                                    <span class="badge badge-secondary">Pending</span>
                                </td>
        
                                <td class="align-middle">
                                    {{ $invitation->updated_at->format(Helper::AMERICAN_SHORT_DATE_TIME) }}
                                </td>
        
                                <td class="text-right align-middle">
                                    <div class="mb-1 mr-1 d-inline-block member-invite-resend">
                                        <button class="btn btn-sm btn-warning" title="Resend"
                                                data-invite='{{ $invitation->id }}'>
                                            <span class="fas fa-redo-alt"></span>
                                        </button>
                                        <form action="{{ route('invite.member.resend', ['id' => $invitation->id ]) }}"
                                            data-invite='{{ $invitation->id }}' class="d-none" method="post">
                                            @csrf
                                        </form>
                                    </div>

                                    <div class="mb-1 mr-1 d-inline-block member-invite-remove">
                                        <button class="btn btn-sm btn-danger" title="Remove"
                                                data-invite='{{ $invitation->id }}'>
                                            <span class="fas fa-trash"></span>
                                        </button>
                                        <form action="{{ route('invite.member.remove', ['id' => $invitation->id ]) }}"
                                            data-invite='{{ $invitation->id }}' class="d-none" method="post">
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
        </div>
    </div>
@endif
