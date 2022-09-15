<div class="row">
    <div class="col">
        <div class="row">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-file-upload"></span> Schedule
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle">
                                @if ($meet->schedule != null)
                                <span class="fas fa-fw fa-file"></span>
                                <a href="{{ $meet->schedule->path }}" target="_blank">
                                    {{ $meet->schedule->name }}
                                </a>
                                @else
                                —
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-cloud-upload-alt"></span> Attachments
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        @if ($meet->files->count() > 0)
                        @foreach ($meet->files as $file)
                        <tr>
                            <td class="align-middle">
                                <span class="fas fa-fw fa-file"></span>
                                <a href="{{ $file->path }}" target="_blank">
                                    {{ $file->name }}
                                </a>
                            </td>
                            <td class="align-middle">
                                <p class="preserve-new-lines mb-0">{{ $file->description }}</p>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        —
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>