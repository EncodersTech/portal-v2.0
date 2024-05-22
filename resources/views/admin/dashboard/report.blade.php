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
                <div class="col-12">
                    <h3>Athlete Statistics</h3>
                    <canvas id="athleteStatistics"></canvas>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h3>Coach Statistics</h3>
                    <canvas id="coachStatistics"></canvas>
                </div>
            </div>
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
                display: true,
                position: 'left'
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
                display: true,
                position: 'left'
            }
        }
    }
  });
</script>
@endsection
