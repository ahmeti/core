<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">


    <title>{{ config('app.name') }}</title>
    <meta name="description" content="{{ config('app.name') }}">
    <link rel="shortcut icon" type="image/png" href="{{ url('/images/favicon.png') }}"/>


    <link href="{{ mix('css/auth.css') }}" rel="stylesheet">
    @php //<link href="{{ mix('css/auth-print.css') }}" rel="stylesheet"> @endphp

    @if( session()->has('ajax-request') )
        <meta name="noAjaxUrl" content="{{ session('ajax-request') }}" />
    @endif

    @if( session()->get('platform') === 'electron' )
        <script>/* for electron */ if (typeof module === 'object') {window.module = module; module = undefined;}</script>
    @endif

    <script type="text/javascript">
        var baseApp = {
            company_uuid: null,
            url: '{{ url('/') }}',
            userid: '{{ Core::userId() }}',
            isMobile: {!! Core::isMobile() ? 'true' : 'false' !!},
            appEnv: '{{ env('APP_ENV') }}',
            platform: '{{ session()->has('platform') ? session()->get('platform') : 'web' }}',
        };
    </script>

    <script src="{{ mix('js/auth.js') }}"></script>

    @if( session()->get('platform') === 'electron' )
        <script>/* for electron */ if (window.module) module = window.module;</script>

    @elseif(session()->get('platform') === 'cordova')
        <script src="{{ mix('js/cordova.js') }}"></script>

    @endif

</head>
<body>
    <div id="wrapper">
        <!-- Navigation -->

        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0;{{ config('app.env') !== 'production' ? 'background-color: lightskyblue;' : '' }}">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <a style="padding:5px 15px" class="navbar-brand{{ Core::userId() > 0 ? ' ajaxPage' : '' }}" href="{{ Core::userId() > 0 ? route('home') : url('/') }}">
                    <img style="width: 140px;max-height: 48px" src="{{ url('/images/logo.png') }}"/>
                </a>
            </div>

            <!-- /.navbar-header -->

            <?php if ( Core::userId() > 0 ){ ?>

            <ul class="nav navbar-top-links navbar-right">

                <li id="advanced-search">
                    <select app-template="Advanced" app-class="advanced-search" set_select2ajax="1" ajaxurl="{{ url('/') }}/ara/advanced/"
                            id="app-advanced-search" class="form-control" placeholder="{{ __('Firma, Kontak, Sayfa Ara...') }}" tabindex="-1" aria-hidden="true"></select>
                </li>

                {{--
                @php ( $countTask = \App\Models\Task::where('kullanici_id', Core::userId())->whereIn('durum', ['wait', 'work'])->count() )

                <li class="app-navbar-top-task" title="Bekleyen görevlerinizi gösterir.">
                    <a class="ajaxPage btn btn-link" href="{{ url('/tasks?kullanici_id='.Core::userId().'&durum[]=wait&durum[]=work') }}">
                        <i class="fa fa-tasks fa-fw"></i>
                        <span class="app-user-task-count badge" style="background-color: #d9534f">{{ $countTask }}</span>
                    </a>
                </li>
                --}}


                <!-- /.dropdown -->
                <li class="app-navbar-top-options" class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>
                        {{ session()->get('platform') === 'electron' ? Core::userFullName() : '' }}
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#"><i class="fa fa-user fa-fw"></i> {{ Core::userFullName() }}</a>
                        </li>
                        <!--<li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>-->
                        <li class="divider"></li>
                        <li>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out fa-fw"></i> {{ __('Çıkış') }}
                            </a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>

            <div class="navbar-default sidebar" role="navigation" style="{{ Core::isMobile() ? '' : 'margin-bottom:120px;margin-top:50px'  }}">
                <div class="sidebar-nav navbar-collapse {{ Core::isMobile() ? 'collapse' : ''  }}" style="{{ Core::isMobile() ? 'height:1px' : ''  }}">
                    <ul class="nav" id="side-menu">


                        @php ( $menu = Core::getUserMenu() )

                        @foreach ($menu as $m)

                            @if ($m['parent_id']==0 && $m['url'] !="")
                                <li>
                                    <a class="ajaxPage {{ $m['class'] }}" href="{{ url('/'.$m['url']) }}"><i class="{{ $m['icon'] }} fa-fw"></i> {{ $m['name'] }}</a>
                                </li>
                            @elseif( $m['parent_id']==0 && $m['url']=="" && $m['id']!=0 && !empty($menu[$m['id']]['sub']) )
                                <li>
                                    <a href="javascript:void(0)" class="{{ $m['class'] }}">
                                        <i class="{{ $m['icon'] }} fa-fw"></i> {{ $m['name'] }}<span class="fa arrow"></span>
                                    </a>
                                    <ul class="nav nav-second-level collapse" aria-expanded="false" style="height: 0">
                                        @php( $countSub = count((array)$menu[$m['id']]['sub']) )
                                        @php( $subI = 1 )
                                        @foreach ((array)$menu[$m['id']]['sub'] as $sub)
                                            <li>
                                                <a class="ajaxPage {{ $sub['class'] }}" href="{{ url('/'.$sub['url']) }}">
                                                    <i class="{{ $sub['icon'] }} fa-fw"></i> {{ $sub['name'] }}
                                                </a>
                                            </li>
                                            @if( str_is('*divider*', $sub['class']) && $countSub > $subI )
                                                <li class="divider" style="margin: 5px ">
                                                    <span style="display:block; border-top: 1px solid #ddd;margin-left: 15px;margin-right: 10px"></span>
                                                </li>
                                            @endif
                                            @php( $subI++ )
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach

                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
            <?php } ?>
        </nav>
        <div id="page-wrapper">
            <div class="container-fluid app-content" id="ajaxPageContainer">
                {!! Core::getBreadcrumb() !!}
                @yield('content')
            </div><!-- /.container-fluid -->
        </div><!-- /#page-wrapper -->
    </div><!-- /#wrapper -->
</body>
</html>