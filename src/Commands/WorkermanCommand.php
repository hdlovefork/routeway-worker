<?php

namespace Dean\RoutewayWorker\Commands;

use Dean\RoutewayWorker\Routing\Router;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Illuminate\Console\Command as LaravelCommand;
use Illuminate\Log\Events\MessageLogged;
use Workerman\Worker;

class WorkermanCommand extends LaravelCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a Workerman server.';
    /**
     * @var Router
     */
    private $router;

    /**
     * Create a new command instance.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        parent::__construct();
        $this->router = $router;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->checkEnv();
        global $argv;
        $action = $this->argument('action');

        $argv[0] = 'wk';
        $argv[1] = $action;
        $argv[2] = $this->option('d') ? '-d' : '';

        $this->start();
    }

    private function start()
    {
        Worker::$logFile = config('worker_logFile');
        $this->startGateWay();
        $this->startBusinessWorker();
        $this->startRegister();
        $this->registerRoute();
        $this->registerMiddleware();
        Worker::runAll();
    }

    private function startBusinessWorker()
    {
        $worker                  = new BusinessWorker();
        $worker->name            = config('routeway.worker_name');
        $worker->count           = config('routeway.worker_count');
        $worker->registerAddress = config('routeway.worker_registerAddress');
        $worker->eventHandler    = config('routeway.worker_eventHandler');
    }

    private function startGateWay()
    {
        // gateway 进程，这里使用Text协议，可以用telnet测试
//        $gateway = new Gateway("websocket://0.0.0.0:2345");
        $gateway = new Gateway(config('routeway.gateway_socketName'));
        // gateway名称，status方便查看
        $gateway->name = config('routeway.gateway_name');
        // gateway进程数
        $gateway->count = config('routeway.gateway_count');
        // 本机ip，分布式部署时使用内网ip
        $gateway->lanIp = config('routeway.gateway_lanIp');
        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
        $gateway->startPort = config('routeway.gateway_startPort');
        // 服务注册地址
        $gateway->registerAddress = config('routeway.gateway_registerAddress');
        // 心跳间隔
        $gateway->pingInterval = config('routeway.gateway_pingInterval');
        // 多少次没有收到心跳就断开连接
        $gateway->pingNotResponseLimit = config('routeway.gateway_pingNotResponseLimit');
        // 心跳数据
        $gateway->pingData = config('routeway.gateway_pingData');
        if (!app()->environment('production')) {
            // 心跳数据
            $gateway->pingData             = '{"type":"ping"}';
            $gateway->pingNotResponseLimit = 0;
        }


        /*
        // 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
        $gateway->onConnect = function($connection)
        {
            $connection->onWebSocketConnect = function($connection , $http_header)
            {
                // 可以在这里判断连接来源是否合法，不合法就关掉连接
                // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
                if($_SERVER['HTTP_ORIGIN'] != 'http://kedou.workerman.net')
                {
                    $connection->close();
                }
                // onWebSocketConnect 里面$_GET $_SERVER是可用的
                // var_dump($_GET, $_SERVER);
            };
        };
        */
    }

    private function startRegister()
    {
        new Register(config('routeway.register'));
    }

    private function checkEnv()
    {
        // 检查扩展
        if (!extension_loaded('pcntl')) {
            $this->error("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
        }

        if (!extension_loaded('posix')) {
            $this->error("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
        }
    }

    private function registerRoute()
    {
        $this->router->loadRoutes(base_path('routes/routeway.php'));
    }

    private function registerMiddleware()
    {
        $this->router->middleware('Liyu\Dingo\SerializerSwitch:array');
    }
}
