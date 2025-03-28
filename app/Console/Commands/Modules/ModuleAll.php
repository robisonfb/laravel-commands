<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ModuleAll extends Command
{
    protected $signature = 'module:all
                            {--m|model= : The name of the model.}
                            ';

    protected $description = 'Create all module files including model, controller, requests, tests, etc.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!$model = $this->option('model')) {
            $this->error('MODEL parameter is required');
            return 1;
        }

        // Criar o Model com factory, migration e seeder
        $command = 'make:model ' . $model . ' -f -m -s';
        $this->info('Creating Model with migration, factory and seeder...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Model');
        }

        // Criar a tabela
        $command = 'module:table ' . $model;
        $this->info('Creating Table...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Table');
        }

        // Criar o Observer
        $command = 'module:observer ' . $model;
        $this->info('Creating Observer...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Observer');
        }

        // Criar a Policy
        $command = 'module:policy ' . $model;
        $this->info('Creating Policy...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Policy');
        }

        // Criar o Controller
        $command = 'module:controller ' . $model;
        $this->info('Creating Controller...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Controller');
        }

        // Criar os Requests (Store e Update)
        $command = 'module:storerequest ' . $model;
        $this->info('Creating Store Request...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Store Request');
        }

        $command = 'module:updaterequest ' . $model;
        $this->info('Creating Update Request...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Update Request');
        }

        // Criar o Repository
        $command = 'module:repository ' . $model;
        $this->info('Creating Repository...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Repository');
        }

        // Criar os Resources (Resource e Collection)
        $command = 'module:resource ' . $model;
        $this->info('Creating Resource...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Resource');
        }

        $command = 'module:collection ' . $model;
        $this->info('Creating Collection...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Collection');
        }

        // Criar o Factory
        $command = 'module:factory ' . $model;
        $this->info('Creating Factory...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Factory');
        }

        // Criar o Seeder
        $command = 'module:seeder ' . $model;
        $this->info('Creating Seeder...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Seeder');
        }

        // Criar os Testes
        $command = 'module:test ' . $model;
        $this->info('Creating Tests...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Tests');
        }

        // Criar o Event Listener
        $command = 'module:eventlistener ' . $model;
        $this->info('Creating Event Listener...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create Event Listener');
        }

        // Criar a Documentação API
        $command = 'module:apidoc ' . $model;
        $this->info('Creating API Documentation...');
        $run = Artisan::call($command);
        if ($run !== 0) {
            $this->error('Failed to create API Documentation');
        }

        $this->info('---------------------------------');
        $this->info('✅ Module created successfully!');
        $this->info('---------------------------------');
        $this->info('Reminders:');
        $this->info('');
        $this->info('1. In -->> routes/api.php');
        $this->info('');
        $this->info("/**");
        $this->info("* " . Str::plural($model) . "");
        $this->info(" */");
        $this->info("Route::apiResource('" . Str::plural(Str::lower($model)) . "', " . $model . "Controller::class);");
        $this->info("Route::get('" . Str::plural(Str::lower($model)) . "/search', [" . $model . "Controller::class, 'search'])->name('" . Str::plural(Str::lower($model)) . ".search');");
        $this->info('');
        $this->info('2. In -->> app/Providers/AppServiceProvider.php (boot)');
        $this->info('');
        $this->info($model . "::observe(" . $model . "Observer::class);");
        $this->info('');
        $this->info('3. In -->> app/Providers/AuthServiceProvider.php ($policies)');
        $this->info('');
        $this->info($model . "::class => " . $model . "Policy::class,");
        $this->info('');
        $this->info('4. Don\'t forget to run migrations and seeders:');
        $this->info('');
        $this->info('php artisan migrate');
        $this->info('php artisan db:seed --class=' . $model . 'Seeder');
        $this->info('');
        $this->info('---------------------------------');
        $this->info('Generate API Documentation:');
        $this->info('');
        $this->info('php artisan l5-swagger:generate');
        $this->info('');
        $this->info('Access documentation at: /api/documentation');
        $this->info('---------------------------------');
    }
}
