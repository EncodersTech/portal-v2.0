<div class="modal fade" id="modal-linked-credit-card" tabindex="-1" role="dialog"
        aria-labelledby="modal-linked-credit-card" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-plus"></span> Link a Credit Card
                </h5>
                <button type="button" class="close modal-linked-credit-card-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                <form method="post" id="stripe-card-link-form">
                    <div class="form-row">
                        <label for="stripe-card-link-card-element">
                            Credit or Debit Card
                        </label>
                        <div id="stripe-card-link-card-element" class="w-100">

                        </div>
                        <div id="stripe-card-link-card-errors" class="text-danger mt-1 small" role="alert"></div>
                    </div>

                    <div class="form-row">
                        <div class="text-info small">
                            <span class="fas fa-info-circle mt-2 mb-2"></span> Your credit card information is securely
                            sent to our payment provider and never transits through our servers.
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 pr-0">
                        <div class="text-right">
                            <button class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                        id="modal-linked-credit-card-spinner" style="display: none;">
                                </span>
                                <span class="fas fa-plus"></span> Link
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <h5 class="border-bottom"><span class="fab fa-cc-stripe"></span> Linked Credit Cards</h5>
    </div>

    <div class="col d-none">
        <input type="hidden" id="stripe-publishable-key" value="{{ config('services.stripe.public') }}">
    </div>
</div>

@if ($stripe_error)
    <div class="alert alert-danger">
        <strong><span class="fas fa-times-circle"></span> Ooh !</strong><br/>
        {{ $stripe_error }}
    </div>
@elseif ($cards == null)
    <div class="alert alert-info">
        <strong><span class="fas fa-exclamation-triangle"></span> Whoops !</strong><br/>
        It looks like you do not have any cards linked to your account yet.
        You can do so by clicking the button below.
    </div>
@else
    <div class="table-responsive-lg">
        <table class="table table-sm table-hover">
            <thead class="thead-dark">
                <tr>
                    <th scope="col" class="align-middle">Brand</th>
                    <th scope="col" class="align-middle">Last 4</th>
                    <th scope="col" class="align-middle">Expiry Date</th>
                    <th scope="col" class="text-right align-middle"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cards as $card)
                    <tr>
                        <td class="align-middle">
                            <img class="credit-card-brand-image" src="{{ $card['image'] }}"
                                    alt="{{ $card['brand'] }}" title="{{ $card['brand'] }}">
                        </td>

                        <td class="align-middle">
                            <span class="font-weight-light">XXXX</span>-{{ $card['last4'] }}
                        </td>

                        <td class="align-middle">
                            {{ $card['exp_month'] }} / {{ $card['exp_year'] }}
                        </td>

                        <td class="text-right credit-card-remove align-middle">
                            <button class="btn btn-sm btn-danger"
                                        data-card="{{ $card['id'] }}" title="Remove">
                                <span class="fas fa-trash"></span>
                            </button>
                            <form action="{{ route('account.card.remove', ['id' => $card['id']]) }}"
                                    data-card="{{ $card['id'] }}" class="d-none" method="post">
                                @csrf
                                @method('DELETE')                            
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="text-right">
    <form id="stripe-card-link-add-form" action="{{ route('account.card.add') }}"
            class="d-none" method="post">
        @csrf
        <input type="hidden" id="stripe-card-link-token" name="card_token" value="">
    </form>
    <a href="#modal-linked-credit-card" class="btn btn-sm btn-primary" data-toggle="modal"
        data-backdrop="static" data-keyboard="false">
        <span class="fas fa-plus"></span> Link a Card
    </a>
</div>