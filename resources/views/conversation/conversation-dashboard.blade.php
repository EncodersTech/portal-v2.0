@extends('layouts.main')


@section('content-header')
    <span class="fas fa-fw fa-dumbbell"></span> {{ $gym->name }}  Conversations
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(owned by ' . $_managed->first_name .')' : ''}}
    </span>
@endsection


@section('content-main')
    @include('include.errors')
    {{--add conversion modal--}}
    <div class="modal fade" id="addConversionUserModal" tabindex="-1" role="dialog"
         aria-labelledby="modal-check-sending-details" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <b>New Conversations </b>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span class="fas fa-times" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="my-3 mx-3">
                    <input id="searchMember" type="search" class="form-control border-0 modal-search-box"
                           placeholder="Search..." autocomplete="off" to>
                </div>


                <div class="content-main pr-3 pl-3 mt-3">
                    <div class="row mb-3">
                        <div class="col-md-12 col-sm-12 col-12 bg-light left-side-wrapper-modal">
                            <div class="left-top">
                                <div class="user-chat-container add-conversation-gym">
                                </div>
                                <div class="text-center selected-user-no-user d-none">
                                    <div class="text-center"><i class="fa fa-2x fa-user" aria-hidden="true"></i></div>
                                    <p>No User Found...</p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right m-3"><a class="btn btn-secondary" data-dismiss="modal"
                                               aria-label="Close">Cancel</a></div>
            </div>
        </div>
    </div>
    <div class="content-main p-3 mt-3">
        <div class="row mb-3 position-relative">
            <div class="px-3 bg-light left-side-wrapper">
                <div class="left-top">
                    <div class="left-title my-4 d-flex justify-content-between"><h4 class="font-weight-bold">
                            Conversations</h4>
                        <div class="chat__people-wrapper-button add-conversation-btn" style="cursor: pointer;"><i
                                class="fas fa-comment-medical" title="New Conversation"></i></div>
                    </div>
                    <div class="search-input position-relative">
                        <div class="input-group mb-3"><span class="input-group-text border-0 search-icon"><i
                                    class="fas fa-search"></i></span> <input type="text"
                                                                             class="form-control border-0 search-box"
                                                                             placeholder="Search"></div>
                    </div>
                    <div class="user-chat-container mt-4" style="min-height: 530px;">
                        <div class="user-conversation">
                            <div class="sidebar-no-conversation text-center d-none" style="margin-top: 230px;">
                                <div class="text-center"><i class="fa fa-comments font-4xl" aria-hidden="true"></i>
                                </div>
                                No conversation yet...
                            </div>
                            @forelse($conversationUsers as $conversationUser)
                                @if($conversationUser->to_id != $gym->id)
                                    <div
                                        class="card user-card user-{{ $conversationUser->receiver->id }} conversation-user flex-row p-2 mb-1 border-0 align-items-center"
                                        data-user="{{ $conversationUser->receiver->id }}">
                                        <div class="user-logo mx-3"><img
                                                src="{{ $conversationUser->receiver->profile_picture }}"
                                                alt="user-avatar"></div>
                                        <div class="user-detail w-100">
                                            <div class="user-name d-flex justify-content-between">
                                                <h6 class="font-weight-bold text-truncate"
                                                    style="max-width: 160px">{{ $conversationUser->receiver->name }}</h6>
                                                @if($conversationUser->where('read_at', null)->where('to_id', $gym->id)->where('from_id', $conversationUser->to_id)->count() > 0)
                                                    <span
                                                        class="bg-success sidebar-conversation-count small text-center">{{ $conversationUser->where('read_at', null)->where('to_id', $gym->id)->where('from_id', $conversationUser->to_id)->count()}}</span>
                                                @endif
                                            </div>
                                            <div class="user-message text-truncate">
                                                <span
                                                    class="w-100">{{ $conversationUser->receiver->user->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="card user-card user-{{ $conversationUser->sender->id }} conversation-user flex-row p-2 mb-1 border-0 align-items-center"
                                        data-user="{{ $conversationUser->sender->id }}">
                                        <div class="user-logo mx-3"><img
                                                src="{{ $conversationUser->sender->profile_picture }}"
                                                alt="user-avatar"></div>
                                        <div class="user-detail w-100">
                                            <div class="user-name d-flex justify-content-between">
                                                <h6 class="font-weight-bold text-truncate"
                                                    style="max-width: 160px">{{ $conversationUser->sender->name }}</h6>
                                                @if($conversationUser->where('read_at', null)->where('to_id', $gym->id)->where('from_id', $conversationUser->from_id)->count() > 0)
                                                    <span
                                                        class="bg-success sidebar-conversation-count small text-center">{{ $conversationUser->where('read_at', null)->where('to_id', $gym->id)->where('from_id', $conversationUser->from_id)->count()}}</span> @endif
                                            </div>
                                            <div class="user-message text-truncate">
                                                <span class="w-100">{{ $conversationUser->sender->user->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="sidebar-no-conversation text-center" style="margin-top: 230px;">
                                    <div class="text-center"><i class="fa fa-comments font-4xl" aria-hidden="true"></i>
                                    </div>
                                    No conversation yet...
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white conversation d-flex flex-grow-1 flex-column">
                <div class="no-selected">
                    <div class="text-center"><i class="fa fa-comments font-4xl" aria-hidden="true"></i></div>
                    No conversation selected yet...
                </div>
                <div class="chat-conversation-menu d-none chat-menu-{{$gym->id}}">
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts-main')
    <script>
        let gymId = "{{$gym->id}}"
        let loggedInUserId = "{{Auth::id()}}"
    </script>
    <script src="{{ mix('js/include/chat-conversion.js') }}"></script>
    @include('dashboard_template.templates')
@endsection
