<div class="row">
    <div class="col-lg mb-3">
        <p>{{ $meet->meet_categories }}</p>
        <div>
            <h6 for="" class="alert alert-info" style="cursor:pointer;" onclick="toogle_size_chart()">View Sizing Charts
                <span class="fas fa-caret fa-caret-down "></span>
            </h6>
            <ul id="size_chart_list">
                <li>
                    <a href="{{ asset('storage/files/all_leo.pdf') }}" target="_blank">
                        LEO Size Chart</a>
                </li>
                <!-- <li>
                    <a href="https://gkelite.azureedge.net/images/static/sizecharts/size-charts-inches-womens-leos.pdf" target="_blank">
                    GK in inches</a>
                </li>
                <li>
                    <a href="https://gkelite.azureedge.net/images/static/sizecharts/size-charts-centimeters-womens-leos.pdf" target="_blank">
                    GK in metric</a>
                </li>
                <li>
                    <a href="https://www.snowflakedesigns.com/sizing-information" target="_blank">
                    SnowFlake</a>
                </li>
                <li>
                    <a href="https://destira.com/pages/size-chart" target="_blank">
                    Destira</a>
                </li>
                <li>
                    <a href="https://www.higoapparel.com/sizing-chart" target="_blank">
                    Higo</a>
                </li> -->
            </ul>
        </div>
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


<script>
    function toogle_size_chart() {
        var x = document.getElementById("size_chart_list");
        if (x.style.display === "none") {
            x.style.display = "block";
            x.previousElementSibling.children[0].classList.add('fa-caret-down');
            x.previousElementSibling.children[0].classList.remove('fa-caret-right');
        } else {
            x.style.display = "none";
            x.previousElementSibling.children[0].classList.remove('fa-caret-down');
            x.previousElementSibling.children[0].classList.add('fa-caret-right');
        }
    }
</script>