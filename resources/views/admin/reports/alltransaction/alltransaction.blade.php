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
    .cancelled{
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
    .unconfirmed{
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
            <h4>All Transaction Report</h4>
        </div>
        <div class="mb-2 pt-2 pb-2">
            <form action="{{ route('admin.alltransaction_report') }}" method="get">
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
                        <th>Payment Date</th>
                        <th>Payment Id</th>
                        <th>From (Gym)</th>
                        <th>To (Meet)</th>
                        <th>Meet Host</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $status = ['','pending', 'completed', 'cancelled', 'failed', 'waitlist pending', 'waitlist confirmed'];
                    $types = ['','Credit Card','Paypal', 'ACH', 'Check', 'AllGym Balance', 'One Time ACH'];
                    if(count($meet_transaction) > 0)
                        foreach ($meet_transaction as $value) {
                            echo '<tr>
                                <td>'.$value['created_at']->format(Helper::AMERICAN_SHORT_DATE).'</td>
                                <td>'.$value['id'].'</td>
                                <td>'.$value['gym'].'</td>
                                <td>'.$value['meet'].'</td>
                                <td>'.$value['host'].'</td>
                                <td>'. number_format($value['total'],2).'</td>
                                <td>'.$types[$value['method']].'</td>
                                <td class="'.$status[$value['status']].'">'.strtoupper($status[$value['status']]).'</td>
                            </tr>';
                        }
                    $status = ['','pending', 'cleared', 'unconfirmed', 'failed'];
                    $types = [
                        2 => "Dwolla Verification",
                        4 => "Registration Payment",
                        99 => "Withdrawal",
                        6 => "Admin Transfer"
                    ];
                    if(count($user_balance_transaction) > 0)
                        foreach ($user_balance_transaction as $value) {
                            echo '<tr>
                                <td>'.$value['created_at']->format(Helper::AMERICAN_SHORT_DATE).'</td>
                                <td>'.$value['id'].'</td>
                                <td>'.$value['first_name'].' '.$value['last_name'].' ('.$value['gym'].')</td>
                                <td>'.$value['meet'].'</td>
                                <td>'.$value['host'].'</td>
                                <td>'. number_format($value['total'],2).'</td>
                                <td>'.$types[$value['type']].'</td>
                                <td class="'.$status[$value['status']].'">'.strtoupper($status[$value['status']]).'</td>
                            </tr>';
                        }

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
            "ordering": true,
            "order": [[ 1, "desc" ]],

            // "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            "pageLength": 100,
        });
    });
</script>
@endsection