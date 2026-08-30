<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    /**
     * 🚀 VINICORE USERNAME-GATEWAY
     * Überschreibt den Standard-Login-Pfad von E-Mail auf Benutzernamen! [source: 1.3.2]
     */
    public function username()
    {
        return 'username'; // 👈 Sagt Laravel, dass die Spalte 'username' der Login-Anker ist! [source: 1.3.2, 1.3.3]
    }

}
