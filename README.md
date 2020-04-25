# 功能
将Web中的中间件、路由、请求参数验证等概念集成到WorkerMan中，使得处理IM消息就像编写Web控制器一样简单

# 安装
`composer require dean/routeway-worker`

# 说明
客户端和服务器通讯使用JSON格式传输数据，包含`type`和`data`2个字段：

```json
{
	"type": "your message type string",
	"data": {
		...
	}
}
```



* type表示该数据包的类型，值为字符串类型，比如：`"login"`（登录）,`"chat"`(聊天)等，需要开发者自定义；
* data表示该数据包的主体内容，值为数组或对象；

# 开始

## 闭包处理

假设服务器收到的消息内容如下：

```json
{
	"type": "hi",
	"data": {}
}
```

使用闭包方式处理hi消息


routes/routeway.php

```php
$router = app(Dean\RoutewayWorker\Routing\Router::class);

// 使用register方法注册hi消息的处理
$router->register('hi',function (SocketRequest $request)
{
   // 生成回复内容
   $res = make_response('hi',['message'=>'Hello World']);
 	// 回复客户端
   \GatewayWorker\Lib\Gateway::sendToCurrentClient($res);
});
```

服务器将发送以下内容至客户端：
```json
{
	"type": "hi",
	"data": {
		"message": "Hello World"
	}
}
```

## Handler处理

执行以下命令会在app\Handlers\目录下生成消息处理器LoginHandler

```php artisan make:handler LoginHandler```

然后在routes/routeway.php，添加如下内容：

```php
$router = app(Dean\RoutewayWorker\Routing\Router::class);

// 使用register方法注册login消息的处理
$router->register('login', 'App\Handlers\LoginHandler');
```

使用中间件方式

```php
$router = app(Dean\RoutewayWorker\Routing\Router::class);

$router->group([
	'middleware' => App\Socket\Middleware\AnyMiddleware::class
],function($router){
		$router->register('login', 'App\Handlers\LoginHandler');
});

// 或者
$router->register('login', 'App\Handlers\LoginHandler')->middleware(App\Socket\Middleware\AnyMiddleware::class)
```

假设服务器收到的login完整的消息内容如下：

```json
{
	"type": "login",
	"data": 
	{
		"username": "dean@example.com",
		"password": "123456"
	}
}
```

下面我们将读出username和password的值

app\Handlers\LoginHandler.php

```php
namespace App\Handlers\LoginHandler;

use App\Requests\Socket\LoginRequest;
user App\User;

class LoginHandler
{
  // $user参数自动注入
	public function handler(LoginRequest $request,User $user) 
	{
		$username = $request->username; // dean@example.com
		$password = $request->password; // 123456
    // 登录逻辑
    ...
	}
}
```

handler方法中的`User $user`参数将会自动注入，而`LoginRequest $request`用于验证消息体data内的参数格式是否正确

app\Requests\Socket\LoginRequest.php

```php
namespace App\Requests\Socket\LoginRequest;

use Dean\RoutewayWorker\Requests\SocketRequest;

public class LoginRequest extends SocketRequest
{

	public function rules() 
	{
        return [
            'username' => 'required|email',
            'password' => 'required|min:6'
        ];
	}
	
	public function messages()
	{
        return [
            'username.required' => '用户名必填',
            'username.email' 	  => '电子邮箱格式不正确',
            ...
        ];
	}
	
	public function attributes()
	{
    	return [
            'username' => '用户名',
            'password' => '密码',
        ];
	}
	
}
```

## helper方法

### 生成回复json字符串

```php
function make_response(string $type, $data = [], TransformerAbstract $transformer = null, $include = null)
```

* $type: 自定义消息类型，如“login"
* $data: 消息数据体，可以是一个数组或者Model
* $transformer: 如果$data为Model时模型转json的转换器，参照[这里](https://fractal.thephpleague.com/transformers/)
* $include: 当使用$data为Model时$transformer额外包含的关联数据,参照[这里](https://fractal.thephpleague.com/transformers/)

### 回复错误消息

```php
function response_error(int $client_id, string $msg, $code = 422)
```

* $client_id：客户端id
* $msg: 错误消息内容
* $code: 错误代码