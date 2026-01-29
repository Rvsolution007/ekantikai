<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectStructureController extends Controller
{
    /**
     * Display the project structure documentation page.
     * Shows visual diagrams of how different sections work together.
     */
    public function index()
    {
        return view('superadmin.project-structure.index');
    }
}
