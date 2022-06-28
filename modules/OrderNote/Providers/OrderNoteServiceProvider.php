<?php

namespace Modules\OrderNote\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderNoteItem;
use Modules\OrderNote\Observers\OrderNoteItemObserver;
use Modules\OrderNote\Observers\OrderNoteObserver;

class OrderNoteServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        OrderNote::observe(OrderNoteObserver::class);
        OrderNoteItem::observe(OrderNoteItemObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
