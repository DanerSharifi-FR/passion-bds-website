<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function dashboard(): Factory|View
    {
        return view('admin.dashboard');
    }

    public function logs(): Factory|View
    {
        return view('admin.logs');
    }
}
