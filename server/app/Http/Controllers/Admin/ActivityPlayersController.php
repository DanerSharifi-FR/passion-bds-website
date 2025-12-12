<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;

class ActivityPlayersController extends Controller
{
    public function index(Activity $activity)
    {
        return view('admin.activities.players', compact('activity'));
    }
}
