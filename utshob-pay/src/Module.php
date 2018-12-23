<?php

namespace AvoRed\UtshobPay;

use Illuminate\Support\ServiceProvider;
use AvoRed\UtshobPay\Payment\UtshobPay;
use AvoRed\Framework\Payment\Facade as PaymentFacade;
use AvoRed\Framework\AdminConfiguration\Facade as AdminConfigurationFacade;

class Module extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerResources();
        $this->registerPaymentOption();
        $this->registerAdminConfiguration();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Registering AvoRed featured Resource
     * e.g. Route, View, Database  & Translation Path
     *
     * @return void
     */
    protected function registerResources()
    {

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'utshob-pay');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'utshob-pay-lag');
       
    }
    
    /**
     * Register the Menus.
     *
     * @return void
     */
    protected function registerAdminConfiguration()
    {
        
        $paymentGroup = AdminConfigurationFacade::get('payment');
        
        $paymentGroup->addConfiguration('payment_utshob_pay_enabled')
                                ->label('Is Utshob Pay Enabled')
                                ->type('select')
                                ->name('payment_utshob_pay_enabled')
                                ->options(function (){
                                    $options = [1 => 'Yes' , 0 => 'No'];
                                    return $options;
                                });
        
    }
    
    /**
     * Register Shippiong Option for App.
     *
     * @return void
     */
    protected function registerPaymentOption()
    {
        $payment = new UtshobPay();
        PaymentFacade::put($payment->identifier(), $payment);
    }

}
