<?php

namespace App\Modules\Page;

use App\Core;
use App\Form;
use App\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {


        dd(Form::test());
        dd(Core::userId());
        dd(Response::status());
        dd('Test');

        return view('home');
    }
}
