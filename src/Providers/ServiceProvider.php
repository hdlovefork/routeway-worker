<?php

namespace Dean\RoutewayWorker\Providers;

use Dean\RoutewayWorker\Requests\Request;
use Dean\RoutewayWorker\Commands\MakeHandlerCommand;
use Dean\RoutewayWorker\Commands\WorkermanCommand;
use Dean\RoutewayWorker\Requests\SocketRequest;
use Dean\RoutewayWorker\Routing\Router;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Router::class, function ($app) {
            return new Router($app);
        });
        $this->app->bind(Request::class, function () {
            return new Request();
        });
        $this->app->alias(Request::class, 'workerman.response');
        
        $this->app->afterResolving(ValidatesWhenResolved::class, function ($resolved) {
            Log::debug("\$resolved->client_id={$resolved->client_id}");
            $resolved->validateResolved();
        });
        
        $this->app->resolving(SocketRequest::class, function ($request, $app) {
            $request = SocketRequest::createFrom($app['workerman.request'], $request);
            $request->setContainer($app);
        });
        
        $this->commands([
            WorkermanCommand::class,
            MakeHandlerCommand::class
        ]);
        
        $this->publishes([
            __DIR__ . '/../routes/routeway.php' => base_path('routes/routeway.php'),
            __DIR__ . '/../config/routeway.php' => base_path('config/routeway.php'),
            __DIR__ . '/../resources/lang/en/routeway.php' => resource_path('lang/en/routeway.php'),
            __DIR__ . '/../resources/lang/zh-CN/routeway.php' => resource_path('lang/zh-CN/routeway.php'),
            
        ], 'routeway');
    }
}
