<?php

return [

    'gateway_socketName'           => env('GATEWAY_SOCKET_NAME', 'websocket://0.0.0.0:2345'),

    // gateway名称，status方便查看
    'gateway_name'                 => env('GATEWAY_NAME', 'Gateway'),

    //gateway进程数
    'gateway_count'                => env('GATEWAY_COUNT', 4),

    // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
    // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
    'gateway_startPort'            => env('GATEWAY_START_PORT', 2900),

    // 服务注册地址
    'gateway_registerAddress'      => env('GATEWAY_REGISTER_ADDRESS', '127.0.0.1:1236'),

    // 心跳间隔
    'gateway_pingInterval'         => env('GATEWAY_PING_INTERVAL', 30),

    // 多少次没有收到心跳就断开连接
    'gateway_pingNotResponseLimit' => env('GATEWAY_PING_NOT_RESPONSE_LIMIT', 3),

    // 心跳数据
    'gateway_pingData'                     => env('PING_DATA', ''),

    // 本机ip，分布式部署时使用内网ip
    'gateway_lanIp'                => env('GATEWAY_LAN_IP', '127.0.0.1'),

    'worker_name' => env('WORKER_NAME', 'BusinessWorker'),

    'worker_count' => env('WORKER_COUNT', 1),

    'worker_registerAddress' => env('WORKER_REGISTER_ADDRESS', '127.0.0.1:1236'),

    'worker_eventHandler' => env('WORKER_EVENT_HANDLER', Dean\RoutewayWorker\Routing\Events::class),

    'worker_logFile' => env('WORKER_LOGFILE', storage_path('logs') . '/routeway.log'),

    'register' => env('REGISTER', 'text://0.0.0.0:1236')
];
