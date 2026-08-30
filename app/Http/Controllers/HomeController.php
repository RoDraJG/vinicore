<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Zeigt das Zentral-Betriebs-Cockpit von vinicore an.
     */
    public function index()
    {
        return view('home');
    }
}
