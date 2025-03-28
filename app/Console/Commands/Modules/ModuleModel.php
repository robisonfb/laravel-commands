<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleModel extends GeneratorCommand
{
    protected $signature = 'module:model
                            {name : The name of the model.}
                            ';

    protected $description = 'Command description';

    public function handle()
    {
        parent::handle();
    }

    protected function getStub()
    {
        return  app_path() . '/Console/Commands/Modules/Stubs/ModuleModel.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Models';
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

        return $this;
    }
}
