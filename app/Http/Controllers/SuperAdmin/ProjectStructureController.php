<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;

class ProjectStructureController extends Controller
{
    // SuperAdmin sidebar items
    protected $superadminItems = [
        [
            'name' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'dashboard',
        ],
        [
            'name' => 'Admins',
            'slug' => 'admins',
            'icon' => 'building',
        ],
        [
            'name' => 'Payments',
            'slug' => 'payments',
            'icon' => 'payment',
        ],
        [
            'name' => 'Credits',
            'slug' => 'credits',
            'icon' => 'credits',
        ],
        [
            'name' => 'AI Config',
            'slug' => 'ai-config',
            'icon' => 'ai',
        ],
        [
            'name' => 'Settings',
            'slug' => 'settings',
            'icon' => 'settings',
        ],
        [
            'name' => 'Connections',
            'slug' => 'connections',
            'icon' => 'connections',
        ],
        [
            'name' => 'Debug',
            'slug' => 'debug',
            'icon' => 'debug',
        ],
    ];

    // Admin panel sidebar items (what shows when admin logs in)
    protected $adminItems = [
        [
            'name' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'dashboard',
        ],
        [
            'name' => 'Leads',
            'slug' => 'leads',
            'icon' => 'leads',
        ],
        [
            'name' => 'Lead Statuses',
            'slug' => 'lead-status',
            'icon' => 'lead-status',
            'parent' => 'leads',
        ],
        [
            'name' => 'Clients',
            'slug' => 'clients',
            'icon' => 'clients',
        ],
        [
            'name' => 'Users',
            'slug' => 'users',
            'icon' => 'users',
        ],
        [
            'name' => 'Chats',
            'slug' => 'chats',
            'icon' => 'chats',
        ],
        [
            'name' => 'Catalogue',
            'slug' => 'catalogue',
            'icon' => 'catalogue',
        ],
        [
            'name' => 'Followups',
            'slug' => 'followups',
            'icon' => 'followups',
        ],
        [
            'name' => 'Templates',
            'slug' => 'followup-templates',
            'icon' => 'templates',
            'parent' => 'followups',
        ],
        [
            'name' => 'Credits',
            'slug' => 'credits',
            'icon' => 'credits',
        ],
        [
            'name' => 'Workflow',
            'slug' => 'workflow',
            'icon' => 'workflow',
        ],
        [
            'name' => 'Settings',
            'slug' => 'settings',
            'icon' => 'settings',
        ],
    ];

    public function index()
    {
        return view('superadmin.project-structure.index', [
            'sidebarItems' => $this->superadminItems
        ]);
    }

    public function show($module)
    {
        // Find the module
        $currentModule = collect($this->superadminItems)->firstWhere('slug', $module);

        if (!$currentModule) {
            abort(404);
        }

        // If it's "admins", show admin sidebar items
        $subItems = [];
        if ($module === 'admins') {
            $subItems = $this->adminItems;
        }

        return view('superadmin.project-structure.show', [
            'currentModule' => $currentModule,
            'subItems' => $subItems,
        ]);
    }

    public function showSub($module, $submodule)
    {
        // Find parent module
        $currentModule = collect($this->superadminItems)->firstWhere('slug', $module);

        if (!$currentModule) {
            abort(404);
        }

        // Find sub module
        $subItem = null;
        if ($module === 'admins') {
            $subItem = collect($this->adminItems)->firstWhere('slug', $submodule);
        }

        if (!$subItem) {
            abort(404);
        }

        // For now, just show empty page as user requested
        return view('superadmin.project-structure.sub', [
            'currentModule' => $currentModule,
            'subItem' => $subItem,
        ]);
    }
}
