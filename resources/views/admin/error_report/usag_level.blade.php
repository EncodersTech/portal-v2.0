<div class="card">
    <div class="card-body">
        <div class="" id="message_level" style="display:none;">
        </div>
        <div class="row mb-2">
            <div class="col-6" onclick="changeCaret('usag_level','usag_level_div');">
                <h4>USAG Levels
                    <i class="fas fa-caret-down" id="usag_level"></i>
                </h4>
            </div>
            <div class="col-6">
                <button onclick="insert_usag_level(this)" class="btn btn-success float-right" data-toggle="modal" data-target="#modal-usag-level-update">Add New Level</button>
            </div>
        </div>
        <div id="usag_level_div">
            <table class="table mt-2" id="usag_level_table">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>level category</th>
                        <th>code</th>
                        <th>name</th>
                        <th>abbrebiation</th>
                        <th>status</th>
                        <th>action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usag_levels as $level)
                        <tr>
                            <td>{{ $level->id }}</td>
                            <td>{{ $level->level_category->name }}</td>
                            <td>{{ $level->code }}</td>
                            <td>{{ $level->name }}</td>
                            <td>{{ $level->abbreviation }}</td>
                            <td>{{ $level->is_disabled ? "Disabled" : "Active" }}</td>
                            <td>
                                <button onclick="update_usag_level(this)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-usag-level-update" data-id="{{ $level->id }}">Update</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>