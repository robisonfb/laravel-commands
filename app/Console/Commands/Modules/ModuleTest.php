<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleTest extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:test
                            {name : Nome do modelo para o qual o store request será gerado com base no template}
                            {--force : Sobrescrever arquivos existentes}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera um teste para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Test';

    /**
     * Executa o comando para gerar o teste
     *
     * @return int
     */
    public function handle()
    {
        // Verifica se o arquivo já existe
        if ($this->alreadyExists($this->getNameInput())) {
            // Se a opção --force foi fornecida, sobrescreve o arquivo
            if ($this->option('force')) {
                $this->info('Sobrescrevendo Teste existente...');

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
     * Retorna o caminho para o arquivo stub do teste
     *
     * @return string|boolean
     */
    protected function getStub()
    {
        $stubPath = app_path() . '/Console/Commands/Modules/Stubs/ModuleTest.stub';

        // Verifica se o arquivo stub existe
        if (!file_exists($stubPath)) {
            $this->error('Arquivo stub não encontrado em: ' . $stubPath);

            return false;
        }

        return $stubPath;
    }

    /**
     * Define o namespace padrão para o teste gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\../tests/Feature/Api';
    }

    /**
     * Qualifica completamente o nome da classe do teste
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        $dir = Str::singular(Str::ucfirst($name));

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        if (!Str::contains(Str::lower($name), 'test')) {
            $name .= 'Test';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $dir . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe do teste
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
     * @param string &$stub Conteúdo do stub com referência
     * @return $this
     */
    protected function replaceModel(&$stub)
    {
        $model = $this->getNameInput();

        // Remove 'Test' do final se estiver presente
        if (Str::endsWith($model, 'Test')) {
            $model = Str::replaceLast('Test', '', $model);
        }

        $stub = str_replace('DummyModel', $model, $stub);
        $stub = str_replace('CamelObject', lcfirst($model), $stub);
        $stub = str_replace($model . 'PluralObject', Str::plural(Str::lower($model)), $stub);
        $stub = str_replace($model . 'Object', Str::lower($model), $stub);

        return $this;
    }
}
