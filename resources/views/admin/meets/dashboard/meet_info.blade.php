<div class="col-12 col-xs-12 col-sm-3 col-md-12 col-lg-3">
    <div class="card">
        <div class="card-header p-2 font-style-12">
            <h6><b>Current handling fee: {{$meet->custom_handling_fee??\App\Helper::getSettingHandlingFee()}}</b></h6>
            <form id="updateHandlingFee">
                @csrf
                <lable><h6>Custom handling fee:</h6></lable>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="customHandlingFee" name="custom_handling_fee" autocomplete="off" value="{{$meet->custom_handling_fee??''}}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit" id="saveBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-header p-2 font-style-12">
            <h6>Copy Meet URL </h6>

            <div class="input-group input-group-sm pt-1">
                <div class="input-group-prepend">
            <span class="input-group-text">
                <span class="fas fa-link"></span>
            </span>
                </div>
                <input type="text" class="form-control bg-light" id="meet-public-url" readonly
                       value="https://www.allgymnastics.com/meet-details/?meet={{$meet->id}}">

                <div class="input-group-append">
                    <button class="btn btn-info" type="button"
                            id="meet-public-url-copy" data-clipboard-target="#meet-public-url">
                        <span class="fas fa-copy"></span></button>
                </div>
            </div>
        </div>

        <div class="card-body p-2 pt-3 font-size-13">
            <h6>Sanctioning Bodies </h6>

            <table class="table table-striped mt-2">
                <tr>
                    <td><b>Meet</b></td>
                    <td>{{ $meet->name }}</td>
                </tr>
                <tr>
                    <td><b>Host</b></td>
                    <td>{{ $meet->gym->name }}</td>
                </tr>
                <tr>
                    <td><b>Date</b></td>
                    <td>{{ \Carbon\Carbon::parse($meet->start_date)->format('M. d, Y') }} - {{ \Carbon\Carbon::parse($meet->end_date)->format('M. d, Y') }}</td>
                </tr>
                <tr>
                    <td><b>Venue</b></td>
                    <td>{{ $meet->venue_name }}, {{ $meet->venue_addr_1, isset($meet->venue_addr_2)?$meet->venue_addr_2:''}}, {{$meet->venue_city}}, {{\App\Helper::getStateName($meet->venue_state_id)}}, {{$meet->venue_zipcode}}</td>
                </tr>
                <tr>
                    <td><b>Registration Period</b></td>
                    <td>{{ \Carbon\Carbon::parse($meet->registration_start_date)->format('M. d, Y') }} - {{ \Carbon\Carbon::parse($meet->registration_end_date)->format('M. d, Y') }}<br><span class="badge font-size-13 badge-{{\App\Models\Meet::STATUS_COLOR[$meet->registration_status]}}">{{\App\Models\Meet::STATUS_ARRAY[$meet->registration_status]}}</span></td>
                </tr>
            </table>
        </div>

        <hr>
        <div class="card-body p-2 pt-3 font-size-13">
            <h6>USAIGC</h6>

            <table class="table table-striped">
                <tr>
                    <td><b>Team Allowed</b></td>
                    <td><span class="{{($team_allow > 0)?'text-primary':'text-danger'}}">{{ ($team_allow > 0)?'Yes':'No' }}</span></td>
                </tr>
                <tr>
                    <td><b>Registration Fee Payment</b></td>
                    <td><span class="text-danger">Registration accepted with deferred payment. Please see meet details.</span></td>
                </tr>
                <tr>
                    <td><b>Listing Status</b></td>
                    <td><span class="badge badge-{{ ($meet->is_published)?'success':'warning'}} font-size-13">{{ ($meet->is_published)?'Published':'Not Publish' }}</span></td>
                </tr>
                <tr>
                    <td><b>Meet URL</b></td>
                    <td><span class="text-primary cursor-pointer copy-meet-url" data-url="https://www.allgymnastics.com/meet-details/?meet={{$meet->id}}">Copy Link</span></td>
                </tr>
            </table>
        </div>
        <hr class="pb-0 mb-0">
        <div class="card-body p-2 pt-3 font-size-13">
            <a class="btn btn-info width-100px" href="{{route('host.meets.dashboard',['host'=>$meet->gym_id,'meets'=>$meet->id])}}">More Details</a>
        </div>
    </div>
</div>
