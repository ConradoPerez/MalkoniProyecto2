<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        return view('supervisor.dashboard');
    }
}