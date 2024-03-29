@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span>
    {{ $tm->step > 1 ? 'Creating ' . $tm->name : 'Create A Meet'}}
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    <div class="content-main p-3">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <ul class="nav flex-column nav-pills" role="tablist" id="create-meet-list-tabs">
                    <li class="nav-item">
                        <span class="nav-link {{ $step == 1 ? 'active' : ''}}">
                            <span class="fas fa-fw fa-align-justify"></span> General
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link {{ $step == 2 ? 'active' : ''}}">
                            <span class="fas fa-fw fa-money-check"></span> Registration
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link {{ $step == 6 ? 'active' : ''}}">
                            <span class="fas fa-fw fa-money-check"></span> Payment
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link {{ $step == 3 ? 'active' : ''}}">
                            <span class="fas fa-fw fa-cogs"></span> Competition Settings
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link {{ $step == 4 ? 'active' : ''}}">
                            <span class="fas fa-fw fa-file-alt"></span> Schedule &amp; Attachments
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link {{ $step == 5 ? 'active' : ''}}">
                            <span class="fas fa-fw fa-address-book"></span> Contact
                        </span>
                    </li>
                </ul>
            </div>

            <div class="col">
                <div class="tab-content">
                    <div class="tab-pane fade show active">
                        <div class="text-info font-weight-bold mb-3">
                            <span class="fas fa-info-circle"></span> Changes are not saved until you hit next.
                            All dates and times are in EST.
                        </div>

                        @switch($step)
                            @case(1)
                                @include('meet.create.1')
                                @break

                            @case(2)
                                @include('meet.create.2')
                                @break
                            
                            @case(3)
                                @include('meet.create.3')
                                @break

                            @case(4)
                                @include('meet.create.4')
                                @break
                            @case(6)
                                @include('meet.create.6')
                                @break

                            @default
                                @include('meet.create.5')
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/meet/create/meet-create-' . ($step == 6 ? 2:$step) . '.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#accept_mailed_check").change(function(e){
                var amc = $("#accept_mailed_check").is(":checked");
                if(amc == true)
                    $('#accept_deposit').prop("disabled",false);
                else{
                    $('#accept_deposit').prop("disabled",true);
                    $('#accept_deposit').prop("checked",false);
                }
            });
            $("#accept_deposit").change(function(e){
                var amc = $("#accept_deposit").is(":checked");
                if(amc == true)
                    $('#deposit_ratio').prop("disabled",false);
                else{
                    $('#deposit_ratio').prop("disabled",true);
                    $('#deposit_ratio').prop("checked",false);
                }
            });
            // $("#viewpaymenttab").click(function(e){
            //     $("#payment_div").show();
            //     $("#registratoin_div").hide();
            // });
            // $("#registrationview").click(function(e){
            //     $("#payment_div").hide();
            //     $("#registratoin_div").show();
            // });
            $("#accept_mailed_check").click(e => {
                var amc = $("#accept_mailed_check").is(":checked");
                if(amc == true)
                    confirmAction(
                        'This option will appear to registrants only if you have a valid credit card linked to your account at the time of registration. This card will be charged for Credit Card and Handling fees. If you chose to defer these fee to registering gym, They will be advised to include them in their entry fee check..<br/><br/>' +
                        '<strong>Do you agree to these terms?</strong>',
                        'green',
                        'fas fa-check',
                        () => {
                            $("#accept_mailed_check").click();
                        }
                    );
            });
            function confirmAction(msg, color, icon, callback) {
                $.confirm({
                    title: 'Are you sure ?',
                    content: msg,
                    icon: icon,
                    type: color,
                    typeAnimated: true,
                    buttons: {
                        no: function () {
                            callback();
                            this.close();
                        },
                        confirm:  {
                            text: 'Yes',
                            btnClass: 'btn-' + color,
                            action: function () {
                                // callback();
                            }
                        }
                    }
                });
            };
        });
    </script>
@endsection