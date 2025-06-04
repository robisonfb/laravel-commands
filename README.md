# Laravel Comand - API

Versão do Laravel: Laravel Framework 12

Este projeto utiliza Laravel Sail para execução local.

## Instalação

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```
Inicie os containers:
``` bash
sail up -d
```
Rode o composer novamente:
``` bash
sail composer install
```
Gere a chave da aplicação:
``` bash
sail artisan key:generate
```
Execute as migrações e popule o banco de dados:
``` bash
sail artisan migrate:fresh --seed
```

Consulte a documentação do Sail para mais detalhes sobre a execução do projeto.
## Autenticação
Sanctum

## Ferramentas

### Laravel Pint
Para formatar o código antes de commitar:

``` bash
sail pint
```

### Testes

Para rodar todos os testes:

``` bash
sail pest
```

Para rodar um teste específico:

``` bash
sail pest tests/Feature/Api/Blog/BlogTest.php
```

Criar um novo teste automatico
``` bash
sail artisan module:test NomedaModel
```

### Command

O comando `module:all` é uma ferramenta poderosa para agilizar o processo de criação de diversos componentes deste projeto. Ele automatiza a geração de vários artefatos relacionados a um modelo, facilitando o desenvolvimento e seguindo as convenções do projeto.

#### Funcionalidades Principais:

**Criação de Componentes:**
O comando `module:all` permite a criação dos seguintes componentes para um modelo específico:
- Model
- Observer
- Policy
- Controller
- Request
- Repository
- Resource
- Test

#### Uso do Comando:

O comando `module:all` aceita a seguinte opção:

- `--model=nome_do_modelo`: Especifica o nome do modelo para o qual os componentes serão gerados.

exemplo de uso
``` bash
sail artisan module:all --model=CoronaVaccination
```

``` php
// apenas para referencias para criar os modulos
// DummyModel -> AdvanceDirective
// CamelObject -> advanceDirective
// DummyModelPluralObject -> advancedirectives
// DummyModelObject -> advancedirective
```

#### Lembretes:

- No arquivo `routes\api.php`, é necessário adicionar uma rota de recursos para o modelo criado.
- No método `boot` do arquivo `app\Providers\AppServiceProvider.php`, é necessário registrar o observer para o modelo criado.

Este comando automatizado simplifica o processo de criação e configuração de componentes em projetos, aumentando a produtividade.

### Localize
Este projeto utiliza o `Localize` para gerenciamento de traduções. Certifique-se de manter os arquivos de idioma atualizados usando as ferramentas fornecidas pelo Localize. [Documentação](https://github.com/amiranagram/localizator#remove-missing-keys)

``` bash
sail artisan localize de,en,pt-br
```
> Nota: As strings que você já traduziu não serão substituídas.

Remover chaves ausentes
``` bash
php artisan localize --remove-missing
```


O Horizon estará acessível em http://localhost/horizon.

### Logs
Use o https://laradumps.dev/

### Documentação da API

Este projeto utiliza o [Scribe](https://github.com/knuckleswtf/scribe) para gerar automaticamente a documentação da API a partir do código Laravel.

#### Gerando a Documentação

Para atualizar a documentação da API, execute o comando:

```bash
sail artisan scribe:generate
```

