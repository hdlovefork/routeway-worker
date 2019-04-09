<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Email: 1602264241@qq.com
 * Date: 2019-01-12
 * Time: 11:47
 */

namespace Dean\RoutewayWorker\Routing;

use Dean\RoutewayWorker\Requests\Request;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Log;

class Events
{
    public static function onWorkerStart($businessWorker)
    {

    }

    public static function onConnect($client_id)
    {
    
    }

    public static function onWebSocketConnect($client_id, $data)
    {
    }

    public static function onMessage($client_id, $msg)
    {
        Log::debug("onMessage--->client_id[{$client_id}]: $msg");
        $msg = json_decode($msg, true);
        // 消息体必须指定type
        if (!$type = array_get($msg, 'type')) {
            Gateway::closeClient($client_id, trans('routeway.format_error'));
            return;
        }
        try{
            $request = new Request($client_id, $msg);
            app()->instance('workerman.request', $request);
            $router = app(Router::class);
            $router->dispatch($request);
        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
    }

    public static function onClose($client_id)
    {
    
    }
}