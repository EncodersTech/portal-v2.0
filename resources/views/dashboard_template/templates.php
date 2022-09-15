<script id="addConversationUserTemplate" type="text/x-jsrender">
<div class="card user-card user-{{:id}} conversation-user flex-row p-2 mb-1 border-0 align-items-center" data-user="{{:id}}">
            <div class="user-logo mx-3">
                <img src="{{:image}}" alt="user-avatar">
            </div>
            <div class="user-detail w-100">
                <div class="user-name d-flex justify-content-between">
                    <h6 class="font-weight-bold text-truncate">{{:name}}</h6>
<!--                    <span class="bg-success sidebar-conversation-count small text-center"></span>-->
                </div>
                <div class="user-message text-truncate">
                    <span class="w-100">{{:email}}</span>
                </div>
            </div>
</div>
</script>

<script id="displayGymTemplate" type="text/x-jsrender">
 <div class="card user-card selected-user-conversion flex-row p-2 mb-1 border-0 align-items-center" data-user="{{:id}}">
                                            <div class="user-logo mx-3">
                                                <img src="{{:image}}" alt="user-avatar">
                                            </div>
                                            <div class="user-detail">
                                                <div class="user-name">
                                                    <h6 class="font-weight-bold text-truncate">{{:name}}</h6>
                                                </div>
                                                <div class="user-message text-truncate">
                                                    <span class="w-100">{{:email}}</span>
                                                </div>
                                            </div>
                                        </div>
</script>

<script id="userConversationTemplate" type="text/x-jsrender">
 <div class="flex-grow-1">
                    <div class="conversation__header d-flex align-items-center justify-content-between p-3" data-userid="{{:id}}">
                        <div class="d-flex align-items-center">
                            <div class="conversation__header-image pr-3">
                                <img src="{{:image}}" alt="user-avatar">
                            </div>
                            <div class="conversation__header-user">
                                <h6 class="font-weight-bold">{{:name}}</h6>
<!--                                <p class="mb-0">Online</p>-->
                            </div>
                        </div>
                        <div class="setting-icon">
                            <i class="fa fa-cog setting-icon-cog"></i>
                            <i class="fa fa-bars setting-icon-bar"></i>
                        </div>
                    </div>

                    <div class="conversation__body p-3">

                    </div>

                    <div class="conversation__footer p-3 d-flex justify-content-between">
                        <div class="conversation-message mr-2 w-100">
                            <input type="text" class="form-control border-0 conversation-search-box conversation-input-box"
                                   placeholder="Type message...">
                        </div>
                        <div class="footer-button d-flex justify-content-between">
                            <div class="footer-send d-flex justify-content-center align-items-center mx-1">
                                <a href="javascript:void(0)" class="send-message"> <i class="fa fa-paper-plane text-dark"></i></a>
                            </div>
                        </div>
                    </div>
</div>

<!--    User About sidebar -->
          <div class="chat-profile">
                <div class="chat-profile__header">
                    <span class="chat-profile__about">About</span>
                    <i class="fas fa-times chat-profile__close-btn"></i>
                </div>
                <div class="chat-profile__person chat-profile__person--active mb-2">
                    <div class="chat-profile__avatar">
                        <img src="{{:image}}" class="img-fluid user-about-image">
                    </div>
                </div>

<!--                <div class="chat-profile__person-status my-3 text-capitalize">Online</div>-->

                <div class="user-profile-data">
<!--                    <div class="chat-profile__divider"></div>-->
<!--                    <div class="chat-profile__column">-->
<!--                        <h6 class="chat-profile__column-title">Bio</h6>-->
<!--                        <p class="chat-profile__column-title-detail text-muted mb-0 user-about">No bio added-->
<!--                            yet...</p>-->
<!--                    </div>-->

                    <div class="chat-profile__divider"></div>
                    <div class="chat-profile__column">
                        <h6 class="chat-profile__column-title">Phone</h6>
                       <p class="chat-profile__column-title-detail text-muted mb-0 user-about">{{:phone}}</p>
<!--                        <p class="chat-profile__column-title-detail text-muted mb-0 user-about">No phone added-->
<!--                            yet...</p>-->
                    </div>

                    <div class="chat-profile__divider"></div>
                    <div class="chat-profile__column">
                        <h6 class="chat-profile__column-title">Email</h6>
                         <p class="chat-profile__column-title-detail text-muted mb-0 user-about">{{:email}}</p>
<!--                        <p class="chat-profile__column-title-detail text-muted mb-0 user-about">-->
<!--                            avanitv2@gmail.com</p>-->
                    </div>
                </div>

                <div class="group-profile-data">
                    <div class="chat-profile__divider"></div>
                    <div class="chat-profile__divider"></div>
                </div>

                <div class="chat-profile__column">
                    <h6 class="chat-profile__column-title">
                       Meets Name
                        <span class="float-right">33</span>
                    </h6>
                    {{for users}}
                            <div class="chat__person-box" data-gymid="{{:gymId}}">
                                <div class="position-relative chat__person-box-status-wrapper">
                                    <div class="chat__person-box-avtar chat__person-box-avtar--active">
                                        <img src="{{:profilePicture}}" class="user-avatar-img" alt="user-image">
                                    </div>
                                </div>

                                <div class="chat__person-box-detail px-3">
                                    <h5 class="mb-1 chat__person-box-name contact-name text-truncate">{{:meetName}}</h5>
                                    <p class="mb-0 chat-message text-truncate">{{:gymName}}</p>
                                </div>
                            </div>
                    {{/for}}
                </div>
            </div>
</script>
<script id="userConversationBodyMessageTemplate" type="text/x-jsrender">
                        {{if receiveMessage}}
                        <div class="conversation-receive d-flex">
                            <div class="receive-image mr-3">
                                <img src="{{:senderImage}}" alt="profile-image">
                            </div>
                            <div class="receive-message">
                                <div class="message mb-1">
                                    <span>
                                        {{:message}}
                                    </span>
                                </div>
                                <span class="receive-time d-flex justify-content-start">
                                    {{:time}}
                                </span>
                            </div>
                        </div>
                        {{/if}}
                        {{if senderMessage}}
                        <div class="conversation-send d-flex flex-row-reverse">
                            <div class="sender-image ml-3">
                                <img src="{{:senderImage}}">
                            </div>
                            <div class="sender-message">
                                <div class="message mb-1">
                                    <span>
                                        {{:message}}
                                    </span>
                                </div>
                                <span class="sender-time d-flex justify-content-end">
                                {{:time}}
                            </span>
                            </div>
                        </div>
                        {{/if}}

</script>
<script id="displayMessageTemplate" type="text/x-jsrender">
{{if receiveMessage}}
                        <div class="conversation-receive d-flex">
                            <div class="receive-image mr-3">
                                <img src="{{:receiverImage}}" alt="profile-image">
                            </div>
                            <div class="receive-message">
                                <div class="message mb-1">
                                    <span>
                                        {{:message}}
                                    </span>
                                </div>
                                <span class="receive-time d-flex justify-content-start">
                                    {{:time}}
                                </span>
                            </div>
                        </div>
                        {{/if}}
                        {{if senderMessage}}
                        <div class="conversation-send d-flex flex-row-reverse">
                            <div class="sender-image ml-3">
                                <img src="{{:senderImage}}">
                            </div>
                            <div class="sender-message">
                                <div class="message mb-1">
                                    <span>
                                        {{:message}}
                                    </span>
                                </div>
                                <span class="sender-time d-flex justify-content-end">
                                {{:time}}
                            </span>
                            </div>
                        </div>
                        {{/if}}
</script>
