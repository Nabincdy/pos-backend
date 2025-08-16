<?php

use App\Models\Setting\AccountSetting;
use App\Models\Setting\CompanySetting;
use App\Models\Setting\FiscalYear;
use Illuminate\Support\Facades\Cache;

if (! function_exists('companySetting')) {
    function companySetting()
    {
        return Cache::rememberForever('company_setting', function () {
            return CompanySetting::first();
        });
    }
}

if (! function_exists('accountSetting')) {
    function accountSetting()
    {
        return Cache::rememberForever('account_setting', function () {
            return AccountSetting::first();
        });
    }
}

if (! function_exists('runningFiscalYear')) {
    function runningFiscalYear()
    {
        return Cache::rememberForever('running_fiscal_year', function () {
            return FiscalYear::where('is_running', 1)->first();
        });
    }
}
