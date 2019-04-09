<?php

namespace Dean\RoutewayWorker\Models;


use GatewayWorker\Lib\Gateway;

trait Helper
{
    public function getClientIdAttribute()
    {
        return $this->client_id();
    }
    
    public function client_id()
    {
        $cacheUser = \Cache::get($this->authorizationKey());
        $client_id = array_get($cacheUser, 'client_id');
        return $client_id ?: array_get(Gateway::getClientIdByUid($this->id), 0);
    }
    
    public function authorizationKey($id = null)
    {
        return 'authorization_' . ($id ? $id : $this->id);
    }
}