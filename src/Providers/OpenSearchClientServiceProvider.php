<?php

namespace CloudMediaSolutions\LaravelScoutOpenSearch\Providers;

use Illuminate\Support\ServiceProvider;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class OpenSearchClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(Client::class, function () {
            return ClientBuilder::fromConfig(config('opensearch.client'));
        });
    }
}
