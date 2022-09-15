<div class="row">
    <div class="col">
        <div class="row">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-info-circle"></span> Competition Details
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-clipboard-check"></span> Categories and Sanctions
                            </td>
                            <td class="align-middle">
                                <ul class="mb-0 small">
                                    @foreach ($allCategories as $bodyName => $categories)
                                    <li>
                                        <div class="font-weight-bold text-primary">{{ $bodyName }}</div>
                                        <ul>
                                            @foreach ($categories as $categoryName)
                                            <li>{{ $categoryName }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-align-center"></span> Competition Format
                            </td>
                            <td class="align-middle">
                                @if ($meet->competition_format->id == \App\Models\MeetCompetitionFormat::OTHER)
                                {{ $meet->meet_competition_format_other }}
                                @else
                                {{ $meet->competition_format->name }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-info-circle"></span> Team Format
                            </td>
                            <td class="align-middle">
                                <p class="preserve-new-lines mb-0">{{ $meet->team_format }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-layer-group"></span> Levels
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <ag-meet-levels-view singular="level" plural="levels"
                    :late="{{ $meet->allow_late_registration ? 'true' : 'false' }}"
                    :meet="{{ $meet }}"
                    :initial="{{ count($bodies) > 0 ? json_encode($bodies) : '{}' }}">
                </ag-meet-levels-view>
            </div>
        </div>
    </div>
</div>