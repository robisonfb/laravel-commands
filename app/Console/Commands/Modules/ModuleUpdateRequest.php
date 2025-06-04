<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleUpdateRequest extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:update-request
                            {name : Nome do modelo para o qual o update request será gerado com base no template}
                            {--force : Sobrescrever arquivos existentes}
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
        // Cria o diretório do modelo antes de gerar o arquivo
        $this->createModelDirectory();

        // Verifica se o arquivo já existe
        if ($this->alreadyExists($this->getNameInput())) {
            // Se a opção --force foi fornecida, sobrescreve o arquivo
            if ($this->option('force')) {
                $this->info('Sobrescrevendo Update Request existente...');

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
     * Retorna o caminho para o arquivo stub do request
     *
     * @return string|boolean
     */
    protected function getStub()
    {
        $stubPath = app_path() . '/Console/Commands/Modules/Stubs/ModuleUpdateRequest.stub';

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
        $name = ltrim($name, '\\/');
        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        // Se já começa com "Update", apenas adiciona o sufixo "Request"
        if (Str::startsWith($name, 'Update')) {
            if (!Str::endsWith($name, 'Request')) {
                $name = $name . 'Request';
            }
        }
        // Se não começa com "Update", adiciona o prefixo "Update" e o sufixo "Request" se necessário
        else {
            // Remove o sufixo "Request" se já existir
            if (Str::endsWith($name, 'Request')) {
                $name = Str::replaceLast('Request', '', $name);
            }

            // Adiciona o prefixo "Update"
            $name = 'Update' . $name;

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

        // Se já começa com "Update", apenas adiciona o sufixo "Request"
        if (Str::startsWith($name, 'Update')) {
            if (!Str::endsWith($name, 'Request')) {
                $name = $name . 'Request';
            }
        }
        // Se não começa com "Update", formata para o padrão "Update{ModelName}Request"
        else {
            // Remove o sufixo "Request" se já existir
            if (Str::endsWith($name, 'Request')) {
                $name = Str::replaceLast('Request', '', $name);
            }

            // Remove o prefixo "Update" se existir para evitar duplicação
            if (Str::startsWith($name, 'Update')) {
                $name = Str::replaceFirst('Update', '', $name);
            }

            // Adiciona o prefixo "Update"
            $name = 'Update' . $name;

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

        // Remove o prefixo 'Update' e o sufixo 'Request' para obter apenas o nome do modelo
        if (Str::startsWith($modelName, 'Update') && Str::endsWith($modelName, 'Request')) {
            $modelName = Str::replaceLast('Request', '', $modelName);
            $modelName = Str::replaceFirst('Update', '', $modelName);
        }

        // Substitui o nome do modelo
        $stub = str_replace('{modelName}', $modelName, $stub);

        // Substitui o nome plural do modelo em minúsculas
        $stub = str_replace('{modelNamePluralLowerCase}', Str::plural(Str::lower($modelName)), $stub);

        // Substitui o nome do modelo em minúsculas
        $stub = str_replace('{modelNameLowerCase}', Str::lower($modelName), $stub);

        return $this;
    }

    /**
     * Cria o diretório específico do modelo dentro de app/Http/Requests/
     *
     * @return void
     */
    protected function createModelDirectory()
    {
        $modelName    = $this->getModelName();
        $requestsPath = app_path('Http/Requests/' . $modelName);

        if (!File::exists($requestsPath)) {
            File::makeDirectory($requestsPath, 0755, true);
            $this->info('Diretório criado: ' . $requestsPath);
        }
    }

    /**
     * Extrai o nome limpo do modelo a partir do input
     *
     * @return string
     */
    protected function getModelName()
    {
        $name = trim($this->argument('name'));

        // Remove prefixos e sufixos para obter apenas o nome do modelo
        if (Str::startsWith($name, 'Update')) {
            $name = Str::replaceFirst('Update', '', $name);
        }

        if (Str::endsWith($name, 'Request')) {
            $name = Str::replaceLast('Request', '', $name);
        }

        return $name;
    }
}
