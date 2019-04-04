<?php

namespace Dean\RoutewayWorker\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeHandlerCommand extends GeneratorCommand
{
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:handler';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate routeway-worker handler';
    
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Handler';
    
    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);
        
        return str_replace('DummyClass', $this->argument('name'), $stub);
    }
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/handler.stub';
    }
    
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Handlers';
    }
    
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the handler'],
        ];
    }
}
