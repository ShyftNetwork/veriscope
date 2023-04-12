<?php

namespace App\Providers;


use App\{ KycTemplate, SmartContractAttestation, UploadAddressFile};
use App\Observers\{ KycTemplateObserver, SmartContractAttestationObserver, UploadAddressFileObserver};
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\KycTemplateEventSubscriber;



class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ]
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        KycTemplateEventSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
        SmartContractAttestation::observe(SmartContractAttestationObserver::class);
        KycTemplate::observe(KycTemplateObserver::class);
        UploadAddressFile::observe(UploadAddressFileObserver::class);

    }
}
