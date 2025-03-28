<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleModel extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:model
                            {name : Nome do modelo}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera um modelo para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Executa o comando para gerar o modelo
     *
     * @return void
     */
    public function handle()
    {
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . 'já existe!');
            return 1; // Código de erro
        }

        return parent::handle();
    }

    /**
     * Retorna o caminho para o arquivo stub do modelo
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleModel.stub';
    }

    /**
     * Define o namespace padrão para o modelo gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Models';
    }

    /**
     * Qualifica completamente o nome da classe do modelo
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        // Verifica se o nome já inclui o namespace
        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        // Converte separadores de diretório em separadores de namespace
        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe do modelo
     *
     * Extende o método pai e adiciona substituições específicas do modelo
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $this->replaceModel($stub);

        return $stub;
    }

    /**
     * Substitui os placeholders relacionados ao modelo no stub
     *
     * Realiza substituições de placeholders no formato {nomeVariavel}
     *
     * @param string &$stub Conteúdo do stub com referência
     * @return $this
     */
    protected function replaceModel(&$stub)
    {
        $modelName = $this->getNameInput();

        // Substitui o nome do modelo
        $stub = str_replace('{modelName}', $modelName, $stub);

        // Substitui o nome plural do modelo em minúsculas
        $stub = str_replace('{modelNamePluralLowerCase}', Str::plural(Str::lower($modelName)), $stub);

        // Substitui o nome do modelo em minúsculas
        $stub = str_replace('{modelNameLowerCase}', Str::lower($modelName), $stub);

        return $this;
    }
}
