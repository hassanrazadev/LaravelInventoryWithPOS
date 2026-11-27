<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- csrf token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IMS-POS') }} | @yield('title')</title>
    <!-- FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- GLOBAL MAINLY STYLES-->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('assets/css/themify-icons.css') }}" type="text/css" rel="stylesheet" />
    <!-- PLUGINS STYLES-->
    <link href="{{ asset('assets/css/jquery.toast.min.css') }}" rel="stylesheet" />

    <!-- PAGE LEVEL STYLES-->
    @yield('css')
    <!-- THEME STYLES-->
    <link href="{{ asset('assets/css/main.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/modern-theme.css') }}" rel="stylesheet" />
</head>

<body class="fixed-navbar @yield('body-classes')">
<div class="page-wrapper">
    <!-- START HEADER-->
    <x-header/>
    <!-- END HEADER-->
    <!-- START SIDEBAR-->
    <x-sidebar/>
    <!-- END SIDEBAR-->
    <div class="content-wrapper">
        <!-- START PAGE CONTENT-->
        <div class="page-content fade-in-up">
           @yield('content')
        </div>
        <!-- END PAGE CONTENT-->
        <!-- START FOOTER -->
        <x-footer/>
        <!-- END FOOTER -->
    </div>
</div>
<!-- BEGIN PAGA BACKDROPS-->
<div class="sidenav-backdrop backdrop"></div>
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
<script src="{{ asset('assets/js/jquery.toast.min.js') }}" type="text/javascript"></script>
<!-- CORE SCRIPTS-->
<script src="{{ asset('assets/js/app.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@yield('js')
<!-- PAGE LEVEL SCRIPTS-->
<script>
    $(document).on('keyup change', '.form-control', function () {
        $(this).siblings('.invalid-feedback').remove();
        $(this).removeClass('is-invalid');
        $(this).parents('.form-group').removeClass('has-error');
    });

    function showToast(heading, text, icon) {
        $.toast({
            heading: heading,
            text: text,
            position: 'bottom-right',
            icon: icon,
            hideAfter: 3000
        });
    }

    @if(session('message'))
        showToast(
            @json(session('status') === false ? 'Error' : 'Success'),
            @json(session('message')),
            @json(session('status') === false ? 'error' : 'success')
        );
    @elseif(session('success'))
        showToast('Success', @json(session('success')), 'success');
    @elseif(session('error'))
        showToast('Error', @json(session('error')), 'error');
    @endif
</script>
</body>

</html>
