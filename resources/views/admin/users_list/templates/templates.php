<script id="usersActionTemplate" type="text/x-jsrender">
    <a title="Change Withdrawal Money" href="javascript:void(0)" class="btn mr-2 datatable-withdrawal-money user-withdrawal-money" data-id="{{:id}}">
        <span class="fas fa-hand-holding-usd"></span>
    </a>
    {{if !isDisabled}}
    <a title="Edit" href="{{:url}}" class="btn mr-2 datatable-edit user-edit" data-id="{{:id}}">
        <i class="fas fa-edit"></i>
    </a>
    {{/if}}
</script>


<script id="UserStatusTemplate" type="text/x-jsrender">
 <div class="form-group">
    <div class="custom-control custom-switch pl-2">
        <input type="checkbox" class="custom-control-input status" id="customSwitch-{{:id}}" value="1" data-id="{{:id}}" {{:checked}}>
        <label class="custom-control-label" for="customSwitch-{{:id}}"></label>
    </div>
 </div>

</script>


<script id="UserMailCheckTemplate" type="text/x-jsrender">
    <div class="icheck-success d-inline">
        <input type="checkbox" {{:checked}} id="checkboxSuccess{{:id}}" class="mailCheckCheckBox" data-id="{{:id}}">
        <label for="checkboxSuccess{{:id}}">
        </label>
    </div>
</script>
