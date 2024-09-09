<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    @section('styles')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @show
    <style>
        .allgymlogo{
            width: 15%;
            background: black;
            height: 8em;
        }
        .center{
            text-align:center;
        }
    </style>
</head>

<body>
<div id="payment_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Payment</h4>
                <button type="button" class="close" onclick="closemodal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" id="stripe-card-link-form" action="{{ route('ticket.buy') }}">
                    @csrf
                    <input type="hidden" name="meet_id" value="{{ $meet->id }}">
                    <input type="hidden" name="tickets" id="tickets" value="">
                    <input type="hidden" name="token" id="stripe-card-link-token">
                    <input type="hidden" name="total" id="total_value">
                    <div class="form-row">
                        <label for="name">Full Name <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <label for="email">Email <span style="color:red">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <label for="phone">Phone <span style="color:red">*</span></label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <label for="stripe-card-link-card-element">
                            Credit or Debit Card
                        </label>
                        <div id="stripe-card-link-card-element" class="w-100 mt-2">

                        </div>
                        <div id="stripe-card-link-card-errors" class="text-danger mt-2 small" role="alert"></div>
                    </div>

                    <div class="form-row mt-2">
                        <div class="text-info small">
                            <span class="fas fa-info-circle mt-2 mb-2"></span> Your credit card information is securely
                            sent to our payment provider and never transits through our servers.
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 pr-0">
                        <div class="text-right">
                            <button class="btn btn-primary" type="button" onclick="process_payment();">
                                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                        id="modal-linked-credit-card-spinner" style="display: none;">
                                </span>
                                <span class="fas fa-plus"></span> Proceed
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>    
<div class="center">
<img src="{{ asset('img/logos/red_and_white_transparent.png') }}" alt="" class="allgymlogo">
</div>
<div class="container mt-3">
    <div class="card p-2">
        <h2 class="center">Meet Attending Tickets</h2>
        <div>
            <!-- error or sucess show -->
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <table>
                <tr>
                    <td><b>Meet Name</b> </td>
                    <td>: {{ $meet->name }}</td>
                </tr>
                <tr>
                    <td><b>Meet Host</b></td>
                    <td>: {{ $meet->gym->name }}</td>
                </tr>
                <tr>
                    <td><b>Start Date</b> </td>
                    <td>: {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }}</td>
                </tr>
                <tr>
                    <td><b>End Date</b></td>
                    <td>: {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}</td>
                </tr>
            </table>
            <div class="row mt-3">
                <div class="col-lg mb-2">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" class="align-middle">Admission</th>
                                <th scope="col" class="align-middle">Type</th>
                                <th scope="col" class="align-middle">Amount</th>
                                <th scope="col" class="align-middle">Ticket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($meet_admissions as $admission)
                                <tr>
                                    <td class="align-middle">
                                        {{ $admission->name }}
                                    </td>

                                    <td class="align-middle">
                                        {{ \App\Models\MeetAdmission::TYPE_NAMES[$admission->type] }}
                                    </td>

                                    <td class="align-middle" id="ticket_price_{{$admission->id}}">
                                        {{
                                            $admission->type == \App\Models\MeetAdmission::TYPE_PAID ?
                                            '$' . number_format($admission->amount, 2) :
                                            '—'
                                        }}
                                    </td>
                                    <td>
                                        <span id="ticke_count_{{$admission->id}}">0</span>
                                        <div class="float-right">
                                            <button class="btn btn-danger" onclick="deduct_ticket({{$admission->id}})">-</button>
                                            <button class="btn btn-success" onclick="add_ticket({{$admission->id}})">+</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3" class="text-right">Subtotal $</td>
                                <td><b id="total_ticket">0</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Processing Fee $</td>
                                <td><b id="processing_fee">0</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Handling Fee $</td>
                                <td><b id="handling_fee">0</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right"><b>Total Fee $</b></td>
                                <td><b id="total_fee">0</b></td>
                            </tr>
                            
                        </tbody>
                    </table>
                    <button class="btn btn-primary float-right mt-3" onclick="payment_modal();">Proceed to Payment</button>
                </div>
            </div>
        </div>
    </div>
</div>
    

</body>

</html>

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        var tickets = [];
        let stripe = Stripe('{{ env('STRIPE_PUBLIC_KEY') }}');
        let card;
        let displayError;
        let busy = true;
        let processing_fee = '<?= $processing_fee; ?>';
        processing_fee = parseFloat(processing_fee/100).toFixed(4);
        let handling_fee = '<?= $handling_fee; ?>';
        handling_fee = parseFloat(handling_fee/100).toFixed(4);

        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        Tawk_API.onStatusChange = function (status){
            if(status === 'online'){
                document.getElementById('live_chat_div').classList.remove('d-none');
            }else{
                document.getElementById('live_chat_div').classList.add('d-none');
            }
        };
        (function(){
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='https://embed.tawk.to/5b4fd91adf040c3e9e0bb714/default';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
        })();

        deduct_ticket = function(id){
            var ticket_count = parseInt(document.getElementById('ticke_count_'+id).innerText);
            if(ticket_count > 0){

                var ticket_price = document.getElementById('ticket_price_'+id).innerText;
                if(ticket_price == '—'){
                    ticket_price = 0;
                }else{
                    
                    ticket_price = ticket_price.replace('$', '');
                    ticket_price = parseFloat(ticket_price);
                }

                ticket_count--;
                document.getElementById('ticke_count_'+id).innerText = ticket_count;
                ticket_price = parseFloat($('#total_ticket').text()) - ticket_price;
                $('#total_ticket').text(ticket_price);
                if(ticket_price >= 0){
                    var p_fee = parseFloat(processing_fee * ticket_price).toFixed(4);
                    var h_fee = parseFloat(handling_fee * ticket_price).toFixed(4);
                    $('#processing_fee').text(p_fee);
                    $('#handling_fee').text(h_fee);
                    $('#total_fee').text((parseFloat(ticket_price) + parseFloat(p_fee) + parseFloat(h_fee)).toFixed(4));
                }
                // remove ticket from array
                for (let i = 0; i < tickets.length; i++) {
                    if(tickets[i].id == id){
                        if(tickets[i].count > 0){
                            tickets[i].count--;
                        }
                        if(tickets[i].count == 0){
                            tickets.splice(i, 1);
                        }
                    }
                }
            }
        }
        add_ticket = function(id){
            var ticket_price = document.getElementById('ticket_price_'+id).innerText;
            if(ticket_price == '—'){
                ticket_price = 0;
            }else{
                ticket_price = ticket_price.replace('$', '');
                ticket_price = parseFloat(ticket_price);
            }
            var ticket_count = parseInt(document.getElementById('ticke_count_'+id).innerText);
            ticket_count++;
            document.getElementById('ticke_count_'+id).innerText = ticket_count;
            ticket_price = ticket_price + parseFloat($('#total_ticket').text());
            $('#total_ticket').text(ticket_price);
            if(ticket_price >= 0){
                    var p_fee = parseFloat(processing_fee * ticket_price).toFixed(4);
                    var h_fee = parseFloat(handling_fee * ticket_price).toFixed(4);
                    $('#processing_fee').text(p_fee);
                    $('#handling_fee').text(h_fee);
                    $('#total_fee').text((parseFloat(ticket_price) + parseFloat(p_fee) + parseFloat(h_fee)).toFixed(4));
                }
            // add ticket to array
            if (tickets.length > 0) {
                for (let i = 0; i < tickets.length; i++) {
                    if(tickets[i].id == id){
                        tickets.splice(i, 1);
                    }
                }
            }
            tickets.push({id: id, count: ticket_count});
        }
        payment_modal = function(){
            // check if user has selected any ticket
            if(tickets.length == 0){
                alert('Please select at least one ticket');
                return;
            }

            $('#payment_modal').modal('show');
            $('#tickets').val(JSON.stringify(tickets));
            $('#total_value').val($('#total_fee').text());

            
            let elements = stripe.elements();
            card = elements.create('card');
            displayError = $('#stripe-card-link-card-errors');
            let spinner = $('#modal-linked-credit-card-spinner');
            card.mount('#stripe-card-link-card-element');
        }
        closemodal = function(){
            $('#payment_modal').modal('hide');
        }

        process_payment = function(e){

            // check required fields
            let name = $('input[name="name"]').val();
            let email = $('input[name="email"]').val();
            let phone = $('input[name="phone"]').val();

            if(name == '' || email == '' || phone == ''){
                displayError.html('Please fill all required fields');
                return;
            }

            let spinner = $('#modal-linked-credit-card-spinner');
            spinner.show();

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    spinner.hide();
                    displayError.html(result.error.message);
                } else {
                    busy = false;
                    $('#stripe-card-link-token').val(result.token.id);
                    $('#stripe-card-link-form').submit();

                }
            });
        };

        // alert timeout
        setTimeout(function(){
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@show