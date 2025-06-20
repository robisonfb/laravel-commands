<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Artisan, File};
use Illuminate\Support\{Str};

class ModuleAll extends Command
{
    protected $signature = 'module:all
                            {--m|model= : O nome do modelo}
                            {--f|force : Sobrescrever arquivos existentes}
                            {--continue : Continuar mesmo se ocorrerem erros não críticos}
                            ';

    protected $description = 'Cria todos os arquivos do módulo incluindo model, controller, requests, tests, etc.';

    // Códigos de erro específicos
    public const ERROR_ALREADY_EXISTS = 3;

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
        $forceOption     = $this->option('force') ? ' --force' : '';
        $continueOnError = $this->option('continue');
        $generateRoute   = true;

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
            // ['Test', 'module:test', true],
        ];

        $this->line('🔨 Criando componentes do módulo ' . $model . '...');

        $failedComponents  = [];
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

        // Adicionar rota automaticamente se a opção estiver habilitada
        if ($generateRoute) {
            $this->line('');
            $this->comment("Adicionando rota para {$model}...");

            $routeResult = $this->addRouteToApiFile($model);

            if ($routeResult === true) {
                $this->info("✅ Rota adicionada com sucesso ao arquivo routes/api.php!");
            } else {
                $this->error("❌ Falha ao adicionar rota: " . $routeResult);
            }
        }

        $this->line('');

        // Gerar coleção do Postman
        // $this->comment("Gerando coleção Postman para {$model}...");

        // Chamar o comando module:postman
        // $postmanCommand = "module:postman {$model}";

        // if ($this->option('force')) {
        //     $postmanCommand .= " --force";
        // }

        // $this->line("Executando: $postmanCommand");
        // $postmanResult = Artisan::call($postmanCommand);

        // Verificar resultado
        // if ($postmanResult === 0) {
        //     $this->info("✅ Coleção Postman gerada com sucesso em postman/{$model}Collection.json!");
        // } elseif ($postmanResult === ModulePostman::ERROR_ALREADY_EXISTS) {
        //     $this->warn("⚠️ Coleção Postman já existe e foi ignorada. Use --force para sobrescrever.");
        // } else {
        //     $this->error("❌ Falha ao gerar coleção Postman");
        // }

        // $this->line('');

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

        // Criamos um array com linhas separadoras entre cada item
        $tableData = [
            ['Rotas API', "Route::apiResource('" . Str::plural(Str::lower($model)) . "', " . $model . "Controller::class);\n● ✅ Já adicionado ao arquivo routes/api.php\n-------------------------------------------"],
            ['Observador', $model . "::observe(" . $model . "Observer::class);\n● Adicione no método boot() do AppServiceProvider ou em outro ServiceProvider apropriado\n-------------------------------------------"],
            ['Política', $model . "::class => " . $model . "Policy::class,\n● Adicione no array \$policies do AuthServiceProvider\n-------------------------------------------"],
            ['Migração', "php artisan migrate\n● Execute para criar a tabela no banco de dados\n-------------------------------------------"],
            ['Seeder', "php artisan db:seed --class=" . $model . "Seeder\n● Execute para popular a tabela com dados iniciais\n-------------------------------------------"],
            //['Coleção Postman', "Importe o arquivo postman/{$model}Collection.json no Postman\n● A coleção já foi gerada com todos os endpoints do recurso {$model}\n-------------------------------------------"],
            ['Testes', "php artisan test\n● Execute para rodar os testes do módulo\n-------------------------------------------"],
            ['Limpeza', "php artisan module:clean\n● Limpa arquivos temporários e caches do módulo\n-------------------------------------------"],
        ];

        $this->table(['Tarefa', 'Comando / Instruções'], $tableData);
        $this->line('');

        return 0;
    }

    /**
     * Adiciona a rota API Resource ao arquivo routes/api.php
     *
     * @param string $model Nome do modelo
     * @return bool|string True se sucesso, mensagem de erro se falha
     */
    protected function addRouteToApiFile($model)
    {
        $apiRoutesPath = base_path('routes/api.php');

        // Verificar se o arquivo existe
        if (!File::exists($apiRoutesPath)) {
            return "Arquivo routes/api.php não encontrado";
        }

        // Ler o conteúdo atual do arquivo
        $contents = File::get($apiRoutesPath);

        // Preparar o namespace do controller
        $controllerNamespace = "App\\Http\\Controllers\\{$model}\\{$model}Controller";
        $controllerImport    = "use {$controllerNamespace};";

        // Verificar se a importação já existe
        $importExists = Str::contains($contents, $controllerImport);

        // Montar a linha da rota
        $routeLine = "Route::apiResource('" . Str::plural(Str::lower($model)) . "', " . $model . "Controller::class);";

        // Verificar se a rota já existe
        if (Str::contains($contents, $routeLine)) {
            return "A rota já existe no arquivo";
        }

        // Se a importação não existe, adiciona ela
        if (!$importExists) {
            // Encontrar último use antes do Route::
            $lastUsePos = -1;
            $useMatches = [];
            preg_match_all('/^use .+;$/m', $contents, $useMatches);

            if (!empty($useMatches[0])) {
                $lastUse    = end($useMatches[0]);
                $lastUsePos = strrpos($contents, $lastUse) + strlen($lastUse);

                // Inserir após o último use
                $contents = substr($contents, 0, $lastUsePos) . "\n" . $controllerImport . substr($contents, $lastUsePos);
            } else {
                // Se não encontrou nenhum use, procura pelo final do namespace
                if (preg_match('/^namespace\s+[^;]+;/m', $contents, $namespaceMatch, PREG_OFFSET_CAPTURE)) {
                    $namespaceEndPos = $namespaceMatch[0][1] + strlen($namespaceMatch[0][0]);
                    $contents        = substr($contents, 0, $namespaceEndPos) . "\n\n" . $controllerImport . substr($contents, $namespaceEndPos);
                } else {
                    // Se nem namespace tem, adiciona depois do <?php
                    $phpPos = strpos($contents, "<?php");

                    if ($phpPos !== false) {
                        $contents = substr($contents, 0, $phpPos + 5) . "\n\n" . $controllerImport . substr($contents, $phpPos + 5);
                    }
                }
            }
        }

        // Encontrar a posição correta para inserir a rota
        $insertPos = strlen($contents);

        // Dividir o conteúdo em linhas para análise mais precisa
        $lines         = explode("\n", $contents);
        $v1GroupStart  = -1;
        $v1GroupEnd    = -1;
        $braceLevel    = 0;
        $insideV1Group = false;

        // Encontrar o início e fim do grupo Route::prefix('v1')
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            // Detectar início do grupo v1
            if (strpos($line, "Route::prefix('v1')->group(function ()") !== false) {
                $v1GroupStart  = $i;
                $insideV1Group = true;
                $braceLevel    = 0;

                continue;
            }

            if ($insideV1Group) {
                // Contar chaves para encontrar o fechamento correto
                $braceLevel += substr_count($line, '{');
                $braceLevel -= substr_count($line, '}');

                // Se chegamos ao nível 0 de chaves, encontramos o fim do grupo
                if ($braceLevel < 0) {
                    $v1GroupEnd = $i;

                    break;
                }
            }
        }

        // Se encontrou o grupo v1, inserir antes do fechamento
        if ($v1GroupStart !== -1 && $v1GroupEnd !== -1) {
            // Calcular a posição em caracteres até a linha de fechamento
            $insertPos = 0;

            for ($i = 0; $i < $v1GroupEnd; $i++) {
                $insertPos += strlen($lines[$i]) + 1; // +1 para o \n
            }

            // Preparar a formatação da nova rota
            $beforeInsert = substr($contents, 0, $insertPos);
            $afterInsert  = substr($contents, $insertPos);

            // Adicionar comentário explicativo e a rota com indentação adequada
            $indent     = "    "; // 4 espaços para estar dentro do grupo v1
            $routeBlock = "\n" . $indent . "// " . $model . " Routes (Guest only) \n" . $indent . $routeLine . "\n";

            // Inserir a nova rota na posição encontrada
            $newContents = $beforeInsert . $routeBlock . $afterInsert;
        } else {
            // Fallback: usar a lógica anterior se não encontrar o grupo v1
            $beforeInsert = substr($contents, 0, $insertPos);
            $afterInsert  = substr($contents, $insertPos);

            $prefix = "";
            $suffix = "";

            if (!empty($beforeInsert) && !str_ends_with(trim($beforeInsert), "\n")) {
                $prefix = "\n";
            }

            if (!empty(trim($afterInsert))) {
                $suffix = "\n";
            }

            $routeBlock  = $prefix . "\n// Rota para " . $model . "\n" . $routeLine . $suffix;
            $newContents = $beforeInsert . $routeBlock . $afterInsert;
        }

        // Salvar o arquivo
        try {
            File::put($apiRoutesPath, $newContents);

            return true;
        } catch (\Exception $e) {
            return "Erro ao salvar o arquivo: " . $e->getMessage();
        }
    }

    /**
     * Valida e sugere o nome correto para o modelo.
     *
     * @param string $name
     * @return array
     */
    protected function validateModelName($name)
    {
        $issues  = [];
        $isValid = true;

        // Verificar se começa com letra maiúscula
        if (!preg_match('/^[A-Z]/', $name)) {
            $issues[] = 'O nome do modelo deve começar com letra maiúscula';
            $isValid  = false;
        }

        // Verificar se contém espaços ou caracteres especiais
        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            $issues[] = 'O nome do modelo não deve conter espaços ou caracteres especiais';
            $isValid  = false;
        }

        // Verificar se está no plural
        if (Str::plural($name) === $name && Str::singular($name) !== $name) {
            $issues[] = 'O nome do modelo deve estar no singular';
            $isValid  = false;
        }

        // Verificar se segue o padrão StudlyCase
        if ($name !== Str::studly($name)) {
            $issues[] = 'O nome do modelo deve seguir o padrão StudlyCase';
            $isValid  = false;
        }

        // Criar sugestão de nome correto
        $suggestion = Str::studly(Str::singular($name));

        return [
            'isValid'    => $isValid,
            'issues'     => $issues,
            'suggestion' => $suggestion,
        ];
    }
}
