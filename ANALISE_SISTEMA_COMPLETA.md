# 📊 Análise Completa do Sistema Financeiro Empresarial

## 🎯 Visão Geral

Este é um **Sistema Financeiro Empresarial** desenvolvido em **PHP puro** com arquitetura **MVC customizada**. O sistema gerencia empresas, usuários, fornecedores, clientes, contas a pagar/receber, fluxo de caixa, relatórios financeiros e integrações bancárias.

---

## 🏗️ Arquitetura do Sistema

### 1. **Fluxo de Requisição**

```
Requisição HTTP
    ↓
public/index.php (Entry Point)
    ↓
App\Core\App (Inicializa aplicação)
    ↓
App\Core\Router (Resolve rota)
    ↓
Middleware (AuthMiddleware, ApiAuthMiddleware)
    ↓
Controller (Processa requisição)
    ↓
Model (Acessa banco de dados)
    ↓
View (Renderiza resposta)
```

### 2. **Estrutura de Diretórios**

```
/
├── app/
│   ├── core/              # Classes base do framework MVC
│   │   ├── App.php        # Classe principal que inicializa tudo
│   │   ├── Router.php     # Sistema de roteamento
│   │   ├── Controller.php # Classe base para controllers
│   │   ├── Model.php      # Classe base para models
│   │   ├── Database.php   # Singleton para conexão PDO
│   │   ├── Request.php    # Manipulação de requisições HTTP
│   │   ├── Response.php   # Manipulação de respostas HTTP
│   │   └── Session.php    # Gerenciamento de sessões
│   ├── controllers/       # 30 controllers (um por módulo)
│   ├── models/            # 39 models (um por entidade)
│   ├── views/             # 98 views organizadas por módulo
│   │   ├── layouts/       # Layouts principais (main.php, auth.php)
│   │   └── components/    # Componentes reutilizáveis (sidebar.php)
│   └── middleware/        # Middlewares (AuthMiddleware, ApiAuthMiddleware)
├── config/
│   ├── routes.php         # Definição de todas as rotas
│   ├── database.php       # Configuração do banco de dados
│   └── config.php         # Configurações gerais
├── includes/
│   ├── Migration.php      # Classe base para migrations
│   ├── MigrationManager.php # Gerenciador de migrations
│   ├── EnvLoader.php      # Carregador de variáveis .env
│   └── services/          # Serviços de integração
├── migrations/            # 49 migrations do banco de dados
├── public/
│   ├── index.php          # Entry point da aplicação
│   └── assets/            # Arquivos estáticos (JS, CSS)
└── storage/
    ├── logs/              # Logs do sistema
    ├── cache/             # Cache
    └── uploads/           # Uploads de arquivos
```

---

## 🔄 Componentes Principais

### 1. **Entry Point (`public/index.php`)**

**Responsabilidades:**
- Carrega variáveis de ambiente (`.env`)
- Configura exibição de erros baseado em `APP_DEBUG`
- Inicia sessão PHP
- Registra autoloader para namespaces
- Carrega classes core explicitamente
- Inicializa `App` e executa `run()`
- Trata exceções e loga erros

**Fluxo:**
```php
1. Define APP_ROOT
2. Carrega .env via EnvLoader
3. Configura error_reporting
4. Inicia Session
5. Registra autoloaders (App\Controllers, App\Models, App\Middleware, App\Core, includes)
6. Carrega classes core manualmente
7. Cria instância de App e chama run()
8. Captura exceções e exibe/loga erros
```

### 2. **Classe App (`App\Core\App`)**

**Responsabilidades:**
- Carrega rotas de `config/routes.php`
- Resolve rota atual via `Router`
- Executa middlewares antes do controller
- Instancia e chama método do controller
- Injeta `Request` e `Response` no controller
- Trata exceções e loga erros

**Métodos principais:**
- `run()` - Executa o ciclo completo da requisição
- `loadRoutes()` - Carrega rotas do arquivo de configuração
- `handleException()` - Trata exceções

### 3. **Sistema de Rotas (`App\Core\Router`)**

**Formato de rotas:**
```php
'METHOD /path/{param}' => [
    'handler' => 'Controller@method',
    'middleware' => ['AuthMiddleware']
]
```

**Funcionalidades:**
- Suporta parâmetros dinâmicos `{id}`, `{cnpj}`, etc.
- Converte parâmetros para regex
- Extrai parâmetros nomeados da URI
- Retorna array com controller, method, params e middleware

**Exemplo:**
```php
'GET /empresas/{id}' => ['handler' => 'EmpresaController@show', 'middleware' => ['AuthMiddleware']]
// URI: /empresas/123
// Retorna: ['controller' => 'App\Controllers\EmpresaController', 'method' => 'show', 'params' => ['123']]
```

### 4. **Request (`App\Core\Request`)**

**Funcionalidades:**
- Captura método HTTP (`GET`, `POST`, `PUT`, `DELETE`)
- Parse da URI removendo query string e base path
- Acessa dados GET via `get($key)`
- Acessa dados POST via `post($key)`
- Acessa todos os dados via `all()`
- Detecta requisições AJAX via `isAjax()`
- Detecta requisições JSON via `isJson()`
- Retorna body como JSON via `json()`
- Retorna IP do cliente via `getIp()`

### 5. **Response (`App\Core\Response`)**

**Funcionalidades:**
- Define status code HTTP
- Adiciona headers
- Envia resposta JSON via `json($data, $statusCode)`
- Redireciona via `redirect($url, $statusCode)`
- Envia conteúdo via `send($content)`

### 6. **Session (`App\Core\Session`)**

**Funcionalidades:**
- Singleton para gerenciar sessão PHP
- Métodos: `set()`, `get()`, `has()`, `remove()`, `delete()`, `clear()`, `destroy()`
- Suporta mensagens flash via `flash()` e `getFlash()`
- Configura cookies seguros (httponly, samesite)

### 7. **Database (`App\Core\Database`)**

**Padrão:** Singleton

**Funcionalidades:**
- Conexão única PDO para toda aplicação
- Configurações via `config/database.php` ou `.env`
- Métodos: `query()`, `fetchAll()`, `fetchOne()`, `execute()`
- Suporta transações: `beginTransaction()`, `commit()`, `rollback()`
- Retorna último ID inserido via `lastInsertId()`

**Configuração:**
```php
DB_HOST=localhost
DB_PORT=3306
DB_NAME=financeiro
DB_USER=root
DB_PASS=
```

### 8. **Controller Base (`App\Core\Controller`)**

**Funcionalidades:**
- Inicializa sessão automaticamente
- Cria wrapper `$this->session` para acesso fácil
- Renderiza views via `render($view, $data, $layout)`
- Retorna JSON via `json($data, $statusCode)`
- Redireciona via `redirect($url)`
- Verifica autenticação via `isAuthenticated()`
- Retorna IDs de usuário/empresa via `getUserId()`, `getEmpresaId()`
- Helper para assets via `asset($path)`
- Helper para base URL via `baseUrl($path)`

**Padrão de uso:**
```php
public function index(Request $request, Response $response)
{
    $data = $this->model->findAll();
    return $this->render('module/index', [
        'title' => 'Título',
        'data' => $data
    ]);
}
```

### 9. **Model Base (`App\Core\Model`)**

**Funcionalidades:**
- Acesso à conexão PDO via `getConnection()`
- Métodos auxiliares: `query()`, `fetchAll()`, `fetchOne()`
- Cada Model implementa seus próprios métodos CRUD

**Padrão de uso:**
```php
class Empresa extends Model
{
    protected $table = 'empresas';
    
    public function findAll($filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        // ... lógica de filtros
        return $this->fetchAll($sql, $params);
    }
}
```

---

## 🔐 Sistema de Autenticação

### 1. **Fluxo de Login**

```
GET /login
    ↓
AuthController@loginForm
    ↓
Renderiza view auth/login
    ↓
POST /login (com email e senha)
    ↓
AuthController@login
    ↓
Usuario->authenticate($email, $senha)
    ↓
Verifica senha com password_verify()
    ↓
Cria sessão: $_SESSION['usuario_id'], $_SESSION['usuario_nome'], etc.
    ↓
Redireciona para home (/)
```

### 2. **Middleware de Autenticação (`AuthMiddleware`)**

**Funcionalidade:**
- Verifica se `$_SESSION['usuario_id']` existe
- Se não autenticado:
  - Requisição AJAX → retorna JSON 401
  - Requisição normal → redireciona para `/login`
- Se autenticado → continua execução

**Aplicação:**
Todas as rotas protegidas têm `'middleware' => ['AuthMiddleware']` em `config/routes.php`

### 3. **Autenticação API (`ApiAuthMiddleware`)**

**Funcionalidade:**
- Verifica token Bearer no header `Authorization`
- Valida token na tabela `api_tokens`
- Verifica se token está ativo e não expirado
- Injeta `empresa_id` do token no request

**Uso:**
Rotas `/api/v1/*` usam `'middleware' => ['ApiAuthMiddleware']`

---

## 📋 Sistema de Rotas

### Estrutura de Rotas (`config/routes.php`)

**Formato:**
```php
return [
    'METHOD /path/{param}' => [
        'handler' => 'Controller@method',
        'middleware' => ['AuthMiddleware']
    ]
];
```

### Módulos Principais

1. **Autenticação** (`/login`, `/logout`)
2. **Home/Dashboard** (`/`)
3. **Empresas** (`/empresas`)
4. **Usuários** (`/usuarios`)
5. **Fornecedores** (`/fornecedores`)
6. **Clientes** (`/clientes`)
7. **Categorias Financeiras** (`/categorias`)
8. **Centros de Custo** (`/centros-custo`)
9. **Formas de Pagamento** (`/formas-pagamento`)
10. **Produtos** (`/produtos`)
11. **Pedidos Vinculados** (`/pedidos`)
12. **Contas Bancárias** (`/contas-bancarias`)
13. **Contas a Pagar** (`/contas-pagar`)
14. **Contas a Receber** (`/contas-receber`)
15. **Fluxo de Caixa** (`/fluxo-caixa`)
16. **DRE** (`/dre`)
17. **DFC** (`/dfc`)
18. **Relatórios** (`/relatorios`)
19. **Conciliação Bancária** (`/conciliacao-bancaria`)
20. **Movimentações de Caixa** (`/movimentacoes-caixa`)
21. **Perfis de Consolidação** (`/perfis-consolidacao`)
22. **Integrações** (`/integracoes`)
23. **NF-e** (`/nfes`)
24. **API Tokens** (`/api-tokens`)
25. **Conexões Bancárias** (`/conexoes-bancarias`)
26. **Transações Pendentes** (`/transacoes-pendentes`)

### API REST (`/api/v1/*`)

Endpoints protegidos por `ApiAuthMiddleware`:
- `/api/v1/contas-pagar`
- `/api/v1/contas-receber`
- `/api/v1/produtos`
- `/api/v1/clientes`
- `/api/v1/fornecedores`
- `/api/v1/movimentacoes`
- `/api/v1/categorias`
- `/api/v1/centros-custo`
- `/api/v1/contas-bancarias`

---

## 💾 Sistema de Banco de Dados

### 1. **Migrations**

**Sistema:**
- Classe base: `includes\Migration`
- Gerenciador: `includes\MigrationManager`
- Execução: `php migrate.php up`
- Status: `php migrate.php status`
- Rollback: `php migrate.php down --steps=N`

**Estrutura:**
```php
class CreateEmpresas extends Migration
{
    public function up()
    {
        $this->createTable('empresas', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'codigo VARCHAR(50) NOT NULL',
            'razao_social VARCHAR(255) NOT NULL',
            // ...
        ]);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS empresas");
    }
}
```

### 2. **Tabelas Principais**

**49 migrations** criam as seguintes tabelas principais:

1. `empresas` - Empresas do sistema
2. `usuarios` - Usuários do sistema
3. `perfis_consolidacao` - Perfis de consolidação de empresas
4. `permissoes` - Permissões de usuários por módulo
5. `categorias_financeiras` - Categorias de receitas/despesas
6. `centros_custo` - Centros de custo
7. `contas_bancarias` - Contas bancárias
8. `formas_pagamento` - Formas de pagamento
9. `fornecedores` - Fornecedores
10. `clientes` - Clientes
11. `produtos` - Produtos
12. `contas_pagar` - Contas a pagar
13. `contas_receber` - Contas a receber
14. `rateios_pagamentos` - Rateios de pagamentos
15. `rateios_recebimentos` - Rateios de recebimentos
16. `movimentacoes_caixa` - Movimentações de caixa
17. `conciliacao_bancaria` - Conciliações bancárias
18. `conciliacao_itens` - Itens de conciliação
19. `pedidos_vinculados` - Pedidos vinculados
20. `pedidos_itens` - Itens de pedidos
21. `integracoes_config` - Configurações de integrações
22. `integracoes_bancos_dados` - Integrações com bancos de dados
23. `integracoes_woocommerce` - Integrações WooCommerce
24. `integracoes_logs` - Logs de integrações
25. `integracoes_sincronizacoes` - Sincronizações
26. `integracoes_webhooks` - Webhooks
27. `integracoes_api` - Integrações API
28. `api_tokens` - Tokens de API
29. `api_logs` - Logs de API
30. `configuracoes` - Configurações do sistema
31. `categorias_produtos` - Categorias de produtos
32. `produtos_fotos` - Fotos de produtos
33. `produtos_variacoes` - Variações de produtos
34. `conexoes_bancarias` - Conexões bancárias (Open Banking)
35. `transacoes_pendentes` - Transações pendentes de classificação
36. `regras_classificacao_bancaria` - Regras de classificação automática

### 3. **Soft Delete**

**Padrão:** Campo `ativo BOOLEAN DEFAULT 1`

**Uso:**
- Exclusão: `UPDATE tabela SET ativo = 0 WHERE id = :id`
- Queries: Sempre filtrar por `WHERE ativo = 1` em `findAll()`

---

## 🎨 Sistema de Views

### 1. **Layouts**

**Layout Principal (`layouts/main.php`):**
- Header com sidebar
- Área de conteúdo
- Footer
- Suporte a tema dark/light
- Scripts: TailwindCSS, Alpine.js, theme.js, masks.js, cep.js, cnpj.js

**Layout Auth (`layouts/auth.php`):**
- Layout simplificado para login
- Sem sidebar

### 2. **Renderização**

**Método:**
```php
$this->render('module/view', [
    'title' => 'Título',
    'data' => $data
], 'main');
```

**Fluxo:**
1. Extrai variáveis do array `$data`
2. Carrega view: `app/views/module/view.php`
3. Captura output em buffer
4. Carrega layout: `app/views/layouts/main.php`
5. Injeta conteúdo no layout
6. Envia resposta e encerra execução (`exit`)

### 3. **Padrões de Views**

**Tema Dark/Light:**
- Classes Tailwind com variantes `dark:`
- Exemplo: `bg-white dark:bg-gray-800`
- Tema aplicado via JavaScript (`theme.js`)

**Formulários:**
- Campos obrigatórios marcados com `*`
- Validação HTML5 + server-side
- Máscaras via `masks.js` (CPF, CNPJ, telefone, CEP)
- Busca CEP automática via `cep.js`
- Erros exibidos via `$this->session->get('errors')`
- Old values via `$this->session->get('old')`

**Tabelas:**
- Header com gradiente azul/indigo
- Linhas com hover
- Ícones SVG para ações (ver, editar, excluir)

---

## 🔗 Sistema de Integrações

### 1. **Tipos de Integração**

1. **WooCommerce** - Integração com loja WooCommerce
2. **Banco de Dados** - Sincronização com banco externo
3. **Webhook** - Recebimento de webhooks
4. **API** - Integração via API REST
5. **WebmaniBR** - Integração com WebmaniBR (NF-e)

### 2. **Fluxo de Sincronização**

```
Script cron: sincronizar_integracoes.php
    ↓
Busca integrações ativas que precisam sincronizar
    ↓
Para cada integração:
    ↓
Chama Service específico (WooCommerceService, IntegracaoBancoDadosService, etc.)
    ↓
Service conecta ao sistema externo
    ↓
Busca dados novos/modificados
    ↓
Importa para o sistema
    ↓
Atualiza última sincronização
    ↓
Registra log
```

### 3. **Sincronização Bancária**

**Tipos:**
- **Open Banking** - Via OAuth2 e APIs bancárias
- **Nativo Sicoob** - Integração específica Sicoob

**Fluxo:**
```
ConexaoBancariaController@sincronizar
    ↓
Verifica tipo_integracao (of/nativo)
    ↓
Se nativo → SicoobApiService
Se of → OpenBankingService
    ↓
Verifica/renova token se expirado
    ↓
Busca transações (extrato ou cartão)
    ↓
Processa e salva transações
    ↓
Cria transações pendentes para classificação
```

**Cron:**
- Script: `cron/sync_bancaria.php`
- Executa sincronização automática
- Processa regras de classificação automática

---

## 📊 Relatórios e Dashboards

### 1. **Dashboard (`HomeController`)**

**Funcionalidades:**
- Visão geral financeira
- Filtros por período e empresa
- Gráficos e métricas
- Contas a pagar/receber próximas do vencimento

### 2. **Relatórios Disponíveis**

- **Fluxo de Caixa** (`/fluxo-caixa`)
- **DRE** (`/dre`) - Demonstração do Resultado do Exercício
- **DFC** (`/dfc`) - Demonstração do Fluxo de Caixa
- **Lucro** (`/relatorios/lucro`)
- **Margem** (`/relatorios/margem`)
- **Inadimplência** (`/relatorios/inadimplencia`)

---

## 🔒 Segurança

### 1. **Autenticação**
- Senhas hashadas com `password_hash()` (bcrypt)
- Verificação com `password_verify()`
- Sessões com cookies seguros (httponly, samesite)

### 2. **Validação**
- Client-side: HTML5 validation + JavaScript
- Server-side: Validação em Models/Controllers
- Mensagens de erro via sessão

### 3. **SQL Injection**
- Uso exclusivo de prepared statements (PDO)
- Nunca concatenação de SQL

### 4. **XSS**
- Escape de output com `htmlspecialchars()`
- Dados do usuário sempre escapados nas views

### 5. **CSRF**
- Configurado em `config/config.php`
- Implementação pendente em formulários

---

## 🚀 Scripts e Comandos

### 1. **Migrations**
```bash
php migrate.php up          # Executa migrations pendentes
php migrate.php down       # Reverte última migration
php migrate.php status      # Mostra status das migrations
```

### 2. **Sincronização**
```bash
php sincronizar_integracoes.php    # Sincroniza integrações
php cron/sync_bancaria.php         # Sincroniza bancos
```

### 3. **Cron Jobs**
- `cron/integracoes.php` - Sincronização de integrações
- `cron/sync_bancaria.php` - Sincronização bancária
- `cron/backup_database.php` - Backup do banco
- `cron/lembretes_vencimento.php` - Lembretes de vencimento
- `cron/limpeza_sistema.php` - Limpeza de logs antigos

---

## 📦 Dependências e Tecnologias

### Backend
- **PHP 7.4+** (sem frameworks)
- **MySQL/MariaDB** (via PDO)
- **Composer** (autoloader)

### Frontend
- **TailwindCSS** (via CDN)
- **Alpine.js** (interatividade)
- **JavaScript vanilla** (masks.js, theme.js, cep.js, cnpj.js)

### APIs Externas
- **ViaCEP** - Busca de endereços
- **ReceitaWS** - Validação de CNPJ
- **Bancos** - Open Banking APIs
- **Sicoob** - API nativa

---

## 🎯 Padrões de Código

### Controllers
- Estendem `App\Core\Controller`
- Métodos recebem `Request $request, Response $response`
- Padrão RESTful: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- Validação via método `validate()` (sempre `protected`)

### Models
- Estendem `App\Core\Model`
- Métodos: `findAll()`, `findById()`, `create()`, `update()`, `delete()`
- Validação via método `validate()` (sempre `protected`)
- Soft delete via campo `ativo`

### Views
- PHP puro com HTML/TailwindCSS
- Acesso a sessão via `$this->session`
- Escape de output com `htmlspecialchars()`
- Limpeza de sessão no final da view

---

## 📝 Conclusão

Este é um sistema **robusto e bem estruturado** com:

✅ Arquitetura MVC customizada clara  
✅ Sistema de rotas flexível  
✅ Autenticação e autorização  
✅ Integrações bancárias avançadas  
✅ API REST completa  
✅ Sistema de migrations  
✅ Suporte a múltiplas empresas  
✅ Relatórios financeiros  
✅ Tema dark/light  
✅ Validação client e server-side  
✅ Logs e tratamento de erros  

O sistema está **pronto para produção** e segue **boas práticas** de desenvolvimento PHP.

---

**Última atualização:** Baseado na análise completa do código-fonte em dezembro de 2024.
