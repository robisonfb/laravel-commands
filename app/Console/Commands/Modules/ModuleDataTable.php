<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleDataTable extends GeneratorCommand
{
    protected $signature = 'module:datatable 
                            {name : The name of the model.}
                            {--p|path= : Path of the module.}
                            ';

    protected $description = 'Create new module classes in the SOLID standard';

    protected $type = 'DataTable';

    public function handle()
    {
        parent::handle();
    }

    protected function getStub()
    {
        return  app_path() . '/Console/Commands/Modules/Stubs/ModuleDataTable.stub';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $this->replaceModelImport($stub)
            ->replaceModel($stub)
            ->replaceColumns($stub)
            ->replaceTableId($stub)
            ->replaceAction($stub)
            ->replaceFilename($stub);

        return $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . $this->laravel['config']->get('datatables-buttons.namespace.base', 'DataTables');
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

        if (!Str::contains(Str::lower($name), 'datatable')) {
            $name .= 'DataTable';
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

    protected function replaceColumns(&$stub)
    {
        $stub = str_replace(
            'DummyColumns',
            $this->getColumns(),
            $stub
        );

        return $this;
    }

    protected function getColumns()
    {

        return $this->parseColumns(
            $this->laravel['config']->get(
                'datatables-buttons.generator.columns',
                'id,name,created_at,updated_at'
            )
        );
    }

    protected function parseColumns($definition, $indentation = 12)
    {
        $columns = is_array($definition) ? $definition : explode(',', $definition);
        $stub    = '';

        foreach ($columns as $key => $column) {
            $stub .= "Column::make('{$column}')->title('{$column}'),";

            if ($key < count($columns) - 1) {
                $stub .= PHP_EOL . str_repeat(' ', $indentation);
            }
        }

        return $stub;
    }

    protected function replaceTableId(&$stub)
    {
        $stub = str_replace(
            'DummyTableId',
            Str::lower($this->getNameInput()) . '-table',
            $stub
        );

        return $this;
    }

    protected function replaceAction(&$stub)
    {
        $stub = str_replace(
            'DummyAction',
            $this->getAction(),
            $stub
        );

        return $this;
    }

    protected function getAction()
    {
        $action = 'pages.';

        if ($path = $this->option('path')) {
            $action .= $path . '.';
        }
        $action .= Str::plural(Str::lower($this->getNameInput())) . '.action';

        return $action;
    }

    protected function replaceFilename(&$stub)
    {
        $stub = str_replace(
            'DummyFilename',
            preg_replace('#datatable$#i', '', $this->getNameInput()),
            $stub
        );

        return $this;
    }
}
