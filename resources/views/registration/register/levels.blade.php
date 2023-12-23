<div class="row">
    <div class="col-lg mb-3">
        <p>{{ $meet->meet_categories }}</p>
        <label for="gym">
            <span class="fas fa-fw fa-dumbbell"></span> Registering Gym <span class="text-danger">*</span>
        </label>

        <select id="gym" class="form-control form-control-sm @error('gym') is-invalid @enderror" name="gym"
            v-model="gymId" required>
            <option value="">(Choose below ...)</option>
            @foreach ($_managed->gyms as $gym)
            @if (!in_array($gym->id, $registeredGyms))
            <option value="{{ $gym->id }}" {{ old('gym') == $gym->id ? 'selected' : '' }}>
                {{ $gym->name }}
            </option>
            @endif
            @endforeach
        </select>

        @error('gym')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col">
        <ag-registration-details singular="athlete" plural="athletes" :late="{{ $meet->isLate() ? 'true' : 'false' }}"
            :gym-id="gymId" :meet-id="{{$meet->id}}" :available_bodies="{{ json_encode($bodies) }}"
            :managed="{{ $_managed->id }}" :initial="[]" @process-data="firstStep"
            :requires_sanction="{{ $required_sanctions }}">
        </ag-registration-details>
    </div>
</div>