<div class="row">
    <div class="col">
        @if ($archived_gyms->count() < 1)
            <div class="alert alert-info">
                You do not have any archived gyms.
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
                                @foreach ($archived_gyms as $gym)
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
                                            
                                            <div class="mb-1 mr-1 d-inline-block gym-restore">
                                                <button class="btn btn-sm btn-primary" title="Restore"
                                                        data-gym='{{ $gym->id }}'>
                                                    <span class="fas fa-box-open"></span> Restore
                                                </button>
                                                <form action="{{ route('gyms.restore', ['gym' => $gym ]) }}"
                                                    data-gym='{{ $gym->id }}' class="d-none" method="post">
                                                    @csrf
                                                    @method('PATCH')
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