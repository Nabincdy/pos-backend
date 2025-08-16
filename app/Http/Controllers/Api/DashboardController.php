<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Crm\Client;
use App\Models\Crm\Supplier;
use App\Models\Hr\Employee;
use App\Models\Inventory\PaymentRecord;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\ReceiptRecord;
use App\Models\Inventory\Sale;
use App\Models\Setting\Month;
use App\Models\User;
use App\Traits\NepaliDateConverter;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    use NepaliDateConverter;

    public Collection $purchases;

    public Collection $sales;

    public function __construct()
    {
        $this->purchases = Purchase::with('purchaseParticulars')
            ->withSum(['paymentRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'paid_amount')
            ->filterData()
            ->latest('purchase_date')
            ->get()->map(function ($purchase) {
                return [
                    'invoice_no' => $purchase->invoice_no,
                    'purchase_date' => $purchase->purchase_date,
                    'purchase_month' => $purchase->purchase_month,
                    'total_amount' => $purchase->purchaseParticulars->sum('total_amount'),
                    'due_amount' => $purchase->purchaseParticulars->sum('total_amount') - $purchase->payment_records_sum_paid_amount,
                ];
            });
        $this->sales = Sale::with('saleParticulars')
            ->withSum(['receiptRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'amount')
            ->filterData()
            ->latest('sales_date')
            ->get()->map(function ($sale) {
                return [
                    'invoice_no' => $sale->invoice_no,
                    'sales_date' => $sale->sales_date,
                    'sales_month' => $sale->sales_month,
                    'total_amount' => $sale->saleParticulars->sum('total_amount'),
                    'due_amount' => $sale->saleParticulars->sum('total_amount') - $sale->receipt_records_sum_amount,
                ];
            });
    }

    public function __invoke()
    {
        return [
            'clients_count' => Client::count(),
            'suppliers_count' => Supplier::count(),
            'employees_count' => Employee::count(),
            'users_count' => User::count(),
            'total_purchase_amount' => $this->purchases->sum('total_amount'),
            'today_purchase_amount' => $this->purchases->where('purchase_date', $this->get_today_nepali_date())->sum('total_amount'),
            'purchase_due_amount' => $this->purchases->sum('due_amount'),
            'today_purchase_due_amount' => $this->purchases->where('purchase_date', $this->get_today_nepali_date())->sum('due_amount'),
            'total_sales_amount' => $this->sales->sum('total_amount'),
            'today_sales_amount' => $this->sales->where('sales_date', $this->get_today_nepali_date())->sum('total_amount'),
            'sales_due_amount' => $this->sales->sum('due_amount'),
            'today_sales_due_amount' => $this->sales->where('sales_date', $this->get_today_nepali_date())->sum('due_amount'),
            'monthlyPurchaseSales' => $this->getMonthlyPurchaseSales(),
            'weeklyPayments' => $this->getWeeklyPayments(),
            'last_purchases' => $this->purchases->take(5),
            'last_purchase_returns' => $this->purchases->take(5),
            'last_sales' => $this->sales->take(5),
        ];
    }

    private function getMonthlyPurchaseSales()
    {
        $months = Month::orderBy('rank')->get()->map(function ($month) {
            $salesTotalAmount = 0;
            $purchaseTotalAmount = 0;
            $purchaseTotalAmount += $this->purchases->where('purchase_month', $month->month)->sum('total_amount');
            $salesTotalAmount += $this->sales->where('sales_month', $month->month)->sum('total_amount');

            return [
                'name' => $month->name,
                'purchaseTotalAmount' => round($purchaseTotalAmount, 2),
                'salesTotalAmount' => round($salesTotalAmount, 2),
            ];
        });

        return [
            'labels' => $months->pluck('name'),
            'fieldSets' => [
                [
                    'label' => 'Purchase',
                    'data' => $months->pluck('purchaseTotalAmount'),
                ],
                [
                    'label' => 'Sales',
                    'data' => $months->pluck('salesTotalAmount'),
                ],
            ],
        ];
    }

    private function getWeeklyPayments()
    {
        $weeklyPaymentRecords = collect();
        $weeklyReceiptRecords = collect();
        $paymentRecords = PaymentRecord::whereBetween('created_at', [now()->subWeek(), now()])->get();
        $receiptRecords = ReceiptRecord::whereBetween('created_at', [now()->subWeek(), now()])->get();
        $weekDays = CarbonPeriod::create(now()->subWeek()->toDateString(), '1 day', now()->toDateString());
        foreach ($weekDays as $weekDay) {
            $weeklyPaymentRecords->push([
                'date' => $weekDay->toDateString(),
                'amount' => $paymentRecords->where('en_payment_date', $weekDay->toDateString())->sum('paid_amount'),
            ]);
            $weeklyReceiptRecords->push([
                'date' => $weekDay->toDateString(),
                'amount' => $receiptRecords->where('en_receipt_date', $weekDay->toDateString())->sum('amount'),
            ]);
        }

        return [
            'labels' => $weeklyPaymentRecords->pluck('date'),
            'fieldSets' => [
                [
                    'label' => 'Payment Sent',
                    'data' => $weeklyPaymentRecords->pluck('amount'),
                ],
                [
                    'label' => 'Payment Received',
                    'data' => $weeklyReceiptRecords->pluck('amount'),
                ],
            ],
        ];
    }
}
