<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(): View
    {
        return view('scan', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }
}
