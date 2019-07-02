<?php

namespace App\Http\Middleware;

use App\Facades\CoreService;
use Closure;

class CheckAjaxRequest
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

        if( auth()->check() && auth()->user()->status != 'on' ){
            // If Blocked User
            abort(403, 'Unauthenticated.');
        }

        if ( CoreService::companyId(false) < 1 && $request->segment(1) === 'home'){
            return redirect('login');
        }

        if( ! $request->ajax() && $request->input('ajax') !== 'no' &&  ! in_array($request->segment(1), $firstSegments) ){
            return redirect('home')->with('ajax-request', url()->full());
        }

        return $next($request);
    }
}
