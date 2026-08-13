<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\FilialPnlController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DocumentCustodyController;
use App\Http\Controllers\MarginLeakController;
use App\Http\Controllers\SmartIntakeController;
use App\Http\Controllers\PricingApprovalController;
use App\Http\Controllers\DocumentWorkflowController;

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
        'https://sites.google.com/view/tarjimalarmarkazi',
        301
    );
});

Route::middleware(['auth'])->group(function () {
    Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'receipt'])->name('expenses.receipt');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/account/settings', [AccountController::class, 'updateSettings'])->name('account.settings.update');
});

Route::get('/health', [HealthController::class, 'show'])->name('health');

Route::get('/track/{trackingToken}', [OrderController::class, 'track'])
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.track');

Route::get('/portal/{trackingToken}', [CustomerPortalController::class, 'show'])
    ->where('trackingToken', '[A-Za-z0-9]+')
    ->name('orders.portal');
Route::get('/track/{partnerCode}/{trackingToken}', [CustomerPortalController::class, 'partnerTrack'])
    ->where(['partnerCode' => '[A-Za-z0-9_-]+', 'trackingToken' => '[A-Za-z0-9]+'])
    ->name('orders.partner-track');
Route::get('/intake', [SmartIntakeController::class, 'start'])->name('intake.start');
Route::post('/intake/analyze', [SmartIntakeController::class, 'analyze'])
    ->middleware('throttle:20,1')
    ->name('intake.analyze');
Route::post('/intake/{token}/ocr', [SmartIntakeController::class, 'uploadOcr'])
    ->middleware('throttle:10,1')
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
    Route::get('/pricing-approvals', [PricingApprovalController::class, 'index'])->name('pricing.approvals.index');
    Route::post('/pricing-approvals/{pricingApproval}/approve', [PricingApprovalController::class, 'approve'])->name('pricing.approvals.approve');
    Route::post('/pricing-approvals/{pricingApproval}/reject', [PricingApprovalController::class, 'reject'])->name('pricing.approvals.reject');
    Route::get('/finance/dashboard', [FinanceDashboardController::class, 'index'])->name('finance.dashboard');
    Route::get('/finance/lookups', [\App\Http\Controllers\FinanceLookupController::class, 'index'])->name('finance.lookups.index');
    Route::post('/finance/lookups/categories', [\App\Http\Controllers\FinanceLookupController::class, 'category'])->name('finance.lookups.category');
    Route::post('/finance/lookups/vendors', [\App\Http\Controllers\FinanceLookupController::class, 'vendor'])->name('finance.lookups.vendor');
    Route::post('/finance/lookups/budgets', [\App\Http\Controllers\FinanceLookupController::class, 'budget'])->name('finance.lookups.budget');
    Route::post('/finance/lookups/cost-centers', [\App\Http\Controllers\FinanceLookupController::class, 'costCenter'])->name('finance.lookups.cost-center');
    Route::get('/finance/margin-leaks', [MarginLeakController::class, 'index'])->name('finance.margin-leaks.index');
    Route::post('/finance/margin-leaks/scan', [MarginLeakController::class, 'scan'])->name('finance.margin-leaks.scan');
    Route::post('/finance/margin-leaks/{marginLeak}/resolve', [MarginLeakController::class, 'resolve'])->name('finance.margin-leaks.resolve');
    Route::post('/finance/cash-reconciliations', [\App\Http\Controllers\CashReconciliationController::class, 'store'])->name('finance.cash-reconciliations.store');
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{date}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
});

Route::middleware(['auth', 'role:admin_filial|admin_manager|super_admin'])->group(function (): void {
    Route::get('/finance/filials', [FilialPnlController::class, 'index'])->name('finance.filials.index');
    Route::get('/finance/filials/{filial}', [FilialPnlController::class, 'show'])->name('finance.filials.show');
    Route::get('/finance/ledger', [\App\Http\Controllers\FinanceLedgerController::class, 'index'])->name('finance.ledger.index');
    Route::post('/finance/ledger/{payment}/confirm', [\App\Http\Controllers\FinanceLedgerController::class, 'confirm'])->name('finance.ledger.confirm');
    Route::post('/finance/ledger/{payment}/cancel', [\App\Http\Controllers\FinanceLedgerController::class, 'cancel'])->name('finance.ledger.cancel');
    Route::post('/finance/ledger/{payment}/refund', [\App\Http\Controllers\FinanceLedgerController::class, 'refund'])->name('finance.ledger.refund');
    Route::get('/finance/ledger/{payment}/proof', [\App\Http\Controllers\FinanceLedgerController::class, 'proof'])->name('finance.ledger.proof');
    Route::post('/finance/cash-sessions', [\App\Http\Controllers\FinanceLedgerController::class, 'openSession'])->name('finance.cash-sessions.open');
    Route::post('/finance/cash-sessions/{cashSession}/close', [\App\Http\Controllers\FinanceLedgerController::class, 'closeSession'])->name('finance.cash-sessions.close');
    Route::post('/finance/cash-sessions/{cashSession}/reconcile', [\App\Http\Controllers\FinanceLedgerController::class, 'reconcileSession'])->name('finance.cash-sessions.reconcile');
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
        ->name('documents.workflow.index');
    Route::get('/documents/workflow/data', [DocumentWorkflowController::class, 'data'])
        ->name('documents.workflow.data');
    Route::get('/documents/{document}/workflow-history', [DocumentWorkflowController::class, 'history'])
        ->name('documents.workflow.history');
    Route::post('/documents/{document}/checklist/{checklist}', [DocumentWorkflowController::class, 'checklist'])
        ->name('documents.workflow.checklist');
    Route::post('/documents/{document}/qa-review', [DocumentWorkflowController::class, 'qaReview'])
        ->name('documents.workflow.qa-review');
    Route::post('/documents/{document}/workflow-status', [DocumentWorkflowController::class, 'transition'])
        ->name('documents.workflow.transition');
    Route::post('/documents/{document}/workflow-assignment', [DocumentWorkflowController::class, 'assign'])
        ->name('documents.workflow.assign');
});
    


       


        // Route::get('/change_session', [PrixodController::class, 'clear_session'])->name('clear_session');





Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login_post');
});
