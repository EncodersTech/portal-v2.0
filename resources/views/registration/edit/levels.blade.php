<div class="row">
    <div class="col">
        <ag-registration-edit-details singular="athlete" plural="athletes"
            :late="{{ $meet->isLate() ? 'true' : 'false' }}" :gym-id="{{ $gym->id }}"
            :meet-id="{{$meet->id}}" :registration-id="{{$registration->id}}"
            :late="{{ $meet->isLate() }}" :managed="{{ $_managed->id }}" @process-data="firstStep" 
            :requires_sanction="{{ $required_sanctions }}" :previous_remaining="{{ $previous_remaining }}" 
            :previous_registration_credit_amount="{{ $previous_registration_credit_amount}}">
        </ag-registration-edit-payment>
    </div>
</div>