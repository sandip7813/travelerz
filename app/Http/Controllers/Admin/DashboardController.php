<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(){
        $this->middleware('auth:web');
    }

    public function index(){
        /* Auth::logout();

        return redirect('login'); */
        return view('admin.dashboard.index');
    }
}
