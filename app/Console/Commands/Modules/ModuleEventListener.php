<?php
/**
 * Classe para geração automática de event listeners de módulos
 *
 * Esta classe estende o GeneratorCommand do Laravel para criar
 * event listeners personalizados a partir de stubs definidos.
 */

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleEventListener extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:event-listener
                            {name : The name of the model.}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera um event listener para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Event Listener';

    /**
     * Executa o comando para gerar o event listener
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
     * Retorna o caminho para o arquivo stub do event listener
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleEventListener.stub';
    }

    /**
     * Define o namespace padrão para o event listener gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Listeners';
    }

    /**
     * Qualifica completamente o nome da classe do event listener
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

        // Garante que o nome termina com 'Listener'
        if (!Str::contains(Str::lower($name), 'listener')) {
            $name .= 'Listener';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe do event listener
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

        // Remove 'Listener' do final se estiver presente
        if (Str::endsWith($modelName, 'Listener')) {
            $modelName = Str::replaceLast('Listener', '', $modelName);
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
