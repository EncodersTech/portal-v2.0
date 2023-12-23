<div class="modal fade" id="modal-athlete-import" tabindex="-1" role="dialog"
        aria-labelledby="modal-athlete-import" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <span class="fas fa-file-import"></span> Import Athletes
                </h5>
                <button type="button" class="close modal-athlete-import-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                <ag-athlete-import :gym="{{ $gym->id }}" csrf="{{ csrf_token() }}"
                    {{ $gym->usaigc_membership != null ? ':usaigc_no="' . $gym->usaigc_membership .'"' : '' }}
                    {{ $gym->nga_membership != null ? ':nga_no="' . $gym->nga_membership .'"' : '' }}>
                </ag-athlete-import>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm mb-2 pr-sm-0">
        <ag-athlete-search-field placeholder="Start typing a name, date of birth, or athlete number ..."
            @active-search-field-text-changed="onSearchTextChanged" identifier="active">
        </ag-athlete-search-field>
    </div>
    <div class="col col-sm-auto mb-2 text-right">
        <div class="mb-1 mr-1 d-inline-block">
            <a href="{{ route('gyms.athletes.create', ['gym' => $gym]) }}" class="btn btn-sm btn-success">
                <span class="fas fa-fw fa-user-plus"></span> Add
            </a>
        </div>

        <div class="mb-1 mr-1 d-inline-block">
            <a href="#modal-athlete-import" class="btn btn-sm btn-primary" data-toggle="modal"
                data-backdrop="static" data-keyboard="false">
                <span class="fas fa-fw fa-file-import"></span> Import Athletes
            </a>
        </div>

        <div class="mb-1 d-inline-block">
            <button class="btn btn-sm btn-danger" id="remove-selected-athletes-button">
                <span class="fas fa-fw fa-trash"></span> Remove
            </button>
            <form action="{{ route('gyms.athletes.batch.delete', ['gym' => $gym]) }}" method="POST"
                id="remove-selected-athletes-form">
                @csrf
                @method('DELETE')
                <input type="hidden" name="selected_athletes_list">
            </form>
        </div>
    </div>
</div>