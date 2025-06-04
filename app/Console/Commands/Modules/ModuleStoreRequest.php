<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleStoreRequest extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:store-request
                            {name : Nome do modelo para o qual o store request será gerado com base no template}
                            {--force : Sobrescrever arquivos existentes}
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
        // Cria a pasta da model se não existir
        $this->createModelDirectory();

        // Verifica se o arquivo já existe
        if ($this->alreadyExists($this->getNameInput())) {
            // Se a opção --force foi fornecida, sobrescreve o arquivo
            if ($this->option('force')) {
                $this->info('Sobrescrevendo Store Request existente...');

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
        $directory = app_path('Http/Requests/' . $modelName);

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

        // Remove 'Store' do início e 'Request' do final se estiverem presentes
        if (Str::startsWith($modelName, 'Store')) {
            $modelName = Str::replaceFirst('Store', '', $modelName);
        }

        if (Str::endsWith($modelName, 'Request')) {
            $modelName = Str::replaceLast('Request', '', $modelName);
        }

        return $modelName;
    }

    /**
     * Retorna o caminho para o arquivo stub do request
     *
     * @return string|boolean
     */
    protected function getStub()
    {
        $stubPath = app_path() . '/Console/Commands/Modules/Stubs/ModuleStoreRequest.stub';

        // Verifica se o arquivo stub existe
        if (!file_exists($stubPath)) {
            $this->error('Arquivo stub não encontrado em: ' . $stubPath);

            return false;
        }

        return $stubPath;
    }

    /**
     * Define o namespace padrão para o request gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $modelName = $this->getModelName();

        return $rootNamespace . '\\Http\\Requests\\' . $modelName;
    }

    /**
     * Qualifica completamente o nome da classe do request
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\\/');
        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        // Se já começa com "Store", apenas adiciona o sufixo "Request"
        if (Str::startsWith($name, 'Store')) {
            if (!Str::endsWith($name, 'Request')) {
                $name = $name . 'Request';
            }
        }
        // Se não começa com "Store", adiciona o prefixo "Store" e o sufixo "Request" se necessário
        else {
            // Remove o sufixo "Request" se já existir
            if (Str::endsWith($name, 'Request')) {
                $name = Str::replaceLast('Request', '', $name);
            }

            // Adiciona o prefixo "Store"
            $name = 'Store' . $name;

            // Adiciona o sufixo "Request"
            if (!Str::endsWith($name, 'Request')) {
                $name = $name . 'Request';
            }
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Obtém o nome simples da classe para gerar
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->argument('name'));

        // Se já começa com "Store", apenas adiciona o sufixo "Request"
        if (Str::startsWith($name, 'Store')) {
            if (!Str::endsWith($name, 'Request')) {
                $name = $name . 'Request';
            }
        }
        // Se não começa com "Store", formata para o padrão "Store{ModelName}Request"
        else {
            // Remove o sufixo "Request" se já existir
            if (Str::endsWith($name, 'Request')) {
                $name = Str::replaceLast('Request', '', $name);
            }

            // Remove o prefixo "Store" se existir para evitar duplicação
            if (Str::startsWith($name, 'Store')) {
                $name = Str::replaceFirst('Store', '', $name);
            }

            // Adiciona o prefixo "Store"
            $name = 'Store' . $name;

            // Adiciona o sufixo "Request"
            if (!Str::endsWith($name, 'Request')) {
                $name = $name . 'Request';
            }
        }

        return $name;
    }

    /**
     * Constrói o conteúdo da classe do request
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

        // Remove o prefixo 'Store' e o sufixo 'Request' para obter apenas o nome do modelo
        if (Str::startsWith($modelName, 'Store') && Str::endsWith($modelName, 'Request')) {
            $modelName = Str::replaceLast('Request', '', $modelName);
            $modelName = Str::replaceFirst('Store', '', $modelName);
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
