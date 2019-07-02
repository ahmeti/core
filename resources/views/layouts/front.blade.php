<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>
    <meta name="description" content="{{ config('app.name') }}">
    <link rel="shortcut icon" type="image/png" href="{{ url('/images/favicon.png') }}"/>

    <link href="{{ mix('/css/front.css') }}" rel="stylesheet">

    @if( session()->get('platform') === 'electron' )
        <script>/* for electron */ if (typeof module === 'object') {window.module = module; module = undefined;}</script>
    @endif

    <script src="{{  mix('js/front.js') }}"></script>

    @if( session()->get('platform') === 'electron' )
        <script>/* for electron */ if (window.module) module = window.module;</script>

    @elseif(session()->get('platform') === 'cordova')
        <script src="{{ mix('js/cordova.js') }}"></script>

    @endif

</head>
<body style="background-color: #fff;">
    <div id="wrapper">

        <div id="page-wrapper" style="min-height: 340px;margin: 0;">

            <div class="container-fluid" style="padding-bottom: 40px;">

                @if( ! empty($title) )
                    <!-- Page Heading -->
                    <div class="row">
                        <div class="col-sm-12">
                            <h1 class="page-header">{{ $title }}</h1>
                        </div>
                    </div>
                    <!-- /.row -->
                @endif

                @yield('content')

            </div><!-- .container-fluid -->

        </div><!-- #page-wrapper -->

    </div><!-- #wrapper -->
</body>
</html>