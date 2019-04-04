<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Email: 1602264241@qq.com
 * Date: 2019-03-02
 * Time: 13:39
 */

namespace Dean\RoutewayWorker\Requests;


use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SocketRequest extends Request implements ValidatesWhenResolved
{
    protected $container;
    protected $validator;
    
    public static function createFrom($from, $to = null)
    {
        $request            = $to ?: new static;
        $request->type      = $from->type;
        $request->msg       = $from->msg;
        $request->data      = $from->data;
        $request->client_id = $from->client_id;
        $request->action    = $from->action;
        return $request;
    }
    
    /**
     * Validate the given class instance.
     *
     * @return void
     * @throws \Exception
     */
    public function validateResolved()
    {
        Log::debug('SocketPacket->validateResolved');
        $instance = $this->validator ?: $this->getValidatorInstance();
        if ($instance->fails()) {
            throw new ValidationException($instance);
        }
    }
    
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
    
    public function rules()
    {
        return [];
    }
    
    public function messages()
    {
        return [];
    }
    
    public function attributes()
    {
        return [];
    }
    
    protected function getValidatorInstance()
    {
        $factory = $this->container->make(ValidationFactory::class);
        
        if (method_exists($this, 'validator')) {
            $validator = $this->container->call([$this, 'validator'], compact('factory'));
        } else {
            $validator = $this->createDefaultValidator($factory);
        }
        $this->setValidator($validator);
        return $validator;
        
    }
    
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        return $factory->make(
            $this->msg, $this->container->call([$this, 'rules']),
            $this->messages(), $this->attributes()
        );
    }
    
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }
    
    public function __get($name)
    {
        return array_get($this->msg, $name, null);
    }
}