<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ValidationRuleParser;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ValidationRuleParser::class);
    }

    public function boot()
    {
        //
    }
}
