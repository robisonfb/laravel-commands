<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

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
        if (!$model = $this->option('model')) {
            $this->error('O parâmetro MODEL é obrigatório');
            return 1;
        }

        // Criar o Model com factory, migration e seeder
        $command = 'make:model ' . $model . ' -f -m -s';
        $this->info('Criando Model com migration, factory e seeder...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Model');
        }

        // Criar a tabela
        $command = 'module:table ' . $model;
        $this->info('Criando Tabela...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar a Tabela');
        }

        // Criar o Observer
        $command = 'module:observer ' . $model;
        $this->info('Criando Observer...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Observer');
        }

        // Criar a Policy
        $command = 'module:policy ' . $model;
        $this->info('Criando Policy...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar a Policy');
        }

        // Criar o Controller
        $command = 'module:controller ' . $model;
        $this->info('Criando Controller...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Controller');
        }

        // Criar os Requests (Store e Update)
        $command = 'module:storerequest ' . $model;
        $this->info('Criando Store Request...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Store Request');
        }

        $command = 'module:updaterequest ' . $model;
        $this->info('Criando Update Request...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Update Request');
        }

        // Criar o Repository
        $command = 'module:repository ' . $model;
        $this->info('Criando Repository...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Repository');
        }

        // Criar os Resources (Resource e Collection)
        $command = 'module:resource ' . $model;
        $this->info('Criando Resource...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Resource');
        }

        $command = 'module:collection ' . $model;
        $this->info('Criando Collection...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar a Collection');
        }

        // Criar o Factory
        $command = 'module:factory ' . $model;
        $this->info('Criando Factory...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar a Factory');
        }

        // Criar o Seeder
        $command = 'module:seeder ' . $model;
        $this->info('Criando Seeder...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Seeder');
        }

        // Criar os Testes
        $command = 'module:test ' . $model;
        $this->info('Criando Testes...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar os Testes');
        }

        // Criar o Event Listener
        $command = 'module:eventlistener ' . $model;
        $this->info('Criando Event Listener...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar o Event Listener');
        }

        // Criar a Documentação API
        $command = 'module:apidoc ' . $model;
        $this->info('Criando Documentação API...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Falha ao criar a Documentação API');
        }

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
}
