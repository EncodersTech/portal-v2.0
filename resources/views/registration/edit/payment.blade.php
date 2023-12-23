<div class="row">
    <div class="col">
        <ag-registration-edit-payment
            :late="{{ $meet->isLate() ? 'true' : 'false' }}" :gym-id="gymId" :meet-id="meetId"
            :managed="{{ $_managed->id }}" :registration-id="{{$registration->id}}"
            :registration-data="registrationData" :payment-options="paymentOptions" :previous_remaining="{{ $previous_remaining }}"
            @back-button="backToFirstStep">
        </ag-registration-details>
    </div>
</div>