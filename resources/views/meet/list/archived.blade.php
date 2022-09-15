<div class="row mb-3">
    <div class="col">
        <ag-search-field placeholder="Start typing a meet name ..."
            @archived-search-field-text-changed="onArchivedSearchTextChanged" identifier="archived">
        </ag-search-field>
    </div>
</div>
<div class="row">
    <div class="col">
        <ag-archived-meet-list :gym="{{ $gym->id }}" :managed="{{ $_managed->id }}"
            :filters="archivedFilters" singular='meet' plural='meets' csrf="{{ csrf_token() }}">
        </ag-archived-meet-list>
    </div>
</div>