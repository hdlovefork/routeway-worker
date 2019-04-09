<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Email: 1602264241@qq.com
 * Date: 2019-03-14
 * Time: 09:45
 */

namespace Dean\RoutewayWorker\Routing;


use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Route
{
    /**
     * 事件类型
     * @var string
     */
    private $type;
    private $container;

    public function __construct($container, string $type, $action)
    {
        $this->action    = $action;
        $this->type      = $type;
        $this->container = $container;
    }

    /**
     * @var array
     */
    public $action;

    public function run($request)
    {
        try {
            if(is_callable($this->action['uses'])){
                $this->container->call($this->action['uses'], [$request]);
            }else{
                $this->container->call("{$this->action['uses']}@handle", [$request]);
            }
        } catch (ValidationException $e) {
            response_error($request->client_id, $e->validator->errors()->first());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function matches($request)
    {
        return strcmp($request->type, $this->type) === 0;
    }

    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return (array)($this->action['middleware'] ?? []);
        }

        if (is_string($middleware)) {
            $middleware = func_get_args();
        }

        $this->action['middleware'] = array_merge(
            (array)($this->action['middleware'] ?? []), $middleware
        );

        return $this;
    }
}