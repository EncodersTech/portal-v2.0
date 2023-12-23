<div class="col-12">
    <div class="card p-3">
        <div class="card-body p-0">
            <div class="row">
                <input type="hidden" name="user_id" value="{{$user->id}}">
                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-fw fa-user-circle"></i> First Name</label><span class="required"> *</span>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-fw fa-user"></i></span>
                        </div>
                        <input type="text" name="first_name" value="{{ $user->first_name }}" class="form-control" placeholder="First Name" required autocomplete="first_name" autofocus>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-fw fa-user-circle"></i> Last Name</label><span class="required"> *</span>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-fw fa-user"></i></span>
                        </div>
                        <input type="text" name="last_name" value="{{ $user->last_name }}" class="form-control" placeholder="Last Name" required autocomplete="last_name" autofocus>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-fw fa-envelope"></i>Email</label><span class="required"> *</span>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-fw fa-envelope"></i></span>
                        </div>
                        <input type="email" name="email" value="{{ $user->email }}" class="form-control" placeholder="Email" required autocomplete="email" autofocus>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-calendar"></i> Date Joined</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        <input type="text" value="{{ $user->created_at }}" class="form-control" placeholder="Created at" readonly>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-fw fa-phone"></i> Office Phone</label><span class="required"> *</span>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-fw fa-phone"></i></span>
                        </div>
                        <input type="text" name="office_phone" value="{{ $user->office_phone }}" class="form-control" placeholder="Office Phone" required autocomplete="office_phone" autofocus>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-fw fa-briefcase"></i> Job Title</label><span class="required"> *</span>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></span>
                        </div>
                        <input type="text" name="job_title" value="{{ $user->job_title }}"  class="form-control" placeholder="Job Title" required autocomplete="job_title" autofocus>
                    </div>
                </div>

                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-shield"></i> Status</label><span class="required"> *</span>
                    {{ $user->is_disabled }}
                    <select class="form-control" id="userStatus" required autofocus>
                        <option value="0" @if(!$user->is_disabled) selected @endif>Active</option>
                        <option value="1" @if($user->is_disabled) selected @endif>Deactive</option>
                    </select>
                </div>
                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-shield"></i> Is Verified</label><span class="required"> *</span>
                    <select class="form-control" id="userVerified" name="email_verified_at" required autofocus>
                        <option value="1" @if($user->email_verified_at != null) selected @endif>Verified</option>
                        <option value="0" @if($user->email_verified_at == null) selected @endif>Unverified</option>
                    </select>
                </div>
                <div class="form-group col-sm-12 col-md-3">
                    <label><i class="fas fa-shield"></i> Payment Gateways </label>
                    <input type="checkbox" name="isGatewayOn" {{ strpos($user->stripe_customer_id, 'cus_') === FALSE ? '':'checked' }} {{ strpos($user->stripe_customer_id, 'cus_') === FALSE ? '':'disabled' }} data-toggle="toggle">
                </div>
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-sm btn-success">
                    <span class="fas fa-save"></span> Save
                </button>
            </div>
        </div>
    </div>
</div>
