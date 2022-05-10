<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserInterface;
use App\Interfaces\TransactionInterface;
use App\Interfaces\CurrencyConversionInterface;
use App\Repositories\UserRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\CurrencyConversionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(TransactionInterface::class, TransactionRepository::class);
        $this->app->bind(CurrencyConversionInterface::class, CurrencyConversionRepository::class);
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
