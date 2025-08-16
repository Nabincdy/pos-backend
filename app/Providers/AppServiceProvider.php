<?php

namespace App\Providers;

use App\Models\Hr\Employee;
use App\Models\Setting\Month;
use App\Observers\EmployeeObserver;
use App\Observers\MonthObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Month::observe(MonthObserver::class);
        Employee::observe(EmployeeObserver::class);
    }
}
