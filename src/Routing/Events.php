<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Email: 1602264241@qq.com
 * Date: 2019-01-12
 * Time: 11:47
 */

namespace Dean\RoutewayWorker\Routing;


use App\Models\User;
use Dean\RoutewayWorker\Requests\Request;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Workerman\Lib\Timer;

class Events
{
    public static function onWorkerStart($businessWorker)
    {

    }

    public static function onConnect($client_id)
    {
        Log::debug("onConnect--->client_id[{$client_id}]");
        // 连接到来后，定时30秒关闭这个链接，需要30秒内发认证并删除定时器阻止关闭连接的执行
        $_SESSION['auth_timer_id'] = Timer::add(10, function ($client_id) {
            Gateway::closeClient($client_id);
        }, array($client_id), false);
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
            Gateway::closeClient($client_id, trans('ws.format_error'));
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
        Log::debug("onClose--->client_id[{$client_id}]");
        if ($user_id = array_get($_SESSION, 'uid')) {
            $key = with(new User())->authorizationKey($user_id);
            if ($cacheUser = Cache::get($key)) {
                unset($cacheUser['client_id']);
                Cache::put($key, $cacheUser, $cacheUser['expire_at']);
            }
        }
    }
}