<div class="row">
    <div class="col-lg mb-3">
        <label for="mso_meet_id" class="mb-1">
            <span class="fas fa-fw fa-star-half-alt"></span>
            Meet Scores Online Meet ID
        </label>
        <div class="d-flex flex-row flex-nowrap">
            <div class="flex-grow-1">
                <input id="mso_meet_id" name="mso_meet_id" autocomplete="mso_meet_id" placeholder="MSO Meet ID"
                    class="form-control form-control-sm @error('mso_meet_id') is-invalid @enderror" 
                    value="{{ $tm->oldOrValue('mso_meet_id') }}"
                    type="text">
            </div>
            <div>
                <img src="{{ asset('/img/mso.png') }}" class="mso-logo ml-2">
            </div>
        </div>
        @error('mso_meet_id')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <div class="text-info small mt-1 mb-1">
            <span class="fas fa-info-circle"></span>
            If you already added your meet on MSO, please type in your Meet ID above.
        </div>
    </div>
</div>