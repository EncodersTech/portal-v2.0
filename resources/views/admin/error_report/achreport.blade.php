<style>
    .unprocessed{
        color: gray;
    }
    .queued{
        color: blue;
    }
    .pending{
        color: orange;
    }
    .declined{
        color: red;
    }
    .settled{
        color: green;
    }
    .completed{
        color: green;
    }
    .voided{
        color: red;
    }
    .refunded{
        color: red;
    }
    .awaiting{
        color: orange;
    }
    
    /* ['unprocessed', 'queued', 'pending', 'declined', 'settled', 'completed', 'voided', 'refunded', 'awaiting ack']; */
</style>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
</head>
<div class="card">
    <div class="card-body">
        <div class="" id="message_level" style="display:none;">
        </div>
        <div>
            <h4>ACH Report</h4>
        </div>
        <div class="mb-2 pt-2 pb-2">
            <form action="{{ route('admin.onetimeach_post') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-4">
                        <div class="row">
                            <div class="col-3">
                                <label for="">From Date</label>
                            </div>
                            <div class="col-9">
                                <div class="input-group date" data-provide="datepicker" id="fromdate">
                                    <input type="text" class="form-control" name="from_date">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-1"></div>
                    <div class="col-4">
                        <div class="row">
                            <div class="col-3">
                                <label for="">To Date</label>
                            </div>
                            <div class="col-9">
                                <div class="input-group date" data-provide="datepicker" id="todate">
                                    <input type="text" class="form-control" name="to_date">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
                
                
            </form>
        </div>
        <div id="logged_div">
            <table class="table" id="usag_level_table">
                <thead>
                    <tr>
                        <th>Payment Id</th>
                        <th>Payment Date</th>
                        <th>Effective Date</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if($items['status'] == 200)
                        foreach ($items['data'] as $value) {
                            echo '<tr>
                                <td>'.$value['paymentid'].'</td>
                                <td>'.$value['paymentdate'].'</td>
                                <td>'.$value['settlementdate'].'</td>
                                <td>'.$value['name'].'</td>
                                <td>'.$value['amount'].'</td>
                                <td class="'.$value['paymentstatus'].'">'.strtoupper($value['paymentstatus']).'</td>
                            </tr>';
                        }
                    else
                        echo '<tr>
                            <td colspan="6">Found None</td>
                        </tr>';
                    ?>
                </tbody>
            </table>
            
        </div>
    </div>
</div>

<div>
</div>
@section('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!-- import data picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
<script>
    $(document).ready(function(){
        $('#fromdate').datepicker({
            format: 'mm/dd/yyyy',
            autoclose: true

        });
        $('#fromdate').datepicker('setDate', <?php echo isset($from_date) ? "'".$from_date."'" : "new Date()" ?>);
        $('#todate').datepicker({
            format: 'mm/dd/yyyy',
            autoclose: true
        });
        $('#todate').datepicker('setDate', <?php echo isset($to_date) ? "'".$to_date."'" : "new Date()" ?>);

        $('#usag_level_table').DataTable({
            "ordering": false
        });
    });
</script>
@endsection