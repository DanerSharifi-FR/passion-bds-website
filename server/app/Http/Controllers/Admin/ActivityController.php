<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ActivityController extends Controller
{
    public function index()
    {
        return view('admin.activities.index');
    }
}
