<div class="col-12">
    <div class="card p-3">
        <div class="card-body p-0">
            <div class="row settings">
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <h6>Source User</h6>
                    <div class="form-group col-sm-12 col-md-12 col-lg-12 pl-0">
                        {{ Form::select('source_user',$users, null, ['id'=>'sourceUser','class'=>'form-control', 'placeholder' => 'Select Source User' ]) }}
                    </div>
                    <div class="source-user-bank">

                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-12">
                        {{ Form::label('amount', 'Amount:') }}
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-fw fa-dollar-sign"></i></span>
                            </div>
                            {{ Form::number('amount', null, ['class' => 'form-control','required','id'=>'amount','autocomplete'=>'off', 'min' => '1', 'step' => '.01']) }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-6">
                    <h6>Destination User</h6>
                    <div class="form-group col-sm-12 col-md-12 col-lg-12 pl-0">
                        {{ Form::select('destination_user',$users, null, ['id'=>'destinationUser','class'=>'form-control', 'placeholder' => 'Select Destination User' ]) }}
                    </div>
                    <div class="destination-user-bank">

                    </div>
                </div>
            </div>
            <div class="text-left">
                <button type="submit" class="btn btn-sm btn-success" id="btnTransfer" data-loading-text="<span class='spinner-border spinner-border-sm'></span> Processing...">
                    <span class="fas fa-save"></span> Transfer
                </button>
            </div>
        </div>
    </div>
</div>
