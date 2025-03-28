<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleService extends GeneratorCommand
{
    protected $signature = 'module:service 
                            {name : The name of the model.}
                            {--t|translation= : Model name translation.}
                            {--p|path= : Path of the module.}
                            ';

    protected $description = 'Command description';

    public function handle()
    {
        parent::handle();
    }

    protected function getStub()
    {
        return  app_path() . '/Console/Commands/Modules/Stubs/ModuleService.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Services';
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

        if (!Str::contains(Str::lower($name), 'service')) {
            $name .= 'Service';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $this->replaceModel($stub)
            ->replaceTranslation($stub)
            ->replaceRoute($stub)
            ->replacePath($stub);

        return $stub;
    }

    protected function replaceModel(&$stub)
    {
        $model = $this->getNameInput();
        $stub  = str_replace('ModelName', $model, $stub);

        return $this;
    }

    protected function replaceTranslation(&$stub)
    {
        $translation = Str::plural($this->option('translation') ?? $this->argument('name'));
        $stub        = str_replace('translation', $translation, $stub);

        return $this;
    }

    protected function replacePath(&$stub)
    {
        $pagePath = '';

        if (!$path = $this->option('path')) {
            $pagePath = '// ';
            $path     = 'admin';
        }
        $pagePath .= 'protected $pagePath = ' . "'{$path}.';";

        $stub = str_replace('pagePath', $pagePath, $stub);

        return $this;
    }

    protected function replaceRoute(&$stub)
    {
        $route = Str::plural(Str::lower($this->getNameInput()));
        $stub  = str_replace('RouteName', $route, $stub);

        return $this;
    }
}
