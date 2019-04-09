#功能
像WEB一样将中间件，路由等概念集成到WorkerMan中

#安装
`composer require dean/routeway-worker`

#说明
客户端和服务器通讯使用JSON格式传输数据，其中包含`type`和`data`2个字段：
* type表示该数据包的类型，值为字符串类型，比如：`"login"`（登录）,`"chat"`(聊天)等，需要开发者自定义；
* data表示该数据包的主体内容，值为数组或对象；

#示例

在routes/routeway.php，添加如下内容：

```
$router = app(Dean\RoutewayWorker\Routing\Router::class);

// 当收到type为hi的消息时，服务器同样回复一个type为id，data为一个数组的数据包
$router->register('hi',function (SocketRequest $request){
   $res = make_response('hi',['message'=>'Hello World']);
   \GatewayWorker\Lib\Gateway::sendToCurrentClient($res);
});
```

`php artisan make:handler LoginHandler`

在routes/routeway.php，添加如下内容：

```
$router = app(Dean\RoutewayWorker\Routing\Router::class);
...
// 处理登录请求
$router->register('login', 'App\Handlers\LoginHandler');

```