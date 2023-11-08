<div class="modal fade" id="modal-usag-level-update" tabindex="-1" role="dialog"
        aria-labelledby="modal-usag-level-update" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fas fa-file-import"></span> Update USAG Level
                </h5>
                <button type="button" class="close"  id="modal-usag-level-update-close" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            
            <div class="modal-body">
                <form action="" method="post" class="form" id="usag_level_update_form">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="code">Code</label>
                                <input type="text" name="code" id="code" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label for="abbrebiation">Abbrebiation</label>
                                <input type="text" name="abbrebiation" id="abbrebiation" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label for="is_disabled">Status</label>
                                <select name="is_disabled" id="is_disabled"  class="form-control">
                                    <option value="0">Active</option>
                                    <option value="1">Disabled</option>
                                </select>
                            </div>
                            <div class="form-group mb-2" style="display:none;" id="label_category_div">
                                <label for="label_category">Label Category</label>
                                <select name="label_category" id="label_category" class="form-control">
                                    @foreach($label_categories as $label_category)
                                        <option value="{{ $label_category->id }}">{{ $label_category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button id="btn-usag" type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>