<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\SuperAdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\WhatsappUserController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CatalogueController;
use App\Http\Controllers\Admin\CatalogueFieldController;
use App\Http\Controllers\Admin\CatalogueImportController;
use App\Http\Controllers\Admin\FollowupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CreditController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\TenantController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home redirect to admin
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Super Admin Authentication Routes (separate login)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    // Guest routes for super admin login
    Route::middleware('guest:superadmin')->group(function () {
        Route::get('/login', [SuperAdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SuperAdminLoginController::class, 'login'])->name('login.submit');
    });

    // Logout route
    Route::post('/logout', [SuperAdminLoginController::class, 'logout'])->name('logout')->middleware('superadmin.auth');
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    });

    // Protected routes
    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });

        // Leads Management
        Route::resource('leads', LeadController::class);
        Route::post('/leads/{lead}/update-stage', [LeadController::class, 'updateStage'])->name('leads.update-stage');
        Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::get('/leads/export/csv', [LeadController::class, 'export'])->name('leads.export');

        // WhatsApp Users
        Route::resource('users', WhatsappUserController::class);
        Route::post('/users/{user}/toggle-bot', [WhatsappUserController::class, 'toggleBot'])->name('users.toggle-bot');

        // Chat Management
        Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
        Route::get('/chats/{identifier}', [ChatController::class, 'show'])->name('chats.show');
        Route::post('/chats/send', [ChatController::class, 'sendMessage'])->name('chats.send');
        Route::post('/chats/send-media', [ChatController::class, 'sendMedia'])->name('chats.send-media');

        // Catalogue Import/Export (must be before resource route)
        Route::get('/catalogue/import/sample', [CatalogueImportController::class, 'downloadSample'])->name('catalogue.import.sample');
        Route::post('/catalogue/import', [CatalogueImportController::class, 'import'])->name('catalogue.import');

        // Catalogue Management
        Route::resource('catalogue', CatalogueController::class);
        Route::post('/catalogue/{catalogue}/toggle-status', [CatalogueController::class, 'toggleStatus'])->name('catalogue.toggle-status');
        Route::delete('/catalogue/clear-all', [CatalogueController::class, 'clearAll'])->name('catalogue.clear-all');

        // Catalogue Fields
        Route::post('/catalogue-fields', [CatalogueFieldController::class, 'store'])->name('catalogue-fields.store');
        Route::put('/catalogue-fields/{field}', [CatalogueFieldController::class, 'update'])->name('catalogue-fields.update');
        Route::delete('/catalogue-fields/{field}', [CatalogueFieldController::class, 'destroy'])->name('catalogue-fields.destroy');
        Route::post('/catalogue-fields/reorder', [CatalogueFieldController::class, 'reorder'])->name('catalogue-fields.reorder');


        // Followups
        Route::get('/followups', [FollowupController::class, 'index'])->name('followups.index');
        Route::post('/followups/{followup}/complete', [FollowupController::class, 'markComplete'])->name('followups.complete');
        Route::post('/followups/{followup}/reschedule', [FollowupController::class, 'reschedule'])->name('followups.reschedule');

        // Credits
        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
        Route::post('/credits/{credit}/add', [CreditController::class, 'addCredits'])->name('credits.add');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-connection', [SettingController::class, 'testConnection'])->name('settings.test-connection');
        Route::post('/settings/get-qr', [SettingController::class, 'getQrCode'])->name('settings.get-qr');
        Route::post('/settings/disconnect', [SettingController::class, 'disconnect'])->name('settings.disconnect');

        // Questionnaire Builder Routes
        Route::prefix('questionnaire')->name('questionnaire.')->group(function () {
            // Questionnaire Fields
            Route::get('/fields', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'index'])->name('fields.index');
            Route::get('/fields/create', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'create'])->name('fields.create');
            Route::post('/fields', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'store'])->name('fields.store');
            Route::get('/fields/{field}/edit', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'edit'])->name('fields.edit');
            Route::put('/fields/{field}', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'update'])->name('fields.update');
            Route::delete('/fields/{field}', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'destroy'])->name('fields.destroy');
            Route::post('/fields/reorder', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'reorder'])->name('fields.reorder');
            Route::post('/fields/{field}/toggle-unique', [\App\Http\Controllers\Admin\QuestionnaireFieldController::class, 'toggleUniqueKey'])->name('fields.toggle-unique');

            // Global Questions
            Route::get('/global', [\App\Http\Controllers\Admin\GlobalQuestionController::class, 'index'])->name('global.index');
            Route::get('/global/create', [\App\Http\Controllers\Admin\GlobalQuestionController::class, 'create'])->name('global.create');
            Route::post('/global', [\App\Http\Controllers\Admin\GlobalQuestionController::class, 'store'])->name('global.store');
            Route::get('/global/{question}/edit', [\App\Http\Controllers\Admin\GlobalQuestionController::class, 'edit'])->name('global.edit');
            Route::put('/global/{question}', [\App\Http\Controllers\Admin\GlobalQuestionController::class, 'update'])->name('global.update');
            Route::delete('/global/{question}', [\App\Http\Controllers\Admin\GlobalQuestionController::class, 'destroy'])->name('global.destroy');

            // Question Templates
            Route::get('/templates', [\App\Http\Controllers\Admin\QuestionTemplateController::class, 'index'])->name('templates.index');
            Route::get('/templates/{fieldName}/edit', [\App\Http\Controllers\Admin\QuestionTemplateController::class, 'edit'])->name('templates.edit');
            Route::post('/templates/{fieldName}', [\App\Http\Controllers\Admin\QuestionTemplateController::class, 'store'])->name('templates.store');
            Route::delete('/templates/{template}', [\App\Http\Controllers\Admin\QuestionTemplateController::class, 'destroy'])->name('templates.destroy');

            // Flowchart Builder (React Flow)
            Route::get('/flowchart', [\App\Http\Controllers\Admin\FlowchartController::class, 'index'])->name('flowchart.index');
            Route::get('/flowchart/data', [\App\Http\Controllers\Admin\FlowchartController::class, 'getData'])->name('flowchart.data');
            Route::post('/flowchart/node', [\App\Http\Controllers\Admin\FlowchartController::class, 'saveNode'])->name('flowchart.node.save');
            Route::delete('/flowchart/node/{node}', [\App\Http\Controllers\Admin\FlowchartController::class, 'deleteNode'])->name('flowchart.node.delete');
            Route::post('/flowchart/connection', [\App\Http\Controllers\Admin\FlowchartController::class, 'saveConnection'])->name('flowchart.connection.save');
            Route::delete('/flowchart/connection/{connection}', [\App\Http\Controllers\Admin\FlowchartController::class, 'deleteConnection'])->name('flowchart.connection.delete');
            Route::post('/flowchart/save-all', [\App\Http\Controllers\Admin\FlowchartController::class, 'saveAll'])->name('flowchart.save-all');
            Route::post('/flowchart/clear', [\App\Http\Controllers\Admin\FlowchartController::class, 'clearFlow'])->name('flowchart.clear');
        });
    });
});


// Super Admin Routes (Protected by superadmin guard)
Route::prefix('superadmin')->name('superadmin.')->middleware(['superadmin.auth'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return redirect()->route('superadmin.dashboard');
    });
    Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');

    // Tenant Management
    Route::resource('tenants', TenantController::class);
    Route::post('/tenants/{tenant}/add-credits', [TenantController::class, 'addCredits'])->name('tenants.add-credits');
    Route::patch('/tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
    Route::post('/tenants/{tenant}/reset-password', [TenantController::class, 'resetPassword'])->name('tenants.reset-password');

    // Payments
    Route::get('/payments', function () {
        return view('superadmin.payments.index', [
            'payments' => \App\Models\Payment::with('tenant')->latest()->paginate(20)
        ]);
    })->name('payments.index');

    // Credits
    Route::get('/credits', function () {
        return view('superadmin.credits.index', [
            'credits' => \App\Models\TenantCredit::with('tenant')->paginate(20)
        ]);
    })->name('credits.index');

    // Settings
    Route::get('/settings', function () {
        return view('superadmin.settings.index');
    })->name('settings.index');
});
