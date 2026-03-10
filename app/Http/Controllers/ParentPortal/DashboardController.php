<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $parent = auth()->user()->parentProfile;
        $children = $parent->students()->with('class')->get();

        return view('parent.dashboard', compact('children'));
    }
}
