<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DocumentCustodyController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\DocumentWorkflowController;
use App\Http\Controllers\FilialPnlController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\FinanceLedgerController;
use App\Http\Controllers\FinanceLookupController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\MarginLeakController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OperationsDashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\BusinessApprovalController;
use App\Http\Controllers\BotContentController;
use App\Http\Controllers\TelegramMessageFileController;
use App\Http\Controllers\PricingApprovalController;
use App\Http\Controllers\SmartIntakeController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

// use dompdf;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->away(
        'https://sites.google.com/view/tarjimalarmarkazi/tarjima-xizmatlari?authuser=0',
        301
    );
});

Route::middleware(['auth'])->group(function () {
    Route::view('/account', 'account.home')
        ->middleware('role:user')
        ->name('account.home');
    Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'receipt'])->name('expenses.receipt');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/account/settings', [AccountController::class, 'updateSettings'])->name('account.settings.update');
});

Route::get('/health', [HealthController::class, 'show'])
    ->middleware('throttle:health')
    ->name('health');

Route::get('/track/{trackingToken}', [OrderController::class, 'track'])
    ->middleware('throttle:60,1')
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.track');

Route::get('/portal/{trackingToken}', [CustomerPortalController::class, 'show'])
    ->middleware('throttle:60,1')
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal');
Route::get('/track/{partnerCode}/{trackingToken}', [CustomerPortalController::class, 'partnerTrack'])
    ->middleware('throttle:60,1')
    ->where(['partnerCode' => '[A-Za-z0-9_-]+', 'trackingToken' => '[A-Za-z0-9]+'])
    ->name('orders.partner-track');
Route::get('/intake', [SmartIntakeController::class, 'start'])
    ->middleware('throttle:30,1')
    ->name('intake.start');
Route::post('/intake/analyze', [SmartIntakeController::class, 'analyze'])
    ->middleware('throttle:20,1')
    ->name('intake.analyze');
Route::post('/intake/{token}/ocr', [SmartIntakeController::class, 'uploadOcr'])
    ->middleware('throttle:5,1')
    ->where('token', '[A-Za-z0-9]+')
    ->name('intake.ocr.upload');
Route::middleware(['auth', 'role:employee|admin_filial|admin_manager|super_admin'])->group(function (): void {
    Route::post('/intake/{token}/ocr/{ocr}/approve', [SmartIntakeController::class, 'approveOcr'])
        ->where('token', '[A-Za-z0-9]+')
        ->name('intake.ocr.approve');
});
Route::get('/portal/{trackingToken}/invoice', [CustomerPortalController::class, 'invoice'])
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal.invoice');
Route::get('/portal/{trackingToken}/qr', [CustomerPortalController::class, 'qr'])
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal.qr');
Route::get('/portal/{trackingToken}/receipt', [CustomerPortalController::class, 'receipt'])
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal.receipt');
Route::get('/portal/{trackingToken}/files/{documentFile}', [CustomerPortalController::class, 'file'])
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal.file');
Route::post('/portal/{trackingToken}/support', [CustomerPortalController::class, 'support'])
    ->middleware('throttle:10,1')
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal.support');
Route::post('/portal/{trackingToken}/repeat', [CustomerPortalController::class, 'repeat'])
    ->middleware('throttle:5,1')
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal.repeat');
Route::get('/payment/{paymentToken}', [CustomerPortalController::class, 'payment'])
    ->where('paymentToken', '[A-Za-z0-9]+')
    ->name('orders.portal.payment');

// holidays

Route::middleware(['auth', 'role:admin_manager|super_admin'])->group(function () {
    Route::get('/bot-content', [BotContentController::class, 'index'])->name('bot-content.index');
    Route::post('/bot-content', [BotContentController::class, 'store'])->name('bot-content.store');
    Route::get('/pricing-approvals', [PricingApprovalController::class, 'index'])->name('pricing.approvals.index');
    Route::post('/pricing-approvals/{pricingApproval}/approve', [PricingApprovalController::class, 'approve'])->name('pricing.approvals.approve');
    Route::post('/pricing-approvals/{pricingApproval}/reject', [PricingApprovalController::class, 'reject'])->name('pricing.approvals.reject');
    Route::get('/finance/dashboard', [FinanceDashboardController::class, 'index'])->name('finance.dashboard');
    Route::get('/finance/lookups', [FinanceLookupController::class, 'index'])->name('finance.lookups.index');
    Route::post('/finance/lookups/categories', [FinanceLookupController::class, 'category'])->name('finance.lookups.category');
    Route::post('/finance/lookups/vendors', [FinanceLookupController::class, 'vendor'])->name('finance.lookups.vendor');
    Route::post('/finance/lookups/budgets', [FinanceLookupController::class, 'budget'])->name('finance.lookups.budget');
    Route::post('/finance/lookups/cost-centers', [FinanceLookupController::class, 'costCenter'])->name('finance.lookups.cost-center');
    Route::get('/finance/margin-leaks', [MarginLeakController::class, 'index'])->name('finance.margin-leaks.index');
    Route::post('/finance/margin-leaks/scan', [MarginLeakController::class, 'scan'])->name('finance.margin-leaks.scan');
    Route::post('/finance/margin-leaks/{marginLeak}/resolve', [MarginLeakController::class, 'resolve'])->name('finance.margin-leaks.resolve');
    Route::post('/finance/cash-reconciliations', [CashReconciliationController::class, 'store'])->name('finance.cash-reconciliations.store');
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{date}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
});

Route::middleware(['auth', 'role:admin_filial|admin_manager|super_admin'])->group(function (): void {
    Route::get('/finance/filials', [FilialPnlController::class, 'index'])->name('finance.filials.index');
    Route::get('/finance/filials/{filial}', [FilialPnlController::class, 'show'])->name('finance.filials.show');
    Route::get('/finance/ledger', [FinanceLedgerController::class, 'index'])->name('finance.ledger.index');
    Route::post('/finance/ledger/{payment}/confirm', [FinanceLedgerController::class, 'confirm'])->name('finance.ledger.confirm');
    Route::post('/finance/ledger/{payment}/cancel', [FinanceLedgerController::class, 'cancel'])->name('finance.ledger.cancel');
    Route::post('/finance/ledger/{payment}/refund', [FinanceLedgerController::class, 'refund'])->name('finance.ledger.refund');
    Route::get('/finance/ledger/{payment}/proof', [FinanceLedgerController::class, 'proof'])->name('finance.ledger.proof');
    Route::post('/finance/cash-sessions', [FinanceLedgerController::class, 'openSession'])->name('finance.cash-sessions.open');
    Route::post('/finance/cash-sessions/{cashSession}/close', [FinanceLedgerController::class, 'closeSession'])->name('finance.cash-sessions.close');
    Route::post('/finance/cash-sessions/{cashSession}/reconcile', [FinanceLedgerController::class, 'reconcileSession'])->name('finance.cash-sessions.reconcile');
});

Route::post('/pricing-approvals', [PricingApprovalController::class, 'store'])
    ->middleware(['auth', 'role:employee|admin_filial|admin_manager|super_admin'])
    ->name('pricing.approvals.store');

// Route::prefix('admin')->group(function () {
//     Route::get('/filial/{filial}/employees-stat', [AdminFilialDocumentController::class, 'employeesStat'])
//         ->name('admin.filial.employees.stat');

//     Route::get('/service/{service}/addons', [AdminFilialDocumentController::class, 'getServiceAddons'])
//         ->name('admin.service.addons');
// });

// Route::get('/courier', [CourierController::class, 'index'])->name('courier.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/operations', [OperationsDashboardController::class, 'index'])->name('operations.dashboard');
    Route::post('/operations/work-items/{workItem}/complete', [OperationsDashboardController::class, 'complete'])->name('operations.work-items.complete');
    Route::get('/leads', [LeadController::class, 'index'])->middleware('role:employee|admin_filial|admin_manager|super_admin')->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->middleware('role:employee|admin_filial|admin_manager|super_admin')->name('leads.store');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->middleware('role:employee|admin_filial|admin_manager|super_admin')->name('leads.update');
    Route::post('/leads/{lead}/activities', [LeadController::class, 'activity'])->middleware('role:employee|admin_filial|admin_manager|super_admin')->name('leads.activity');
    Route::post('/leads/{lead}/telegram-reply', [LeadController::class, 'telegramReply'])
        ->middleware('role:employee|admin_filial|admin_manager|super_admin')
        ->name('leads.telegram.reply');
    Route::get('/telegram-messages/{telegramMessage}/file', [TelegramMessageFileController::class, 'show'])
        ->middleware('role:employee|admin_filial|admin_manager|super_admin')
        ->name('telegram-messages.file');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->middleware('role:employee|admin_filial|admin_manager|super_admin')->name('leads.convert');
    Route::get('/operations/approvals', [BusinessApprovalController::class, 'index'])->name('operations.approvals.index');
    Route::post('/operations/approvals/{approval}/resolve', [BusinessApprovalController::class, 'resolve'])->name('operations.approvals.resolve');
    Route::get('/change-password', [AuthenticatedSessionController::class, 'changePassword'])->name('change-password');
    Route::post('/destroy', [AuthenticatedSessionController::class, 'destroy'])->name('destroy');
    Route::get('/document-files/{documentFile}', [DocumentFileController::class, 'show'])
        ->name('document-files.show');
    Route::get('/documents/{document}/custody', [DocumentCustodyController::class, 'index'])
        ->name('document-custody.index');
    Route::post('/documents/{document}/custody', [DocumentCustodyController::class, 'store'])
        ->name('document-custody.store');
    Route::get('/document-custody/{event}/photo', [DocumentCustodyController::class, 'photo'])
        ->name('document-custody.photo');
    Route::get('/weather', [WeatherController::class, 'show'])
        ->middleware('throttle:weather')
        ->name('weather');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/checklist/{checklist}', [OrderController::class, 'toggleChecklist'])
        ->name('orders.checklist.toggle');
    Route::post('/orders/{order}/payments', [OrderController::class, 'recordPayment'])
        ->name('orders.payments.store');
    Route::post('/orders/{order}/payment-link', [OrderController::class, 'createPaymentLink'])
        ->name('orders.payment-link.store');
    Route::post('/orders/{order}/notifications', [OrderController::class, 'notifyCustomer'])
        ->name('orders.notifications.store');
    Route::post('/orders/{order}/delivery', [OrderController::class, 'assignDelivery'])
        ->name('orders.delivery.assign');
    Route::post('/orders/{order}/costs', [OrderController::class, 'recordCost'])
        ->name('orders.costs.store');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/qr', [OrderController::class, 'qr'])->name('orders.qr');

    Route::get('/documents/workflow', [DocumentWorkflowController::class, 'index'])
        ->middleware('role:super_admin')
        ->name('documents.workflow.index');
    Route::get('/documents/workflow/data', [DocumentWorkflowController::class, 'data'])
        ->middleware('role:super_admin')
        ->name('documents.workflow.data');
});

// Route::get('/change_session', [PrixodController::class, 'clear_session'])->name('clear_session');

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login_post');
});
