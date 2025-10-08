<div class="alert alert-primary">
    <strong class="d-block mb-2">
        <span class="fas fa-exclamation-circle"></span> Adding a new Meet
    </strong>

    <p>
        You can add a meet by clicking the button below.<br/>
        Please be advised that meets that have registrations cannot be removed from your account.<br/>
        Instead, you can archive them.
    </p>

    @if ($_managed->id == 6)
        <div class="text-right">
            <a href="{{ route('gyms.meets.create', ['gym' => $gym]) }}" class="btn btn-success">
                <span class="fas fa-plus"></span> Add a New Meet
            </a>
        </div>
    @endif
</div>

<div class="row mb-3">
    <div class="col">
        <ag-search-field placeholder="Start typing a meet name ..."
            @active-search-field-text-changed="onSearchTextChanged" identifier="active">
        </ag-search-field>
    </div>
</div>
<div class="row">
    <div class="col">
        <ag-meet-list :gym="{{ $gym->id }}" :managed="{{ $_managed->id }}" :filters="filters"
            singular='meet' plural='meets' csrf="{{ csrf_token() }}">
        </ag-meet-list>
    </div>
</div>