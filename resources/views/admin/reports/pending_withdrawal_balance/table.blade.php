<table class="table table-responsive-sm table-striped table-bordered" id="pendingWithdrawalBalanceTbl">
    <thead class="bg-dark">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Balance</th>
    </tr>
    </thead>
    @foreach($users as $user)
    <tr>
        <td>{{$user->fullName()}}</td>
        <td>{{$user->email}}</td>
        <td>{{$user->office_phone}}</td>
        <td><i class="fas fa-dollar-sign"></i> {{number_format($user->cleared_balance),2}}</td>
    </tr>
    @endforeach
    <tbody>
    </tbody>
</table>
