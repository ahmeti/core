<?php

namespace App\Http\Middleware;

use App\Core;
use Closure;

class AuthAjaxRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $firstSegments = ['home'];

        if( auth()->check() && auth()->user()->status != 1 ){
            // If Blocked User
            abort(403, 'Unauthenticated.');
        }

        if ( Core::companyId() < 1 && $request->segment(1) === 'home'){
            return redirect('login');
        }

        if( ! $request->ajax() && $request->input('ajax') !== 'no' &&  ! in_array($request->segment(1), $firstSegments) ){
            return redirect('home')->with('ajax-request', url()->full());
        }

        return $next($request);
    }
}