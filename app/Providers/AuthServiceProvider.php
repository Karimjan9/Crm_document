<?php

namespace App\Providers;

use App\Models\DocumentsModel;
use App\Models\DocumentFileModel;
use App\Models\ClientsModel;
use App\Policies\ClientPolicy;
use App\Policies\DocumentFilePolicy;
use App\Policies\DocumentsPolicy;
use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DocumentsModel::class => DocumentsPolicy::class,
        DocumentFileModel::class => DocumentFilePolicy::class,
        ClientsModel::class => ClientPolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
