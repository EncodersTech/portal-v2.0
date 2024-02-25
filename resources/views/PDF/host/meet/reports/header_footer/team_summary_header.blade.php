<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
</head>
<body>
<div class="header summary-header">
    <div class="header-text">
        <h1 class="mb-0">
            Team Report
        </h1>
        <h2 class="mb-0">
            Meet: {{ $meet->name }}
        </h2>
        <h4 class="mb-0">
            Date: {{ now()->format(\App\Helper::AMERICAN_FULL_DATE) }}
        </h4>
        <h4 class="mb-0">
            Counts do not include scratches
        </h4>
    </div>
    <div class="logo-container">
        @include('PDF.host.meet.reports.common_logo_image')
    </div>
</div>
</body>
</html>

