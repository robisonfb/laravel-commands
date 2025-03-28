<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModulePolicy extends GeneratorCommand
{
    protected $signature = 'module:policy 
                            {name : The name of the model.}
                            ';

    protected $description = 'Command description';

    public function handle()
    {
        parent::handle();
    }

    protected function getStub()
    {
        return  app_path() . '/Console/Commands/Modules/Stubs/ModulePolicy.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Policies';
    }

    protected function qualifyClass($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        if (!Str::contains(Str::lower($name), 'policy')) {
            $name .= 'Policy';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $this->replaceModel($stub);

        return $stub;
    }

    protected function replaceModel(&$stub)
    {
        $model = $this->getNameInput();
        $stub  = str_replace('DummyModel', $model, $stub);
        $stub  = str_replace($model . 'Object', Str::lower($model), $stub);

        return $this;
    }
}
