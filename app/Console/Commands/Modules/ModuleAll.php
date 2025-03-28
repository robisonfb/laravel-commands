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
                            {--f|force : Sobrescrever arquivos existentes}
                            {--continue : Continuar mesmo se ocorrerem erros não críticos}
                            ';

    protected $description = 'Cria todos os arquivos do módulo incluindo model, controller, requests, tests, etc.';

    // Códigos de erro específicos
    const ERROR_ALREADY_EXISTS = 3;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Cabeçalho inicial
        $this->alert('🚀 Gerador Automático de Módulo Laravel');

        if (!$modelInput = $this->option('model')) {
            $this->error('O parâmetro MODEL é obrigatório');
            return 1;
        }

        // Validar e corrigir nome do modelo
        $validationResult = $this->validateModelName($modelInput);

        if (!$validationResult['isValid']) {
            $this->warn('⚠️ Problemas no nome do modelo');
            $this->line('Problemas encontrados:');

            foreach ($validationResult['issues'] as $issue) {
                $this->comment('- ' . $issue);
            }

            $this->line('Nome sugerido: ' . $validationResult['suggestion']);

            if (!$this->confirm('Deseja continuar usando o nome sugerido?', true)) {
                $this->error('❌ Operação cancelada pelo usuário.');
                return 1;
            }

            $model = $validationResult['suggestion'];
            $this->info('✅ Usando: ' . $model);
        } else {
            $model = $modelInput;
        }

        // Opções adicionais para os comandos
        $forceOption = $this->option('force') ? ' --force' : '';
        $continueOnError = $this->option('continue');

        // Array de componentes a serem criados
        $components = [
            ['Model', 'module:model', true],
            ['Migration', 'module:migration', true],
            ['Factory', 'module:factory', true],
            ['Observer', 'module:observer', true],
            ['Policy', 'module:policy', true],
            ['Seeder', 'module:seeder', false],
            ['Controller', 'module:controller', false],
            ['Store Request', 'module:store-request', false],
            ['Update Request', 'module:update-request', false],
            ['Resource', 'module:resource', false],
            ['Collection', 'module:collection', false],
            ['Test', 'module:test', false],
        ];

        $this->line('🔨 Criando componentes do módulo ' . $model . '...');

        $failedComponents = [];
        $skippedComponents = [];

        // Criar cada componente
        foreach ($components as $component) {
            $this->line('');
            $this->comment("Criando {$component[0]}...");

            $command = $component[1] . ' ' . $model . $forceOption;
            $this->line("Executando: $command");

            $runCommand = Artisan::call($command);

            // Verificar código de retorno
            if ($runCommand !== 0) {
                // Obter a saída do comando para verificar o erro específico
                $output = Artisan::output();

                // Se o arquivo já existe e não estamos forçando a sobrescrita
                if ($runCommand === self::ERROR_ALREADY_EXISTS && !$this->option('force')) {
                    $skippedComponents[] = $component[0];
                    $this->warn("⚠️ {$component[0]} já existe e foi ignorado. Use --force para sobrescrever.");

                    // Se for um componente crítico e não estamos continuando em erros
                    if ($component[2] && !$continueOnError) {
                        $this->error("❌ Um componente crítico já existe e não foi sobrescrito.");
                        $this->info("👉 Use --force para sobrescrever ou --continue para ignorar erros não críticos.");
                        return 1;
                    }
                } else {
                    $failedComponents[] = $component[0];
                    $this->error("❌ Falha ao criar {$component[0]}");
                    $this->line("Saída do comando: " . trim($output));

                    // Se for um componente crítico
                    if ($component[2] && !$continueOnError) {
                        $this->error("❌ Erro crítico na criação do módulo.");
                        $this->info("👉 Use --continue para ignorar erros não críticos e prosseguir.");
                        return 1;
                    }
                }
            } else {
                $this->info("✅ {$component[0]} criado com sucesso!");
            }
        }

        $this->line('');

        // Checagem final
        if (!empty($failedComponents)) {
            $this->warn('⚠️ Alguns componentes não foram criados devido a erros:');
            foreach ($failedComponents as $failed) {
                $this->comment('- ' . $failed);
            }
        }

        if (!empty($skippedComponents)) {
            $this->warn('⚠️ Alguns componentes já existiam e foram ignorados:');
            foreach ($skippedComponents as $skipped) {
                $this->comment('- ' . $skipped);
            }
            $this->line("👉 Use --force para sobrescrever arquivos existentes.");
        }

        $this->line('');
        $this->alert('✅ Módulo criado com sucesso! 🎉');

        // Seção de lembretes
        $this->line('');
        $this->comment('🔔 Próximos passos:');

        $this->table(['Tarefa', 'Comando / Instruções'], [
            ['Rotas API', "Route::apiResource('" . Str::plural(Str::lower($model)) . "', " . $model . "Controller::class);\n● Adicione no arquivo routes/api.php"],
            ['Observador', $model . "::observe(" . $model . "Observer::class);\n● Adicione no método boot() do AppServiceProvider ou em outro ServiceProvider apropriado"],
            ['Política', $model . "::class => " . $model . "Policy::class,\n● Adicione no array \$policies do AuthServiceProvider"],
            ['Migração', "php artisan migrate\n● Execute para criar a tabela no banco de dados"],
            ['Seeder', "php artisan db:seed --class=" . $model . "Seeder\n● Execute para popular a tabela com dados iniciais"],
            ['Documentação API', "php artisan l5-swagger:generate\n● Execute para gerar/atualizar a documentação da API"]
        ]);

        $this->line('');
        $this->comment('Acesse a documentação em: /api/documentation');

        return 0;
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
