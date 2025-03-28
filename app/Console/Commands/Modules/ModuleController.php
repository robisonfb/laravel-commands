<?php
/**
 * Classe para geração automática de controladores de módulos
 *
 * Esta classe estende o GeneratorCommand do Laravel para criar
 * controladores personalizados a partir de stubs definidos.
 */

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleController extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:controller
                        {name : Nome do modelo para o qual o controlador será gerado com base no template}
                        ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera um controlador para A Model especificado';

    /**
     * Executa o comando para gerar o controlador
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
    }

    /**
     * Retorna o caminho para o arquivo stub do controlador
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleController.stub';
    }

    /**
     * Define o namespace padrão para o controlador gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Http\\Controllers';
    }

    /**
     * Qualifica completamente o nome da classe do controlador
     *
     * Adiciona o namespace apropriado e garante o sufixo 'Controller'
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

        // Garante que o nome termina com 'Controller'
        if (!Str::contains(Str::lower($name), 'controller')) {
            $name .= 'Controller';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe do controlador
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
