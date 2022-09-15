<div class="row">
    <div class="col">
        <h5 class="border-bottom"><span class="fas fa-unlock-alt"></span> Change Password</h5>
    </div>
</div>
<div class="row">    
    <div class="col">
        <form method="POST" action="{{ route('account.password.reset') }}">
            @csrf
            @method('PATCH')

            <div class="row">
                <div class="col-lg mb-3">
                    <label for="old_password" class="mb-1">
                        <span class="fas fa-fw fa-unlock"></span> Old Password <span class="text-danger">*</span>
                    </label>
                    <input id="old_password" type="password" class="form-control @error('old_password') is-invalid @enderror" 
                            name="old_password" placeholder="Old password" required autofocus>
                    @error('old_password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-lg mb-3">
                    <label for="password" class="mb-1">
                        <span class="fas fa-fw fa-unlock-alt"></span> New Password <span class="text-danger">*</span>
                    </label>

                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                            name="password" placeholder="New password" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-lg mb-3">
                    <label for="password_confirmation" class="mb-1">
                        <span class="fas fa-fw fa-unlock-alt"></span> Confirm New Password <span class="text-danger">*</span>
                    </label>
                    <input id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                            name="password_confirmation" placeholder="Confirm New password" required>
                    @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col mb-3 text-right">
                    <button type="submit" class="btn btn-sm btn-success">
                        <span class="fas fa-save"></span> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div> 