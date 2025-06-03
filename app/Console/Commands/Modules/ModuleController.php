<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ModuleController extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:controller
                        {name : Nome do modelo para o qual o controlador será gerado com base no template}
                        {--force : Sobrescrever arquivos existentes}
                        ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera um controlador para a Model especificada';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Executa o comando para gerar o controlador
     *
     * @return int
     */
    public function handle()
    {
        // Cria a pasta da model se não existir
        $this->createModelDirectory();

        // Verifica se o arquivo já existe
        if ($this->alreadyExists($this->getNameInput())) {
            // Se a opção --force foi fornecida, sobrescreve o arquivo
            if ($this->option('force')) {
                $this->info('Sobrescrevendo Controller existente...');

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
            $this->info($this->type . ' criado com sucesso!');
        }

        return $result;
    }

    /**
     * Cria o diretório da model se não existir
     *
     * @return void
     */
    protected function createModelDirectory()
    {
        $modelName = $this->getModelName();
        $directory = app_path('Http/Controllers/' . $modelName);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info('Diretório criado: ' . $directory);
        }
    }

    /**
     * Obtém o nome da model limpo
     *
     * @return string
     */
    protected function getModelName()
    {
        $modelName = $this->getNameInput();

        // Remove 'Controller' do final se estiver presente
        if (Str::endsWith($modelName, 'Controller')) {
            $modelName = Str::replaceLast('Controller', '', $modelName);
        }

        return $modelName;
    }

    /**
     * Retorna o caminho para o arquivo stub do controlador
     *
     * @return string|boolean
     */
    protected function getStub()
    {
        $stubPath = app_path() . '/Console/Commands/Modules/Stubs/ModuleController.stub';

        // Verifica se o arquivo stub existe
        if (!file_exists($stubPath)) {
            $this->error('Arquivo stub não encontrado em: ' . $stubPath);

            return false;
        }

        return $stubPath;
    }

    /**
     * Define o namespace padrão para o controlador gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $modelName = $this->getModelName();
        return $rootNamespace . '\\Http\\Controllers\\' . $modelName;
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

        // Remove 'Controller' do final se estiver presente
        if (Str::endsWith($modelName, 'Controller')) {
            $modelName = Str::replaceLast('Controller', '', $modelName);
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
