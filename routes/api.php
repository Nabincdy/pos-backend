<?php

use App\Http\Controllers\Api\Account\AccountHeadController;
use App\Http\Controllers\Api\Account\AccountOpeningBalanceController;
use App\Http\Controllers\Api\Account\AccountReportController;
use App\Http\Controllers\Api\Account\BankAccountController;
use App\Http\Controllers\Api\Account\JournalVoucherController;
use App\Http\Controllers\Api\Account\LedgerController;
use App\Http\Controllers\Api\Account\LedgerGroupController;
use App\Http\Controllers\Api\Account\PaymentVoucherController;
use App\Http\Controllers\Api\Account\ReceiptVoucherController;
use App\Http\Controllers\Api\Crm\ClientController;
use App\Http\Controllers\Api\Crm\ClientGroupController;
use App\Http\Controllers\Api\Crm\CompanyController;
use App\Http\Controllers\Api\Crm\SupplierController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Hr\AdvanceSalaryController;
use App\Http\Controllers\Api\Hr\DepartmentController;
use App\Http\Controllers\Api\Hr\DesignationController;
use App\Http\Controllers\Api\Hr\EmployeeAttendanceController;
use App\Http\Controllers\Api\Hr\EmployeeController;
use App\Http\Controllers\Api\Hr\EmployeeSalaryController;
use App\Http\Controllers\Api\Hr\LeaveController;
use App\Http\Controllers\Api\Hr\LeaveTypeController;
use App\Http\Controllers\Api\Hr\PayableChargeController;
use App\Http\Controllers\Api\Hr\PayHeadController;
use App\Http\Controllers\Api\Hr\ReportController;
use App\Http\Controllers\Api\Hr\SalaryPaymentController;
use App\Http\Controllers\Api\Inventory\BrandController;
use App\Http\Controllers\Api\Inventory\InventoryReportController;
use App\Http\Controllers\Api\Inventory\PaymentRecordController;
use App\Http\Controllers\Api\Inventory\ProductCategoryController;
use App\Http\Controllers\Api\Inventory\ProductController;
use App\Http\Controllers\Api\Inventory\ProductOpeningController;
use App\Http\Controllers\Api\Inventory\PurchaseController;
use App\Http\Controllers\Api\Inventory\PurchaseReturnController;
use App\Http\Controllers\Api\Inventory\QuotationController;
use App\Http\Controllers\Api\Inventory\ReceiptRecordController;
use App\Http\Controllers\Api\Inventory\SaleController;
use App\Http\Controllers\Api\Inventory\SalesReturnController;
use App\Http\Controllers\Api\Inventory\StockAdjustmentController;
use App\Http\Controllers\Api\Inventory\UnitController;
use App\Http\Controllers\Api\Inventory\WarehouseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Setting\AccountSettingController;
use App\Http\Controllers\Api\Setting\BankController;
use App\Http\Controllers\Api\Setting\CompanySettingController;
use App\Http\Controllers\Api\Setting\FiscalYearController;
use App\Http\Controllers\Api\Setting\MonthController;
use App\Http\Controllers\Api\Setting\TaxController;
use App\Http\Controllers\Api\Sms\SmsController;
use App\Http\Controllers\Api\Sms\SmsTemplateController;
use App\Http\Controllers\Api\UserManagement\AuthController;
use App\Http\Controllers\Api\UserManagement\PermissionController;
use App\Http\Controllers\Api\UserManagement\RoleController;
use App\Http\Controllers\Api\UserManagement\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::prefix('admin')->as('admin.')->middleware(['auth:sanctum', 'checkRoleMiddleware'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    //setting
    Route::prefix('setting')->as('setting.')->group(function () {
        Route::apiResource('companySetting', CompanySettingController::class)->only('index', 'update');
        Route::get('fiscalYear/running', [FiscalYearController::class, 'runningFiscalYear'])->name('runningFiscalYear');
        Route::put('fiscalYear/{fiscalYear}/updateStatus', [FiscalYearController::class, 'updateStatus'])->name('fiscalYear.updateStatus');
        Route::apiResource('fiscalYear', FiscalYearController::class);
        Route::apiResource('month', MonthController::class);
        Route::get('tax/generate/code', [TaxController::class, 'taxCode'])->name('tax.code');
        Route::apiResource('tax', TaxController::class);
        Route::apiResource('accountSetting', AccountSettingController::class)->only('index', 'update');
        Route::apiResource('bank', BankController::class);
    });

    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'profile'])->name('profile');
        Route::put('/updateProfile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
        Route::put('/updatePassword', [ProfileController::class, 'updatePassword'])->name('updatePassword');
    });
    Route::get('permission', PermissionController::class);
    Route::apiResource('role', RoleController::class);
    Route::put('user/{user}/updateUserStatus', [UserController::class, 'updateUserStatus'])->name('user.updateUserStatus');
    Route::apiResource('user', UserController::class);

    //account
    Route::prefix('account')->as('account.')->group(function () {
        Route::get('accountHead', AccountHeadController::class)->name('accountHead');
        Route::apiResource('ledgerGroup', LedgerGroupController::class);
        Route::put('ledger/{ledger}/updateStatus', [LedgerController::class, 'updateStatus'])->name('ledger.updateStatus');
        Route::apiResource('ledger', LedgerController::class);
        Route::apiResource('accountOpeningBalance', AccountOpeningBalanceController::class);
        Route::get('bankAccount/generate/code', [BankAccountController::class, 'bankAccountCode'])->name('bankAccount.code');
        Route::apiResource('bankAccount', BankAccountController::class);
        Route::get('journalVoucher/generate/code', [JournalVoucherController::class, 'journalVoucherCode'])->name('journalVoucher.code');
        Route::apiResource('journalVoucher', JournalVoucherController::class);
        Route::get('paymentVoucher/generate/code', [PaymentVoucherController::class, 'paymentVoucherCode'])->name('paymentVoucher.code');
        Route::apiResource('paymentVoucher', PaymentVoucherController::class);
        Route::get('receiptVoucher/generate/code', [ReceiptVoucherController::class, 'receiptVoucherCode'])->name('receiptVoucher.code');
        Route::apiResource('receiptVoucher', ReceiptVoucherController::class);
        Route::prefix('report')->as('report.')->controller(AccountReportController::class)->group(function () {
            Route::get('general-ledger', 'generalLedger')->name('generalLedger');
            Route::get('balance-sheet', 'BalanceSheet')->name('balanceSheet');
            Route::get('profit-loss', 'profitLoss')->name('profitLoss');
            Route::get('day-book', 'dayBook')->name('dayBook');
            Route::get('trial-balance', 'trialBalance')->name('trialBalance');
        });
    });

    //crm
    Route::prefix('crm')->as('crm.')->group(function () {
        Route::get('clientGroup/generate/code', [ClientGroupController::class, 'clientGroupCode'])->name('clientGroup.code');
        Route::apiResource('clientGroup', ClientGroupController::class);
        Route::get('company/generate/code', [CompanyController::class, 'companyCode'])->name('company.code');
        Route::apiResource('company', CompanyController::class);
        Route::get('client/generate/code', [ClientController::class, 'clientCode'])->name('client.code');
        Route::get('client/excel/sample-download', [ClientController::class, 'downloadSample'])->name('client.sample-download');
        Route::post('client/excel/import', [ClientController::class, 'import'])->name('client.import');
        Route::get('client/excel/export', [ClientController::class, 'export'])->name('client.export');
        Route::get('client/sale/due-report', [ClientController::class, 'clientDueReport'])->name('client.due-report');
        Route::post('client/send/payment-reminder-sms', [ClientController::class, 'sendPaymentReminderSms'])->name('client.send-payment-reminder-sms');
        Route::apiResource('client', ClientController::class);
        Route::get('supplier/generate/code', [SupplierController::class, 'supplierCode'])->name('supplier.code');
        Route::get('supplier/excel/sample-download', [SupplierController::class, 'downloadSample'])->name('supplier.sample-download');
        Route::post('supplier/excel/import', [SupplierController::class, 'import'])->name('supplier.import');
        Route::get('supplier/excel/export', [SupplierController::class, 'export'])->name('supplier.export');
        Route::apiResource('supplier', SupplierController::class);
    });

    //inventory
    Route::prefix('inventory')->as('inventory.')->group(function () {
        Route::apiResource('unit', UnitController::class);
        Route::get('warehouse/generate/code', [WarehouseController::class, 'warehouseCode'])->name('warehouse.code');
        Route::get('warehouse/stock/list', [WarehouseController::class, 'stockWarehouses'])->name('stock-warehouse');
        Route::apiResource('warehouse', WarehouseController::class);
        Route::apiResource('brand', BrandController::class);
        Route::get('productCategory/generate/code', [ProductCategoryController::class, 'productCategoryCode'])->name('productCategory.code');
        Route::apiResource('productCategory', ProductCategoryController::class);
        Route::get('product/generate/code', [ProductController::class, 'productCode'])->name('product.code');
        Route::get('product/all', [ProductController::class, 'allProducts'])->name('product.all');
        Route::get('product/product-stock/warehouse-wise', [ProductController::class, 'warehouseWiseProductStocks'])->name('product-stock.warehouse-wise');
        Route::get('product/stock/out', [ProductController::class, 'outOfStockProducts'])->name('product.stock.out');
        Route::get('product/excel/sample-download', [ProductController::class, 'downloadSample'])->name('product.sample-download');
        Route::post('product/excel/import', [ProductController::class, 'import'])->name('product.import');
        Route::get('product/excel/export', [ProductController::class, 'export'])->name('product.export');
        Route::get('product/{product}/stock-batches', [ProductController::class, 'productStockBatches'])->name('product.stock-batches');
        Route::apiResource('product', ProductController::class);
        Route::get('purchase/generate/code', [PurchaseController::class, 'purchaseCode'])->name('purchase.code');
        Route::get('purchase/all', [PurchaseController::class, 'allPurchases'])->name('purchase.all');
        Route::apiResource('purchase', PurchaseController::class);
        Route::get('paymentRecord/generate/code', [PaymentRecordController::class, 'paymentRecordCode'])->name('paymentRecord.code');
        Route::get('paymentRecord/all', [PaymentRecordController::class, 'allPaymentRecords'])->name('paymentRecord.all');
        Route::apiResource('paymentRecord', PaymentRecordController::class);
        Route::apiResource('productOpening', ProductOpeningController::class);
        Route::get('purchaseReturn/generate/code', [PurchaseReturnController::class, 'purchaseReturnCode'])->name('purchase.code');
        Route::get('purchaseReturn/all', [PurchaseReturnController::class, 'allPurchaseReturns'])->name('purchaseReturn.all');
        Route::apiResource('purchaseReturn', PurchaseReturnController::class);
        Route::get('sale/generate/code', [SaleController::class, 'salesCode'])->name('sale.code');
        Route::get('sale/all', [SaleController::class, 'allSales'])->name('sale.all');
        Route::apiResource('sale', SaleController::class);
        Route::get('salesReturn/generate/code', [SalesReturnController::class, 'salesReturnCode'])->name('sale.code');
        Route::get('salesReturn/all', [SalesReturnController::class, 'allSalesReturns'])->name('salesReturn.all');
        Route::apiResource('salesReturn', SalesReturnController::class);
        Route::get('quotation/generate/code', [QuotationController::class, 'quotationCode'])->name('quotation.code');
        Route::get('quotation/all', [QuotationController::class, 'allQuotations'])->name('quotation.all');
        Route::post('quotation/{quotation}/convertToSale', [QuotationController::class, 'convertToSale'])->name('quotation.convertToSale');
        Route::apiResource('quotation', QuotationController::class);
        Route::get('receiptRecord/generate/code', [ReceiptRecordController::class, 'receiptRecordCode'])->name('receiptRecord.code');
        Route::get('receiptRecord/all', [ReceiptRecordController::class, 'allReceiptRecords'])->name('receiptRecord.all');
        Route::apiResource('receiptRecord', ReceiptRecordController::class);
        Route::apiResource('stockAdjustment', StockAdjustmentController::class);
        Route::prefix('report')->as('report.')->controller(InventoryReportController::class)->group(function () {
            Route::get('stock-summary', 'stockSummaryCategoryWise')->name('stockSummary.categoryWise');
            Route::get('stock-summary/{productCategory}', 'productStockSummary')->name('productStockSummary');
            Route::get('product-ledger/{product}', 'productLedger')->name('productLedger');
            Route::get('product-expiry-report', 'expiryProducts')->name('product-expiry-report');
            Route::get('purchase/product-wise', 'productWisePurchase')->name('productWisePurchase');
            Route::get('purchase/summary', 'purchaseSummary')->name('purchase.summary');
            Route::get('sale/product-wise', 'productWiseSales')->name('sale.product-wise');
            Route::get('sale/summary', 'salesSummary')->name('sale.summary');
        });
    });
    //human resource
    Route::prefix('hr')->as('hr.')->group(function () {
        Route::apiResource('department', DepartmentController::class);
        Route::apiResource('designation', DesignationController::class);
        Route::get('employee/generate/code', [EmployeeController::class, 'employeeCode'])->name('employee.code');
        Route::put('employee/{employee}/updateStatus', [EmployeeController::class, 'updateStatus'])->name('employee.updateStatus');
        Route::apiResource('employee', EmployeeController::class);
        Route::apiResource('payHead', PayHeadController::class);
        Route::apiResource('leaveType', LeaveTypeController::class);
        Route::apiResource('leave', LeaveController::class);
        Route::get('employee/salary/latest', [EmployeeSalaryController::class, 'salaryList'])->name('employee.salaryList');
        Route::apiResource('employee/{employee}/employeeSalary', EmployeeSalaryController::class)->names('employee.employeeSalary');
        Route::apiResource('advanceSalary', AdvanceSalaryController::class);
        Route::apiResource('payableCharge', PayableChargeController::class);
        Route::apiResource('salaryPayment', SalaryPaymentController::class);
        Route::apiResource('employeeAttendance', EmployeeAttendanceController::class);

        //employee import
        Route::get('employeeExport', [EmployeeController::class, 'employeeExport']);
        Route::post('employeeImport', [EmployeeController::class, 'employeeImport']);

        Route::prefix('report')->as('report.')->controller(ReportController::class)->group(function () {
            Route::get('employee/{employee}/salary-ledger', 'salaryLedger')->name('salary-ledger');
        });
    });

    //sms
    Route::prefix('sms')->as('sms.')->group(function () {
        Route::apiResource('smsTemplate', SmsTemplateController::class);
        Route::get('credit-balance', [SmsController::class, 'creditBalance'])->name('credit-balance');
        Route::post('send-single-sms', [SmsController::class, 'sendSingleSms'])->name('send-single-sms');
        Route::post('send-group-sms', [SmsController::class, 'sendGroupSms'])->name('send-group-sms');
        Route::get('sentMessages', [SmsController::class, 'sentMessages'])->name('sentMessages');
    });
});
