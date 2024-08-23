<div class="d-flex flex-row flex-no-wrap mb-2">
    <div class="flex-grow-1">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control search-field" placeholder="Gym name ..." id="gym_name" onkeyup="search_gym()">
        </div>
    </div>
</div>


@foreach($gymSummary as $gym)
<div class="btn btn-primary btn-block text-left left-btn mb-2">
    <div class="d-flex flex-wrap flex-row">
        <div class="pr-2">
            <img class="gym-picture rounded-circle" alt="Gym Picture"
                    src="{{ $gym['profile_image'] }}" title="Gym Picture">
        </div>
        <div class="flex-grow-1 d-flex align-items-center" onclick="expended({{$gym['id']}})">
            <strong id="gym_names_{{$gym['id']}}">
                {{ $gym['name'] }}
            </strong>
            <span class="text-secondary ml-1">
                | <span>
                    {{ $gym['athlete'] }} Athletes
                </span>
                | <span>
                    {{ $gym['coach'] }} Coaches
                </span>
            </span>
            <span id="caret_{{$gym['id']}}" class="fas fa-fw fa-caret-right"></span>
        </div>
    </div>
</div>
<div id="expended_{{$gym['id']}}" class="p-1 pl-3 custom-panel-body mb-1" style="display:none;">
    <div class="mb-1">
        <div>
            <div class="d-flex flex-no-wrap flex-row">
                <div class="flex-grow-1">
                    <h4><b>Entered Meet: </b></h4>
                    <div class="table-responsive-lg">
                        <table class="table table-sm table-hover">
                            <tbody >
                                @foreach($gym['meet'] as $meet)
                                <tr>
                                    <td><span class="fas fa-fw fa-receipt"></span>{{ $meet }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforEach

<script>
    function expended(id) {
        var expended = document.getElementById('expended_'+id);
        var caret_ = document.getElementById('caret_'+id);
        if (expended.style.display === 'none') {
            expended.style.display = 'block';
            caret_.classList.add('fa-caret-down');
            caret_.classList.remove('fa-caret-right');

        } else {
            expended.style.display = 'none';
            caret_.classList.add('fa-caret-right');
            caret_.classList.remove('fa-caret-down');
        }
    }
    function search_gym()
    {
        var gym_name = document.getElementById('gym_name').value.toLowerCase();
        var gym_names = document.querySelectorAll('[id^="gym_names_"]');
        for (var i = 0; i < gym_names.length; i++) {
            var gym = gym_names[i].innerText.toLowerCase();
            if (gym.includes(gym_name)) {
                gym_names[i].parentElement.parentElement.parentElement.style.display = 'block';
            } else {
                gym_names[i].parentElement.parentElement.parentElement.style.display = 'none';
            }
        } 
    }
</script>