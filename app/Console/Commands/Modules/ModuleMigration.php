<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleMigration extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:migration
                            {name : Nome do modelo para o qual a migração será gerada com base no template}
                            {--force : Sobrescrever arquivos existentes}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera uma migração para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     * Executa o comando para gerar a migração
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->getNameInput();

        // Cria o nome da tabela a partir do nome do modelo (pluralizado e snake_case)
        $tableName = Str::plural(Str::snake($name));

        // Define o nome do arquivo de migração
        $fileName = date('Y_m_d_His') . '_create_' . $tableName . '_table';

        $path = $this->getPath($fileName);

        // Verifica se já existe uma migração para esta tabela
        if ($this->migrationExists($tableName) && !$this->option('force')) {
            $this->error($this->type . ' para tabela ' . $tableName . ' já existe! Use --force para sobrescrever.');

            return 3; // Código de erro específico para arquivo existente
        }

        // Cria os diretórios se necessário
        $this->makeDirectory($path);

        try {
            // Gera e salva o arquivo
            $content = $this->buildClass($name);

            if (empty($content)) {
                $this->error('Não foi possível gerar o conteúdo da migração.');

                return 1;
            }

            $this->files->put($path, $content);

            $this->info($this->type . ' criada com sucesso para tabela ' . $tableName . '.');

            return 0; // Código de sucesso
        } catch (\Exception $e) {
            $this->error('Erro ao criar a migração: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Verifica se já existe uma migração para a tabela especificada
     *
     * @param string $tableName
     * @return bool
     */
    protected function migrationExists($tableName)
    {
        $migrationPath = $this->laravel->databasePath() . '/migrations/';
        $files         = glob($migrationPath . '*_create_' . $tableName . '_table.php');

        return !empty($files);
    }

    /**
     * Retorna o caminho para o arquivo stub da migração
     *
     * @return string|boolean
     */
    protected function getStub()
    {
        $stubPath = app_path() . '/Console/Commands/Modules/Stubs/ModuleMigration.stub';

        // Verifica se o arquivo stub existe
        if (!file_exists($stubPath)) {
            $this->error('Arquivo stub não encontrado em: ' . $stubPath);

            return false;
        }

        return $stubPath;
    }

    /**
     * Define o namespace padrão para a migração gerada
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Database\\Migrations';
    }

    /**
     * Obter o caminho do arquivo para a migração.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $path = $this->laravel->databasePath() . '/migrations/' . $name . '.php';

        return $path;
    }

    /**
     * Constrói o conteúdo da classe de migração
     *
     * @param string $name Nome do modelo
     * @return string
     */
    protected function buildClass($name)
    {
        try {
            $stubPath = $this->getStub();

            if (!$stubPath) {
                return '';
            }

            $stub = $this->files->get($stubPath);
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

        // Substitui o nome do modelo
        $stub = str_replace('{modelName}', $modelName, $stub);

        // Substitui o nome da tabela (plural e snake_case)
        $tableName = Str::plural(Str::snake($modelName));
        $stub      = str_replace('{tableName}', $tableName, $stub);

        // Substitui o nome plural do modelo em minúsculas
        $stub = str_replace('{modelNamePluralLowerCase}', Str::plural(Str::lower($modelName)), $stub);

        // Substitui o nome do modelo em minúsculas
        $stub = str_replace('{modelNameLowerCase}', Str::lower($modelName), $stub);

        // Substitui a data/hora atual
        $stub = str_replace('{dateTime}', date('Y-m-d H:i:s'), $stub);

        return $this;
    }
}
