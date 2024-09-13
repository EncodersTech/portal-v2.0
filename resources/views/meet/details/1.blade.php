<div class="row dd" style="font-size:10px;">
    <div class="col-md-4">
        <h4>General Entry</h4>
        @foreach($mini_level as $key=>$value)
            <div>{{ $key }}</div>
            <ul>
            @foreach($value as $k=>$v)
                <div>{{ $k }}</div>
                @if($v["has_change"])
                    <div>Starts with: {{number_format($v["fee"],2) }}</div>
                @else
                <div>All Level: {{number_format($v["fee"],2) }}</div>
                @endif
            @endforeach
            </ul>
        @endforeach
    </div>
    @if($meet->registration_first_discount_is_enable )
    <div class="col-md-4">
        <h4>Early Entry</h4>
        @foreach($mini_level as $key=>$value)
            <div>{{ $key }}</div>
            <ul>
            @foreach($value as $k=>$v)
                <div>{{ $k }}</div>
                @if($v["has_change"])
                    <div>Starts with: {{number_format($v["registration_fee_first"],2) }}</div>
                @else
                <div>All Level: {{number_format($v["registration_fee_first"],2) }}</div>
                @endif
            @endforeach
            </ul>
        @endforeach
    </div>
    @endif
    @if($meet->registration_second_discount_is_enable)
    <div class="col-md-4">
        <h4>Standard Entry</h4>
        @foreach($mini_level as $key=>$value)
            <div>{{ $key }}</div>
            <ul>
            @foreach($value as $k=>$v)
                <div>{{ $k }}</div>
                @if($v["has_change"])
                    <div>Starts with: {{number_format($v["registration_fee_second"],2) }}</div>
                @else
                <div>All Level: {{number_format($v["registration_fee_second"],2) }}</div>
                @endif
            @endforeach
            </ul>
        @endforeach
    </div>
    @endif
    @if($meet->registration_third_discount_is_enable )
    <div class="col-md-4">
        <h4>Standard 2 Entry</h4>
        @foreach($mini_level as $key=>$value)
            <div>{{ $key }}</div>
            <ul>
            @foreach($value as $k=>$v)
                <div>{{ $k }}</div>
                @if($v["has_change"])
                    <div>Starts with: {{number_format($v["registration_fee_third"],2) }}</div>
                @else
                <div>All Level: {{number_format($v["registration_fee_third"],2) }}</div>
                @endif
            @endforeach
            </ul>
        @endforeach
    </div>
    @endif
</div>
<div class="row">
    <div class="col">
        <div class="row">
            <div class="col">
                <h5 class="border-bottom"><span class="fas fa-fw fa-align-justify"></span> General Info</h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-dumbbell"></span> Host
                            </td>

                            <td class="align-middle">
                                {{ $meet->gym->name }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-calendar-week"></span> Meet Name
                            </td>

                            <td class="align-middle">
                                {{ $meet->name }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-link"></span> Meet Website
                            </td>

                            <td class="align-middle">
                                <a href="{{ $meet->website }}" class="link-word-break-all" target="_blank">
                                    {{ $meet->website }}
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-info-circle"></span> Description
                            </td>
                            <td class="align-middle">
                                <p class="preserve-new-lines mb-0">{{ $meet->description }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-calendar-alt"></span> Start Date
                            </td>
                            <td class="align-middle">
                                {{ $meet->start_date->format(Helper::AMERICAN_SHORT_DATE) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-calendar-alt"></span> End Date
                            </td>
                            <td class="align-middle">
                                {{ $meet->end_date->format(Helper::AMERICAN_SHORT_DATE) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-dumbbell"></span> Equipment
                            </td>
                            <td class="align-middle">
                                <p class="preserve-new-lines mb-0">{{ $meet->equipement }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-info-circle"></span> Notes
                            </td>
                            <td class="align-middle">
                                <p class="preserve-new-lines mb-0"
                                    >{{ $meet->notes !== null ? $meet->notes : '—' }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-info-circle"></span> Special Annoucements
                            </td>
                            <td class="align-middle">
                                <p class="preserve-new-lines mb-0">{{
                                    $meet->special_annoucements !== null ? $meet->special_annoucements : '—'
                                }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-tshirt"></span> T-shirt Chart
                            </td>
                            <td class="align-middle">
                                {{ $meet->tshirt_chart != null ? $meet->tshirt_chart->name : '—' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-female"></span> Leo Chart
                            </td>
                            <td class="align-middle">
                                {{ $meet->leo_chart != null ? $meet->leo_chart->name : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-certificate"></span> Meet Featured
                            </td>
                            <td class="align-middle">
                                {{ $meet->is_featured == true ? 'YES' : 'NO' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-money-bill-alt"></span> Admissions
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" class="align-middle">Admission</th>
                            <th scope="col" class="align-middle">Type</th>
                            <th scope="col" class="align-middle">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($meet->admissions as $admission)
                            <tr>
                                <td class="align-middle">
                                    {{ $admission->name }}
                                </td>

                                <td class="align-middle">
                                    {{ \App\Models\MeetAdmission::TYPE_NAMES[$admission->type] }}
                                </td>

                                <td class="align-middle">
                                    {{
                                        $admission->type == \App\Models\MeetAdmission::TYPE_PAID ?
                                        '$' . number_format($admission->amount, 2) :
                                        '—'
                                    }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-building"></span> Venue
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-building"></span> Venue Name
                            </td>
                            <td class="align-middle">
                                {{ $meet->venue_name }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-link"></span> Venue Website
                            </td>
                            <td class="align-middle">
                                <a href="{{ $meet->venue_website }}" class="link-word-break-all" target="_blank">
                                    {{ $meet->venue_website }}
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-map-marker-alt"></span> Address Line 1
                            </td>
                            <td class="align-middle">
                                {{ $meet->venue_addr_1}}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-map-marker"></span> Address Line 2
                            </td>
                            <td class="align-middle">
                                {{ $meet->venue_addr_2 != null ? $meet->venue_addr_2 : '—' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-map-marked-alt"></span> City
                            </td>
                            <td class="align-middle">
                                {{ $meet->venue_city}}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-map-marked"></span> State
                            </td>
                            <td class="align-middle">
                                {{ $meet->venue_state->name }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle font-weight-bold">
                                <span class="fas fa-fw fa-map-marked-alt"></span> Zip code
                            </td>
                            <td class="align-middle">
                                {{ $meet->venue_zipcode }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($meet->show_participate_clubs && count($meet->registrations) > 0)
        <div class="row mt-3">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-users"></span> Participating Clubs
                </h5>
            </div>
        </div>
        <div class="row">
            <div class="col-lg mb-2">
            <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                    @foreach($meet->registrations as $m) 
                    <tr>
                        <td class="align-middle">
                        <span class="fas fa-fw fa-dumbbell"></span> <b>{{$m->gym->name}}</b>
                        </td>
                    </tr>   
                    @endforeach
                        
                    </tobdy>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
