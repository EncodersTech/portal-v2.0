<div class="row">
    <div class="col">
        <ag-registration-payment
            :late="{{ $meet->isLate() ? 'true' : 'false' }}" :gym-id="gymId" :meet-id="{{$meet->id}}"
            :managed="{{ $_managed->id }}" :registration-data="registrationData"
            :payment-options="paymentOptions" @back-button="backToFirstStep">
        </ag-registration-details>
    </div>
</div>