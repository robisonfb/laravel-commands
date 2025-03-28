<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Pluralizer;

class ModuleAll extends Command
{
    protected $signature = 'module:all
                            {--m|model= : O nome do modelo.}
                            ';

    protected $description = 'Cria todos os arquivos do módulo incluindo model, controller, requests, tests, etc.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!$modelInput = $this->option('model')) {
            $this->error('O parâmetro MODEL é obrigatório');
            return 1;
        }

        // Validar e corrigir nome do modelo
        $validationResult = $this->validateModelName($modelInput);

        if (!$validationResult['isValid']) {
            $this->error('O nome do modelo não segue o padrão recomendado.');
            $this->info('Problemas encontrados:');

            foreach ($validationResult['issues'] as $issue) {
                $this->warn('- ' . $issue);
            }

            $this->info('Nome sugerido: ' . $validationResult['suggestion']);

            if (!$this->confirm('Deseja continuar usando o nome sugerido?', true)) {
                $this->error('Operação cancelada pelo usuário.');
                return 1;
            }

            $model = $validationResult['suggestion'];
            $this->info('Usando: ' . $model);
        } else {
            $model = $modelInput;
        }

        // Criar cada componente separadamente usando os stubs personalizados
        $this->info('Criando componentes do módulo ' . $model . '...');

        // Criar o Model
        $this->info('Criando Model...');
        // $modelCommand = 'module:model ' . $model;
        // $runModel = Artisan::call($modelCommand);
        // if ($runModel !== 0) {
        //     $this->error('Falha ao criar o Model');
        //     return 1;
        // }

        // Criar a Migration
        $this->info('Criando Migration...');
        // $migrationCommand = 'module:migration ' . $model;
        // $runMigration = Artisan::call($migrationCommand);
        // if ($runMigration !== 0) {
        //     $this->error('Falha ao criar a Migration');
        //     return 1;
        // }

        // // Criar a Factory
        $this->info('Criando Factory...');
        $factoryCommand = 'module:factory ' . $model;
        $runFactory = Artisan::call($factoryCommand);
        if ($runFactory !== 0) {
            $this->error('Falha ao criar a Factory');
            return 1;
        }

        // Criar o Seeder
        $this->info('Criando Seeder...');
        $seederCommand = 'module:seeder ' . $model;
        $runSeeder = Artisan::call($seederCommand);
        if ($runSeeder !== 0) {
            $this->error('Falha ao criar o Seeder');
            return 1;
        }

        // Criar o Controller
        $this->info('Criando Controller...');
        $controllerCommand = 'module:controller ' . $model;
        $runController = Artisan::call($controllerCommand);
        if ($runController !== 0) {
            $this->error('Falha ao criar o Controller');
            return 1;
        }

        // Criar os Requests
        $this->info('Criando Store Request...');
        $storeRequestCommand = 'module:store-request ' . $model;
        $runStoreRequest = Artisan::call($storeRequestCommand);
        if ($runStoreRequest !== 0) {
            $this->error('Falha ao criar o Store Request');
            return 1;
        }

        $this->info('Criando Update Request...');
        $updateRequestCommand = 'module:update-request ' . $model;
        $runUpdateRequest = Artisan::call($updateRequestCommand);
        if ($runUpdateRequest !== 0) {
            $this->error('Falha ao criar o Update Request');
            return 1;
        }

        // Criar o Resource
        $this->info('Criando Resource...');
        $resourceCommand = 'module:resource ' . $model;
        $runResource = Artisan::call($resourceCommand);
        if ($runResource !== 0) {
            $this->error('Falha ao criar o Resource');
            return 1;
        }

        // // Criar a Collection
        // $this->info('Criando Collection...');
        // $collectionCommand = 'module:collection ' . $model;
        // $runCollection = Artisan::call($collectionCommand);
        // if ($runCollection !== 0) {
        //     $this->error('Falha ao criar a Collection');
        //     return 1;
        // }

        $this->info('---------------------------------');
        $this->info('✅ Módulo criado com sucesso!');
        $this->info('---------------------------------');
        $this->info('Lembretes:');
        $this->info('');
        $this->info('1. Em -->> routes/api.php');
        $this->info('');
        $this->info("/**");
        $this->info("* " . Str::plural($model) . "");
        $this->info(" */");
        $this->info("Route::apiResource('" . Str::plural(Str::lower($model)) . "', " . $model . "Controller::class);");
        $this->info("Route::get('" . Str::plural(Str::lower($model)) . "/search', [" . $model . "Controller::class, 'search'])->name('" . Str::plural(Str::lower($model)) . ".search');");
        $this->info('');
        $this->info('2. Em -->> app/Providers/AppServiceProvider.php (boot)');
        $this->info('');
        $this->info($model . "::observe(" . $model . "Observer::class);");
        $this->info('');
        $this->info('3. Em -->> app/Providers/AuthServiceProvider.php ($policies)');
        $this->info('');
        $this->info($model . "::class => " . $model . "Policy::class,");
        $this->info('');
        $this->info('4. Não se esqueça de executar as migrações e seeders:');
        $this->info('');
        $this->info('php artisan migrate');
        $this->info('php artisan db:seed --class=' . $model . 'Seeder');
        $this->info('');
        $this->info('---------------------------------');
        $this->info('Gerar Documentação da API:');
        $this->info('');
        $this->info('php artisan l5-swagger:generate');
        $this->info('');
        $this->info('Acesse a documentação em: /api/documentation');
        $this->info('---------------------------------');
    }

    /**
     * Valida e sugere o nome correto para o modelo.
     *
     * @param string $name
     * @return array
     */
    protected function validateModelName($name)
    {
        $issues = [];
        $isValid = true;

        // Verificar se começa com letra maiúscula
        if (!preg_match('/^[A-Z]/', $name)) {
            $issues[] = 'O nome do modelo deve começar com letra maiúscula';
            $isValid = false;
        }

        // Verificar se contém espaços ou caracteres especiais
        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            $issues[] = 'O nome do modelo não deve conter espaços ou caracteres especiais';
            $isValid = false;
        }

        // Verificar se está no plural
        if (Str::plural($name) === $name && Str::singular($name) !== $name) {
            $issues[] = 'O nome do modelo deve estar no singular';
            $isValid = false;
        }

        // Verificar se segue o padrão StudlyCase
        if ($name !== Str::studly($name)) {
            $issues[] = 'O nome do modelo deve seguir o padrão StudlyCase';
            $isValid = false;
        }

        // Criar sugestão de nome correto
        $suggestion = Str::studly(Str::singular($name));

        return [
            'isValid' => $isValid,
            'issues' => $issues,
            'suggestion' => $suggestion
        ];
    }
}
