<?php namespace Hideyo\Ecommerce\Backend\Controllers;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Auth;
use Notification;

class AuthController extends Controller
{

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    protected $guard = 'hideyobackend';

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
        return view('hideyo_backend::auth.login');
    }
    
    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        Auth::guard('hideyobackend')->logout();

        return redirect()->intended('/hideyo/admin');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email', 'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('hideyobackend')->attempt($credentials)) {
            return redirect()->intended('/hideyo/admin');
        }

        Notification::error('inloggegevens zijn fout');
     
        return redirect('/hideyo/admin/security/login')
                    ->withInput($request->only('email', 'remember'));
    }
}
