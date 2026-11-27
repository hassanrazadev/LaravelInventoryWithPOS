<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'IMS-POS') }} | @yield('title')</title>
    <!-- FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- GLOBAL MAINLY STYLES-->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('assets/css/themify-icons.css') }}" type="text/css" rel="stylesheet" />
    <!-- THEME STYLES-->
    <link href="{{ asset('assets/css/main.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/modern-theme.css') }}" rel="stylesheet" />

    <!-- PAGE LEVEL STYLES-->
    @yield('css')
</head>

<body class="ims-auth-body">
<div class="ims-auth-wrapper">
    <div class="ims-auth-brand">
        <span class="ims-brand-badge"><i class="fa fa-cubes"></i></span>
        <span class="ims-auth-brand-text">{{ config('app.name', 'IMS-POS') }}</span>
    </div>
    <div class="ims-auth-card">
        @yield('content')
    </div>
    <div class="ims-auth-footer">
        {{ now()->year }} © {{ config('app.name', 'IMS-POS') }} — Inventory &amp; Point of Sale
    </div>
</div>

<!-- BEGIN PAGA BACKDROPS-->
<div class="preloader-backdrop">
    <div class="page-preloader">Loading</div>
</div>
<!-- END PAGA BACKDROPS-->
<!-- CORE PLUGINS-->
<script src="{{ asset('assets/js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/metisMenu.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}" type="text/javascript"></script>

<!-- CORE SCRIPTS-->
<script src="{{ asset('assets/js/app.min.js') }}" type="text/javascript"></script>

@yield('js')
</body>

</html>
