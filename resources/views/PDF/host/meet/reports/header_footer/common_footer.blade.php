<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
</head>
<body>
<div class="footer" style="display: flex">
    <h5 class="mb-0 text-left">
        {{ now()->format(Helper::AMERICAN_FULL_DATE_TIME) }}
    </h5>
    <h5 class="mb-0 text-right">
        AllGymnastics.com
    </h5>
</div>
</body>
</html>

