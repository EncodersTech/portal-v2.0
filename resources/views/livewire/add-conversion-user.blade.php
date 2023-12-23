<div>
    <div class="content-main pr-3 pl-3 mt-3">
        <div class="row mb-3">
            <div class="col-md-12 col-sm-12 col-12 bg-light left-side-wrapper-modal">
                <div class="left-top">
                    <div class="user-chat-container">
                        @forelse($users as $user)
                            <div class="card user-card selected-user-conversion flex-row p-2 mb-1 border-0 align-items-center" data-user="{{ $user->id }}">
                                <input type="hidden" class="add-chat-user-id" value="{{ $user->id }}">
                                <div class="user-logo mx-3">
                                    <img src="{{ $user->profile_picture }}" alt="user-avatar">
                                </div>
                                <div class="user-detail">
                                    <div class="user-name">
                                        <h6 class="font-weight-bold">{{ $user->full_name }}</h6>
                                    </div>
                                    <div class="user-message">
                                        <span>{{ $user->email }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center">
                                <div class="text-center">
                                    <i class="fa fa-2x fa-user" aria-hidden="true"></i>
                                </div>
                                <p>No User Found...</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
