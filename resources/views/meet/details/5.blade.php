<div class="row">
    <div class="col">
        <div class="row">
            <div class="col">
                <h5 class="border-bottom">
                    <span class="fas fa-fw fa-id-card-alt"></span> Primary Contact
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg mb-2">
                <table class="table table-sm table-striped table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="align-middle">
                                <span class="fas fa-fw fa-user-circle"></span> Name
                            </td>

                            <td class="align-middle">
                                {{ $meet->primary_contact_first_name . ' ' . $meet->primary_contact_last_name }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">
                                <span class="fas fa-fw fa-envelope"></span> Email
                            </td>

                            <td class="align-middle">
                                <a href="mailto:{{ $meet->primary_contact_email }}" target="_blank">
                                    {{ $meet->primary_contact_email }}
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">
                                <span class="fas fa-fw fa-phone"></span> Phone No.
                            </td>

                            <td class="align-middle">
                                {{ "+".phone($meet->primary_contact_phone, $meet->gym->country->code)->getPhoneNumberInstance()->getCountryCode()." ".phone($meet->primary_contact_phone,  $meet->gym->country->code)->formatNational() }}
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">
                                <span class="fas fa-fw fa-fax"></span> Fax No.
                            </td>

                            <td class="align-middle">
                                {{ $meet->primary_contact_fax != null ? "+".phone($meet->primary_contact_fax, $meet->gym->country->code)->getPhoneNumberInstance()->getCountryCode()." ".phone($meet->primary_contact_fax,  $meet->gym->country->code)->formatNational() : '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($meet->secondary_contact)
            <div class="row mt-3">
                <div class="col">
                    <h5 class="border-bottom">
                        <span class="fas fa-fw fa-id-card"></span> Secondary Contact
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-2">
                    <table class="table table-sm table-striped table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="align-middle">
                                    <span class="fas fa-fw fa-user-circle"></span> Name
                                </td>

                                <td class="align-middle">
                                    {{ $meet->secondary_contact_first_name . ' ' . $meet->secondary_contact_last_name }}
                                </td>
                            </tr>

                            <tr>
                                <td class="align-middle">
                                    <span class="fas fa-fw fa-envelope"></span> Email
                                </td>

                                <td class="align-middle">
                                    <a href="mailto:{{ $meet->secondary_contact_email }}" target="_blank">
                                        {{ $meet->secondary_contact_email }}
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="align-middle">
                                    <span class="fas fa-fw fa-phone"></span> Phone No.
                                </td>

                                <td class="align-middle">
                                    {{ $meet->secondary_contact_phone }}
                                </td>
                            </tr>

                            <tr>
                                <td class="align-middle">
                                    <span class="fas fa-fw fa-fax"></span> Fax No.
                                </td>

                                <td class="align-middle">
                                    {{ $meet->secondary_contact_fax != null ? $meet->secondary_contact_fax : '—' }}
                                </td>
                            </tr>

                            @if ($is_own)
                                <tr>
                                    <td class="align-middle">
                                        <span class="fas fa-fw fa-question-circle"></span> Receives copy of meet mails
                                    </td>

                                    <td class="align-middle">
                                        {{ $meet->secondary_cc ? 'Yes' : 'No' }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
