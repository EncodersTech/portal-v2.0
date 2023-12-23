<div class="row admin-dashboard__header-card">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h4><i class="fas fa-dollar-sign"></i> {{number_format($admin_fee, 2)}}</h4>
                <p>Admin Fee Collected</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h4><i class="fas fa-dollar-sign"></i>  {{ number_format($process_fee, 2) }}</h4>
                <p>Credit Card Processing Fee</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <a href="{{route('pending.withdrawal.balance.report')}}" data-toggle="tooltip" title="Pending Withdrawal Balance Report" >
            <div class="small-box bg-gray">
                <div class="inner">
                    <h4><i class="fas fa-dollar-sign"></i> {{ number_format($withdrawal_balance,2) }}</h4>
                    <p>Pending Withdrawal Balance</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h4><i class="fas fa-dollar-sign"></i> {{ number_format(abs($pending_withdrawal_request),2) }}</h4>
                <p>Balance Withdrawal Requested</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-maroon">
            <div class="inner">
                <h4><i class="fas fa-dollar-sign"></i> {{ number_format($pending_withdrawal_cc,2) }}</h4>
                <p>Pending Withdrawal CC Balance</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

</div>
