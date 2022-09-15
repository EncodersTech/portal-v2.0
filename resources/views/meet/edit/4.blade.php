<div class="row">
    <div class="col">
        <form method="POST" action="{{ route('gyms.meets.update.4', ['gym' => $gym, 'meet' => $meet]) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom">
                        <span class="fas fa-fw fa-copy"></span>
                        Schedule &amp; Attachement
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="text-info font-weight-bold mb-3">
                        <span class="fas fa-info-circle"></span>
                        PNG / JPEG / PDF only. {{ $meet_max_file_count }} files maximum, {{ $meet_max_file_size }}
                        maximum.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <ag-meet-files :limit="{{ $meet_max_file_count }}" :initial_files="{{
                        $meet->oldOrValue('files') != null ?
                        $meet->oldOrValue('files') :
                        '[]'
                    }}" :initial_schedule="{{
                        $meet->oldOrValue('schedule') != null ?
                        $meet->oldOrValue('schedule') :
                        'null'
                    }}"></ag-meet-files>

                </div>
            </div>

            <div class="d-flex flex-row flex-nowrap mt-3">
                <div class="flex-grow-1">
                    <a href="{{ route('gyms.meets.index', ['gym' => $gym]) }}" class="btn btn-primary">
                        <span class="fas fa-long-arrow-alt-left"></span> Back
                    </a>
                </div>

                <div class="ml-3">
                    <button class="btn btn-success" type="submit">
                        <span class="fas fa-save"></span> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>