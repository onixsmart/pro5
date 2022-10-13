<?php

namespace App\Providers;

use App\Models\Tenant\Document;
use App\Observers\DocumentObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

use App\Notifications\system\DatabaseChannel;
use Illuminate\Notifications\Channels\DatabaseChannel as IlluminateDatabaseChannel;

class AppServiceProvider extends ServiceProvider
{
	public function boot()
	{
		if (config('tenant.force_https')) {
			URL::forceScheme('https');
		}
		Document::observe(DocumentObserver::class);

		$this->app->instance(IlluminateDatabaseChannel::class, new DatabaseChannel);

	}

	public function register()
	{
	}
}
