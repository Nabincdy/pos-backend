<?php

namespace Database\Seeders;

use App\Models\Account\AccountHead;
use Illuminate\Database\Seeder;

class AccountLedgerSeeder extends Seeder
{
    public function run()
    {
        collect([
            [
                'name' => 'Assets',
                'ledgerGroups' => collect([
                    [
                        'group_name' => 'Current Assets',
                        'code' => 'CA-01',
                        'auto_generated' => true,
                        'ledgerGroups' => collect([
                            [
                                'group_name' => 'Cash Account',
                                'code' => 'cash-01',
                                'auto_generated' => true,
                                'ledgers' => collect([
                                    [
                                        'ledger_name' => 'Cash In Hand',
                                        'code' => 'cash-02',
                                        'category' => 'Cash',
                                        'auto_generated' => true,
                                    ],
                                ]),
                            ],
                            [
                                'group_name' => 'Bank Account',
                                'code' => 'bank-ac-01',
                                'auto_generated' => true,
                                'ledgers' => collect([
                                ]),
                            ],
                            [
                                'group_name' => 'Sundry Debtors',
                                'code' => 'SD-01',
                                'auto_generated' => true,
                                'ledgers' => collect([

                                ]),
                            ],
                            [
                                'group_name' => 'Loan & Advances',
                                'code' => 'loan-advance',
                                'auto_generated' => true,
                                'ledgers' => collect([
                                    [
                                        'ledger_name' => 'Advance Salary',
                                        'code' => 'advance-salary',
                                        'auto_generated' => true,
                                    ],
                                ]),
                            ],
                        ]),
                    ],
                    [
                        'group_name' => 'Fixed Assets',
                        'code' => 'FA-01',
                        'auto_generated' => true,
                        'ledgerGroups' => collect([
                            [
                                'group_name' => 'Furniture',
                                'code' => 'fn-01',
                                'auto_generated' => true,
                                'ledgers' => collect([

                                ]),
                            ],
                        ]),
                    ],
                ]),
            ],
            [
                'name' => 'Liabilities',
                'ledgerGroups' => collect([
                    [
                        'group_name' => 'Current Liabilities',
                        'code' => 'CL-01',
                        'auto_generated' => true,
                        'ledgerGroups' => collect([
                            [
                                'group_name' => 'Sundry Creditors',
                                'code' => 'sc-01',
                                'auto_generated' => true,
                                'ledgers' => collect([

                                ]),
                            ],
                            [
                                'group_name' => 'Accounts Payable',
                                'code' => 'ac-payable',
                                'auto_generated' => true,
                                'ledgers' => collect([
                                    [
                                        'ledger_name' => 'Staff Salary Payable',
                                        'code' => 'salary-payable',
                                        'auto_generated' => true,
                                    ],
                                ]),
                            ],
                            [
                                'group_name' => 'Duties & Taxes',
                                'code' => 'tax-ac',
                                'auto_generated' => true,
                                'ledgers' => collect([

                                ]),
                            ],
                        ]),
                    ],
                ]),
            ],
            [
                'name' => 'Income',
                'ledgerGroups' => collect([
                    [
                        'group_name' => 'Direct Income',
                        'code' => 'DI-01',
                        'auto_generated' => true,
                        'ledgerGroups' => collect([
                            [
                                'group_name' => 'Sales Account',
                                'code' => 'sale-account',
                                'auto_generated' => true,
                                'ledgers' => collect([
                                    [
                                        'ledger_name' => 'Sales Ac',
                                        'code' => 'sales-ac',
                                        'auto_generated' => true,
                                    ],
                                ]),
                            ],
                        ]),
                    ],
                    [
                        'group_name' => 'Indirect Income',
                        'code' => 'II-01',
                        'auto_generated' => true,
                        'ledgerGroups' => collect([
                            [
                                'group_name' => 'Other Incomes',
                                'code' => 'other-income',
                                'auto_generated' => true,
                            ],
                        ]),
                    ],
                ]),
            ],
            [
                'name' => 'Expense',
                'ledgerGroups' => collect([
                    [
                        'group_name' => 'Direct Expenses',
                        'code' => 'DE-01',
                        'auto_generated' => true,
                        'ledgerGroups' => collect([
                            [
                                'group_name' => 'Purchase Account',
                                'code' => 'purchase-account',
                                'auto_generated' => true,
                                'ledgers' => collect([
                                    [
                                        'ledger_name' => 'Purchase Ac',
                                        'code' => 'purchase-ac',
                                        'auto_generated' => true,
                                    ],
                                ]),
                            ],
                            [
                                'group_name' => 'Salary Expenses',
                                'code' => 'salary-expense',
                                'auto_generated' => true,
                                'ledgers' => collect([

                                ]),
                            ],
                        ]),
                    ],
                    [
                        'group_name' => 'Indirect Expenses',
                        'code' => 'IE-01',
                        'auto_generated' => true,
                        'ledgerGroups' => collect([
                            [
                                'group_name' => 'Office Expenses',
                                'code' => 'OE-01',
                                'auto_generated' => true,
                            ],
                        ]),
                    ],
                ]),
            ],
        ])->each(function ($accountHead) {
            $newAccountHead = AccountHead::create([
                'name' => $accountHead['name'],
            ]);
            $accountHead['ledgerGroups']->each(function ($ledgerGroup) use ($newAccountHead) {
                $newLedgerGroup = $newAccountHead->ledgerGroups()->create([
                    'group_name' => $ledgerGroup['group_name'],
                    'code' => $ledgerGroup['code'],
                    'auto_generated' => $ledgerGroup['auto_generated'],
                ]);
                if (! empty($ledgerGroup['ledgerGroups'])) {
                    $ledgerGroup['ledgerGroups']->each(function ($ledgerSubGroup) use ($newLedgerGroup) {
                        $newLedgerSubGroup = $newLedgerGroup->ledgerGroups()->create([
                            'group_name' => $ledgerSubGroup['group_name'],
                            'code' => $ledgerSubGroup['code'],
                            'auto_generated' => $ledgerSubGroup['auto_generated'],
                        ]);
                        if (! empty($ledgerSubGroup['ledgers'])) {
                            $ledgerSubGroup['ledgers']->each(function ($ledger) use ($newLedgerSubGroup) {
                                $newLedgerSubGroup->ledgers()->create([
                                    'ledger_name' => $ledger['ledger_name'],
                                    'code' => $ledger['code'],
                                    'category' => $ledger['category'] ?? null,
                                    'auto_generated' => $ledger['auto_generated'],
                                ]);
                            });
                        }
                    });
                }
            });
        });
    }
}
