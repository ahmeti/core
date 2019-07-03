<?php

namespace App\Modules\Page;

use App\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        view()->addNamespace('Page', app_path('Modules/Page/Views'));
    }

    public function index(Request $req)
    {
        $isRedirect = $req->session()->has('ajax-request');
        $isAjax = $req->ajax();

        if ( $isRedirect === true) {
            return view('Page::HomeRedirect');

        }elseif ( $isAjax === false ) {
            return view('Page::Home');
        }

        $body = view('Page::HomeAjax')
            ->render();

        return Response::title('Homepage')
            ->body($body)
            ->get();
    }
}