<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleUpdateRequest extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:update-request
                            {name: Nome do modelo para o qual o controlador será gerado com base no template}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera uma classe de request para update do módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Update Request';

    /**
     * Executa o comando para gerar o request
     *
     * @return int
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
     * Retorna o caminho para o arquivo stub do request
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleUpdateRequest.stub';
    }

    /**
     * Define o namespace padrão para o request gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Http\\Requests';
    }

    /**
     * Qualifica completamente o nome da classe do request
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');
        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        // Adiciona o sufixo "UpdateRequest" se ainda não existir
        if (!Str::endsWith($name, 'UpdateRequest')) {
            $name = $name . 'UpdateRequest';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe do request
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

        // Remove 'UpdateRequest' do final se estiver presente
        if (Str::endsWith($modelName, 'UpdateRequest')) {
            $modelName = Str::replaceLast('UpdateRequest', '', $modelName);
        }

        // Substitui o nome do modelo
        $stub = str_replace('{modelName}', $modelName, $stub);

        // Substitui o nome plural do modelo em minúsculas
        $stub = str_replace('{modelNamePluralLowerCase}', Str::plural(Str::lower($modelName)), $stub);

        // Substitui o nome do modelo em minúsculas
        $stub = str_replace('{modelNameLowerCase}', Str::lower($modelName), $stub);

        return $this;
    }
}
