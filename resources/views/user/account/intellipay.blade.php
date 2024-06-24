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
                <form method="post" id="intellipay-card-link-form" action="{{ route('account.icard.add') }}">
                    @csrf
                    <div class="form-row">
                        <div class="alert alert-info">
                            <span class="fas fa-info-circle mt-2 mb-2"></span>
                            A one time $1 charge will be added to your card to verify validity. Within 72 hours it will automatically be returned. 
                        </div>
                        <label for="" class="text-info small">
                            <?php 
                                if($cards != null)
                                {
                                    echo '<span class="fas fa-info-circle mt-2 mb-2"></span> Adding a new card to your account will replace the existing one.';
                                }
                            ?>
                        </label>
                    </div>
                    <div class="form-row">
                        <label for="">Name</label>
                        <input type="text" name="cardname" class="form-control" value="<?= auth()->user()->fullName() ?>">
                    </div>
                    <div class="form-row">
                        <label for="">Card Number</label>
                        <input type="text" name="cardnumber" id="cardnumber" class="form-control" maxlength="19" onpaste="return false;" ondrop="return false;" autocomplete="off">
                    </div>
                    <div class="form-row">
                        <label for="">Expiry Date</label>
                        <input type="text" name="cardexpirydate" id="cardexpirydate" class="form-control" placeholder="mm/yy" maxlength="5">
                    </div>
                    <div class="form-row">
                        <label for="">CVV</label>
                        <input type="text" name="cardcvv" id="cardcvv" class="form-control" maxlength="4">
                    </div>

                    <div class="form-row">
                        <div class="text-info small">
                            <span class="fas fa-info-circle mt-2 mb-2"></span> Your credit card information is securely
                            sent to our payment provider and never transits through our servers.
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 pr-0">
                        <div class="text-right">
                            <button class="btn btn-primary" id="intellipay_submitbtn">
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
</div>

@if ($cards == null)
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
                            {{ $card['expires']['month'] }} / {{ $card['expires']['year'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="text-right">
    <a href="#modal-linked-credit-card" class="btn btn-sm btn-primary" data-toggle="modal"
        data-backdrop="static" data-keyboard="false">
        <span class="fas fa-plus"></span> Link a Card
    </a>
</div>