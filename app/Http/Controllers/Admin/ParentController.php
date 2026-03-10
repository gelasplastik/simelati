<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;

class ParentController extends Controller
{
    public function index()
    {
        $items = ParentModel::query()->with(['user', 'students.class'])->latest()->paginate(20);

        return view('admin.master.parents', compact('items'));
    }
}
