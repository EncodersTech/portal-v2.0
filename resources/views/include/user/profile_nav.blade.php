<ul class="nav nav-tabs" role="tablist" id="profile-tabs">
    <li class="nav-item">
        <a class="nav-link{{ $active_tab == 'profile' ? ' active' : ''}}"
            href="{{ route('account.profile') }}" role="tab">
            My Profile
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ $active_tab == 'payment_options' ? ' active' : ''}}"
            href="{{ route('account.payment.options') }}" role="tab">
            Payment Options
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ $active_tab == 'balance_transactions' ? ' active' : ''}}"
            href="{{ route('account.balance.transactions') }}"  role="tab">
            Transaction Balances
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ $active_tab == 'access_management' ? ' active' : ''}}"
            href="{{ route('account.access.management') }}"  role="tab">
            Access Management
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ $active_tab == 'schedule_withdraw' ? ' active' : ''}}"
            href="{{ route('account.balance.schedule_withdraw') }}"  role="tab">
            Schedule Withdraw
        </a>
    </li>
</ul>
