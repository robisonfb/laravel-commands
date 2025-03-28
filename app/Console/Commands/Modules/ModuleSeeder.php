<?php
/**
 * Classe para geração automática de seeders de módulos
 *
 * Esta classe estende o GeneratorCommand do Laravel para criar
 * seeders personalizados a partir de stubs definidos.
 */

namespace App\Console\Commands\Modules;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModuleSeeder extends GeneratorCommand
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:seeder
                            {name : The name of the model.}
                            ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera um seeder para o módulo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Seeder';

    /**
     * Executa o comando para gerar o seeder
     *
     * @return int
     */
    public function handle()
    {
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');
            return 1; // Código de erro
        }

        return parent::handle();
    }

    /**
     * Retorna o caminho para o arquivo stub do seeder
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path() . '/Console/Commands/Modules/Stubs/ModuleSeeder.stub';
    }

    /**
     * Define o namespace padrão para o seeder gerado
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Database\\Seeders';
    }

    /**
     * Obter o caminho completo do arquivo para o seeder.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace('App\\Database\\Seeders\\', '', $name);
        $name = str_replace('\\', '/', $name);

        return database_path('seeders/' . $name . '.php');
    }

    /**
     * Qualifica completamente o nome da classe do seeder
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

        if (!Str::contains(Str::lower($name), 'seeder')) {
            $name .= 'Seeder';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
    }

    /**
     * Constrói o conteúdo da classe do seeder
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

        // Remove 'Seeder' do final se estiver presente
        if (Str::endsWith($modelName, 'Seeder')) {
            $modelName = Str::replaceLast('Seeder', '', $modelName);
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
