@extends('admin.layouts.app')

@section('page_css')
    <link href="{{mix('assets/admin/style/dashboard.css')}}" rel="stylesheet" type="text/css"/>
    <style>
        h3{
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex">
                <div class="col-sm-6 d-flex">
                    <h1 class="m-0">Admin Dashboard</h1>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form method="get">
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="start_date">From</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo $start_date ;?>">
                        </div>
                    </div>
                    <div class="col-3">
                        <label for="end_date">To</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo $end_date ;?>">
                    </div>
                    <div class="col-3">
                        <label for="end_date">&nbsp;</label>
                        <input type="submit" value="Submit" class="form-control btn btn-success">
                    </div> 
                </div>
            </form>
            <br>
            <hr>

            <div class="row">
                <div class="col-4">
                    <h3>User Statistics</h3>
                    <canvas id="userStatistics"></canvas>
                </div>
                <div class="col-4">
                    <h3>User Balance Statistics</h3>
                    <canvas id="userBalanceStatistics"></canvas>
                </div>
                <div class="col-4">
                    <h3>Meet Transactions</h3>
                    <canvas id="transactionStatistics"></canvas>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-6">
                    <h3>Meet Registration Statistics</h3>
                    <canvas id="meetRegistrationStatistics"></canvas>
                </div>
                <div class="col-6">
                    <h3>Gym Registration Statistics</h3>
                    <canvas id="gymRegistrationStatistics"></canvas>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-6">
                    <h3>Athlete Statistics</h3>
                    <canvas id="athleteStatistics"></canvas>
                </div>
                <div class="col-6">
                    <h3>Coach Statistics</h3>
                    <canvas id="coachStatistics"></canvas>
                </div>
            </div>
            <hr>
        </div>
    </section>
@endsection

@section('page_js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('scripts')
<script>
  const userStatistics = document.getElementById('userStatistics');
  const userBalanceStatistics = document.getElementById('userBalanceStatistics');
  const transactionStatistics = document.getElementById('transactionStatistics');
  const athleteStatistics = document.getElementById('athleteStatistics');
  const meetRegistrationStatistics = document.getElementById('meetRegistrationStatistics');
  const gymRegistrationStatistics = document.getElementById('gymRegistrationStatistics');

  new Chart(userStatistics, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($user_statistics)); ?>,
        datasets: [{
            label: '# of users',
            data: <?php echo json_encode(array_values($user_statistics)); ?>,
            borderWidth: 1
        }]
    }
  });
  new Chart(transactionStatistics, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($transaction_statistics)); ?>,
        datasets: [{
            label: '# transaction amount $',
            data: <?php echo json_encode(array_values($transaction_statistics)); ?>,
            borderWidth: 1
        }]
    }
  });
  new Chart(userBalanceStatistics, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($user_balance_statistics)); ?>,
        datasets: [{
            label: '# transaction amount $',
            data: <?php echo json_encode(array_values($user_balance_statistics)); ?>,
            borderWidth: 1
        }]
    }
  });
  new Chart(athleteStatistics, {
    type: 'radar',
    data: {
        labels: ["USAG","USAIGC","NGA","AAU"],
        datasets: <?php echo json_encode($athlete_statistics); ?>
    },
    options: {
        plugins: {
            legend: {
                display: false
            }
        }
    }
  });
  new Chart(coachStatistics, {
    type: 'radar',
    data: {
        labels: ["USAG","USAIGC","NGA","AAU"],
        datasets: <?php echo json_encode($coach_statistics); ?>
    },
    options: {
        plugins: {
            legend: {
                display: false
            }
        }
    }
  });
  new Chart(meetRegistrationStatistics, {
    type: 'polarArea',
    data: <?php echo json_encode($meet_registration_statistics); ?>,
    options: {
        plugins: {
            legend: {
                display: true,
                labels: {
                    generateLabels: (chart) => {
                        const datasets = chart.data.datasets;
                        return datasets[0].data.map((data, i) => ({
                        text: `${chart.data.labels[i]} (${data})`,
                        fillStyle: datasets[0].backgroundColor[i],
                        index: i
                        }))
                    }
                }
            }
        }
    }
  });
  new Chart(gymRegistrationStatistics, {
    type: 'polarArea',
    data: <?php echo json_encode($gym_registration_statistics); ?>,
    options: {
        plugins: {
            legend: {
                display: true,
                labels: {
                    generateLabels: (chart) => {
                        const datasets = chart.data.datasets;
                        return datasets[0].data.map((data, i) => ({
                        text: `${chart.data.labels[i]} (${data})`,
                        fillStyle: datasets[0].backgroundColor[i],
                        index: i
                        }))
                    }
                }
            }
        }
    }
  });
</script>
@endsection
