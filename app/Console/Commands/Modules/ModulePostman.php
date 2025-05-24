<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModulePostman extends Command
{
    /**
     * Define a assinatura do comando no Artisan
     *
     * @var string
     */
    protected $signature = 'module:postman
                        {name : Nome do modelo para o qual a coleção Postman será gerada}
                        {--f|force : Sobrescrever arquivos existentes}
                        ';

    /**
     * Descrição do propósito do comando
     *
     * @var string
     */
    protected $description = 'Gera uma coleção Postman para o modelo especificado';

    /**
     * O tipo de classe que está sendo gerada.
     *
     * @var string
     */
    protected $type = 'Postman Collection';

    /**
     * Códigos de erro específicos
     */
    public const ERROR_ALREADY_EXISTS = 3;

    /**
     * Executa o comando
     *
     * @return int
     */
    public function handle()
    {
        $model = $this->argument('name');

        // Verifica se o arquivo já existe
        $collectionPath = base_path('postman/' . $model . 'Collection.json');

        if (File::exists($collectionPath) && !$this->option('force')) {
            $this->error($this->type . ' já existe! Use --force para sobrescrever.');

            return self::ERROR_ALREADY_EXISTS;
        }

        // Criar diretório se não existir
        $directory = base_path('postman');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info('Diretório para coleções Postman criado: ' . $directory);
        }

        // Gerar a coleção
        $result = $this->generatePostmanCollection($model);

        if ($result === true) {
            $this->info($this->type . ' criada com sucesso em: postman/' . $model . 'Collection.json');

            return 0;
        } else {
            $this->error('Erro ao gerar a coleção Postman: ' . $result);

            return 1;
        }
    }

    /**
     * Gera um arquivo de coleção do Postman para as rotas do modelo
     *
     * @param string $model Nome do modelo
     * @return bool|string True se sucesso, mensagem de erro se falha
     */
    protected function generatePostmanCollection($model)
    {
        try {
            // Diretório para armazenar as coleções do Postman
            $directory = base_path('postman');

            // Nome do arquivo de saída
            $fileName = $directory . '/' . $model . 'Collection.json';

            // Obter o nome da tabela (plural e em minúsculas)
            $resourceName = Str::plural(Str::lower($model));

            // Definir URL base (pode ser configurável)
            $baseUrl = "{{base_url}}/api";

            // Verificar se o controlador existe
            $controllerPath   = app_path('Http/Controllers/' . $model . 'Controller.php');
            $controllerExists = File::exists($controllerPath);

            // Criar a estrutura da coleção do Postman
            $collection = [
                'info' => [
                    'name'        => "$model API",
                    '_postman_id' => Str::uuid()->toString(),
                    'description' => "Coleção de endpoints para o recurso $model",
                    'schema'      => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                ],
                'variable' => [
                    [
                        'key'   => 'base_url',
                        'value' => 'http://localhost:8000',
                        'type'  => 'string',
                    ],
                    [
                        'key'   => 'id',
                        'value' => '1',
                        'type'  => 'string',
                    ],
                ],
                'item' => [],
            ];

            // Se o controlador existir, tenta obter os métodos implementados
            $endpoints = [];

            if ($controllerExists) {
                $controllerContent = File::get($controllerPath);

                // Verifica quais métodos estão implementados
                $hasIndex   = preg_match('/public\s+function\s+index\s*\(/i', $controllerContent);
                $hasShow    = preg_match('/public\s+function\s+show\s*\(/i', $controllerContent);
                $hasStore   = preg_match('/public\s+function\s+store\s*\(/i', $controllerContent);
                $hasUpdate  = preg_match('/public\s+function\s+update\s*\(/i', $controllerContent);
                $hasDestroy = preg_match('/public\s+function\s+destroy\s*\(/i', $controllerContent);

                // Adicionar endpoints com base nos métodos encontrados
                if ($hasIndex) {
                    $endpoints[] = [
                        'name'        => "Listar todos os $resourceName",
                        'method'      => 'GET',
                        'url'         => "$baseUrl/$resourceName",
                        'path'        => ['api', $resourceName],
                        'description' => "Retorna todos os $resourceName cadastrados",
                    ];
                }

                if ($hasShow) {
                    $endpoints[] = [
                        'name'        => "Obter um $model específico",
                        'method'      => 'GET',
                        'url'         => "$baseUrl/$resourceName/{{id}}",
                        'path'        => ['api', $resourceName, '{{id}}'],
                        'description' => "Retorna os detalhes de um $model específico",
                    ];
                }

                if ($hasStore) {
                    $endpoints[] = [
                        'name'        => "Criar novo $model",
                        'method'      => 'POST',
                        'url'         => "$baseUrl/$resourceName",
                        'path'        => ['api', $resourceName],
                        'description' => "Cria um novo $model",
                    ];
                }

                if ($hasUpdate) {
                    $endpoints[] = [
                        'name'        => "Atualizar $model existente",
                        'method'      => 'PUT',
                        'url'         => "$baseUrl/$resourceName/{{id}}",
                        'path'        => ['api', $resourceName, '{{id}}'],
                        'description' => "Atualiza um $model existente",
                    ];
                }

                if ($hasDestroy) {
                    $endpoints[] = [
                        'name'        => "Remover $model",
                        'method'      => 'DELETE',
                        'url'         => "$baseUrl/$resourceName/{{id}}",
                        'path'        => ['api', $resourceName, '{{id}}'],
                        'description' => "Remove um $model do sistema",
                    ];
                }
            }

            // Se não encontrou métodos no controlador ou o controlador não existe, usa o padrão RESTful
            if (empty($endpoints)) {
                $endpoints = [
                    [
                        'name'        => "Listar todos os $resourceName",
                        'method'      => 'GET',
                        'url'         => "$baseUrl/$resourceName",
                        'path'        => ['api', $resourceName],
                        'description' => "Retorna todos os $resourceName cadastrados",
                    ],
                    [
                        'name'        => "Obter um $model específico",
                        'method'      => 'GET',
                        'url'         => "$baseUrl/$resourceName/{{id}}",
                        'path'        => ['api', $resourceName, '{{id}}'],
                        'description' => "Retorna os detalhes de um $model específico",
                    ],
                    [
                        'name'        => "Criar novo $model",
                        'method'      => 'POST',
                        'url'         => "$baseUrl/$resourceName",
                        'path'        => ['api', $resourceName],
                        'description' => "Cria um novo $model",
                    ],
                    [
                        'name'        => "Atualizar $model existente",
                        'method'      => 'PUT',
                        'url'         => "$baseUrl/$resourceName/{{id}}",
                        'path'        => ['api', $resourceName, '{{id}}'],
                        'description' => "Atualiza um $model existente",
                    ],
                    [
                        'name'        => "Remover $model",
                        'method'      => 'DELETE',
                        'url'         => "$baseUrl/$resourceName/{{id}}",
                        'path'        => ['api', $resourceName, '{{id}}'],
                        'description' => "Remove um $model do sistema",
                    ],
                ];
            }

            // Criar os itens da coleção com base nos endpoints
            foreach ($endpoints as $endpoint) {
                $item = [
                    'name'    => $endpoint['name'],
                    'request' => [
                        'method' => $endpoint['method'],
                        'header' => [
                            [
                                'key'   => 'Accept',
                                'value' => 'application/json',
                            ],
                        ],
                        'url' => [
                            'raw'  => $endpoint['url'],
                            'host' => ["{{base_url}}"],
                            'path' => $endpoint['path'],
                        ],
                        'description' => $endpoint['description'],
                    ],
                    'response' => [],
                ];

                // Adicionar Content-Type e body para POST e PUT
                if (in_array($endpoint['method'], ['POST', 'PUT'])) {
                    $item['request']['header'][] = [
                        'key'   => 'Content-Type',
                        'value' => 'application/json',
                    ];

                    $item['request']['body'] = [
                        'mode'    => 'raw',
                        'raw'     => $this->generateSampleRequestBody($model),
                        'options' => [
                            'raw' => [
                                'language' => 'json',
                            ],
                        ],
                    ];
                }

                $collection['item'][] = $item;
            }

            // Salvar o arquivo JSON da coleção
            File::put($fileName, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return true;
        } catch (\Exception $e) {
            return "Erro ao gerar a coleção do Postman: " . $e->getMessage();
        }
    }

    /**
     * Gera um corpo de requisição de exemplo com base no modelo
     *
     * @param string $model Nome do modelo
     * @return string JSON formatado com exemplo de requisição
     */
    protected function generateSampleRequestBody($model)
    {
        // Tentar obter os campos da migration do modelo
        $fields = $this->extractFieldsFromMigration($model);

        // Se não conseguir extrair os campos, usar exemplos genéricos
        if (empty($fields)) {
            // Criar corpo genérico baseado no nome do modelo
            $sampleData = [
                'name'        => 'Exemplo de ' . $model,
                'description' => 'Descrição de exemplo para ' . $model,
                'active'      => true,
                'created_at'  => date('Y-m-d H:i:s'),
            ];
        } else {
            $sampleData = [];

            foreach ($fields as $field => $type) {
                // Gerar valor de exemplo com base no tipo do campo
                switch ($type) {
                    case 'string':
                        $sampleData[$field] = "Exemplo de $field";

                        break;
                    case 'integer':
                    case 'bigInteger':
                        $sampleData[$field] = 1;

                        break;
                    case 'boolean':
                        $sampleData[$field] = true;

                        break;
                    case 'decimal':
                    case 'float':
                    case 'double':
                        $sampleData[$field] = 10.99;

                        break;
                    case 'date':
                        $sampleData[$field] = date('Y-m-d');

                        break;
                    case 'dateTime':
                        $sampleData[$field] = date('Y-m-d H:i:s');

                        break;
                    case 'json':
                        $sampleData[$field] = ['key' => 'value'];

                        break;
                    default:
                        $sampleData[$field] = "Valor para $field";
                }
            }
        }

        // Remover campos que não devem estar no corpo da requisição
        unset($sampleData['id']);
        unset($sampleData['created_at']);
        unset($sampleData['updated_at']);
        unset($sampleData['deleted_at']);

        return json_encode($sampleData, JSON_PRETTY_PRINT);
    }

    /**
     * Tenta extrair os campos da migration do modelo
     *
     * @param string $model Nome do modelo
     * @return array Associativo com nome do campo => tipo
     */
    protected function extractFieldsFromMigration($model)
    {
        // Nome da tabela no plural
        $tableName = Str::plural(Str::snake($model));

        // Procurar o arquivo de migração
        $migrationFiles = File::glob(database_path('migrations/*_create_' . $tableName . '_table.php'));

        if (empty($migrationFiles)) {
            return [];
        }

        // Pegar o arquivo mais recente
        $migrationFile = end($migrationFiles);
        $content       = File::get($migrationFile);

        // Extrair campos usando expressão regular
        $fields = [];
        preg_match_all('/\$table->(\w+)\(\'(\w+)\'\)/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $type          = $match[1];
            $name          = $match[2];
            $fields[$name] = $type;
        }

        return $fields;
    }
}
