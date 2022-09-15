<div class="row mb-3">
    <div class="col-sm mb-2 pr-sm-0">
        <ag-athlete-search-field placeholder="Start typing a name, date of birth, or athlete number ..."
            @faulty-search-field-text-changed="onFaultySearchTextChanged" identifier="faulty">
        </ag-athlete-search-field>
    </div>
    <div class="col col-sm-auto mb-2 text-right">
        <div class="mb-1 d-inline-block">
            <button class="btn btn-sm btn-danger" id="remove-selected-failed-athletes-button">
                <span class="fas fa-fw fa-trash"></span> Remove
            </button>
            <form action="{{ route('gyms.athletes.failed.import.batch.delete', ['gym' => $gym]) }}" method="POST"
                id="remove-selected-failed-athletes-form">
                @csrf
                @method('DELETE')
                <input type="hidden" name="selected_failed_athletes_list">
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <ag-faulty-athlete-list :gym="{{ $gym->id }}" :managed="{{ $_managed->id }}" :search="faultySearch"
            @failed-athlete-selected-changed="onFailedAthleteSelectChanged">
        </ag-faulty-athlete-list>
    </div>
</div>