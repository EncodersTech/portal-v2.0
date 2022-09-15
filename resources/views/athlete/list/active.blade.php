@include('athlete.list.topbar')

<div class="row">
    <div class="col-lg-auto pr-lg-0">    
        <ag-athlete-filters @athlete-filters-changed="onFiltersChanged"></ag-athlete-filters>  
    </div>

    <div class="col">
        <ag-athlete-list :gym="{{ $gym->id }}" :managed="{{ $_managed->id }}" :filters="filters"
            @athlete-selected-changed="onSelectedAthletesChanged">
        </ag-athlete-list>
    </div>
</div>