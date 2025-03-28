<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleTest extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:test
                            {name : O nome do módulo.}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera um teste para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Policy';

    public function handle()
    {
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . 'já existe!');
            return 1; // Código de erro
        }

        return parent::handle();
    }

    protected function getStub()
    {
        return  app_path() . '/Console/Commands/Modules/Stubs/ModuleTest.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\../tests/Feature/Api';
    }
    protected function qualifyClass($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        $dir = Str::Singular(Str::ucfirst($name));

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        if (!Str::contains(Str::lower($name), 'test')) {
            $name .= 'Test';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $dir . '\\' . $name;
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

        $stub = str_replace('CamelObject', lcfirst($model), $stub);
        $stub = str_replace($model . 'PluralObject', Str::plural(Str::lower($model)), $stub);
        $stub = str_replace($model . 'Object', Str::lower($model), $stub);

        return $this;
    }
}
