<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ApiToken;

/**
 * Controller para Documentação da API
 */
class ApiDocController extends Controller
{
    /**
     * Exibe a documentação completa da API
     */
    public function index(Request $request, Response $response)
    {
        // Verificar se usuário está logado para mostrar seus tokens
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $tokens = [];
        
        if ($usuarioId) {
            $tokenModel = new ApiToken();
            $tokens = $tokenModel->findByUsuario($usuarioId);
        }
        
        // Definir estrutura da documentação
        $apiDoc = $this->getApiDocumentation();
        
        // Renderizar sem layout (a view tem HTML completo)
        $viewPath = __DIR__ . '/../views/api_docs/index.php';
        
        // Extrair variáveis para a view
        extract([
            'apiDoc' => $apiDoc,
            'tokens' => $tokens,
            'baseUrl' => $this->getBaseUrl(),
        ]);
        
        // Incluir view diretamente sem layout
        include $viewPath;
        return;
    }
    
    /**
     * Retorna URL base da API
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    /**
     * Estrutura completa da documentação da API
     */
    private function getApiDocumentation()
    {
        return [
            'info' => [
                'title' => 'API Financeiro Empresarial',
                'version' => '1.1.0',
                'description' => 'API RESTful para integração com o Sistema Financeiro Empresarial. Gerencie contas a pagar/receber, produtos, clientes, fornecedores e movimentações financeiras. ⭐ NOVO: Suporte a pedidos com produtos, auto-cadastro via SKU e cálculo automático de lucro/margem.',
                'changelog' => [
                    'v1.1.0 (Janeiro 2026)' => [
                        '✅ Suporte a Pedidos Vinculados em Contas a Receber',
                        '✅ Auto-cadastro de produtos via SKU',
                        '✅ Cálculo automático de Lucro e Margem',
                        '✅ Campo pedido_id em Contas a Receber',
                        '✅ Campo sku em Produtos (único por empresa)',
                    ],
                    'v1.0.0 (Dezembro 2025)' => [
                        '🚀 Lançamento inicial da API',
                        '✅ Endpoints básicos para todos os módulos',
                        '✅ Sistema de autenticação via Bearer Token',
                    ]
                ]
            ],
            
            'authentication' => [
                'type' => 'Bearer Token',
                'description' => 'Todas as requisições à API devem incluir um token de autenticação no header Authorization.',
                'header_name' => 'Authorization',
                'header_format' => 'Bearer {seu_token_aqui}',
            ],
            
            'endpoints' => [
                'contas_pagar' => [
                    'name' => 'Contas a Pagar',
                    'description' => 'Gerenciamento de contas a pagar',
                    'base_url' => '/api/v1/contas-pagar',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/contas-pagar',
                            'description' => 'Lista todas as contas a pagar',
                            'params' => [
                                ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filtrar por status (pendente, pago, vencido)'],
                                ['name' => 'fornecedor_id', 'type' => 'integer', 'required' => false, 'description' => 'Filtrar por fornecedor'],
                                ['name' => 'data_inicio', 'type' => 'date', 'required' => false, 'description' => 'Data inicial (YYYY-MM-DD)'],
                                ['name' => 'data_fim', 'type' => 'date', 'required' => false, 'description' => 'Data final (YYYY-MM-DD)'],
                            ],
                            'response' => [
                                'success' => true,
                                'data' => [
                                    [
                                        'id' => 1,
                                        'empresa_id' => 1,
                                        'fornecedor_id' => 5,
                                        'fornecedor_nome' => 'Fornecedor LTDA',
                                        'categoria_id' => 3,
                                        'categoria_nome' => 'Despesas Operacionais',
                                        'descricao' => 'Pagamento de serviços',
                                        'numero_documento' => 'NF-001234',
                                        'valor_total' => 1500.00,
                                        'valor_pago' => 0.00,
                                        'data_emissao' => '2026-01-10',
                                        'data_competencia' => '2026-01-10',
                                        'data_vencimento' => '2026-01-15',
                                        'data_pagamento' => null,
                                        'status' => 'pendente',
                                        'centro_custo_id' => 2,
                                        'centro_custo_nome' => 'TI',
                                        'conta_bancaria_id' => 1,
                                        'banco_nome' => 'Banco do Brasil',
                                        'forma_pagamento_id' => 1,
                                        'forma_pagamento_nome' => 'Boleto',
                                        'tem_rateio' => false,
                                        'observacoes' => 'Nota fiscal 12345',
                                    ]
                                ],
                                'total' => 1
                            ]
                        ],
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/contas-pagar/{id}',
                            'description' => 'Busca uma conta específica',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID da conta'],
                            ],
                            'response' => [
                                'success' => true,
                                'data' => [
                                    'id' => 1,
                                    'empresa_id' => 1,
                                    'fornecedor_id' => 5,
                                    'fornecedor_nome' => 'Fornecedor LTDA',
                                    'categoria_id' => 3,
                                    'categoria_nome' => 'Despesas Operacionais',
                                    'descricao' => 'Pagamento de serviços',
                                    'numero_documento' => 'NF-001234',
                                    'valor_total' => 1500.00,
                                    'valor_pago' => 0.00,
                                    'data_emissao' => '2026-01-10',
                                    'data_competencia' => '2026-01-10',
                                    'data_vencimento' => '2026-01-15',
                                    'data_pagamento' => null,
                                    'status' => 'pendente',
                                    'centro_custo_id' => 2,
                                    'conta_bancaria_id' => 1,
                                    'forma_pagamento_id' => 1,
                                    'tem_rateio' => false,
                                    'observacoes' => 'Nota fiscal 12345',
                                ]
                            ]
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/contas-pagar',
                            'description' => 'Cria uma nova conta a pagar',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da empresa'],
                                'fornecedor_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID do fornecedor'],
                                'categoria_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da categoria financeira'],
                                'descricao' => ['type' => 'string', 'required' => true, 'description' => 'Descrição da conta'],
                                'valor_total' => ['type' => 'decimal', 'required' => true, 'description' => 'Valor total da conta'],
                                'numero_documento' => ['type' => 'string', 'required' => false, 'description' => 'Número do documento (nota fiscal, etc)'],
                                'data_emissao' => ['type' => 'date', 'required' => false, 'description' => 'Data de emissão (YYYY-MM-DD). Padrão: data atual'],
                                'data_competencia' => ['type' => 'date', 'required' => true, 'description' => 'Data de competência (YYYY-MM-DD). IMPORTANTE: Usado para regime de competência'],
                                'data_vencimento' => ['type' => 'date', 'required' => true, 'description' => 'Data de vencimento (YYYY-MM-DD)'],
                                'data_pagamento' => ['type' => 'date', 'required' => false, 'description' => 'Data de pagamento (YYYY-MM-DD). Se informado, marca como pago'],
                                'valor_pago' => ['type' => 'decimal', 'required' => false, 'description' => 'Valor já pago. Padrão: 0'],
                                'status' => ['type' => 'string', 'required' => false, 'description' => 'Status: pendente, pago, parcial, vencido, cancelado. Padrão: pendente'],
                                'centro_custo_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID do centro de custo'],
                                'conta_bancaria_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da conta bancária'],
                                'forma_pagamento_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da forma de pagamento'],
                                'tem_rateio' => ['type' => 'boolean', 'required' => false, 'description' => 'Indica se a conta tem rateio entre empresas. Padrão: false'],
                                'observacoes' => ['type' => 'text', 'required' => false, 'description' => 'Observações adicionais'],
                            ],
                            'response' => [
                                'success' => true,
                                'message' => 'Conta a pagar criada com sucesso!',
                                'data' => ['id' => 1]
                            ]
                        ],
                        [
                            'method' => 'PUT',
                            'endpoint' => '/api/v1/contas-pagar/{id}',
                            'description' => 'Atualiza uma conta existente',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID da conta'],
                            ],
                            'body' => [
                                'fornecedor_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID do fornecedor'],
                                'categoria_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da categoria financeira'],
                                'descricao' => ['type' => 'string', 'required' => false, 'description' => 'Descrição da conta'],
                                'valor_total' => ['type' => 'decimal', 'required' => false, 'description' => 'Valor total da conta'],
                                'numero_documento' => ['type' => 'string', 'required' => false, 'description' => 'Número do documento'],
                                'data_emissao' => ['type' => 'date', 'required' => false, 'description' => 'Data de emissão (YYYY-MM-DD)'],
                                'data_competencia' => ['type' => 'date', 'required' => false, 'description' => 'Data de competência (YYYY-MM-DD). IMPORTANTE: Usado para regime de competência'],
                                'data_vencimento' => ['type' => 'date', 'required' => false, 'description' => 'Data de vencimento (YYYY-MM-DD)'],
                                'data_pagamento' => ['type' => 'date', 'required' => false, 'description' => 'Data de pagamento (para marcar como pago)'],
                                'valor_pago' => ['type' => 'decimal', 'required' => false, 'description' => 'Valor já pago'],
                                'status' => ['type' => 'string', 'required' => false, 'description' => 'Status: pendente, pago, parcial, vencido, cancelado'],
                                'centro_custo_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID do centro de custo'],
                                'conta_bancaria_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da conta bancária'],
                                'forma_pagamento_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da forma de pagamento'],
                                'observacoes' => ['type' => 'text', 'required' => false, 'description' => 'Observações'],
                            ],
                            'response' => [
                                'success' => true,
                                'message' => 'Conta a pagar atualizada com sucesso!'
                            ]
                        ],
                        [
                            'method' => 'DELETE',
                            'endpoint' => '/api/v1/contas-pagar/{id}',
                            'description' => 'Exclui uma conta a pagar',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID da conta'],
                            ],
                            'response' => [
                                'success' => true,
                                'message' => 'Conta a pagar excluída com sucesso!'
                            ]
                        ],
                    ]
                ],
                
                'contas_receber' => [
                    'name' => 'Contas a Receber',
                    'description' => 'Gerenciamento de contas a receber (com suporte a pedidos e produtos)',
                    'base_url' => '/api/v1/contas-receber',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/contas-receber',
                            'description' => 'Lista todas as contas a receber',
                            'params' => [
                                ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filtrar por status (pendente, recebido, vencido, parcial, cancelado)'],
                                ['name' => 'cliente_id', 'type' => 'integer', 'required' => false, 'description' => 'Filtrar por cliente'],
                                ['name' => 'data_inicio', 'type' => 'date', 'required' => false, 'description' => 'Data inicial (YYYY-MM-DD)'],
                                ['name' => 'data_fim', 'type' => 'date', 'required' => false, 'description' => 'Data final (YYYY-MM-DD)'],
                            ],
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/contas-receber',
                            'description' => 'Cria uma nova conta a receber SIMPLES (sem produtos)',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da empresa'],
                                'cliente_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID do cliente'],
                                'categoria_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da categoria financeira'],
                                'descricao' => ['type' => 'string', 'required' => true, 'description' => 'Descrição da conta'],
                                'valor_total' => ['type' => 'decimal', 'required' => true, 'description' => 'Valor total da conta'],
                                'numero_documento' => ['type' => 'string', 'required' => false, 'description' => 'Número do documento (nota fiscal, etc)'],
                                'data_emissao' => ['type' => 'date', 'required' => false, 'description' => 'Data de emissão (YYYY-MM-DD). Padrão: data atual'],
                                'data_competencia' => ['type' => 'date', 'required' => true, 'description' => 'Data de competência (YYYY-MM-DD)'],
                                'data_vencimento' => ['type' => 'date', 'required' => true, 'description' => 'Data de vencimento (YYYY-MM-DD)'],
                                'centro_custo_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID do centro de custo'],
                                'observacoes' => ['type' => 'text', 'required' => false, 'description' => 'Observações adicionais'],
                            ],
                            'response' => [
                                'success' => true,
                                'message' => 'Conta a receber criada com sucesso!',
                                'data' => ['id' => 1]
                            ]
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/contas-receber',
                            'description' => '⭐ Cria conta a receber COM PEDIDO E PRODUTOS (calcula lucro/margem automaticamente)',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da empresa'],
                                'cliente_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID do cliente'],
                                'categoria_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da categoria financeira'],
                                'descricao' => ['type' => 'string', 'required' => true, 'description' => 'Descrição da venda'],
                                'data_vencimento' => ['type' => 'date', 'required' => true, 'description' => 'Data de vencimento (YYYY-MM-DD)'],
                                'data_emissao' => ['type' => 'date', 'required' => false, 'description' => 'Data de emissão (padrão: hoje)'],
                                'data_competencia' => ['type' => 'date', 'required' => true, 'description' => 'Data de competência (YYYY-MM-DD)'],
                                'numero_documento' => ['type' => 'string', 'required' => false, 'description' => 'Número da NF/Recibo'],
                                'criar_pedido' => ['type' => 'boolean', 'required' => true, 'description' => '⭐ OBRIGATÓRIO: true para criar pedido com produtos'],
                                'pedido' => ['type' => 'object', 'required' => true, 'description' => 'Dados do pedido', 'fields' => [
                                    'numero_pedido' => ['type' => 'string', 'required' => false, 'description' => 'Número do pedido (auto-gerado se omitido)'],
                                    'data_pedido' => ['type' => 'date', 'required' => false, 'description' => 'Data do pedido (padrão: hoje)'],
                                    'status' => ['type' => 'string', 'required' => false, 'description' => 'Status: pendente, processando, concluido, cancelado (padrão: pendente)'],
                                    'produtos' => ['type' => 'array', 'required' => true, 'description' => '⭐ Array de produtos do pedido', 'items' => [
                                        'produto_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID do produto (use OU produto_id OU sku)'],
                                        'sku' => ['type' => 'string', 'required' => false, 'description' => '⭐ SKU do produto (busca/cria automaticamente)'],
                                        'nome' => ['type' => 'string', 'required' => false, 'description' => 'Nome do produto (obrigatório se usar SKU novo)'],
                                        'quantidade' => ['type' => 'decimal', 'required' => true, 'description' => 'Quantidade vendida (aceita decimais)'],
                                        'valor_unitario' => ['type' => 'decimal', 'required' => true, 'description' => 'Preço de venda unitário'],
                                        'custo_unitario' => ['type' => 'decimal', 'required' => false, 'description' => 'Custo unitário (busca do cadastro se omitido)'],
                                        'unidade_medida' => ['type' => 'string', 'required' => false, 'description' => 'UN, KG, L, etc (padrão: UN)'],
                                        'codigo' => ['type' => 'string', 'required' => false, 'description' => 'Código interno (auto-gerado para novos)'],
                                        'descricao' => ['type' => 'text', 'required' => false, 'description' => 'Descrição do produto (para auto-cadastro)'],
                                    ]],
                                ]],
                            ],
                            'response' => [
                                'success' => true,
                                'id' => 15,
                                'pedido_id' => 20,
                                'valor_total' => 250.00,
                                'valor_custo_total' => 150.00,
                                'lucro' => 100.00,
                                'margem_lucro' => 66.67,
                                'produtos_criados' => 2,
                                'produtos_vinculados' => 2,
                                'message' => 'Conta a receber criada com sucesso. 2 produtos foram criados automaticamente.'
                            ],
                            'example' => [
                                'empresa_id' => 1,
                                'cliente_id' => 10,
                                'categoria_id' => 15,
                                'descricao' => 'Venda de produtos via API',
                                'data_vencimento' => '2026-02-28',
                                'data_emissao' => '2026-01-20',
                                'data_competencia' => '2026-01-20',
                                'numero_documento' => 'NF-789',
                                'criar_pedido' => true,
                                'pedido' => [
                                    'numero_pedido' => 'PED-123',
                                    'data_pedido' => '2026-01-20',
                                    'status' => 'concluido',
                                    'produtos' => [
                                        [
                                            'sku' => 'PROD-EXT-001',
                                            'nome' => 'Produto da API',
                                            'quantidade' => 5,
                                            'valor_unitario' => 100.00,
                                            'custo_unitario' => 60.00,
                                            'unidade_medida' => 'UN'
                                        ],
                                        [
                                            'produto_id' => 2,
                                            'quantidade' => 2,
                                            'valor_unitario' => 250.00
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ],
                
                'produtos' => [
                    'name' => 'Produtos',
                    'description' => 'Gerenciamento de produtos (com suporte a SKU para auto-cadastro)',
                    'base_url' => '/api/v1/produtos',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/produtos',
                            'description' => 'Lista todos os produtos',
                            'params' => [
                                ['name' => 'empresa_id', 'type' => 'integer', 'required' => false, 'description' => 'Filtrar por empresa'],
                                ['name' => 'busca', 'type' => 'string', 'required' => false, 'description' => 'Buscar por código, SKU ou nome'],
                                ['name' => 'categoria_id', 'type' => 'integer', 'required' => false, 'description' => 'Filtrar por categoria'],
                            ],
                            'response' => [
                                'success' => true,
                                'data' => [
                                    [
                                        'id' => 1,
                                        'empresa_id' => 1,
                                        'codigo' => 'PROD001',
                                        'sku' => 'SKU-PROD-001',
                                        'nome' => 'Produto Exemplo',
                                        'custo_unitario' => 60.00,
                                        'preco_venda' => 99.90,
                                        'margem_lucro' => 66.5,
                                        'estoque' => 50,
                                        'unidade_medida' => 'UN',
                                        'ativo' => true
                                    ]
                                ]
                            ]
                        ],
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/produtos/{id}',
                            'description' => 'Busca um produto específico',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID do produto'],
                            ],
                            'response' => [
                                'success' => true,
                                'data' => [
                                    'id' => 1,
                                    'empresa_id' => 1,
                                    'codigo' => 'PROD001',
                                    'sku' => 'SKU-PROD-001',
                                    'codigo_barras' => '7891234567890',
                                    'nome' => 'Produto Exemplo',
                                    'descricao' => 'Descrição detalhada',
                                    'custo_unitario' => 60.00,
                                    'preco_venda' => 99.90,
                                    'margem_lucro' => 66.5,
                                    'unidade_medida' => 'UN',
                                    'estoque' => 50,
                                    'estoque_minimo' => 10,
                                    'categoria_id' => 5,
                                    'categoria_nome' => 'Eletrônicos',
                                    'ativo' => true
                                ]
                            ]
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/produtos',
                            'description' => 'Cria um novo produto',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da empresa'],
                                'codigo' => ['type' => 'string', 'required' => true, 'description' => 'Código interno do produto'],
                                'sku' => ['type' => 'string', 'required' => false, 'description' => '⭐ SKU - Identificador único para integração (único por empresa)'],
                                'codigo_barras' => ['type' => 'string', 'required' => false, 'description' => 'Código de barras EAN-13'],
                                'nome' => ['type' => 'string', 'required' => true, 'description' => 'Nome do produto'],
                                'descricao' => ['type' => 'text', 'required' => false, 'description' => 'Descrição detalhada'],
                                'custo_unitario' => ['type' => 'decimal', 'required' => true, 'description' => 'Custo de compra/produção'],
                                'preco_venda' => ['type' => 'decimal', 'required' => true, 'description' => 'Preço de venda'],
                                'unidade_medida' => ['type' => 'string', 'required' => false, 'description' => 'UN, KG, L, M, CX, etc (padrão: UN)'],
                                'estoque' => ['type' => 'decimal', 'required' => false, 'description' => 'Quantidade em estoque (padrão: 0)'],
                                'estoque_minimo' => ['type' => 'decimal', 'required' => false, 'description' => 'Estoque mínimo para alerta (padrão: 0)'],
                                'categoria_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da categoria do produto'],
                            ],
                            'response' => [
                                'success' => true,
                                'id' => 1,
                                'margem_lucro' => 66.5,
                                'message' => 'Produto criado com sucesso'
                            ],
                            'example' => [
                                'empresa_id' => 1,
                                'codigo' => 'PROD-002',
                                'sku' => 'SKU-PROD-002',
                                'nome' => 'Novo Produto',
                                'custo_unitario' => 100.00,
                                'preco_venda' => 200.00,
                                'unidade_medida' => 'UN',
                                'estoque' => 50,
                                'estoque_minimo' => 10
                            ]
                        ],
                        [
                            'method' => 'PUT',
                            'endpoint' => '/api/v1/produtos/{id}',
                            'description' => 'Atualiza um produto',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID do produto'],
                            ],
                            'body' => [
                                'codigo' => ['type' => 'string', 'required' => false, 'description' => 'Código interno'],
                                'sku' => ['type' => 'string', 'required' => false, 'description' => 'SKU do produto'],
                                'nome' => ['type' => 'string', 'required' => false, 'description' => 'Nome do produto'],
                                'custo_unitario' => ['type' => 'decimal', 'required' => false, 'description' => 'Custo unitário'],
                                'preco_venda' => ['type' => 'decimal', 'required' => false, 'description' => 'Preço de venda'],
                                'estoque' => ['type' => 'decimal', 'required' => false, 'description' => 'Quantidade em estoque'],
                            ],
                            'response' => [
                                'success' => true,
                                'margem_lucro' => 75.0,
                                'message' => 'Produto atualizado com sucesso'
                            ]
                        ],
                        [
                            'method' => 'DELETE',
                            'endpoint' => '/api/v1/produtos/{id}',
                            'description' => 'Exclui um produto (soft delete)',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID do produto'],
                            ],
                            'response' => [
                                'success' => true,
                                'message' => 'Produto excluído com sucesso'
                            ]
                        ],
                    ]
                ],
                
                'pedidos' => [
                    'name' => 'Pedidos',
                    'description' => 'Gerenciamento de pedidos vinculados',
                    'base_url' => '/api/v1/pedidos',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/pedidos',
                            'description' => 'Lista todos os pedidos',
                            'params' => [
                                ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filtrar por status (pendente, processando, concluido, cancelado)'],
                                ['name' => 'origem', 'type' => 'string', 'required' => false, 'description' => 'Filtrar por origem (woocommerce, manual, externo)'],
                                ['name' => 'cliente_id', 'type' => 'integer', 'required' => false, 'description' => 'Filtrar por cliente'],
                                ['name' => 'data_inicio', 'type' => 'date', 'required' => false, 'description' => 'Data inicial (YYYY-MM-DD)'],
                                ['name' => 'data_fim', 'type' => 'date', 'required' => false, 'description' => 'Data final (YYYY-MM-DD)'],
                            ],
                            'response' => [
                                'success' => true,
                                'data' => [
                                    [
                                        'id' => 1,
                                        'empresa_id' => 1,
                                        'cliente_id' => 10,
                                        'cliente_nome' => 'Cliente Exemplo',
                                        'numero_pedido' => 'PED-2026-001',
                                        'data_pedido' => '2026-01-06',
                                        'total' => 299.90,
                                        'status' => 'pendente',
                                        'origem' => 'manual',
                                        'total_itens' => 3,
                                    ]
                                ],
                                'total' => 1
                            ]
                        ],
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/pedidos/{id}',
                            'description' => 'Busca um pedido específico com seus itens',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID do pedido'],
                            ],
                            'response' => [
                                'success' => true,
                                'data' => [
                                    'id' => 1,
                                    'numero_pedido' => 'PED-2026-001',
                                    'total' => 299.90,
                                    'status' => 'pendente',
                                    'itens' => [
                                        [
                                            'produto_id' => 5,
                                            'produto_nome' => 'Produto A',
                                            'quantidade' => 2,
                                            'preco_unitario' => 99.90,
                                            'subtotal' => 199.80,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/pedidos',
                            'description' => 'Cria um novo pedido',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true, 'description' => 'ID da empresa'],
                                'cliente_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID do cliente'],
                                'numero_pedido' => ['type' => 'string', 'required' => false, 'description' => 'Número do pedido (gerado automaticamente se não fornecido)'],
                                'data_pedido' => ['type' => 'date', 'required' => true, 'description' => 'Data do pedido (YYYY-MM-DD)'],
                                'total' => ['type' => 'decimal', 'required' => true, 'description' => 'Valor total do pedido'],
                                'status' => ['type' => 'string', 'required' => false, 'description' => 'Status (padrão: pendente)'],
                                'origem' => ['type' => 'string', 'required' => false, 'description' => 'Origem (padrão: externo)'],
                                'observacoes' => ['type' => 'text', 'required' => false, 'description' => 'Observações do pedido'],
                                'itens' => ['type' => 'array', 'required' => false, 'description' => 'Array de itens do pedido'],
                            ],
                            'response' => [
                                'success' => true,
                                'id' => 1,
                                'message' => 'Pedido criado com sucesso'
                            ]
                        ],
                        [
                            'method' => 'PUT',
                            'endpoint' => '/api/v1/pedidos/{id}',
                            'description' => 'Atualiza um pedido',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID do pedido'],
                            ],
                            'body' => [
                                'status' => ['type' => 'string', 'required' => false, 'description' => 'Novo status do pedido'],
                                'total' => ['type' => 'decimal', 'required' => false, 'description' => 'Novo total'],
                                'observacoes' => ['type' => 'text', 'required' => false, 'description' => 'Observações'],
                            ],
                        ],
                        [
                            'method' => 'DELETE',
                            'endpoint' => '/api/v1/pedidos/{id}',
                            'description' => 'Exclui um pedido',
                            'params' => [
                                ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'ID do pedido'],
                            ],
                        ],
                    ]
                ],
                
                'clientes' => [
                    'name' => 'Clientes',
                    'description' => 'Gerenciamento de clientes',
                    'base_url' => '/api/v1/clientes',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/clientes',
                            'description' => 'Lista todos os clientes',
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/clientes',
                            'description' => 'Cria um novo cliente',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true],
                                'nome' => ['type' => 'string', 'required' => true],
                                'email' => ['type' => 'email', 'required' => false],
                                'telefone' => ['type' => 'string', 'required' => false],
                                'cpf_cnpj' => ['type' => 'string', 'required' => false],
                            ],
                        ],
                    ]
                ],
                
                'fornecedores' => [
                    'name' => 'Fornecedores',
                    'description' => 'Gerenciamento de fornecedores',
                    'base_url' => '/api/v1/fornecedores',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/fornecedores',
                            'description' => 'Lista todos os fornecedores',
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/fornecedores',
                            'description' => 'Cria um novo fornecedor',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true],
                                'nome' => ['type' => 'string', 'required' => true],
                                'email' => ['type' => 'email', 'required' => false],
                                'telefone' => ['type' => 'string', 'required' => false],
                                'cpf_cnpj' => ['type' => 'string', 'required' => false],
                            ],
                        ],
                    ]
                ],
                
                'movimentacoes' => [
                    'name' => 'Movimentações de Caixa',
                    'description' => 'Gerenciamento de movimentações financeiras',
                    'base_url' => '/api/v1/movimentacoes',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/movimentacoes',
                            'description' => 'Lista todas as movimentações',
                            'params' => [
                                ['name' => 'tipo', 'type' => 'string', 'required' => false, 'description' => 'Filtrar por tipo (entrada/saida)'],
                            ],
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/movimentacoes',
                            'description' => 'Cria uma nova movimentação',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true],
                                'tipo' => ['type' => 'string', 'required' => true, 'description' => 'entrada ou saida'],
                                'valor' => ['type' => 'decimal', 'required' => true],
                                'descricao' => ['type' => 'string', 'required' => true],
                                'data' => ['type' => 'date', 'required' => true],
                            ],
                        ],
                    ]
                ],
            ],
            
            'errors' => [
                ['code' => 400, 'message' => 'Bad Request', 'description' => 'Requisição inválida ou parâmetros faltando'],
                ['code' => 401, 'message' => 'Unauthorized', 'description' => 'Token inválido ou ausente'],
                ['code' => 403, 'message' => 'Forbidden', 'description' => 'Token sem permissão para esta ação'],
                ['code' => 404, 'message' => 'Not Found', 'description' => 'Recurso não encontrado'],
                ['code' => 422, 'message' => 'Unprocessable Entity', 'description' => 'Erro de validação de dados'],
                ['code' => 500, 'message' => 'Internal Server Error', 'description' => 'Erro interno do servidor'],
            ],
        ];
    }
}
