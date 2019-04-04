<?php

use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

/**
 * 生成一个Socket端的回复字符串
 * @param string $type 事件类型:['login'|...]
 * @param mixed $data 事件数据或Model
 * @param TransformerAbstract $transformer $data为Model时该参数为Transformer对象
 * @param null $include
 * @return string
 */
function make_response(string $type, $data = [], TransformerAbstract $transformer = null, $include = null)
{
    $response = app('workerman.response')->setType($type);
    if (is_array($data)) {
        $response->setData($data);
    } else {
        $array = model_array($data, $transformer, is_array($include) ? $include : [$include]);
        $response->setData(array_get($array, 'data', $array));

    }
    return $response->toString();
}

/**
 * 将对象转成json格式字符串
 * @param $model
 * @param $transformer
 * @param null $include
 * @return array
 */
function model_array($model, $transformer, $include = null)
{
    $factory = app('Dingo\Api\Transformer\Factory');
    if ($model instanceof Collection && !$model->isEmpty()) {
        $class = get_class($model->first());
    } else {
        $class = get_class($model);
    }
    $factory->register($class, $transformer);
    $request = app('request');
    // 保存原include值
    $origin = $request->input('include');
    $request->query->set('include', $include);
    $result = $factory->transform($model);
    // 恢复原include值
    $request->query->set('include', $origin);
    return $result;
}

function response_error($client_id, $msg, $code = 422)
{
    \GatewayWorker\Lib\Gateway::sendToClient($client_id,
        make_response('error', compact('code', 'msg'))
    );
}