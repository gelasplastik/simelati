<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $items = Subject::query()->orderBy('name')->paginate(20);

        return view('admin.master.subjects', compact('items'));
    }
}
