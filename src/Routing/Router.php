<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Email: 1602264241@qq.com
 * Date: 2019-01-15
 * Time: 16:40
 */

namespace Dean\RoutewayWorker\Routing;


use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\RouteDependencyResolverTrait;
use Illuminate\Support\Facades\Log;

class Router
{
    use RouteDependencyResolverTrait;

    private $routes = [];

    protected $container  = null;
    private   $middleware = [];
    protected $groupStack = [];

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function middleware($middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function findRoute($request)
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
                return $route;
            }
        }
        throw new \Exception('not found route');
    }

    public function dispatch($request)
    {
        (new Pipeline(app()))
            ->send($request)
            ->through($this->middleware)
            ->then($this->dispatchToRouter());
    }

    public function group(array $attributes, $callback)
    {
        $attributes = $this->mergeLastGroupAttributes($attributes);
        $this->groupStack[] = $attributes;
        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }

    /**
     * 注册路由
     * @param $event
     * @param $action
     * @return Route
     */
    public function register($event, $action)
    {
        $action = ['uses' => $action];
        $action = $this->mergeLastGroupAttributes($action);
        return $this->addRoute($event, $action);
    }

    protected function addRoute(string $event, $action)
    {
        $route          = new Route($this->container, $event, $action);
        $this->routes[] = $route;
        return $route;
    }

    private function dispatchToRouter()
    {
        return function ($request) {
            $this->runRoute($request, $this->findRoute($request));
        };
    }

    private function runRoute($request, $route)
    {
        Log::debug("run--->client_id:{$request->client_id}");
        $this->runRouteWithinStack($route, $request);
    }

    protected function mergeLastGroupAttributes(array $attributes)
    {
        if (empty($this->groupStack)) {
            return $this->mergeGroup($attributes, []);
        }

        return $this->mergeGroup($attributes, end($this->groupStack));
    }

    protected function mergeGroup(array $new, array $old)
    {
        return array_merge_recursive($old, $new);
    }

    protected function runRouteWithinStack(Route $route, $request)
    {
        $middleware = $route->middleware();
        (new Pipeline($this->container))
            ->send($request)
            ->through($middleware)
            ->then(function ($request) use ($route) {
                $route->run($request);
            });
    }

    public function loadRoutes($routes)
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            require $routes;
        }
    }
}