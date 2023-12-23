<div class="modal fade" id="modal-contact-us" tabindex="-1" role="dialog" aria-labelledby="modal-contact-us" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-info">
                    <span class="fas fa-question-circle"></span> Contact Us
                </h5>
                <button type="button" id="modal-contact-us-close" class="close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                <form accept="#" onsubmit="return false;" novalidate>

                    <div class="row mb-3 @auth d-none @endguest">
                        <div class="col">
                            <label for="modal-contact-us-email">
                                <span class="fas fa-fw fa-envelope"></span> {{ __('messages.email') }} <span class="text-danger">*</span>
                            </label>
                            <input id="modal-contact-us-email" type="email" class="form-control" 
                                    name="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" 
                                    autocomplete="email" autofocus>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a href="#chat_div" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="chat_div">
                                Available from Mon-Fri 8:30am-5:00pm EST</a>
                        </div>
                    </div>
                    <div class="collapse show" id="chat_div">
                        <div class="row p-1" id="live_chat_div">
                            <div class="col-4">
                                <i class="fas fa-comment-dots"></i>
                                Live Chat
                            </div>
                            <div class="col-8 text-right">
                                <a href="javascript:void(Tawk_API.toggle())" class="btn btn-success"> Start Chat </a>
                            </div>
                        </div>
                        <div class="row p-1">
                            <div class="col-4">
                                <i class="fas fa-phone"></i>
                                Call Us :
                            </div>
                            <div class="col-8 text-right">
                                <a href="tel:"> 844-425-5496 </a>
                            </div>
                        </div>
                    </div>
                    <hr>
                    
                    <div class="row">
                        <div class="col">
                            <a href="#all_time_support" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="chat_div">24/7 Support</a>
                        </div>
                    </div>
                    <div class="collapse show" id="all_time_support">
                        <div class="row p-1">
                            <div class="col-4">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                Mail Us: 
                            </div>
                            <div class="col-8 text-right">
                                <a href="mailto:" > support@allgymnastics.com </a>
                            </div>
                        </div>
                        <div class="row p-1">
                            <div class="col-12">
                                <hr>
                                Or, send a message ...
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="modal-contact-us-message">
                                    <span class="fas fa-fw fa-comment"></span> {{ __('messages.message') }} <span class="text-danger">*</span>
                                </label>
                                <textarea id="modal-contact-us-message" class="form-control" rows="5"
                                        name="message" placeholder="{{ __('messages.message') }}"
                                        required autocomplete="message"></textarea>
                                <span class="small text-secondary">
                                    <span id="modal-contact-us-message-char-count">750</span> characters left.
                                </span>
                            </div>
                        </div>

                    

                        <div class="row mt-3" id="modal-contact-us-alert-container" style="display: none;">
                            <div class="col">
                                <div class="" id="modal-contact-us-alert">
                                    <span class="" id="modal-contact-us-alert-icon"></span>
                                    <span id="modal-contact-us-alert-text"></span>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="modal-footer pb-0 pr-0">
                        <button type="submit" id="modal-contact-us-submit" class="btn btn-danger">
                            <span class="spinner-border spinner-border-sm" id="modal-contact-us-spinner"
                                    style="display: none;" role="status" aria-hidden="true">
                            </span>
                            <span class="fas fa-paper-plane"></span> {{ __('messages.submit')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>