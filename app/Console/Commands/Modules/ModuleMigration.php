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
                            {name : Nome do modelo para o qual o controlador será gerado com base no template}
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

        // Verifica se o arquivo já existe
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . 'já existe!');
            return 1; // Código de erro
        }

        // Cria os diretórios se necessário
        $this->makeDirectory($path);

        // Gera e salva o arquivo
        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type . ' created successfully.');

        return 0; // Código de sucesso
    }

    /**
     * Retorna o caminho para o arquivo stub da migração
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleMigration.stub';
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
        $stub = $this->files->get($this->getStub());

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
