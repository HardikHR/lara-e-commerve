<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Razorpay\Api\Api;

class RazorpayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Api::class, function () {
            return new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        });
    }

    public function boot()
    {
        //
    }
}
