<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\DebugService;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    protected DebugService $debugService;

    public function __construct(DebugService $debugService)
    {
        $this->debugService = $debugService;
    }

    /**
     * Show list of all admins with debug status
     */
    public function index()
    {
        $adminBadges = $this->debugService->getAdminBadges();

        return view('superadmin.debug.index', [
            'adminBadges' => $adminBadges
        ]);
    }

    /**
     * Show detailed debug scan for a specific admin
     */
    public function show(Admin $admin)
    {
        $scanResult = $this->debugService->runFullScan($admin);

        return view('superadmin.debug.show', [
            'admin' => $admin,
            'result' => $scanResult
        ]);
    }

    /**
     * AJAX endpoint for real-time scanning
     */
    public function scan(Admin $admin)
    {
        $scanResult = $this->debugService->runFullScan($admin);

        return response()->json($scanResult);
    }
}
