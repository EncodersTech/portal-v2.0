<div class="row">    
    <div class="col">
        <form method="POST" action="{{ route('gyms.meets.store.4', ['gym' => $gym, 'temporary' => $tm]) }}" enctype="multipart/form-data">
            @csrf

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
                        PNG / JPEG / PDF only. {{ $meet_max_file_count }} files maximum, {{ $meet_max_file_size }} maximum.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <ag-meet-files :limit="{{ $meet_max_file_count }}" :initial_files="{{
                        $tm->oldOrValue('files') != null ?
                        $tm->oldOrValue('files') :
                        '[]'
                    }}" :initial_schedule="{{
                        $tm->oldOrValue('schedule') != null ?
                        $tm->oldOrValue('schedule') :
                        'null'
                    }}"></ag-meet-files>
                </div>
            </div>

            <div class="d-flex flex-row flex-nowrap mt-3">
                <div class="flex-grow-1">
                    <a href="{{ route('gyms.meets.create.step.view', ['gym' => $gym, 'step' => ($step - 1), 'temporary' => $tm]) }}"
                        class="btn btn-primary">
                        <span class="fas fa-long-arrow-alt-left"></span> Back
                    </a>
                </div>

                <div class="ml-3">
                    <button class="btn btn-success" type="submit">
                        Next <span class="fas fa-long-arrow-alt-right"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>