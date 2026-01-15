<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AlloController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.allos.requests');
    }

    public function requests(): Factory|View
    {
        return view('admin.allos', ['activeView' => 'requests']);
    }

    public function catalog(): Factory|View
    {
        return view('admin.allos', ['activeView' => 'catalog']);
    }
}
