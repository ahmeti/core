<?php

namespace App\Modules\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        view()->addNamespace('Auth', app_path('Modules/Auth/Views'));
        return view('Auth::Login');
    }

    protected function authenticated(Request $request, $user)
    {
        if( auth()->user() ) {
            session()->put('company_id', auth()->user()->company_id);
            session()->put('user_id', auth()->user()->id);
            session()->save();
        }

        if( Cookie::has('redirect_url') ){
            $redirectUrl = Cookie::get('redirect_url');
            return redirect($redirectUrl)->cookie(cookie()->forget('redirect_url'));
        }

        return redirect('/home')->cookie(cookie()->forget('redirect_url'));
    }
}
