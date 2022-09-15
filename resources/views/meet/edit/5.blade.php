<div class="row">    
    <div class="col">
        <form method="POST" action="{{ route('gyms.meets.update.5', ['gym' => $gym, 'meet' => $meet]) }}">
            @csrf
            @method('PATCH')

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom">
                        <div class="d-flex flex-row flex-nowrap">
                            <div class="flex-grow-1">
                                <span class="fas fa-fw fa-id-card-alt"></span>
                                Primary Contact
                            </div>
                            <div class="ml-2">
                                <button type="button" class="btn btn-sm btn-link" id="primary_use_own_info">
                                    <span class="fas fa-copy"></span> Use
                                    {{ $_managed->isCurrentUser() ? 'my' : $_managed->fullName() }}
                                    profile info
                                </button>
                            </div>
                        </div>
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="primary_contact_first_name" class="mb-1">
                        <span class="fas fa-fw fa-user-circle"></span>
                        First Name <span class="text-danger">*</span>
                    </label>
                    <input id="primary_contact_first_name" name="primary_contact_first_name"
                        autocomplete="primary_contact_first_name" placeholder="First name"
                        class="form-control form-control-sm @error('primary_contact_first_name') is-invalid @enderror" 
                        value="{{ $meet->oldOrValue('primary_contact_first_name') }}"
                        type="text" data-primary-info="{{ $_managed->first_name }}"
                        required autofocus>
                    @error('primary_contact_first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="primary_contact_last_name" class="mb-1">
                        <span class="far fa-fw fa-user-circle"></span>
                        Last Name <span class="text-danger">*</span>
                    </label>
                    <input id="primary_contact_last_name" name="primary_contact_last_name"
                        autocomplete="primary_contact_last_name" placeholder="Last name"
                        class="form-control form-control-sm @error('primary_contact_last_name') is-invalid @enderror" 
                        value="{{ $meet->oldOrValue('primary_contact_last_name') }}"
                        type="text" data-primary-info="{{ $_managed->last_name }}" required>
                    @error('primary_contact_last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="email" class="mb-1">
                        <span class="fas fa-fw fa-envelope"></span>
                        Email <span class="text-danger">*</span>
                    </label>
                    <input id="primary_contact_email" name="primary_contact_email"
                        autocomplete="primary_contact_email" placeholder="Email address"
                        class="form-control form-control-sm @error('primary_contact_email') is-invalid @enderror" 
                        value="{{ $meet->oldOrValue('primary_contact_email') }}"
                        type="primary_contact_email" data-primary-info="{{ $_managed->email }}" required>
                    @error('primary_contact_email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="primary_contact_phone" class="mb-1">
                        <span class="fas fa-fw fa-phone"></span> Phone No.
                        <span class="text-danger">*</span>
                    </label>
                    <input id="primary_contact_phone" type="text" name="primary_contact_phone"
                        class="form-control form-control-sm @error('primary_contact_phone') is-invalid @enderror" 
                        value="{{ $meet->oldOrValue('primary_contact_phone') }}"
                        placeholder="Phone number" autocomplete="primary_contact_phone"
                        data-primary-info="{{ $_managed->office_phone }}" required>
                    @error('primary_contact_phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-lg mb-3">
                    <label for="primary_contact_fax" class="mb-1">
                        <span class="fas fa-fw fa-fax"></span> Fax No.
                    </label>
                    <input id="primary_contact_fax" type="text" name="primary_contact_fax"
                        class="form-control form-control-sm @error('primary_contact_fax') is-invalid @enderror" 
                        value="{{ $meet->oldOrValue('primary_contact_fax') }}"
                        placeholder="Fax number" autocomplete="primary_contact_fax">
                    @error('primary_contact_fax')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <h5 class="border-bottom">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox"
                                name="secondary_contact" id="secondary_contact"
                                {{ $meet->oldOrValue('secondary_contact') ? 'checked' : '' }}>
                            <label class="form-check-label" for="secondary_contact">
                                <span class="fas fa-fw fa-id-card"></span> Secondary Contact
                            </label>
                        </div>
                    </h5>
                </div>
            </div>

            <div class="secondary-info-fields">
                @if (count($secondaries))
                    <div class="row small mb-2">
                        <div class="col-lg mb-1 text-info">
                            <span class="fas fa-info-circle"></span> You can select one of your account
                            managers to fill the secondary contact fields with their info.
                        </div>
                        <div class="col-auto mb-1">
                            <select class="form-control form-control-sm"
                                id="secondary_use_own_info" name="secondary_use_own_info">
                                <option value="" selected>(Choose one ...)</option>
                                @foreach ($secondaries as $secondary)
                                    <option value="{{ $secondary->id }}"
                                        data-secondary-first="{{ $secondary->first_name }}"
                                        data-secondary-last="{{ $secondary->last_name }}"
                                        data-secondary-email="{{ $secondary->email }}"
                                        data-secondary-job="{{ $secondary->job_title }}"
                                        data-secondary-phone="{{ $secondary->office_phone }}">
                                        {{ $secondary->fullName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <div class="row mb-2">
                        <div class="col-lg mb-1 font-weight-bold text-info">
                            <span class="fas fa-info-circle"></span> When you have users added to manage your account,
                            you will be able to select one and fill the secondary contact fields with their info.
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg mb-3">
                        <label for="secondary_contact_first_name" class="mb-1">
                            <span class="fas fa-fw fa-user-circle"></span>
                            First Name <span class="text-danger">*</span>
                        </label>
                        <input id="secondary_contact_first_name" name="secondary_contact_first_name"
                            autocomplete="secondary_contact_first_name" placeholder="First name"
                            class="form-control form-control-sm @error('secondary_contact_first_name') is-invalid @enderror" 
                            value="{{ $meet->oldOrValue('secondary_contact_first_name') }}"
                            type="text"
                            required autofocus>
                        @error('secondary_contact_first_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-lg mb-3">
                        <label for="secondary_contact_last_name" class="mb-1">
                            <span class="far fa-fw fa-user-circle"></span>
                            Last Name <span class="text-danger">*</span>
                        </label>
                        <input id="secondary_contact_last_name" name="secondary_contact_last_name"
                            autocomplete="secondary_contact_last_name" placeholder="Last name"
                            class="form-control form-control-sm @error('secondary_contact_last_name') is-invalid @enderror" 
                            value="{{ $meet->oldOrValue('secondary_contact_last_name') }}"
                            type="text" required>
                        @error('secondary_contact_last_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg mb-3">
                        <label for="email" class="mb-1">
                            <span class="fas fa-fw fa-envelope"></span>
                            Email <span class="text-danger">*</span>
                        </label>
                        <input id="secondary_contact_email" name="secondary_contact_email"
                            autocomplete="secondary_contact_email" placeholder="Email address"
                            class="form-control form-control-sm @error('secondary_contact_email') is-invalid @enderror" 
                            value="{{ $meet->oldOrValue('secondary_contact_email') }}"
                            type="secondary_contact_email" required>
                        @error('secondary_contact_email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-lg mb-3">
                        <label for="job" class="mb-1">
                            <span class="fas fa-fw fa-envelope"></span>
                            Title / Position <span class="text-danger">*</span>
                        </label>
                        <input id="secondary_contact_job_title" name="secondary_contact_job_title"
                            autocomplete="secondary_contact_job_title" placeholder="Title / Position"
                            class="form-control form-control-sm @error('secondary_contact_job_title') is-invalid @enderror" 
                            value="{{ $meet->oldOrValue('secondary_contact_job_title') }}"
                            type="secondary_contact_job_title" required>
                        @error('secondary_contact_job_title')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg mb-3">
                        <label for="secondary_contact_phone" class="mb-1">
                            <span class="fas fa-fw fa-phone"></span> Phone No.
                            <span class="text-danger">*</span>
                        </label>
                        <input id="secondary_contact_phone" type="text" name="secondary_contact_phone"
                            class="form-control form-control-sm @error('secondary_contact_phone') is-invalid @enderror" 
                            value="{{ $meet->oldOrValue('secondary_contact_phone') }}"
                            placeholder="+1 631-555-5555 / (631) 555-5555" autocomplete="secondary_contact_phone" required>
                        @error('secondary_contact_phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-lg mb-3">
                        <label for="secondary_contact_fax" class="mb-1">
                            <span class="fas fa-fw fa-fax"></span> Fax No.
                        </label>
                        <input id="secondary_contact_fax" type="text" name="secondary_contact_fax"
                            class="form-control form-control-sm @error('secondary_contact_fax') is-invalid @enderror" 
                            value="{{ $meet->oldOrValue('secondary_contact_fax') }}"
                            placeholder="+1 631-555-5555 / (631) 555-5555" autocomplete="secondary_contact_fax">
                        @error('secondary_contact_fax')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                name="secondary_cc" id="secondary_cc"
                                {{ $meet->oldOrValue('secondary_cc') ? 'checked' : '' }}>
                            <label class="form-check-label" for="secondary_cc">
                                Send a copy of meet emails
                            </label>
                        </div> 
                    </div>
                </div>
            </div>

            <div class="d-flex flex-row flex-nowrap mt-3">
                <div class="flex-grow-1">
                    <a href="{{ route('gyms.meets.index', ['gym' => $gym]) }}"
                        class="btn btn-primary">
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