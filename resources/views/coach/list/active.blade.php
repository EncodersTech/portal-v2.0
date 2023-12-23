@include('coach.list.topbar')

<ag-coach-list :gym="{{ $gym->id }}" :managed="{{ $_managed->id }}" :search="search"
    @coach-selected-changed="onSelectedCoachesChanged">
</ag-coach-list>