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

# 示例

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

执行以下命令生成消息处理器Handler

```php artisan make:handler LoginHandler```

然后在routes/routeway.php，添加如下内容：

```
$router = app(Dean\RoutewayWorker\Routing\Router::class);
...
// 使用register方法注册login消息的处理
$router->register('login', 'App\Handlers\LoginHandler');

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

app\Handlers\LoginHandler.php

```php
namespace App\Handlers\LoginHandler;

use App\Requests\Socket\LoginRequest;

class LoginHandler
{
	public function handler(LoginRequest $request) 
	{
		$username = $request->username; // dean@example.com
		$password = $request->password; // 123456
    // 登录逻辑
    ...
	}
}
```

上面的LoginRequest用于验证消息体data内的参数格式是否正确

app\Requests\Socket\LoginRequest.php

```php
namespace App\Requests\Socket\LoginRequest;

use Dean\RoutewayWorker\Requests\SocketRequest;

public class LoginRequest extends SocketRequest{

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
        'username' => ’用户名',
        'password' => '密码',
      ];
	}
	
}
```

