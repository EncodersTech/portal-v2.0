<div class="mt-2">
    <div class="alert alert-danger mb-3" id="validationErrorsBox" style="display: none"></div>
    <h5 class="pb-1 border-bottom">
        <span class="fas fa-fw fa-mail-bulk"></span> Email Participants
        <span class="float-right">
            <a href="#" data-toggle="modal" data-target="#previousMailsModal" class="btn btn-primary"
                style="padding:2px 5px;">Show Previous Mails</a>
        </span>
    </h5>

    <div class="row"> @{{ selectedGymMailable }}
        <div class="col-12 col-xs-12 col-sm-3 col-md-12 col-lg-3 mb-3 selectGymDiv">
            <div class="text-info small mb-1"><span class="fas fa-info-circle"></span> Please scroll down in case of
                long list.</div>
            <label for="name" class="mb-1 mb-3 mt-2">Select Club/Gym: <span class="text-danger">*</span></label><br>
            <input type="checkbox" class="form-group mr-2" id="ckbCheckAll"><b> Select All</b><br>
            @foreach($registerGyms as $key => $registerGym)
            <input type="checkbox" name="registerGym[]" class="form-group mr-2 gymCheck" id="sendMailable_{{$key}}"
                value="{{$key}}"> {{$registerGym}}<br>
            @endforeach
            <div><b>Pending: </b></div>
            @foreach($registerGymsPending as $key => $registerGym)
            <input type="checkbox" name="registerGym[]" class="form-group mr-2 gymCheck" id="sendMailable_{{$key}}"
                value="{{$key}}"> {{$registerGym}}<br>
            @endforeach
        </div>

        <div class="col-12 col-xs-12 col-sm-9 col-md-12 col-lg-9">
            <div class="row">
                <input type="hidden" name="meet_id" value="{{$meet->id}}">
                <div class="col-12 mb-3">
                    <label for="name" class="mb-1">Subject: <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><span class="fas fa-fw fa-comment-alt"></span></span>
                        </div>
                        <input id="subject" type="text" class="form-control" name="subject" value="{{ old('subject') }}"
                            placeholder="Subject" required autocomplete="off" autofocus>
                    </div>
                </div>

                <div class="col-12 mb-3 summernote">
                    <label for="name" class="mb-1">Message: <span class="text-danger">*</span></label>
                    <textarea name="message" id="messageBody" class="form-control"></textarea>
                </div>

                <div class="form-group col-md-6">
                    <div class="row">
                        <div class="col-sm-4 col-6">
                            {{ Form::label('file', 'Attachment:') }}
                            <label class="image__file-upload"> Choose
                                {{ Form::file('attachments',['id'=>'documentImage','class' => 'd-none document-file']) }}
                            </label>
                        </div>
                        <div class="col-sm-4 col-6 mt-1">
                            <img id='previewImage' class="img-thumbnail thumbnail-preview image-stretching"
                                src="{{ asset('img/logos/logo.png') }}" />
                        </div>
                    </div>
                </div>

            </div>
            <div class="text-right">
                <button class="btn btn-primary" type="submit" id="sedMailNotification">Send</button>
                <a href="{{url()->current()}}" id="btnCancel" class="btn btn-secondary ml-1">Cancel</a>
            </div>
        </div>


    </div>
</div>

<!-- modal to view previous emails -->
<div class="modal fade" id="previousMailsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Previous Emails</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body
                p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Sent To</th>
                                <th>Attachment</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($massMailers as $massMailer)
                            <tr>
                                <td>{{$massMailer->subject}}</td>
                                <td>
                                    {!!$massMailer->message !!}
                                </td>
                                <td>{{$massMailer->registered_gym_names}}</td>
                                <td>
                                    @if($massMailer->attachments)
                                    <a href="{{$massMailer->attachments}}"
                                        target="_blank">Attachment</a>
                                    @endif
                                </td>
                                <td>{{$massMailer->created_at}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>