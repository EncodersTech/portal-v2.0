<div class="row">
    <div class="col-12 col-xs-12 col-sm-6 col-md-6 col-lg-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-dollar-sign"></i></span>

            <div class="info-box-content">
                <span class="info-box-text text-uppercase">Total Earned</span>
                <span class="info-box-number">&#36; {{ number_format($total_earn,2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-xs-12 col-sm-6 col-md-6 col-lg-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-running"></i></span>

            <div class="info-box-content">
                <span class="info-box-text text-uppercase">Total Athletes</span>
                <span class="info-box-number">{{$total_ath}} Athletes</span>
            </div>
        </div>
    </div>

    <div class="clearfix hidden-md-up"></div>

    <div class="col-12 col-xs-12 col-sm-6 col-md-6 col-lg-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>

            <div class="info-box-content">
                <span class="info-box-text text-uppercase">Total Coaches</span>
                <span class="info-box-number">{{ $total_coa }} Coaches</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-xs-12 col-sm-6 col-md-6 col-lg-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-info-circle"></i></span>

            <div class="info-box-content">
                <span class="info-box-text text-uppercase">Total Gyms</span>
                <span class="info-box-number">{{ $total_gym }} Gyms</span>
            </div>
        </div>
    </div>
</div>
