<?php
/**
 * Classe para geração automática de requests de store para módulos
 *
 * Esta classe estende o GeneratorCommand do Laravel para criar
 * requests personalizados a partir de stubs definidos.
 */

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleStoreRequest extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:store-request
                            {name : The name of the model.}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera uma classe de request para store do módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Store Request';

    /**
     * Executa o comando para gerar o request
     *
     * @return int
     */
    public function handle()
    {
        $result = parent::handle();

        if ($result === false) {
            return 1;
        }

        return 0;
    }

    /**
     * Retorna o caminho para o arquivo stub do request
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleStoreRequest.stub';
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

        // Adiciona o sufixo "StoreRequest" se ainda não existir
        if (!Str::endsWith($name, 'StoreRequest')) {
            $name = $name . 'StoreRequest';
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

        // Remove 'StoreRequest' do final se estiver presente
        if (Str::endsWith($modelName, 'StoreRequest')) {
            $modelName = Str::replaceLast('StoreRequest', '', $modelName);
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
