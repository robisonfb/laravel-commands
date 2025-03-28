<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleCollection extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:collection
                            {name : Nome do modelo para o qual o controlador será gerado com base no template}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera uma resource collection para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Collection';

    /**
     * Executa o comando para gerar a collection
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
     * Retorna o caminho para o arquivo stub da collection
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleCollection.stub';
    }

    /**
     * Define o namespace padrão para a collection gerada
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Http\\Resources';
    }

    /**
     * Qualifica completamente o nome da classe da collection
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

        // Garante que o nome termina com 'Collection'
        if (!Str::contains(Str::lower($name), 'collection')) {
            $name .= 'Collection';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe da collection
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

        // Remove 'Collection' do final se estiver presente
        if (Str::endsWith($modelName, 'Collection')) {
            $modelName = Str::replaceLast('Collection', '', $modelName);
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
