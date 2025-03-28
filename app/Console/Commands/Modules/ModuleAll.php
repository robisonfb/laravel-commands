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

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!$model = $this->option('model')) {
            $this->info('MODEL parameter is not optional');

            return;
        }

        //        $command = 'module:model ' . $model;
        $command = 'make:model ' . $model . ' -f -m -s';
        $this->info('Make Model...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Model');
        }

        $command = 'make:observer ' . $model . 'Observer';
        $this->info('Make Observer...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Model');
        }

        $command = 'module:policy ' . $model;
        $this->info('Make Policy...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Policy');
        }

        $command = 'module:controller ' . $model;
        $this->info('Make Controller...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Controller');
        }

        $command = 'module:request ' . $model;
        $this->info('Make Request...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Request');
        }

        $command = 'module:repository ' . $model;
        $this->info('Make Repository...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Repository');
        }

        $command = 'module:resource ' . $model;
        $this->info('Make Resource...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Resource');
        }

        $command = 'module:test ' . $model;
        $this->info('Make Test...');
        $run = Artisan::call($command);

        if ($run !== 0) {
            $this->info('Failed Make Test');
        }

        $this->info('---------------------------------');
        $this->info('Reminders:');
        $this->info('');
        $this->info('In -->> routes\api.php');
        $this->info('');
        $this->info("/**");
        $this->info("* " . Str::plural($model) . "");
        $this->info(" */");
        $this->info("Route::apiResource('" . Str::plural(Str::lower($model)) . "', " . $model . "Controller::class);");
        $this->info('');
        $this->info('In -->> app\Providers\AppServiceProvider.php (boot)');
        $this->info('');
        $this->info($model . "::observe(" . $model . "Observer::class);");
        $this->info('');
        $this->info('---------------------------------');
    }
}
