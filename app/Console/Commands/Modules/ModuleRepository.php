<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleRepository extends GeneratorCommand
{
    protected $signature = 'module:repository 
                            {name : The name of the model.}
                            ';

    protected $description = 'Create new module classes in the SOLID standard';

    protected $type = 'Repository';

    public function handle()
    {
        parent::handle();
    }

    protected function getStub()
    {
        return  app_path() . '/Console/Commands/Modules/Stubs/ModuleRepository.stub';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $this->replaceModelImport($stub)
            ->replaceModel($stub)
            ->replaceInterfaceImport($stub)
            ->replaceInterface($stub);

        return $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Repositories';
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

        if (!Str::contains(Str::lower($name), 'repository')) {
            $name .= 'Repository';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    protected function getModel()
    {
        $name           = $this->getNameInput();
        $rootNamespace  = $this->laravel->getNamespace();
        $model          = 'Models';
        $modelNamespace = 'Models';

        return $model
            ? $rootNamespace . '\\' . ($modelNamespace ? $modelNamespace . '\\' : '') . Str::singular($name)
            : $rootNamespace . '\\User';
    }

    protected function replaceModelImport(&$stub)
    {

        $stub = str_replace(
            'DummyModel',
            str_replace('\\\\', '\\', $this->getModel()),
            $stub
        );

        return $this;
    }

    protected function replaceModel(&$stub)
    {
        $model = explode('\\', $this->getModel());
        $model = array_pop($model);
        $stub  = str_replace('ModelName', $model, $stub);

        return $this;
    }

    protected function replaceInterfaceImport(&$stub)
    {
        $rootNamespace = $this->laravel->getNamespace();
        $interface     = $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\Contracts\\' . $this->getNameInput() . 'RepositoryInterface';
        $stub          = str_replace(
            'DummyInterface',
            $interface,
            $stub
        );

        return $this;
    }

    protected function replaceInterface(&$stub)
    {
        $interface = $this->getNameInput() . 'RepositoryInterface';
        $stub      = str_replace('InterfaceName', $interface, $stub);

        return $this;
    }
}
