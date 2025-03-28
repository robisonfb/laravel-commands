<?php

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
                            {name : Nome do modelo para o qual a factory será gerada com base no template}
                            {--force : Sobrescrever arquivos existentes}
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
        // Verifica se o arquivo já existe
        if ($this->alreadyExists($this->getNameInput())) {
            // Se a opção --force foi fornecida, sobrescreve o arquivo
            if ($this->option('force')) {
                $this->info('Sobrescrevendo Factory existente...');

                return parent::handle();
            }

            // Informa ao usuário que o arquivo já existe e que ele pode usar --force
            $this->error($this->type . ' já existe! Use --force para sobrescrever.');

            // Retorna código de erro específico para 'arquivo já existe'
            return 3;
        }

        // Se o arquivo não existe, continua normalmente
        $result = parent::handle();

        if ($result === 0) {
            $this->info($this->type . ' criada com sucesso!');
        }

        return $result;
    }

    /**
     * Retorna o caminho para o arquivo stub da factory
     *
     * @return string|boolean
     */
    protected function getStub()
    {
        $stubPath = app_path() . '/Console/Commands/Modules/Stubs/ModuleFactory.stub';

        // Verifica se o arquivo stub existe
        if (!file_exists($stubPath)) {
            $this->error('Arquivo stub não encontrado em: ' . $stubPath);

            return false;
        }

        return $stubPath;
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
        try {
            $stub = parent::buildClass($name);
            $this->replaceModel($stub);

            return $stub;
        } catch (\Exception $e) {
            $this->error('Erro ao construir a classe: ' . $e->getMessage());

            return '';
        }
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

        // Substitui o nome plural do modelo em minúsculas
        $stub = str_replace('{modelNamePluralLowerCase}', Str::plural(Str::lower($modelName)), $stub);

        // Substitui o nome do modelo em minúsculas
        $stub = str_replace('{modelNameLowerCase}', Str::lower($modelName), $stub);

        return $this;
    }
}
