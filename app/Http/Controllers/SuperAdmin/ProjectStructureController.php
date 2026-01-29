<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;

class ProjectStructureController extends Controller
{
    public function index()
    {
        // List of all sidebar items in superadmin
        $sidebarItems = [
            [
                'name' => 'Dashboard',
                'route' => 'superadmin.dashboard',
                'icon' => 'dashboard',
            ],
            [
                'name' => 'Admins',
                'route' => 'superadmin.admins.index',
                'icon' => 'building',
            ],
            [
                'name' => 'Payments',
                'route' => 'superadmin.payments.index',
                'icon' => 'payment',
            ],
            [
                'name' => 'Credits',
                'route' => 'superadmin.credits.index',
                'icon' => 'credits',
            ],
            [
                'name' => 'AI Config',
                'route' => 'superadmin.ai-config.index',
                'icon' => 'ai',
            ],
            [
                'name' => 'Settings',
                'route' => 'superadmin.settings.index',
                'icon' => 'settings',
            ],
            [
                'name' => 'Connections',
                'route' => 'superadmin.connections.index',
                'icon' => 'connections',
            ],
            [
                'name' => 'Debug',
                'route' => 'superadmin.debug.index',
                'icon' => 'debug',
            ],
        ];

        return view('superadmin.project-structure.index', compact('sidebarItems'));
    }
}
