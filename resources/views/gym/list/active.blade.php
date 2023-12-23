<div class="alert alert-primary">
    <strong class="d-block mb-2">
        <span class="fas fa-exclamation-circle"></span> Adding a new Gym
    </strong>

    <p>
        You can add a gym by clicking the button below. Please be advised that users are unable to remove gyms from their account once added, but can be archived.<br/>
    </p>
    <p>
        If you would like to remove a gym from your account, please contact us.
    </p>

    <div class="text-right">
        <a href="{{ route('gyms.create') }}" class="btn btn-success">
            <span class="fas fa-plus"></span> Add a New Gym
        </a>
    </div>
</div>

<div class="row">
    <div class="col">
        @if ($active_gyms->count() < 1)
            <div class="alert alert-info">
                You do not have any active gyms.
            </div>
        @else
            <div class="row">
                <div class="col">
                    <div class="table-responsive-lg">
                        <table class="table table-sm table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" class="gym-picture-column text-center align-middle">
                                        <span class="fas fa-fw fa-image"></span>
                                    </th>
                                    <th scope="col" class="align-middle">Name</th>
                                    <th scope="col" class="text-right align-middle"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($active_gyms as $gym)
                                    <tr>
                                        <td class="gym-picture-column align-middle">
                                            <img class="gym-picture rounded-circle" alt="Gym Picture"
                                                    src="{{ $gym->profile_picture }}" title="Gym Picture">
                                        </td>
                
                                        <td class="align-middle">
                                            <a href="{{ route('gyms.show', ['gym' => $gym ]) }}">
                                                {{ $gym->name }}
                                            </a>
                                        </td>
                    
                                        <td class="text-right align-middle">
                                            <div class="mb-1 mr-1 d-inline-block">
                                                <a href="{{ route('gyms.show', ['gym' => $gym ]) }}" 
                                                    class="btn btn-sm btn-info" title="View">
                                                        <span class="fas fa-eye"></span> View
                                                </a>
                                            </div>
                                            
                                            <div class="mb-1 mr-1 d-inline-block">
                                                <a href="{{ route('gyms.edit', ['gym' => $gym ]) }}" 
                                                    class="btn btn-sm btn-success" title="Edit">
                                                        <span class="fas fa-edit"></span> Edit
                                                </a>
                                            </div>

                                            <div class="mb-1 mr-1 d-inline-block gym-archive">
                                                <button class="btn btn-sm btn-primary" title="Archive"
                                                        data-gym='{{ $gym->id }}'>
                                                    <span class="fas fa-archive"></span> Archive
                                                </button>
                                                <form action="{{ route('gyms.destroy', ['gym' => $gym ]) }}"
                                                    data-gym='{{ $gym->id }}' class="d-none" method="post">
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
    </div>
</div>