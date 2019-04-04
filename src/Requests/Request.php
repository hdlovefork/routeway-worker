<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Email: 1602264241@qq.com
 * Date: 2019-01-15
 * Time: 15:21
 */

namespace Dean\RoutewayWorker\Requests;


class Request
{
    public    $type;
    protected $data = [];

    public $client_id;
    public $msg;
    public $action;

    public function __construct($client_id = null, $msg = null)
    {
        $this->client_id = $client_id;
        $this->msg       = array_get($msg,'data');
        $this->type      = array_get($msg,'type');
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function toString()
    {
        $data = [
            'type' => $this->type,
            'data' => count($this->data) === 0 ? null : $this->data
        ];
        // $data = array_merge($data, $this->data);
        return json_encode($data);
    }

}