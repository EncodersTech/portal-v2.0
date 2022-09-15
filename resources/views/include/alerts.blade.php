<div id="alerts">
    @foreach ([
        'error' => ['icon' => 'times-circle', 'class' => 'danger'],
        'warning' => ['icon' => 'exclamation-triangle', 'class' => 'warning'],
        'success' => ['icon' => 'check-circle', 'class' => 'success'],
        'info' => ['icon' => 'info-circle', 'class' => 'info']
    ] as $msg => $style)
        @if(Session::has($msg))
            <div class="alert alert-{{ $style['class'] }}">
                <span class="fas fa-{{ $style['icon'] }}"></span> {{ Session::get($msg) }} 
            </div>
        @endif
    @endforeach
</div>