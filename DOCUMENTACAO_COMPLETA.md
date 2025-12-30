# DOCUMENTAÇÃO COMPLETA - SISTEMA FINANCEIRO EMPRESARIAL

## 1. VISÃO GERAL DO SISTEMA

Sistema financeiro multi-empresa desenvolvido em PHP MVC + MySQL com:
- Gestão financeira completa (contas a pagar/receber, fluxo de caixa, DRE, DFC)
- Múltiplas empresas no mesmo banco de dados
- Integrações dinâmicas (bancos de dados externos, WooCommerce)
- Relatórios gerenciais avançados
- Conciliação bancária automatizada
- Controle de custos e receitas por projeto/produto
- **Regime de competência e regime de caixa** (data de competência)
- **Sistema inteligente de identificação de formas de pagamento**

---

## 2. ARQUITETURA E ESTRUTURA DE PASTAS

### 2.1. Arquitetura MVC

O sistema utiliza arquitetura **MVC (Model-View-Controller)** para separação de responsabilidades:

- **Model**: Lógica de negócio e acesso a dados (PDO)
- **View**: Apresentação (templates HTML com TailwindCSS)
- **Controller**: Coordenação entre Model e View, processa requisições

### 2.2. Estrutura de Pastas

```
financeiro/
├── app/                      # Aplicação principal (MVC)
│   ├── core/                 # Classes core do framework MVC
│   │   ├── App.php           # Classe principal da aplicação
│   │   ├── Controller.php    # Classe base para controllers
│   │   ├── Model.php         # Classe base para models
│   │   ├── Database.php      # Classe de conexão PDO
│   │   ├── Router.php        # Sistema de rotas
│   │   ├── Request.php       # Manipulação de requisições
│   │   ├── Response.php      # Manipulação de respostas
│   │   └── Session.php        # Gerenciamento de sessões
│   │
│   ├── controllers/          # Controllers (lógica de controle)
│   │   ├── AuthController.php
│   │   ├── EmpresaController.php
│   │   ├── ContaPagarController.php
│   │   ├── ContaReceberController.php
│   │   ├── FluxoCaixaController.php
│   │   ├── ConciliacaoController.php
│   │   ├── DREController.php
│   │   ├── DFCController.php
│   │   ├── CategoriaController.php
│   │   ├── CentroCustoController.php
│   │   ├── FormaPagamentoController.php
│   │   ├── IntegracaoController.php
│   │   ├── ProdutoController.php
│   │   ├── PedidoController.php
│   │   └── RelatorioController.php
│   │
│   ├── models/               # Models (lógica de negócio e dados)
│   │   ├── Empresa.php
│   │   ├── Usuario.php
│   │   ├── ContaPagar.php
│   │   ├── ContaReceber.php
│   │   ├── RateioPagamento.php
│   │   ├── RateioRecebimento.php
│   │   ├── MovimentacaoCaixa.php
│   │   ├── ConciliacaoBancaria.php
│   │   ├── CategoriaFinanceira.php
│   │   ├── CentroCusto.php
│   │   ├── ContaBancaria.php
│   │   ├── FormaPagamento.php
│   │   ├── FormaPagamentoPadrao.php
│   │   ├── Fornecedor.php
│   │   ├── Cliente.php
│   │   ├── Produto.php
│   │   ├── PedidoVinculado.php
│   │   └── Integracao.php
│   │
│   ├── views/                # Views (templates de apresentação)
│   │   ├── layouts/          # Layouts base
│   │   │   ├── main.php      # Layout principal
│   │   │   └── auth.php      # Layout de autenticação
│   │   ├── components/       # Componentes reutilizáveis
│   │   │   ├── header.php
│   │   │   ├── sidebar.php
│   │   │   ├── footer.php
│   │   │   ├── alert.php
│   │   │   └── pagination.php
│   │   ├── auth/             # Views de autenticação
│   │   ├── empresas/        # Views de empresas
│   │   ├── contas/           # Views de contas
│   │   ├── fluxo_caixa/      # Views de fluxo de caixa
│   │   ├── conciliacao/      # Views de conciliação
│   │   ├── dre/              # Views de DRE
│   │   ├── dfc/              # Views de DFC
│   │   └── ... (outras views)
│   │
│   └── middleware/           # Middlewares (autenticação, permissões, etc)
│       ├── AuthMiddleware.php
│       ├── PermissionMiddleware.php
│       └── CSRFMiddleware.php
│
├── config/                   # Configurações
│   ├── database.php          # Configurações de conexão PDO
│   ├── config.php            # Configurações gerais
│   ├── constants.php         # Constantes do sistema
│   └── routes.php            # Definição de rotas
│
├── includes/                 # Classes auxiliares
│   ├── helpers/              # Funções auxiliares
│   │   ├── functions.php     # Funções globais
│   │   ├── validations.php   # Validações
│   │   └── formata_dados.php # Formatação de dados
│   ├── services/             # Serviços (lógica complexa)
│   │   ├── AuthService.php
│   │   ├── PermissionService.php
│   │   ├── ConsolidacaoService.php
│   │   ├── RateioService.php
│   │   ├── ConciliacaoService.php
│   │   └── FormaPagamentoIAService.php
│   ├── repositories/         # Repositories (acesso a dados)
│   │   ├── EmpresaRepository.php
│   │   ├── ContaPagarRepository.php
│   │   └── ... (outros repositories)
│   ├── Migration.php         # Classe base para migrations
│   └── MigrationManager.php  # Gerenciador de migrations
│
├── public/                   # Arquivos públicos (web root)
│   ├── index.php             # Entry point da aplicação
│   ├── assets/               # Assets estáticos
│   │   ├── css/              # CSS compilado (TailwindCSS)
│   │   ├── js/               # JavaScript
│   │   └── images/           # Imagens
│   └── .htaccess             # Configuração Apache
│
├── migrations/               # Sistema de migrations
│   ├── 001_create_empresas.php
│   ├── 002_create_usuarios.php
│   └── ... (todas as migrations)
│
├── cron/                     # Scripts agendados
│   ├── sincronizar_bancos.php
│   ├── processar_webhooks.php
│   ├── gerar_relatorios.php
│   └── processar_formas_pagamento.php
│
├── api/                      # Endpoints API REST
│   ├── routes.php            # Rotas da API
│   ├── controllers/          # Controllers da API
│   └── middleware/           # Middlewares da API
│
├── storage/                  # Armazenamento
│   ├── logs/                 # Logs do sistema
│   ├── cache/                # Cache de arquivos
│   └── uploads/              # Uploads de arquivos
│
├── tests/                    # Testes (opcional)
│   ├── unit/
│   └── integration/
│
└── vendor/                   # Dependências Composer (se usar)
```

---

## 3. ARQUITETURA MVC DETALHADA

### 3.1. Fluxo de Requisição MVC

```
1. Requisição HTTP → public/index.php
2. Router → Identifica rota e controller
3. Middleware → Valida autenticação/permissões
4. Controller → Processa requisição
5. Model → Acessa dados via PDO
6. Controller → Recebe dados do Model
7. View → Renderiza template com dados
8. Response → Retorna HTML/JSON
```

### 3.2. Estrutura de Classes Core

**App.php** - Classe principal:
- Inicializa aplicação
- Carrega configurações
- Registra rotas
- Executa middleware
- Dispara controller

**Router.php** - Sistema de rotas:
- Define rotas (GET, POST, PUT, DELETE)
- Resolve URLs para controllers
- Suporta parâmetros dinâmicos
- Exemplo: `/empresas/{id}` → `EmpresaController::show($id)`

**Database.php** - Conexão PDO:
- Singleton pattern
- Prepared statements
- Transações
- Query builder básico (opcional)

**Controller.php** - Classe base:
- Métodos comuns (render, redirect, json)
- Validação de CSRF
- Acesso a Request/Response
- Helpers de autenticação

**Model.php** - Classe base:
- Métodos CRUD básicos
- Conexão com banco via Database
- Validações
- Relacionamentos

### 3.3. Exemplo de Controller

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Empresa;
use App\Services\EmpresaService;

class EmpresaController extends Controller
{
    protected $empresaService;
    
    public function __construct()
    {
        $this->empresaService = new EmpresaService();
    }
    
    public function index()
    {
        $empresas = Empresa::all();
        $this->render('empresas/index', ['empresas' => $empresas]);
    }
    
    public function store()
    {
        $data = $this->request->post();
        
        if ($this->empresaService->create($data)) {
            $this->redirect('/empresas')->with('success', 'Empresa criada!');
        } else {
            $this->redirect('/empresas')->with('error', 'Erro ao criar empresa');
        }
    }
}
```

### 3.4. Exemplo de Model

```php
<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Empresa extends Model
{
    protected $table = 'empresas';
    protected $fillable = ['codigo', 'razao_social', 'nome_fantasia', 'cnpj'];
    
    public static function findByCnpj($cnpj)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM empresas WHERE cnpj = :cnpj");
        $stmt->execute(['cnpj' => $cnpj]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function usuarios()
    {
        return Usuario::where('empresa_id', $this->id);
    }
}
```

### 3.5. Exemplo de View

```php
<?php
// views/empresas/index.php
$this->layout('layouts/main', ['title' => 'Empresas']); 
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Empresas</h1>
        <a href="/empresas/create" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Nova Empresa
        </a>
    </div>
    
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razão Social</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CNPJ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($empresas as $empresa): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($empresa['codigo']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($empresa['razao_social']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($empresa['cnpj']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
```

### 3.6. Sistema de Rotas

**config/routes.php**:
```php
<?php
return [
    // Autenticação
    'GET /login' => 'AuthController@loginForm',
    'POST /login' => 'AuthController@login',
    'POST /logout' => 'AuthController@logout',
    
    // Empresas
    'GET /empresas' => 'EmpresaController@index',
    'GET /empresas/create' => 'EmpresaController@create',
    'POST /empresas' => 'EmpresaController@store',
    'GET /empresas/{id}' => 'EmpresaController@show',
    'GET /empresas/{id}/edit' => 'EmpresaController@edit',
    'PUT /empresas/{id}' => 'EmpresaController@update',
    'DELETE /empresas/{id}' => 'EmpresaController@destroy',
    
    // Contas a Pagar
    'GET /contas-pagar' => 'ContaPagarController@index',
    'POST /contas-pagar' => 'ContaPagarController@store',
    // ... outras rotas
];
```

### 3.7. Middleware

**Exemplo de Middleware de Autenticação**:
```php
<?php
namespace App\Middleware;

use App\Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function handle($request, $next)
    {
        if (!isset($_SESSION['usuario_id'])) {
            return redirect('/login');
        }
        
        return $next($request);
    }
}
```

### 3.8. Repository Pattern (Opcional)

Para acesso complexo a dados, pode-se usar Repository:

```php
<?php
namespace App\Repositories;

use App\Core\Database;

class EmpresaRepository
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function findWithUsuarios($id)
    {
        $sql = "SELECT e.*, u.nome as usuario_nome 
                FROM empresas e 
                LEFT JOIN usuarios u ON u.empresa_id = e.id 
                WHERE e.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

---

## 4. ESTRUTURA DO BANCO DE DADOS

### 3.1. TABELAS PRINCIPAIS

**empresas**
- id (PK, INT, AUTO_INCREMENT)
- codigo (VARCHAR(20), UNIQUE)
- razao_social (VARCHAR(255))
- nome_fantasia (VARCHAR(255))
- cnpj (VARCHAR(18), UNIQUE)
- grupo_empresarial_id (INT, nullable) - Para agrupar empresas relacionadas
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)
- configuracoes (JSON) - configurações específicas da empresa

**usuarios**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT, nullable) - NULL = acesso a todas empresas
- nome (VARCHAR(255))
- email (VARCHAR(255), UNIQUE)
- senha (VARCHAR(255)) - password_hash
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)
- ultimo_acesso (DATETIME, nullable)
- empresas_consolidadas_padrao (JSON, nullable) - IDs das empresas para consolidação padrão

**perfis_consolidacao**
- id (PK, INT, AUTO_INCREMENT)
- usuario_id (FK, INT, nullable) - NULL = perfil compartilhado
- nome (VARCHAR(255))
- empresas_ids (JSON) - Array de IDs das empresas a consolidar
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**permissoes**
- id (PK, INT, AUTO_INCREMENT)
- usuario_id (FK, INT)
- modulo (VARCHAR(50))
- acao (VARCHAR(50)) - criar, editar, excluir, visualizar
- empresa_id (FK, INT, nullable) - NULL = todas empresas

**categorias_financeiras**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- codigo (VARCHAR(20))
- nome (VARCHAR(255))
- tipo (ENUM('receita', 'despesa'))
- categoria_pai_id (FK, INT, nullable)
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**centros_custo**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- codigo (VARCHAR(20))
- nome (VARCHAR(255))
- centro_pai_id (FK, INT, nullable)
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**contas_bancarias**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- banco_codigo (VARCHAR(10))
- banco_nome (VARCHAR(255))
- agencia (VARCHAR(20))
- conta (VARCHAR(20))
- tipo_conta (ENUM('corrente', 'poupanca', 'investimento'))
- saldo_inicial (DECIMAL(15,2), DEFAULT 0)
- saldo_atual (DECIMAL(15,2), DEFAULT 0)
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**contas_pagar**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT) - Empresa principal responsável pela conta
- fornecedor_id (FK, INT, nullable)
- categoria_id (FK, INT)
- centro_custo_id (FK, INT, nullable)
- numero_documento (VARCHAR(100))
- descricao (TEXT)
- valor_total (DECIMAL(15,2))
- valor_pago (DECIMAL(15,2), DEFAULT 0)
- **data_emissao (DATE)** - Data de emissão do documento
- **data_competencia (DATE)** - Data de competência (regime de competência)
- data_vencimento (DATE)
- data_pagamento (DATE, nullable)
- status (ENUM('pendente', 'pago', 'vencido', 'cancelado', 'parcial'))
- forma_pagamento_id (FK, INT, nullable) - Vinculado à tabela formas_pagamento
- conta_bancaria_id (FK, INT, nullable)
- **tem_rateio (BOOLEAN, DEFAULT 0)** - Indica se possui rateio entre empresas
- observacoes (TEXT, nullable)
- data_cadastro (DATETIME)
- usuario_cadastro_id (FK, INT)

**contas_receber**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT) - Empresa principal responsável pela conta
- cliente_id (FK, INT, nullable)
- categoria_id (FK, INT)
- centro_custo_id (FK, INT, nullable)
- numero_documento (VARCHAR(100))
- descricao (TEXT)
- valor_total (DECIMAL(15,2))
- valor_recebido (DECIMAL(15,2), DEFAULT 0)
- **data_emissao (DATE)** - Data de emissão do documento
- **data_competencia (DATE)** - Data de competência (regime de competência)
- data_vencimento (DATE)
- data_recebimento (DATE, nullable)
- status (ENUM('pendente', 'recebido', 'vencido', 'cancelado', 'parcial'))
- forma_recebimento_id (FK, INT, nullable) - Vinculado à tabela formas_pagamento
- conta_bancaria_id (FK, INT, nullable)
- **tem_rateio (BOOLEAN, DEFAULT 0)** - Indica se possui rateio entre empresas
- observacoes (TEXT, nullable)
- data_cadastro (DATETIME)
- usuario_cadastro_id (FK, INT)

**rateios_pagamentos** - Rateio de pagamentos entre empresas
- id (PK, INT, AUTO_INCREMENT)
- conta_pagar_id (FK, INT) - Conta a pagar original
- empresa_id (FK, INT) - Empresa que participa do rateio
- valor_rateio (DECIMAL(15,2)) - Valor que esta empresa pagou/é responsável
- percentual (DECIMAL(5,2)) - Percentual do valor total (0-100)
- data_competencia (DATE) - Data de competência específica deste rateio
- observacoes (TEXT, nullable)
- data_cadastro (DATETIME)
- usuario_cadastro_id (FK, INT)
- FOREIGN KEY (conta_pagar_id) REFERENCES contas_pagar(id) ON DELETE CASCADE
- FOREIGN KEY (empresa_id) REFERENCES empresas(id)
- CHECK (valor_rateio >= 0)
- CHECK (percentual >= 0 AND percentual <= 100)

**rateios_recebimentos** - Rateio de recebimentos entre empresas
- id (PK, INT, AUTO_INCREMENT)
- conta_receber_id (FK, INT) - Conta a receber original
- empresa_id (FK, INT) - Empresa que participa do rateio
- valor_rateio (DECIMAL(15,2)) - Valor que esta empresa recebeu/é responsável
- percentual (DECIMAL(5,2)) - Percentual do valor total (0-100)
- data_competencia (DATE) - Data de competência específica deste rateio
- observacoes (TEXT, nullable)
- data_cadastro (DATETIME)
- usuario_cadastro_id (FK, INT)
- FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE CASCADE
- FOREIGN KEY (empresa_id) REFERENCES empresas(id)
- CHECK (valor_rateio >= 0)
- CHECK (percentual >= 0 AND percentual <= 100)

**formas_pagamento**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- codigo (VARCHAR(20))
- nome (VARCHAR(255))
- tipo (ENUM('pagamento', 'recebimento', 'ambos'))
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**formas_pagamento_padroes** - Tabela para aprendizado automático
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- forma_pagamento_id (FK, INT)
- origem (VARCHAR(100)) - origem do pagamento (ex: "BANCO DO BRASIL", "PIX", "TED", etc)
- descricao_padrao (VARCHAR(255), nullable) - padrão de descrição
- fornecedor_id (FK, INT, nullable) - se sempre vem do mesmo fornecedor
- cliente_id (FK, INT, nullable) - se sempre vem do mesmo cliente
- categoria_id (FK, INT, nullable) - categoria mais comum
- confianca (DECIMAL(5,2), DEFAULT 0) - porcentagem de acerto (0-100)
- quantidade_uso (INT, DEFAULT 0) - quantas vezes foi usado
- quantidade_acerto (INT, DEFAULT 0) - quantas vezes acertou
- ultimo_uso (DATETIME, nullable)
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**movimentacoes_caixa**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- tipo (ENUM('entrada', 'saida'))
- categoria_id (FK, INT)
- centro_custo_id (FK, INT, nullable)
- conta_bancaria_id (FK, INT)
- descricao (TEXT)
- valor (DECIMAL(15,2))
- data_movimentacao (DATE) - Data real do movimento (regime de caixa)
- data_competencia (DATE, nullable) - Data de competência (se vinculado a conta pagar/receber)
- conciliado (BOOLEAN, DEFAULT 0)
- conciliacao_id (FK, INT, nullable)
- referencia_id (FK, INT, nullable) - ID da conta pagar/receber vinculada
- referencia_tipo (VARCHAR(20), nullable) - 'conta_pagar' ou 'conta_receber'
- forma_pagamento_id (FK, INT, nullable)
- origem_movimento (VARCHAR(100), nullable) - origem identificada (ex: "BANCO DO BRASIL", "PIX", etc)
- observacoes (TEXT, nullable)
- data_cadastro (DATETIME)

**conciliacao_bancaria**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- conta_bancaria_id (FK, INT)
- data_inicio (DATE)
- data_fim (DATE)
- saldo_extrato (DECIMAL(15,2))
- saldo_sistema (DECIMAL(15,2))
- diferenca (DECIMAL(15,2))
- status (ENUM('aberta', 'fechada'))
- observacoes (TEXT, nullable)
- data_cadastro (DATETIME)

**conciliacao_itens**
- id (PK, INT, AUTO_INCREMENT)
- conciliacao_id (FK, INT)
- movimentacao_id (FK, INT, nullable)
- descricao_extrato (TEXT)
- valor_extrato (DECIMAL(15,2))
- data_extrato (DATE)
- tipo_extrato (ENUM('credito', 'debito'))
- vinculado (BOOLEAN, DEFAULT 0)
- forma_pagamento_sugerida_id (FK, INT, nullable) - Sugestão do sistema
- observacoes (TEXT, nullable)

**fornecedores**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- tipo (ENUM('fisica', 'juridica'))
- nome_razao_social (VARCHAR(255))
- cpf_cnpj (VARCHAR(18))
- email (VARCHAR(255), nullable)
- telefone (VARCHAR(20), nullable)
- endereco (JSON, nullable)
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**clientes**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- tipo (ENUM('fisica', 'juridica'))
- nome_razao_social (VARCHAR(255))
- cpf_cnpj (VARCHAR(18))
- email (VARCHAR(255), nullable)
- telefone (VARCHAR(20), nullable)
- endereco (JSON, nullable)
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**produtos**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- codigo (VARCHAR(50))
- nome (VARCHAR(255))
- descricao (TEXT, nullable)
- custo_unitario (DECIMAL(15,2), DEFAULT 0)
- preco_venda (DECIMAL(15,2), DEFAULT 0)
- unidade_medida (VARCHAR(20), DEFAULT 'UN')
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**pedidos_vinculados**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- origem (VARCHAR(50)) - 'woocommerce', 'externo', etc
- origem_id (VARCHAR(100)) - ID no sistema origem
- numero_pedido (VARCHAR(100))
- cliente_id (FK, INT, nullable)
- data_pedido (DATETIME)
- data_atualizacao (DATETIME)
- status (VARCHAR(50))
- valor_total (DECIMAL(15,2))
- valor_custo_total (DECIMAL(15,2))
- dados_origem (JSON) - dados completos do pedido
- sincronizado_em (DATETIME)

**pedidos_itens**
- id (PK, INT, AUTO_INCREMENT)
- pedido_id (FK, INT)
- produto_id (FK, INT, nullable)
- codigo_produto_origem (VARCHAR(100))
- nome_produto (VARCHAR(255))
- quantidade (DECIMAL(10,3))
- valor_unitario (DECIMAL(15,2))
- valor_total (DECIMAL(15,2))
- custo_unitario (DECIMAL(15,2))
- custo_total (DECIMAL(15,2))

### 3.2. TABELAS DE INTEGRAÇÕES

**integracoes_config**
- id (PK, INT, AUTO_INCREMENT)
- empresa_id (FK, INT)
- tipo (ENUM('banco_dados', 'woocommerce', 'api'))
- nome (VARCHAR(255))
- ativo (BOOLEAN, DEFAULT 1)
- configuracoes (JSON)
- ultima_sincronizacao (DATETIME, nullable)
- proxima_sincronizacao (DATETIME, nullable)
- intervalo_sincronizacao (INT) - minutos
- data_cadastro (DATETIME)

**integracoes_bancos_dados**
- id (PK, INT, AUTO_INCREMENT)
- integracao_id (FK, INT)
- nome_conexao (VARCHAR(255))
- tipo_banco (ENUM('mysql', 'postgresql', 'sqlserver', 'oracle'))
- host (VARCHAR(255))
- porta (INT)
- database (VARCHAR(255))
- usuario (VARCHAR(255))
- senha (TEXT) - criptografada
- tabela_origem (VARCHAR(255))
- colunas_selecionadas (JSON)
- condicoes (JSON) - WHERE conditions
- mapeamento_colunas (JSON) - mapeamento para tabelas do sistema
- tabela_destino (VARCHAR(100)) - qual tabela do sistema receberá os dados
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**integracoes_woocommerce**
- id (PK, INT, AUTO_INCREMENT)
- integracao_id (FK, INT)
- url_site (VARCHAR(255))
- consumer_key (VARCHAR(255))
- consumer_secret (VARCHAR(255))
- webhook_secret (VARCHAR(255))
- eventos_webhook (JSON) - ['order.created', 'order.updated']
- sincronizar_produtos (BOOLEAN, DEFAULT 1)
- sincronizar_pedidos (BOOLEAN, DEFAULT 1)
- empresa_vinculada_id (FK, INT)
- ativo (BOOLEAN, DEFAULT 1)
- data_cadastro (DATETIME)

**integracoes_logs**
- id (PK, INT, AUTO_INCREMENT)
- integracao_id (FK, INT)
- tipo (ENUM('sucesso', 'erro', 'aviso'))
- mensagem (TEXT)
- dados (JSON, nullable)
- data_execucao (DATETIME)

**integracoes_sincronizacoes**
- id (PK, INT, AUTO_INCREMENT)
- integracao_id (FK, INT)
- tipo_sincronizacao (VARCHAR(100))
- registros_processados (INT, DEFAULT 0)
- registros_inseridos (INT, DEFAULT 0)
- registros_atualizados (INT, DEFAULT 0)
- registros_erros (INT, DEFAULT 0)
- tempo_execucao (INT) - segundos
- status (ENUM('sucesso', 'erro', 'parcial'))
- data_inicio (DATETIME)
- data_fim (DATETIME, nullable)
- log_erros (JSON, nullable)

---

## 5. MÓDULOS E FUNCIONALIDADES DETALHADAS

### 4.1. MÓDULO DE EMPRESAS
- CRUD completo de empresas
- Troca de contexto (seleção da empresa ativa na sessão)
- **Seleção múltipla de empresas para consolidação**
- Configurações específicas por empresa
- Filtros e relatórios por empresa
- Controle de acesso multi-empresa
- **Visualização consolidada de dados de múltiplas empresas**

### 4.2. MÓDULO DE CONTAS A PAGAR/RECEBER

#### 4.2.1. Campos Principais
- **Data de Emissão**: Data em que o documento foi emitido
- **Data de Competência**: Data em que o fato gerador ocorreu (regime de competência)
- **Data de Vencimento**: Data prevista para pagamento/recebimento
- **Data de Pagamento/Recebimento**: Data real do pagamento/recebimento (regime de caixa)

#### 4.2.2. Funcionalidades
- CRUD completo com todas as datas
- Baixa parcial/total com data de pagamento/recebimento
- Duplicação de títulos
- Agendamento de pagamentos recorrentes
- **Sistema de Rateio entre Empresas**:
  - Opção de ratear um pagamento/recebimento entre múltiplas empresas
  - Interface para definir qual empresa pagou/recebeu qual parte (valor ou percentual)
  - Validação automática: soma dos rateios deve ser igual ao valor total
  - Cada empresa pode ter data de competência diferente no rateio
  - Visualização dos rateios na tela de detalhes da conta
  - Edição e exclusão de rateios (com validações)
  - Relatórios consideram os rateios por empresa
- Filtros avançados:
  - Por período (emissão, competência, vencimento, pagamento)
  - Status, categoria, centro de custo
  - Fornecedor/cliente
  - Forma de pagamento
  - **Seleção de múltiplas empresas para consolidação**
  - **Filtrar contas com rateio**
- Relatórios:
  - Títulos a vencer
  - Títulos vencidos
  - Pagos/recebidos por período
  - **Relatório por competência** (DRE) - Considera rateios por empresa
  - **Relatório por caixa** (fluxo real) - Considera rateios por empresa
  - **Relatórios consolidados** (múltiplas empresas) - Considera rateios
  - **Relatório de Rateios**: Lista todos os rateios por empresa
- Exportação para Excel/PDF
- **Sugestão automática de forma de pagamento** baseada em aprendizado

#### 4.2.3. Sistema de Rateio entre Empresas

**4.2.3.1. Conceito**
- Permite dividir um pagamento ou recebimento entre múltiplas empresas
- Cada empresa participa com um valor ou percentual específico
- Útil para despesas compartilhadas, receitas compartilhadas, ou quando uma empresa paga em nome de outras

**4.2.3.2. Funcionalidades do Rateio**
- **Criar Rateio**: 
  - Ao cadastrar ou editar uma conta a pagar/receber, opção "Ratear entre Empresas"
  - Interface para adicionar empresas participantes
  - Definir valor ou percentual para cada empresa
  - Validação automática: soma dos rateios deve ser igual ao valor total
  - Cada empresa pode ter data de competência diferente no rateio
- **Visualizar Rateios**:
  - Na tela de detalhes da conta, mostra todos os rateios vinculados
  - Lista empresas participantes, valores e percentuais
  - Indica qual empresa é a principal (empresa_id da conta)
- **Editar Rateios**:
  - Permite alterar valores/percentuais dos rateios
  - Adicionar ou remover empresas do rateio
  - Validação sempre mantida (soma = valor total)
- **Excluir Rateios**:
  - Permite remover rateios individuais ou todos
  - Ao excluir todos, a conta volta ao estado normal (sem rateio)
- **Validações**:
  - Soma dos valores dos rateios deve ser igual ao valor total da conta
  - Soma dos percentuais deve ser igual a 100%
  - Não permite valores negativos
  - Não permite percentuais fora do intervalo 0-100%

**4.2.3.3. Impacto nos Relatórios**
- **DRE por Competência**: Considera os rateios, mostrando cada parte na empresa correta
- **Fluxo de Caixa**: Considera os rateios na consolidação
- **Relatórios Consolidados**: Rateios são automaticamente considerados
- **Relatório de Rateios**: Lista todos os rateios realizados, permitindo análise por empresa

**4.2.3.4. Exemplo Prático**
- Conta a Pagar: R$ 10.000,00 (Empresa A)
- Rateio:
  - Empresa A: R$ 6.000,00 (60%)
  - Empresa B: R$ 4.000,00 (40%)
- Nos relatórios:
  - Empresa A: mostra R$ 6.000,00
  - Empresa B: mostra R$ 4.000,00
  - Consolidado: mostra R$ 10.000,00 (sem duplicação)

#### 4.2.4. Regime de Competência vs Caixa
- **Regime de Competência**: Relatórios baseados em `data_competencia`
  - DRE (Demonstração do Resultado do Exercício)
  - Análise de receitas/despesas quando ocorreram
  - **Considera rateios**: Cada parte do rateio usa sua própria data de competência
- **Regime de Caixa**: Relatórios baseados em `data_pagamento`/`data_recebimento`
  - Fluxo de Caixa Real
  - Conciliação bancária
  - Saldo disponível
  - **Considera rateios**: Rateios são considerados na consolidação

### 4.3. MÓDULO DE FLUXO DE CAIXA

#### 4.3.1. Tipos de Fluxo de Caixa
- **Fluxo Real (Regime de Caixa)**: Baseado em `data_pagamento`/`data_recebimento`
  - Mostra entradas e saídas reais
  - Saldo disponível no banco
  - Projeção baseada em títulos a receber/pagar
  
- **Fluxo por Competência**: Baseado em `data_competencia`
  - Mostra quando os fatos ocorreram
  - Usado para análise gerencial
  - Comparação com orçamento

#### 4.3.2. Funcionalidades
- Projeção por período (diário, semanal, mensal, anual)
- Entradas e saídas previstas e realizadas
- Saldo inicial e final
- Gráficos de evolução
- Comparativo entre períodos
- Filtros: empresa, conta bancária, período, categoria
- **Consolidação de múltiplas empresas** (soma de fluxos)
- Exportação Excel/PDF
- Dashboard com indicadores

### 4.4. MÓDULO DE CONCILIAÇÃO BANCÁRIA

#### 4.4.1. Funcionalidades
- Importação de extrato (OFX, CSV, TXT)
- Conciliação manual e automática
- Regras de matching:
  - Por valor (± tolerância configurável)
  - Por data (± dias configurável)
  - Por descrição (similaridade de texto)
- Saldo inicial e final do período
- Diferenças e ajustes
- Histórico de conciliações
- Relatório de não conciliados
- **Identificação automática de forma de pagamento** nos extratos
- Sugestão de vinculação baseada em aprendizado

#### 4.4.2. Processo de Conciliação
1. Importar extrato bancário
2. Sistema identifica movimentações no período
3. **Sistema sugere forma de pagamento** para cada item
4. Matching automático com movimentações do sistema
5. Usuário revisa e confirma matches
6. Concilia manualmente itens não matchados
7. Confirma ou corrige forma de pagamento sugerida
8. Fechar conciliação
9. Gerar relatório
10. **Sistema aprende com as confirmações**

### 4.5. MÓDULO DRE (Demonstração do Resultado do Exercício)

#### 4.5.1. Baseado em Regime de Competência
- Utiliza `data_competencia` das contas pagar/receber
- Agrupamento por categorias financeiras
- Receitas e despesas do período
- Resultado líquido

#### 4.5.2. Funcionalidades
- Geração automática por período
- Agrupamento hierárquico por categorias
- Comparativo entre períodos
- Percentuais sobre receita
- DRE por centro de custo
- DRE consolidada (múltiplas empresas)
- Exportação Excel/PDF

### 4.6. MÓDULO DFC (Demonstração dos Fluxos de Caixa)

#### 4.6.1. Baseado em Regime de Caixa
- Utiliza `data_pagamento`/`data_recebimento`
- Atividades operacionais, investimento e financiamento

#### 4.6.2. Funcionalidades
- Método direto e indireto
- Atividades operacionais
- Atividades de investimento
- Atividades de financiamento
- Comparativo entre períodos
- **DFC consolidada (múltiplas empresas)** - Seleção de empresas para somar dados
- Exportação Excel/PDF

### 4.7. MÓDULO DE CONSOLIDAÇÃO DE EMPRESAS

#### 4.7.1. Visão Geral
- Sistema permite visualizar dados consolidados de 2 ou mais empresas
- Soma de valores de todas as empresas selecionadas
- Mantém separação por empresa quando necessário
- Disponível em todos os módulos principais

#### 4.7.2. Funcionalidades de Consolidação

**4.7.2.1. Seleção de Empresas**
- Interface para selecionar múltiplas empresas
- Checkbox para cada empresa disponível
- Filtro por empresas ativas
- Salvar seleção como favorita (perfil de consolidação)
- Troca rápida entre visualização individual e consolidada

**4.7.2.2. Módulos com Consolidação**
- **Contas a Pagar/Receber**: Soma de valores, quantidades, por período
- **Fluxo de Caixa**: Consolidação de entradas e saídas
- **DRE**: Demonstração consolidada por competência
- **DFC**: Fluxos de caixa consolidados
- **Conciliação Bancária**: Visão consolidada de todas as contas
- **Centros de Custo**: Análise consolidada por centro
- **Categorias**: Receitas e despesas consolidadas
- **Pedidos Vinculados**: Vendas consolidadas de todas as origens
- **Dashboard**: KPIs consolidados

**4.7.2.3. Tipos de Consolidação**
- **Consolidação Total**: Soma simples de todos os valores
- **Consolidação com Detalhamento**: Mostra total + detalhamento por empresa
- **Consolidação por Categoria**: Agrupa por categoria e soma valores
- **Consolidação por Centro de Custo**: Agrupa por centro e soma valores
- **Consolidação por Período**: Comparativo consolidado entre períodos

**4.7.2.4. Regras de Consolidação**
- Elimina transações entre empresas do grupo (se configurado)
- Considera moeda única (conversão se necessário)
- Mantém datas de competência e caixa separadas
- Agrupa categorias equivalentes entre empresas
- Trata centros de custo equivalentes

**4.7.2.5. Interface de Consolidação**
- Seletor de empresas no topo de cada módulo
- Indicador visual quando em modo consolidado
- Toggle rápido entre individual/consolidado
- Filtros aplicados a todas empresas selecionadas
- Exportação consolidada (Excel/PDF)
- Gráficos consolidados

**4.7.2.6. Perfis de Consolidação**
- Salvar combinações de empresas como perfis
- Exemplos: "Grupo Completo", "Empresas Operacionais", "Holding"
- Acesso rápido a perfis salvos
- Compartilhamento de perfis entre usuários (se permitido)

### 4.8. MÓDULO DE FORMAS DE PAGAMENTO E IA

#### 4.7.1. Cadastro de Formas de Pagamento
- CRUD de formas de pagamento
- Tipos: Pagamento, Recebimento, Ambos
- Código e nome descritivo

#### 4.7.2. Sistema Inteligente de Identificação

**4.7.2.1. Aprendizado Automático**
- Sistema aprende padrões de identificação
- Armazena padrões na tabela `formas_pagamento_padroes`
- Calcula taxa de confiança baseada em histórico
- Sugere forma de pagamento automaticamente

**4.7.2.2. Fontes de Aprendizado**
- **Extratos bancários**: Identifica origem (banco, tipo de transação)
  - Ex: "BANCO DO BRASIL", "PIX", "TED", "DOC", "TED IN", "TED OUT"
  - Padrões de descrição
- **Contas pagar/receber**: Aprende com vinculações manuais
  - Fornecedor/cliente específico
  - Categoria comum
  - Descrição padrão
- **Conciliação bancária**: Aprende com confirmações do usuário
  - Vinculação de extrato com movimentação
  - Confirmação de forma de pagamento

**4.7.2.3. Algoritmo de Sugestão**
1. **Busca por origem exata**: Verifica se já existe padrão para a origem
2. **Busca por similaridade**: Compara descrição com padrões existentes
3. **Busca por fornecedor/cliente**: Se sempre vem do mesmo, sugere forma padrão
4. **Busca por categoria**: Se categoria comum, sugere forma padrão
5. **Calcula confiança**: Baseado em quantidade de uso e acertos
6. **Sugere forma de pagamento**: Se confiança > 70%, sugere automaticamente

**4.7.2.4. Processo de Aprendizado**
- Quando usuário confirma sugestão: incrementa `quantidade_acerto`
- Quando usuário corrige: cria novo padrão ou atualiza existente
- Quando usuário vincula manualmente: cria padrão se não existir
- Recalcula confiança periodicamente
- Desativa padrões com baixa confiança (< 50%)

**4.7.2.5. Interface de Aprendizado**
- Tela para visualizar padrões aprendidos
- Edição manual de padrões
- Teste de sugestões
- Estatísticas de acerto
- Limpeza de padrões antigos/inativos

**4.7.2.6. Aplicações Práticas**
- **Importação de extrato**: Sugere forma de pagamento automaticamente
- **Cadastro de conta pagar/receber**: Sugere forma baseada em fornecedor/cliente
- **Conciliação bancária**: Sugere vinculação com forma de pagamento
- **Movimentações manuais**: Sugere forma baseada em descrição/origem

### 4.9. MÓDULO DE CENTROS DE CUSTO
- Estrutura hierárquica (pai/filho)
- Relatórios por centro de custo
- Rateio automático
- Análise de rentabilidade
- DRE por centro de custo

### 4.10. MÓDULO DE CATEGORIAS FINANCEIRAS
- Estrutura hierárquica
- Categorias por tipo (receita/despesa)
- Relatórios por categoria
- Controle de orçamento
- DRE por categoria

### 4.11. MÓDULO DE INTEGRAÇÕES

#### 4.10.1. Integração com Bancos de Dados Externos
- Interface para adicionar conexão
- Teste de conexão em tempo real
- Listagem dinâmica de tabelas do banco externo
- Seleção de tabela e colunas com preview
- Configuração de condições WHERE (interface visual)
- Mapeamento de colunas para tabelas do sistema
- Agendamento de sincronização (intervalo em minutos)
- Execução manual ou automática (cron)
- Logs detalhados de sincronização
- Tratamento de erros e retry automático
- Detecção de alterações (INSERT/UPDATE)
- Histórico completo de sincronizações
- Notificações de erros por email

#### 4.10.2. Integração WooCommerce
- Configuração de credenciais (URL, Consumer Key/Secret)
- Webhook para pedidos criados/atualizados
- Validação de assinatura HMAC do webhook
- Processamento assíncrono de webhooks (fila)
- Vinculação de pedidos (sem alterar no WooCommerce)
- Busca automática de produtos por código/SKU
- Cadastro automático de produtos não encontrados
- Cálculo de custos dos pedidos
- Sincronização bidirecional (opcional)
- Logs detalhados de webhooks recebidos
- Retry automático em caso de erro
- Dashboard de sincronizações

### 4.12. MÓDULO DE PRODUTOS
- CRUD completo de produtos
- Controle de custos
- Histórico de alterações de preço/custo
- Relatório de margem de contribuição
- Vinculação com pedidos
- Importação em lote

### 4.13. MÓDULO DE PEDIDOS VINCULADOS
- Visualização de pedidos de todas as origens
- Detalhamento completo
- Cálculo de custos e margens
- Filtros: origem, período, status, cliente
- Relatórios de vendas por origem
- Exportação Excel/PDF
- Dashboard de vendas

---

## 6. FLUXOS DE TRABALHO DETALHADOS

### 5.1. Fluxo de Cadastro de Conta a Pagar com Competência

1. Usuário acessa módulo de contas a pagar
2. Clica em "Nova Conta"
3. Preenche dados básicos:
   - Fornecedor, categoria, centro de custo
   - Número do documento, descrição
   - Valor total
4. **Preenche datas**:
   - Data de emissão (padrão: hoje)
   - **Data de competência** (quando ocorreu o fato gerador)
   - Data de vencimento
5. Sistema sugere forma de pagamento baseada em:
   - Fornecedor (se já tem padrão)
   - Categoria (se já tem padrão)
   - Descrição (busca por similaridade)
6. Usuário confirma ou seleciona forma de pagamento
7. Salva conta a pagar
8. **Sistema registra padrão** se forma foi confirmada
9. Conta aparece em:
   - Relatórios por competência (DRE)
   - Fluxo de caixa projetado (data vencimento)
   - Títulos a vencer

### 5.2. Fluxo de Baixa de Conta com Regime de Caixa

1. Usuário acessa conta a pagar pendente
2. Clica em "Baixar" ou "Pagar"
3. Informa:
   - Valor pago (pode ser parcial)
   - **Data de pagamento** (regime de caixa)
   - Conta bancária
   - Forma de pagamento (sistema sugere baseado em aprendizado)
4. Sistema cria movimentação de caixa com:
   - `data_movimentacao` = data de pagamento (caixa)
   - `data_competencia` = data de competência da conta (competência)
5. Atualiza status da conta (pago/parcial)
6. **Sistema aprende** com a forma de pagamento confirmada
7. Movimentação aparece em:
   - Fluxo de caixa real (data pagamento)
   - Conciliação bancária
   - DFC (data pagamento)

### 5.3. Fluxo de Conciliação com IA de Formas de Pagamento

1. Usuário importa extrato bancário
2. Sistema processa cada linha do extrato
3. **Para cada item, sistema**:
   - Identifica origem (banco, tipo transação)
   - Busca padrões aprendidos
   - **Sugere forma de pagamento** com nível de confiança
   - Tenta fazer matching automático com movimentações
4. Interface mostra:
   - Itens do extrato
   - Forma de pagamento sugerida (com % de confiança)
   - Movimentação matchada (se encontrada)
5. Usuário:
   - Confirma sugestões corretas
   - Corrige sugestões incorretas
   - Vincula manualmente itens não matchados
6. **Sistema aprende**:
   - Incrementa acertos nas confirmações
   - Cria/atualiza padrões nas correções
   - Recalcula confiança dos padrões
7. Fecha conciliação
8. Gera relatório

### 5.4. Fluxo de Integração Banco de Dados

1. Usuário acessa módulo de integrações
2. Clica em "Nova Integração" > "Banco de Dados"
3. Preenche dados de conexão:
   - Tipo de banco, host, porta
   - Database, usuário, senha
4. Clica em "Testar Conexão"
5. Sistema lista todas as tabelas disponíveis
6. Usuário seleciona tabela origem
7. Sistema lista colunas da tabela
8. Usuário seleciona colunas desejadas
9. Define condições WHERE (interface visual)
10. Seleciona tabela destino no sistema
11. Mapeia colunas origem → destino
12. Configura intervalo de sincronização
13. Salva integração
14. Executa primeira sincronização manual
15. Sistema agenda próximas execuções (cron)
16. Logs são gerados a cada execução
17. Notificações em caso de erro

### 5.5. Fluxo de Visualização Consolidada

1. Usuário acessa qualquer módulo (DRE, Fluxo de Caixa, Contas, etc)
2. No topo da página, encontra seletor de empresas
3. Por padrão, mostra apenas empresa ativa na sessão
4. Usuário clica em "Consolidar Empresas"
5. Interface mostra lista de empresas disponíveis com checkboxes
6. Usuário seleciona 2 ou mais empresas
7. Opções disponíveis:
   - **Visualização Consolidada Total**: Soma simples de todos os valores
   - **Visualização com Detalhamento**: Mostra total + valores por empresa
   - **Salvar como Perfil**: Salva seleção para uso futuro
8. Sistema aplica filtros e consolida dados:
   - Soma valores de todas empresas selecionadas
   - Agrupa por categoria/centro de custo quando aplicável
   - Mantém datas e períodos
   - Elimina transações internas (se configurado)
9. Exibe relatório consolidado:
   - Totais consolidados destacados
   - Detalhamento por empresa (se selecionado)
   - Gráficos consolidados
   - Comparativos consolidados
10. Usuário pode exportar dados consolidados
11. Sistema salva preferência do usuário (última consolidação usada)

### 5.6. Fluxo de Webhook WooCommerce

1. Pedido criado/atualizado no WooCommerce
2. WooCommerce envia webhook para sistema
3. Sistema valida assinatura HMAC
4. Adiciona webhook na fila de processamento
5. Processa webhook assincronamente:
   - Extrai dados do pedido
   - Busca cliente no sistema (ou cria)
   - Para cada item do pedido:
     - Busca produto por código/SKU
     - Se não encontrar, cadastra produto (se configurado)
     - Obtém custo do produto
   - Calcula custo total do pedido
   - Cria/atualiza pedido vinculado
6. Se configurado, cria conta a receber automaticamente
7. Registra log da operação
8. Retry automático em caso de erro

---

## 7. RELATÓRIOS ESPECÍFICOS

### 6.1. Relatórios por Regime de Competência
- **DRE**: Receitas e despesas por `data_competencia`
- **DRE Consolidada**: Soma de receitas e despesas de múltiplas empresas
- **Análise de Resultados**: Comparativo mensal/trimestral/anual
- **Análise Consolidada**: Comparativo entre empresas do grupo
- **Orçamento vs Realizado**: Por competência
- **Orçamento Consolidado**: Orçamento vs realizado do grupo
- **Margem por Categoria**: Baseado em competência
- **Margem Consolidada**: Margem total do grupo por categoria

### 6.2. Relatórios por Regime de Caixa
- **Fluxo de Caixa Real**: Entradas e saídas por `data_pagamento`/`data_recebimento`
- **Fluxo Consolidado**: Soma de fluxos de múltiplas empresas
- **DFC**: Demonstração dos Fluxos de Caixa
- **DFC Consolidada**: DFC do grupo empresarial
- **Saldo Disponível**: Saldo real no banco
- **Saldo Consolidado**: Soma de saldos de todas as contas do grupo
- **Projeção de Caixa**: Baseada em títulos a receber/pagar
- **Projeção Consolidada**: Projeção total do grupo

### 6.3. Relatórios Comparativos
- **Competência vs Caixa**: Comparação lado a lado
- **Competência vs Caixa Consolidado**: Comparação do grupo
- **Análise de Inadimplência**: Diferença entre competência e caixa
- **Inadimplência Consolidada**: Análise do grupo
- **Fluxo Projetado vs Realizado**: Comparação
- **Fluxo Consolidado**: Projetado vs realizado do grupo

### 6.4. Relatórios de Formas de Pagamento
- **Uso de Formas de Pagamento**: Por período (individual ou consolidado)
- **Uso Consolidado**: Formas de pagamento do grupo
- **Eficiência da IA**: Taxa de acerto das sugestões (por empresa ou consolidado)
- **Padrões Aprendidos**: Listagem com confiança (por empresa ou consolidado)
- **Análise por Origem**: Quais origens mais comuns (individual ou consolidado)
- **Análise Consolidada**: Origens mais usadas no grupo

### 6.5. Relatórios e Gráficos com Consolidação

#### 6.5.1. Todos os Relatórios Suportam Consolidação
- **Seletor de Empresas**: Interface no topo de cada relatório para selecionar empresas
- **Modo Individual**: Mostra dados apenas da empresa selecionada
- **Modo Consolidado**: Soma dados de 2 ou mais empresas selecionadas
- **Perfis Salvos**: Salvar combinações de empresas como favoritos
- **Toggle Rápido**: Botão para alternar entre individual e consolidado

#### 6.5.2. Gráficos com Opção de Consolidação
- **Gráficos de Linha**: 
  - Opção de mostrar linha única consolidada ou linhas separadas por empresa
  - Legenda indicando empresas
  - Múltiplas séries (receita, despesa, lucro) em modo consolidado
- **Gráficos de Barras**:
  - Barras agrupadas por empresa ou barra única consolidada
  - Comparativo entre empresas lado a lado
  - Empilhamento por categoria quando consolidado
- **Gráficos de Pizza**:
  - Distribuição consolidada ou por empresa individual
  - Opção de ver participação de cada empresa no total
  - Múltiplos gráficos lado a lado (um por empresa) quando não consolidado
- **Gráficos de Área**:
  - Área única consolidada ou áreas empilhadas por empresa
  - Visualização de evolução temporal consolidada
- **Gráficos de Dispersão**:
  - Comparativo entre empresas em eixos X e Y
  - Identificação de outliers no grupo
- **Todos os gráficos**: Possuem toggle para alternar entre individual e consolidado
- **Exportação**: Gráficos podem ser exportados em ambos os modos

#### 6.5.3. Relatórios que Consideram Rateios
- **DRE por Competência**: Considera rateios de pagamentos/recebimentos por empresa
- **Fluxo de Caixa**: Considera rateios na consolidação (evita duplicação)
- **Relatório de Contas a Pagar/Receber**: Mostra valores rateados por empresa
- **Relatório de Rateios**: Lista todos os rateios realizados com detalhamento
- **Dashboard**: KPIs consideram rateios corretamente por empresa

### 6.5. Relatórios Consolidados Específicos
- **Dashboard Consolidado**: KPIs do grupo empresarial
- **Contas a Pagar/Receber Consolidadas**: Total de títulos do grupo
- **Centros de Custo Consolidados**: Análise por centro de custo do grupo
- **Categorias Consolidadas**: Receitas e despesas por categoria do grupo
- **Pedidos Consolidados**: Vendas totais de todas as empresas
- **Conciliação Consolidada**: Visão geral de todas as contas bancárias

---

## 8. SISTEMA DE PERMISSÕES

- Controle granular por módulo e ação
- Permissões por empresa (ou todas)
- Perfis pré-definidos:
  - **Administrador**: Acesso total
  - **Financeiro**: Contas, fluxo, conciliação, relatórios
  - **Visualizador**: Apenas visualização
  - **Integrações**: Apenas módulo de integrações
- Permissões customizadas por usuário
- Auditoria de ações (logs)
- Controle de acesso por IP (opcional)

---

## 9. SEGURANÇA

- Autenticação com hash de senha (password_hash PHP)
- Sessões seguras (httponly, secure) - gerenciadas por Session class
- CSRF protection (tokens) - middleware CSRFMiddleware
- SQL Injection prevention (prepared statements PDO sempre)
- XSS protection (htmlspecialchars nas views, sanitização automática)
- Validação rigorosa de entrada (Request class + Validation helpers)
- Logs de auditoria (todas ações críticas) - Monolog integrado
- Backup automático do banco (diário)
- Criptografia de dados sensíveis:
  - Senhas de integração (openssl_encrypt)
  - Credenciais de bancos externos
- Rate limiting em APIs (middleware RateLimitMiddleware)
- Validação de webhooks (HMAC)
- PDO com prepared statements em todos os Models
- Transações para operações críticas (Database::transaction())

---

## 10. TECNOLOGIAS E BIBLIOTECAS

- **Backend**:
  - PHP 8.0+ (orientado a objetos, MVC)
  - MySQL 5.7+ / MariaDB 10.3+
  - PDO para acesso ao banco de dados
  - Arquitetura MVC (Model-View-Controller)
  - Sistema de rotas customizado
  - Autoloading de classes (PSR-4)
  
- **Frontend**:
  - HTML5, CSS3
  - TailwindCSS 3.x (framework CSS utility-first)
  - JavaScript (Vanilla ES6+)
  - Chart.js (gráficos)
  - DataTables (tabelas interativas)
  - Alpine.js (opcional, para interatividade leve)
  - Flatpickr ou DatePicker (seleção de datas)
  
- **Bibliotecas PHP**:
  - PHPMailer (envio de emails)
  - TCPDF ou FPDF (geração de PDFs)
  - PhpSpreadsheet (leitura/escrita Excel)
  - Monolog (logs avançados)
  
- **APIs**:
  - WooCommerce REST API
  - JWT (se necessário autenticação API)

---

## 11. MELHORIAS E RECURSOS AVANÇADOS

### 10.1. Dashboard Executivo

#### 10.1.1. Visualização por Empresa ou Consolidada
- **Seletor de Visualização**: Toggle entre "Empresa Individual" e "Consolidado"
- **Empresa Individual**: Mostra dados apenas da empresa selecionada na sessão
- **Consolidado**: Permite selecionar 2 ou mais empresas e somar todos os dados
- **Perfis Salvos**: Salvar combinações de empresas como perfis de consolidação favoritos

#### 10.1.2. KPIs Principais (Disponíveis em Ambos os Modos)
- **Receita Total**: Soma de todas as receitas (por empresa ou consolidado)
- **Despesa Total**: Soma de todas as despesas (por empresa ou consolidado)
- **Lucro/Prejuízo**: Receita - Despesa
- **Saldo Disponível**: Saldo em contas bancárias (por empresa ou consolidado)
- **Títulos a Receber**: Valor total pendente
- **Títulos a Pagar**: Valor total pendente
- **Fluxo de Caixa do Mês**: Entradas - Saídas do mês atual
- **Margem de Lucro**: Percentual de lucro sobre receita

#### 10.1.3. Gráficos e Visualizações
- **Gráficos de Evolução**: 
  - Receitas e despesas ao longo do tempo (por empresa ou consolidado)
  - Opção de mostrar linhas separadas por empresa ou linha única consolidada
- **Gráfico de Pizza**: Distribuição de receitas/despesas por categoria
- **Gráfico de Barras**: Comparativo mensal/trimestral/anual
- **Gráfico de Linha**: Evolução do saldo ao longo do tempo
- **Todos os gráficos suportam**: Visualização individual ou consolidada

#### 10.1.4. Alertas e Notificações
- Títulos vencidos (por empresa ou consolidado)
- Títulos próximos a vencer (configurável)
- Saldo baixo em contas bancárias
- Erros em integrações
- Conciliações pendentes
- Notificações por email

#### 10.1.5. Comparativos
- Comparativo com período anterior (mês, trimestre, ano)
- Comparativo entre empresas (quando em modo consolidado)
- Variação percentual e absoluta

### 10.2. Alertas e Notificações
- Títulos vencidos
- Títulos próximos a vencer (configurável)
- Saldo baixo em contas bancárias
- Erros em integrações
- Conciliações pendentes
- Notificações por email

### 10.3. Orçamento
- Cadastro de orçamento anual/mensal
- Comparativo orçado vs realizado
- Por categoria, centro de custo, empresa
- Alertas de desvio

### 10.4. Previsão de Fluxo de Caixa
- Projeção baseada em histórico
- Considera sazonalidade
- Alertas de saldo negativo projetado

### 10.5. Multi-moeda (Futuro)
- Cadastro de moedas
- Taxa de câmbio
- Conversão automática
- Relatórios em moeda base

### 10.6. Workflow de Aprovações
- Níveis de aprovação
- Notificações para aprovadores
- Histórico de aprovações

### 10.7. Assinatura Digital
- Upload de documentos
- Vinculação com contas
- Visualização e download

### 10.8. API REST
- Endpoints para integrações externas
- Autenticação JWT
- Documentação Swagger

### 10.9. Sistema de Filas
- Processamento assíncrono
- Retry automático
- Monitoramento de filas

### 10.10. Cache
- Cache de relatórios pesados
- Redis ou Memcached (opcional)
- Invalidação automática

---

## 12. CONSIDERAÇÕES DE IMPLEMENTAÇÃO

### 11.1. Arquitetura MVC

**Padrão MVC Implementado**:

- **Model**: 
  - Classes que representam entidades do banco de dados
  - Métodos para CRUD usando PDO
  - Validações de dados
  - Lógica de negócio específica da entidade
  - Exemplo: `Empresa::create()`, `ContaPagar::findByEmpresa()`

- **View**: 
  - Templates PHP com HTML e TailwindCSS
  - Separação de apresentação da lógica
  - Componentes reutilizáveis
  - Sistema de layouts
  - Exemplo: `views/empresas/index.php`, `views/components/header.php`

- **Controller**: 
  - Processa requisições HTTP
  - Chama métodos do Model
  - Passa dados para View
  - Gerencia autenticação e permissões
  - Exemplo: `EmpresaController::index()`, `ContaPagarController::store()`

**Padrões de Código**:
- PSR-4 autoloading
- Namespaces organizados por módulo
- Classes com responsabilidade única
- Injeção de dependências
- Repository pattern para acesso a dados
- Service layer para lógica complexa
- Comentários PHPDoc
- Padrão de nomenclatura consistente (camelCase para métodos, PascalCase para classes)

### 11.2. Performance
- Índices no banco de dados
- Queries otimizadas
- Paginação em listagens
- Lazy loading quando possível
- Cache de consultas frequentes

### 11.3. Escalabilidade
- Suporte a crescimento
- Arquitetura preparada para múltiplas empresas
- Processamento assíncrono
- Otimização de recursos

### 11.4. Manutenibilidade
- Documentação inline
- Estrutura clara
- Logs detalhados
- Tratamento de erros robusto

### 11.5. Testes
- Testes de integração
- Validação de dados
- Testes de performance
- Testes de segurança

---

## 13. EXEMPLOS DE QUERIES IMPORTANTES

### 13.1. DRE por Competência (Individual)
```sql
SELECT 
    c.nome as categoria,
    SUM(CASE WHEN cp.id IS NOT NULL THEN cp.valor_total ELSE 0 END) as despesas,
    SUM(CASE WHEN cr.id IS NOT NULL THEN cr.valor_total ELSE 0 END) as receitas
FROM categorias_financeiras c
LEFT JOIN contas_pagar cp ON cp.categoria_id = c.id 
    AND cp.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cp.status != 'cancelado'
LEFT JOIN contas_receber cr ON cr.categoria_id = c.id 
    AND cr.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cr.status != 'cancelado'
WHERE c.empresa_id = :empresa_id
GROUP BY c.id
ORDER BY c.codigo
```

### 13.2. DRE Consolidada (Múltiplas Empresas)
```sql
SELECT 
    c.nome as categoria,
    e.nome_fantasia as empresa,
    SUM(CASE WHEN cp.id IS NOT NULL THEN cp.valor_total ELSE 0 END) as despesas,
    SUM(CASE WHEN cr.id IS NOT NULL THEN cr.valor_total ELSE 0 END) as receitas
FROM categorias_financeiras c
INNER JOIN empresas e ON e.id = c.empresa_id
LEFT JOIN contas_pagar cp ON cp.categoria_id = c.id 
    AND cp.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cp.status != 'cancelado'
LEFT JOIN contas_receber cr ON cr.categoria_id = c.id 
    AND cr.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cr.status != 'cancelado'
WHERE c.empresa_id IN (:empresas_ids) -- Array de IDs das empresas
GROUP BY c.id, e.id
ORDER BY c.codigo, e.nome_fantasia

-- Para total consolidado (sem detalhamento por empresa):
SELECT 
    c.nome as categoria,
    SUM(CASE WHEN cp.id IS NOT NULL THEN cp.valor_total ELSE 0 END) as despesas,
    SUM(CASE WHEN cr.id IS NOT NULL THEN cr.valor_total ELSE 0 END) as receitas
FROM categorias_financeiras c
LEFT JOIN contas_pagar cp ON cp.categoria_id = c.id 
    AND cp.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cp.status != 'cancelado'
    AND cp.empresa_id IN (:empresas_ids)
LEFT JOIN contas_receber cr ON cr.categoria_id = c.id 
    AND cr.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cr.status != 'cancelado'
    AND cr.empresa_id IN (:empresas_ids)
WHERE c.empresa_id IN (:empresas_ids)
GROUP BY c.id
ORDER BY c.codigo
```

### 13.3. Fluxo de Caixa Real (Individual)
```sql
SELECT 
    DATE(m.data_movimentacao) as data,
    SUM(CASE WHEN m.tipo = 'entrada' THEN m.valor ELSE 0 END) as entradas,
    SUM(CASE WHEN m.tipo = 'saida' THEN m.valor ELSE 0 END) as saidas,
    SUM(CASE WHEN m.tipo = 'entrada' THEN m.valor ELSE -m.valor END) as saldo_dia
FROM movimentacoes_caixa m
WHERE m.empresa_id = :empresa_id
    AND m.data_movimentacao BETWEEN :data_inicio AND :data_fim
GROUP BY DATE(m.data_movimentacao)
ORDER BY data
```

### 13.4. Fluxo de Caixa Consolidado (Múltiplas Empresas)
```sql
SELECT 
    DATE(m.data_movimentacao) as data,
    e.nome_fantasia as empresa,
    SUM(CASE WHEN m.tipo = 'entrada' THEN m.valor ELSE 0 END) as entradas,
    SUM(CASE WHEN m.tipo = 'saida' THEN m.valor ELSE 0 END) as saidas,
    SUM(CASE WHEN m.tipo = 'entrada' THEN m.valor ELSE -m.valor END) as saldo_dia
FROM movimentacoes_caixa m
INNER JOIN empresas e ON e.id = m.empresa_id
WHERE m.empresa_id IN (:empresas_ids)
    AND m.data_movimentacao BETWEEN :data_inicio AND :data_fim
GROUP BY DATE(m.data_movimentacao), e.id
ORDER BY data, e.nome_fantasia

-- Para total consolidado (sem detalhamento por empresa):
SELECT 
    DATE(m.data_movimentacao) as data,
    SUM(CASE WHEN m.tipo = 'entrada' THEN m.valor ELSE 0 END) as entradas,
    SUM(CASE WHEN m.tipo = 'saida' THEN m.valor ELSE 0 END) as saidas,
    SUM(CASE WHEN m.tipo = 'entrada' THEN m.valor ELSE -m.valor END) as saldo_dia
FROM movimentacoes_caixa m
WHERE m.empresa_id IN (:empresas_ids)
    AND m.data_movimentacao BETWEEN :data_inicio AND :data_fim
GROUP BY DATE(m.data_movimentacao)
ORDER BY data
```

### 13.5. DRE com Rateios Considerados

```sql
-- DRE por competência considerando rateios
SELECT 
    e.id as empresa_id,
    e.nome_fantasia as empresa,
    c.nome as categoria,
    SUM(
        CASE 
            WHEN cp.id IS NOT NULL THEN 
                COALESCE(
                    (SELECT SUM(rp.valor_rateio) 
                     FROM rateios_pagamentos rp 
                     WHERE rp.conta_pagar_id = cp.id AND rp.empresa_id = e.id),
                    CASE WHEN cp.empresa_id = e.id THEN cp.valor_total ELSE 0 END
                )
            ELSE 0 
        END
    ) as despesas,
    SUM(
        CASE 
            WHEN cr.id IS NOT NULL THEN 
                COALESCE(
                    (SELECT SUM(rr.valor_rateio) 
                     FROM rateios_recebimentos rr 
                     WHERE rr.conta_receber_id = cr.id AND rr.empresa_id = e.id),
                    CASE WHEN cr.empresa_id = e.id THEN cr.valor_total ELSE 0 END
                )
            ELSE 0 
        END
    ) as receitas
FROM empresas e
CROSS JOIN categorias_financeiras c
LEFT JOIN contas_pagar cp ON (
    (cp.empresa_id = e.id OR EXISTS (
        SELECT 1 FROM rateios_pagamentos rp 
        WHERE rp.conta_pagar_id = cp.id AND rp.empresa_id = e.id
    ))
    AND cp.categoria_id = c.id
    AND cp.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cp.status != 'cancelado'
)
LEFT JOIN contas_receber cr ON (
    (cr.empresa_id = e.id OR EXISTS (
        SELECT 1 FROM rateios_recebimentos rr 
        WHERE rr.conta_receber_id = cr.id AND rr.empresa_id = e.id
    ))
    AND cr.categoria_id = c.id
    AND cr.data_competencia BETWEEN :data_inicio AND :data_fim
    AND cr.status != 'cancelado'
)
WHERE e.id IN (:empresas_ids) -- Array de IDs de empresas
GROUP BY e.id, c.id
ORDER BY e.nome_fantasia, c.codigo
```

### 13.6. Relatório de Rateios Realizados

```sql
-- Lista todos os rateios de pagamentos
SELECT 
    cp.id as conta_pagar_id,
    cp.descricao as descricao_conta,
    cp.valor_total as valor_total_conta,
    e_principal.nome_fantasia as empresa_principal,
    rp.empresa_id,
    e_rateio.nome_fantasia as empresa_rateio,
    rp.valor_rateio,
    rp.percentual,
    rp.data_competencia,
    rp.observacoes
FROM rateios_pagamentos rp
INNER JOIN contas_pagar cp ON rp.conta_pagar_id = cp.id
INNER JOIN empresas e_principal ON cp.empresa_id = e_principal.id
INNER JOIN empresas e_rateio ON rp.empresa_id = e_rateio.id
WHERE rp.empresa_id IN (:empresas_ids)
ORDER BY cp.id, rp.empresa_id

-- Lista todos os rateios de recebimentos
SELECT 
    cr.id as conta_receber_id,
    cr.descricao as descricao_conta,
    cr.valor_total as valor_total_conta,
    e_principal.nome_fantasia as empresa_principal,
    rr.empresa_id,
    e_rateio.nome_fantasia as empresa_rateio,
    rr.valor_rateio,
    rr.percentual,
    rr.data_competencia,
    rr.observacoes
FROM rateios_recebimentos rr
INNER JOIN contas_receber cr ON rr.conta_receber_id = cr.id
INNER JOIN empresas e_principal ON cr.empresa_id = e_principal.id
INNER JOIN empresas e_rateio ON rr.empresa_id = e_rateio.id
WHERE rr.empresa_id IN (:empresas_ids)
ORDER BY cr.id, rr.empresa_id
```

### 13.7. Fluxo de Caixa Consolidado com Rateios

```sql
-- Fluxo de caixa consolidado considerando rateios (evita duplicação)
-- Usando UNION para combinar pagamentos e recebimentos
SELECT 
    data_movimento,
    SUM(entradas) as entradas,
    SUM(saidas) as saidas,
    SUM(entradas - saidas) as saldo_dia
FROM (
    -- Entradas (recebimentos)
    SELECT 
        DATE(cr.data_recebimento) as data_movimento,
        CASE 
            WHEN cr.tem_rateio = 1 THEN
                (SELECT COALESCE(SUM(rr.valor_rateio), 0)
                 FROM rateios_recebimentos rr
                 WHERE rr.conta_receber_id = cr.id 
                 AND rr.empresa_id IN (:empresas_ids))
            ELSE
                CASE WHEN cr.empresa_id IN (:empresas_ids) THEN cr.valor_recebido ELSE 0 END
        END as entradas,
        0 as saidas
    FROM contas_receber cr
    WHERE cr.data_recebimento IS NOT NULL
    AND cr.data_recebimento BETWEEN :data_inicio AND :data_fim
    AND (cr.empresa_id IN (:empresas_ids) OR cr.tem_rateio = 1)
    
    UNION ALL
    
    -- Saídas (pagamentos)
    SELECT 
        DATE(cp.data_pagamento) as data_movimento,
        0 as entradas,
        CASE 
            WHEN cp.tem_rateio = 1 THEN
                (SELECT COALESCE(SUM(rp.valor_rateio), 0)
                 FROM rateios_pagamentos rp
                 WHERE rp.conta_pagar_id = cp.id 
                 AND rp.empresa_id IN (:empresas_ids))
            ELSE
                CASE WHEN cp.empresa_id IN (:empresas_ids) THEN cp.valor_pago ELSE 0 END
        END as saidas
    FROM contas_pagar cp
    WHERE cp.data_pagamento IS NOT NULL
    AND cp.data_pagamento BETWEEN :data_inicio AND :data_fim
    AND (cp.empresa_id IN (:empresas_ids) OR cp.tem_rateio = 1)
) fluxo
GROUP BY data_movimento
ORDER BY data_movimento
```

### 13.8. Sugestão de Forma de Pagamento
```sql
SELECT 
    fp.id,
    fp.nome,
    fpp.confianca,
    fpp.quantidade_uso,
    fpp.quantidade_acerto
FROM formas_pagamento_padroes fpp
INNER JOIN formas_pagamento fp ON fp.id = fpp.forma_pagamento_id
WHERE fpp.empresa_id = :empresa_id
    AND (
        fpp.origem = :origem
        OR (fpp.fornecedor_id = :fornecedor_id AND fpp.fornecedor_id IS NOT NULL)
        OR (fpp.cliente_id = :cliente_id AND fpp.cliente_id IS NOT NULL)
    )
    AND fpp.ativo = 1
    AND fpp.confianca >= 50
ORDER BY fpp.confianca DESC, fpp.quantidade_uso DESC
LIMIT 1
```

---

## 13. SISTEMA DE MIGRATIONS

### 13.1. Visão Geral
O sistema utiliza um sistema de migrations para gerenciar a estrutura do banco de dados de forma versionada e controlada. Isso permite:
- Versionamento do schema do banco de dados
- Aplicação incremental de mudanças
- Rollback de alterações quando necessário
- Controle de versão em equipe
- Deploy automatizado em diferentes ambientes

### 13.2. Estrutura de Migrations

**Localização**: `migrations/`

**Formato de Nomenclatura**: `{numero}_{descricao}.php`
- Exemplo: `001_create_empresas.php`
- Exemplo: `002_create_usuarios.php`
- Exemplo: `015_add_campo_rateio_contas_pagar.php`

**Numeração**: Sequencial, sempre incrementando (001, 002, 003...)

### 13.3. Classe Base Migration

Todas as migrations devem estender a classe `Migration` localizada em `includes/Migration.php`.

**Métodos Obrigatórios**:
- `up()`: Executa a migration (cria/modifica tabelas)
- `down()`: Reverte a migration (rollback)

**Métodos Auxiliares Disponíveis**:
- `createTable($tableName, $columns, $options)`: Cria uma tabela
- `addColumn($tableName, $columnName, $definition)`: Adiciona coluna
- `dropColumn($tableName, $columnName)`: Remove coluna
- `addIndex($tableName, $indexName, $columns, $unique)`: Adiciona índice
- `dropIndex($tableName, $indexName)`: Remove índice
- `addForeignKey($tableName, $constraintName, $column, $refTable, $refColumn, $onDelete, $onUpdate)`: Adiciona FK
- `dropForeignKey($tableName, $constraintName)`: Remove FK
- `execute($sql)`: Executa SQL customizado

### 13.4. Exemplo de Migration

```php
<?php
require_once __DIR__ . '/../includes/Migration.php';

class Migration_001_CreateEmpresas extends Migration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "codigo VARCHAR(20) NOT NULL UNIQUE",
            "razao_social VARCHAR(255) NOT NULL",
            "nome_fantasia VARCHAR(255) NOT NULL",
            "cnpj VARCHAR(18) UNIQUE",
            "grupo_empresarial_id INT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "configuracoes JSON NULL"
        ];
        
        $this->createTable('empresas', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Adicionar índices
        $this->addIndex('empresas', 'idx_cnpj', ['cnpj']);
        $this->addIndex('empresas', 'idx_grupo', ['grupo_empresarial_id']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS empresas");
    }
}
```

### 13.5. Tabela de Controle de Migrations

O sistema mantém uma tabela `migrations` para controlar quais migrations já foram executadas:

**Estrutura da tabela migrations**:
- `id` (PK, INT, AUTO_INCREMENT)
- `migration_name` (VARCHAR(255), UNIQUE) - Nome do arquivo da migration
- `executed_at` (DATETIME) - Data/hora de execução
- `execution_time` (DECIMAL(10,3)) - Tempo de execução em segundos
- `status` (ENUM('success', 'failed')) - Status da execução
- `error_message` (TEXT, nullable) - Mensagem de erro se falhou

### 13.6. MigrationManager

Classe responsável por gerenciar as migrations, localizada em `includes/MigrationManager.php`.

**Funcionalidades**:
- Listar migrations pendentes
- Executar migrations pendentes
- Executar rollback de migrations
- Verificar status das migrations
- Validar integridade das migrations

**Métodos Principais**:
- `run()`: Executa todas as migrations pendentes
- `rollback($steps)`: Reverte N migrations
- `status()`: Mostra status de todas as migrations
- `refresh()`: Faz rollback de tudo e executa novamente (cuidado!)

### 13.7. Comandos de Migration

**Script CLI**: `migrate.php` (na raiz do projeto)

**Comandos Disponíveis**:
```bash
# Executar migrations pendentes
php migrate.php up

# Executar rollback (última migration)
php migrate.php down

# Executar rollback de N migrations
php migrate.php down --steps=3

# Ver status das migrations
php migrate.php status

# Refresh (rollback tudo + up tudo) - CUIDADO!
php migrate.php refresh

# Criar nova migration (template)
php migrate.php create NomeDaMigration
```

### 13.8. Ordem de Execução das Migrations

As migrations devem ser criadas na ordem correta de dependências:

1. **001_create_empresas.php** - Base para tudo
2. **002_create_usuarios.php** - Depende de empresas
3. **003_create_perfis_consolidacao.php** - Depende de usuarios
4. **004_create_permissoes.php** - Depende de usuarios e empresas
5. **005_create_categorias_financeiras.php** - Depende de empresas
6. **006_create_centros_custo.php** - Depende de empresas
7. **007_create_contas_bancarias.php** - Depende de empresas
8. **008_create_formas_pagamento.php** - Depende de empresas
9. **009_create_fornecedores.php** - Depende de empresas
10. **010_create_clientes.php** - Depende de empresas
11. **011_create_produtos.php** - Depende de empresas
12. **012_create_contas_pagar.php** - Depende de empresas, categorias, centros_custo, fornecedores, formas_pagamento, contas_bancarias
13. **013_create_contas_receber.php** - Depende de empresas, categorias, centros_custo, clientes, formas_pagamento, contas_bancarias
14. **014_create_rateios_pagamentos.php** - Depende de contas_pagar e empresas
15. **015_create_rateios_recebimentos.php** - Depende de contas_receber e empresas
16. **016_create_movimentacoes_caixa.php** - Depende de empresas, categorias, centros_custo, contas_bancarias, formas_pagamento
17. **017_create_conciliacao_bancaria.php** - Depende de empresas e contas_bancarias
18. **018_create_conciliacao_itens.php** - Depende de conciliacao_bancaria e movimentacoes_caixa
19. **019_create_formas_pagamento_padroes.php** - Depende de formas_pagamento e empresas
20. **020_create_pedidos_vinculados.php** - Depende de empresas e clientes
21. **021_create_pedidos_itens.php** - Depende de pedidos_vinculados e produtos
22. **022_create_integracoes_config.php** - Depende de empresas
23. **023_create_integracoes_bancos_dados.php** - Depende de integracoes_config
24. **024_create_integracoes_woocommerce.php** - Depende de integracoes_config
25. **025_create_integracoes_logs.php** - Depende de integracoes_config
26. **026_create_integracoes_sincronizacoes.php** - Depende de integracoes_config

### 13.9. Boas Práticas

1. **Sempre crie migrations incrementais**: Uma migration por mudança lógica
2. **Nunca modifique migrations já executadas**: Crie uma nova migration para corrigir
3. **Teste o rollback**: Sempre teste se o `down()` funciona corretamente
4. **Use transações quando possível**: Para operações que podem falhar parcialmente
5. **Documente migrations complexas**: Adicione comentários explicando a lógica
6. **Valide dados antes de migrar**: Verifique se dados existentes são compatíveis
7. **Backup antes de migrations em produção**: Sempre faça backup antes de executar em produção
8. **Execute migrations em ordem**: Nunca pule números ou execute fora de ordem

### 13.10. Migrations em Ambiente de Produção

**Checklist antes de executar em produção**:
- [ ] Backup completo do banco de dados
- [ ] Testar migrations em ambiente de homologação
- [ ] Verificar se não há migrations pendentes
- [ ] Executar em horário de baixo tráfego (se possível)
- [ ] Monitorar logs durante execução
- [ ] Verificar integridade dos dados após execução
- [ ] Ter plano de rollback pronto

---

## 14. PRÓXIMOS PASSOS DE IMPLEMENTAÇÃO

1. **Fase 1 - Estrutura Base e Core MVC**
   - Criar estrutura de pastas MVC (app/core, app/controllers, app/models, app/views)
   - Implementar classes core (App, Router, Controller, Model, Database)
   - Sistema de rotas
   - Autoloading PSR-4
   - Configurar banco de dados (Database.php com PDO)
   - **Sistema de migrations** (Migration.php, MigrationManager.php)
   - **Criar todas as migrations iniciais** (001 a 026)
   - Middleware de autenticação
   - Sistema de sessões
   - **Configurar TailwindCSS** (via CDN ou build process)

2. **Fase 2 - Módulos Fundamentais (MVC)**
   - Criar Models: Empresa, Categoria, CentroCusto, FormaPagamento, ContaBancaria, Fornecedor, Cliente
   - Criar Controllers: EmpresaController, CategoriaController, etc.
   - Criar Views: templates com TailwindCSS
   - CRUD completo de empresas (Model + Controller + View)
   - CRUD completo de categorias
   - CRUD completo de centros de custo
   - CRUD completo de formas de pagamento
   - CRUD completo de contas bancárias
   - CRUD completo de fornecedores/clientes
   - **Sistema de consolidação básico** (seleção múltipla de empresas)

3. **Fase 3 - Contas e Movimentações (MVC)**
   - Criar Models: ContaPagar, ContaReceber, MovimentacaoCaixa, RateioPagamento, RateioRecebimento
   - Criar Controllers: ContaPagarController, ContaReceberController, MovimentacaoCaixaController
   - Criar Views: formulários e listagens
   - Contas a pagar (com todas as datas) - CRUD completo
   - Contas a receber (com todas as datas) - CRUD completo
   - Movimentações de caixa - CRUD completo
   - Baixa de títulos (lógica no Controller/Service)

4. **Fase 4 - Relatórios Básicos**
   - Fluxo de caixa (competência e caixa)
   - DRE por competência
   - DFC por caixa
   - Relatórios de contas
   - **Relatórios consolidados** (múltiplas empresas)

5. **Fase 5 - Conciliação**
   - Importação de extratos
   - Matching automático
   - Conciliação manual
   - Relatórios

6. **Fase 6 - IA de Formas de Pagamento**
   - Sistema de aprendizado
   - Sugestões automáticas
   - Interface de padrões
   - Aplicação em conciliação

7. **Fase 7 - Integrações**
   - Integração com bancos de dados
   - Integração WooCommerce
   - Processamento de webhooks
   - Logs e monitoramento

8. **Fase 8 - Produtos e Pedidos**
   - CRUD de produtos
   - Vinculação de pedidos
   - Cálculo de custos
   - Relatórios de vendas

9. **Fase 9 - Dashboard e Melhorias**
   - Dashboard executivo
   - Alertas e notificações
   - Exportações avançadas
   - Otimizações

10. **Fase 10 - Testes e Ajustes**
    - Testes completos
    - Correção de bugs
    - Ajustes de performance
    - Documentação final

---

## 15. OBSERVAÇÕES IMPORTANTES

### 15.1. Data de Competência
- **Obrigatória** em contas pagar/receber
- Usada para DRE e análise gerencial
- Pode ser diferente da data de emissão
- Exemplo: Serviço prestado em janeiro (competência), faturado em fevereiro (emissão), recebido em março (caixa)

### 15.2. Sistema de IA de Formas de Pagamento
- Aprendizado contínuo
- Melhora com o tempo
- Requer confirmações do usuário inicialmente
- Pode ser desabilitado por empresa
- Padrões podem ser editados manualmente

### 15.3. Performance
- Índices em todas as FKs
- Índices em datas (competência, vencimento, pagamento)
- Índices em status
- Queries otimizadas
- Cache de relatórios pesados

### 15.4. Backup e Segurança
- Backup diário automático
- Criptografia de dados sensíveis
- Logs de auditoria
- Controle de acesso rigoroso

### 15.5. Consolidação de Empresas
- **Disponível em todos os módulos principais**
- Seleção múltipla de empresas via interface
- Perfis de consolidação salvos por usuário
- Consolidação total ou com detalhamento por empresa
- Eliminação de transações internas (opcional)
- Exportação de relatórios consolidados
- Performance otimizada para grandes volumes

---

## 16. EXEMPLOS DE USO DA CONSOLIDAÇÃO

### 16.1. Exemplo: DRE Consolidada do Grupo
- Seleciona empresas: "Empresa A", "Empresa B", "Empresa C"
- Período: Janeiro a Dezembro de 2024
- Visualização: Consolidada Total
- Resultado: DRE única somando receitas e despesas das 3 empresas
- Exporta para Excel para apresentação à diretoria

### 16.2. Exemplo: Fluxo de Caixa Consolidado
- Seleciona empresas: Todas as empresas do grupo
- Período: Próximos 90 dias
- Visualização: Consolidada com Detalhamento
- Resultado: Mostra fluxo total + fluxo individual de cada empresa
- Identifica qual empresa está gerando mais caixa

### 16.3. Exemplo: Contas a Pagar Consolidadas
- Seleciona empresas: Empresas operacionais
- Filtro: Títulos vencidos
- Visualização: Consolidada Total
- Resultado: Total de títulos vencidos do grupo
- Ação: Prioriza pagamentos mais críticos

### 15.4. Exemplo: Dashboard Consolidado

**Cenário**: Visualizar KPIs consolidados do grupo empresarial

**Passos**:
1. Acessa Dashboard
2. Seleciona modo "Consolidado"
3. Seleciona empresas: "Empresa A", "Empresa B", "Empresa C"
4. Visualiza:
   - Receita Total: R$ 500.000,00 (soma das 3 empresas)
   - Despesa Total: R$ 350.000,00 (soma das 3 empresas)
   - Lucro: R$ 150.000,00
   - Saldo Disponível: R$ 200.000,00 (soma de todas as contas)
5. Gráficos mostram evolução consolidada
6. Comparativo com mês anterior consolidado

### 15.5. Exemplo: Rateio de Pagamento entre Empresas

**Cenário**: Uma conta de energia elétrica compartilhada entre 3 empresas

**Situação**:
- Conta a Pagar: R$ 10.000,00 (Empresa A - principal)
- Despesa compartilhada entre Empresa A, B e C

**Passos**:
1. Cadastra conta a pagar na Empresa A:
   - Descrição: "Energia Elétrica - Compartilhada"
   - Valor Total: R$ 10.000,00
   - Data de Competência: 01/12/2024
2. Clica em "Ratear entre Empresas"
3. Adiciona rateios:
   - Empresa A: R$ 5.000,00 (50%) - Data Competência: 01/12/2024
   - Empresa B: R$ 3.000,00 (30%) - Data Competência: 01/12/2024
   - Empresa C: R$ 2.000,00 (20%) - Data Competência: 01/12/2024
4. Sistema valida: Soma = R$ 10.000,00 ✓
5. Salva conta com rateio

**Resultado nos Relatórios**:
- **DRE Empresa A**: Mostra R$ 5.000,00 em despesas
- **DRE Empresa B**: Mostra R$ 3.000,00 em despesas
- **DRE Empresa C**: Mostra R$ 2.000,00 em despesas
- **DRE Consolidada**: Mostra R$ 10.000,00 (sem duplicação)
- **Fluxo de Caixa**: Quando pago, considera rateios corretamente

### 15.6. Exemplo: Rateio de Recebimento entre Empresas

**Cenário**: Venda conjunta de projeto onde 2 empresas participaram

**Situação**:
- Conta a Receber: R$ 50.000,00 (Empresa A - principal)
- Receita compartilhada: Empresa A (70%) e Empresa B (30%)

**Passos**:
1. Cadastra conta a receber na Empresa A:
   - Descrição: "Projeto Conjunto XYZ"
   - Valor Total: R$ 50.000,00
   - Data de Competência: 15/12/2024
2. Cria rateio:
   - Empresa A: R$ 35.000,00 (70%)
   - Empresa B: R$ 15.000,00 (30%)
3. Salva conta com rateio

**Resultado nos Relatórios**:
- **DRE Empresa A**: Mostra R$ 35.000,00 em receitas
- **DRE Empresa B**: Mostra R$ 15.000,00 em receitas
- **DRE Consolidada**: Mostra R$ 50.000,00 (sem duplicação)

### 15.7. Exemplo: Dashboard com Gráficos Consolidados

**Cenário**: Visualizar gráficos consolidados de receitas e despesas

**Passos**:
1. Acessa Dashboard
2. Seleciona modo "Consolidado"
3. Seleciona empresas: "Empresa A", "Empresa B"
4. Visualiza gráficos:
   - **Gráfico de Linha**: Evolução mensal consolidada (linha única somando ambas)
   - **Gráfico de Barras**: Comparativo mensal consolidado
   - **Gráfico de Pizza**: Distribuição por categoria consolidada
5. Alterna para modo "Individual" para ver linhas separadas por empresa
6. Exporta gráficos consolidados em PDF

### 15.8. Exemplo: Relatório Consolidado com Rateios

**Cenário**: Gerar DRE consolidada considerando rateios

**Situação**:
- Empresa A tem conta rateada com Empresa B
- Empresa B tem conta rateada com Empresa C
- Quer ver DRE consolidada de A+B+C sem duplicação

**Passos**:
1. Acessa módulo DRE
2. Seleciona modo "Consolidado"
3. Seleciona empresas: A, B, C
4. Período: Janeiro a Dezembro 2024
5. Sistema automaticamente:
   - Considera rateios de pagamentos
   - Considera rateios de recebimentos
   - Evita duplicação de valores
   - Mostra valores corretos por empresa no detalhamento
6. Gera DRE consolidada correta
- Seleciona empresas: Grupo completo
- Visualização: KPIs consolidados
- Resultado: Receita total, despesa total, lucro líquido, saldo consolidado
- Comparativo: Mês atual vs mês anterior consolidado

---

**FIM DA DOCUMENTAÇÃO**

Esta documentação cobre toda a arquitetura e funcionalidades do sistema financeiro empresarial, incluindo:

- **Arquitetura MVC**: Sistema desenvolvido em PHP com padrão Model-View-Controller
- **PDO**: Acesso ao banco de dados usando PDO com prepared statements
- **TailwindCSS**: Framework CSS utility-first para interface moderna
- **Sistema de Migrations**: Versionamento e controle de schema do banco de dados
- **Data de competência**: Para relatórios em regime de competência e regime de caixa
- **Sistema inteligente de identificação de formas de pagamento**: Aprendizado automático de padrões
- **Consolidação de múltiplas empresas**: Visualização consolidada em dashboard, relatórios e gráficos
- **Sistema de rateio entre empresas**: Divisão de pagamentos/recebimentos entre múltiplas empresas

