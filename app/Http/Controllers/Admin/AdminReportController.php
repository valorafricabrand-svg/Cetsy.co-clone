<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminReportController extends Controller
{
    public function index()
    {
        // your reporting logic here…
        return view('admin.reports.index');
    }
}
