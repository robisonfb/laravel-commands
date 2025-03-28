<?php
/**
 * Classe para geração automática de factories de módulos
 *
 * Esta classe estende o GeneratorCommand do Laravel para criar
 * factories personalizadas a partir de stubs definidos.
 */

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleFactory extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:factory
                            {name : The name of the model.}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera uma factory para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Factory';

    /**
     * Executa o comando para gerar a factory
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
     * Retorna o caminho para o arquivo stub da factory
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleFactory.stub';
    }

    /**
     * Define o namespace padrão para a factory gerada
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Database\\Factories';
    }

    /**
     * Obter o caminho completo do arquivo para a factory.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace('App\\Database\\Factories\\', '', $name);
        $name = str_replace('\\', '/', $name);

        return database_path('factories/' . $name . '.php');
    }

    /**
     * Qualifica completamente o nome da classe da factory
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

        if (!Str::contains(Str::lower($name), 'factory')) {
            $name .= 'Factory';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe da factory
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

        // Remove 'Factory' do final se estiver presente
        if (Str::endsWith($modelName, 'Factory')) {
            $modelName = Str::replaceLast('Factory', '', $modelName);
        }

        // Substitui o nome do modelo
        $stub = str_replace('{modelName}', $modelName, $stub);

        // Substitui o nome de classe completo do modelo
        $modelNamespace = $this->rootNamespace() . 'Models\\' . $modelName;
        $stub = str_replace('{modelNamespace}', $modelNamespace, $stub);

        // Substitui o nome plural do modelo em minúsculas
        $stub = str_replace('{modelNamePluralLowerCase}', Str::plural(Str::lower($modelName)), $stub);

        // Substitui o nome do modelo em minúsculas
        $stub = str_replace('{modelNameLowerCase}', Str::lower($modelName), $stub);

        // Substitui o nome da factory
        $factoryName = $modelName . 'Factory';
        $stub = str_replace('{factoryName}', $factoryName, $stub);

        return $this;
    }
}
