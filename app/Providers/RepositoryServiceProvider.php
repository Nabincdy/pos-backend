<?php

namespace App\Providers;

use App\Contracts\Inventory\PurchaseInterface;
use App\Repositories\Inventory\PurchaseRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * repositories list with contracts
     *
     * @var array
     */
    protected array $repositories = [
        PurchaseInterface::class => PurchaseRepository::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->repositories as $interface => $repository) {
            $this->app->bind($interface, $repository);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
