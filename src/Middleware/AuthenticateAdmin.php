<?php

namespace Hideyo\Ecommerce\Backend\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use View;
use Hideyo\Ecommerce\Backend\Models\Shop as Shop;

class AuthenticateAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard('hideyobackend')->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('/hideyo/admin/security/login');
            }
        }

        if (Auth::guard('hideyobackend')->check()) {
            View::share('this_user', Auth::guard('hideyobackend')->user());
            
            View::share('available_shops', Shop::all());
        }

        return $next($request);
    }
}
