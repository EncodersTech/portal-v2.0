<div class="mt-1 border-bottom">
    <div class="row">
        <div class="col-4 font-weight-bold">
            <span class="fas fa-calendar-check "></span> Meet Name
        </div>
        <div class="col-4">
            {{ $meet->name }}
        </div>
    </div>
</div>
<div class="mt-1 border-bottom">
    <div class="row">
        <div class="col-4 font-weight-bold">
            <span class="fas fa-dumbbell"></span> Hosted By
        </div>
        <div class="col-4">
            {{ $meet->gym->name }}
        </div>
    </div>
</div>
<div class="mt-1 border-bottom">
    <div class="row">
        <div class="col-4 font-weight-bold">
            <span class="fas fa-calendar-day"></span> Meet Dates
        </div>
        <div class="col-4">
            {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }} —
            {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}<br/>
        </div>
    </div>
</div>
<div class="mt-1 border-bottom">
    <div class="row">
        <div class="col-4 font-weight-bold">
            <span class="fas fa-calendar-check"></span> Registration
        </div>
        <div class="col-4">
            {{ $meet->registration_start_date->format(Helper::AMERICAN_SHORT_DATE) }} —
            {{ $meet->registration_end_date->format(Helper::AMERICAN_SHORT_DATE) }}<br/>
        </div>
    </div>

    @if ($meet->allow_late_registration)
        <div class="row">
                <div class="col-4 font-weight-bold">
                    <span class="far fa-calendar-check"></span> Late Registration
                </div>
                <div class="col-4">
                    {{ $meet->late_registration_start_date->format(Helper::AMERICAN_SHORT_DATE) }} —
                    {{ $meet->late_registration_end_date->format(Helper::AMERICAN_SHORT_DATE) }}<br/>
                </div>
            </div>
    @endif
</div>
<div class="mt-1 border-bottom">
    <div class="row">
        <div class="col-4 font-weight-bold">
            <span class="fas fa-calendar-day"></span> Scratch Date
        </div>
        <div class="col-4">
            {{ $meet->registration_scratch_end_date->format(Helper::AMERICAN_SHORT_DATE) }}
        </div>
    </div>
</div>
<div class="mt-1 border-bottom">
    <div class="row">
        <div class="col-4 font-weight-bold">
            <span class="fas fa-building"></span> Venue
        </div>
        <div class="col-4">
            {{ $meet->venue_addr_1 }}
            @if ($meet->venue_addr_2 != null)
                ,{{ $meet->venue_addr_2 }}
            @endif

            , {{ $meet->venue_city . ', ' . $meet->venue_state->code . ' ' . $meet->venue_zipcode }}<br/>
        </div>
    </div>
</div>
<!-- <div class="form-group mb-0 mt-3 border-bottom">
    <label for="meet-public-url" class="font-weight-bold mb-0">
        <span class="fas fa-link"></span> Meet's public URL
    </label>

    <div class="input-group input-group-sm">
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
                <span class="fas fa-copy"></span> Copy
            </button>
        </div>
    </div>

    <div class="mt-1 mb-1 text-right"
        id="meet-public-url-copy-success-message" style="visibility: hidden">
        <span class="badge badge-dark">
            <span class="fas fa-check-circle"></span>
            <span id="meet-public-url-copy-success"></span>
        </span>
    </div>
</div> -->