<div class="modal fade" id="withdrawalMoneyModal" tabindex="-1" role="dialog" aria-labelledby="withdrawal-money-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawal-money-modal-title">Withdrawal Money</h5>
            </div>
            {{ Form::open(['id'=>'changeWithdrawalMoneyForm', 'class' => "horizontal-scroll"]) }}
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="validationErrorsBox"></div>
                {{ Form::hidden('user_id',null,['id'=>'userId']) }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group align-items-center">
                            {{ Form::label('total', 'Amount to withdraw', ['class' => 'font-weight-bold']) }}
                            <span class="text-danger">*</span>
                            {{ Form::number('total', null, ['class' => 'form-control', 'required', 'id' => 'total', 'autocomplete' => 'off', 'min' => 1]) }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group align-items-center">
                            {{ Form::label('clears_on', 'Clears On', ['class' => 'font-weight-bold']) }}
                            {{ Form::text('clears_on', null, ['class' => 'form-control', 'id' => 'clearsOn', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group align-items-center">
                            {{ Form::label('description', 'Description', ['class' => 'font-weight-bold']) }}
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'id' => 'description', 'autocomplete' => 'off', 'rows' => 2]) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer mt-3 d-flex flex-row">
                <div class="flex-grow-1">
                    <button data-dismiss="modal" class="btn btn-secondary">
                        <span class="far fa-times-circle"></span> Close
                    </button>
                </div>
                <div>
                    {!! Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-info', 'id' => 'btnWithdrawMoneySave', 'data-loading-text' => "<span class='spinner-border spinner-border-sm'></span> Processing..."]) !!}
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
