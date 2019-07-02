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
            $includeIndex = false;
            return view('Page::HomeFirst', compact('includeIndex'));

        }elseif ( $isAjax === false ) {
            $includeIndex = true;
            return view('Page::HomeFirst', compact('includeIndex'));
        }

        $body = view('Page::HomeFirst')
            ->render();

        return Response::title('Anasayfa')
            ->body($body)
            ->get();
    }
}
