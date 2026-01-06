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
                'version' => '1.0.0',
                'description' => 'API RESTful para integração com o Sistema Financeiro Empresarial. Gerencie contas a pagar/receber, produtos, clientes, fornecedores e movimentações financeiras.',
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
                                        'descricao' => 'Pagamento de serviços',
                                        'valor' => 1500.00,
                                        'data_vencimento' => '2026-01-15',
                                        'data_pagamento' => null,
                                        'status' => 'pendente',
                                        'categoria_id' => 3,
                                        'centro_custo_id' => 2,
                                        'conta_bancaria_id' => 1,
                                        'forma_pagamento_id' => 1,
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
                                    'descricao' => 'Pagamento de serviços',
                                    'valor' => 1500.00,
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
                                'descricao' => ['type' => 'string', 'required' => true, 'description' => 'Descrição da conta'],
                                'valor' => ['type' => 'decimal', 'required' => true, 'description' => 'Valor da conta'],
                                'data_vencimento' => ['type' => 'date', 'required' => true, 'description' => 'Data de vencimento (YYYY-MM-DD)'],
                                'categoria_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da categoria'],
                                'centro_custo_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID do centro de custo'],
                                'conta_bancaria_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da conta bancária'],
                                'forma_pagamento_id' => ['type' => 'integer', 'required' => false, 'description' => 'ID da forma de pagamento'],
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
                                'descricao' => ['type' => 'string', 'required' => false, 'description' => 'Descrição da conta'],
                                'valor' => ['type' => 'decimal', 'required' => false, 'description' => 'Valor da conta'],
                                'data_vencimento' => ['type' => 'date', 'required' => false, 'description' => 'Data de vencimento'],
                                'data_pagamento' => ['type' => 'date', 'required' => false, 'description' => 'Data de pagamento (para marcar como pago)'],
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
                    'description' => 'Gerenciamento de contas a receber',
                    'base_url' => '/api/v1/contas-receber',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/contas-receber',
                            'description' => 'Lista todas as contas a receber',
                            'params' => [
                                ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filtrar por status'],
                                ['name' => 'cliente_id', 'type' => 'integer', 'required' => false, 'description' => 'Filtrar por cliente'],
                            ],
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/contas-receber',
                            'description' => 'Cria uma nova conta a receber',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true],
                                'cliente_id' => ['type' => 'integer', 'required' => true],
                                'descricao' => ['type' => 'string', 'required' => true],
                                'valor' => ['type' => 'decimal', 'required' => true],
                                'data_vencimento' => ['type' => 'date', 'required' => true],
                            ],
                        ],
                    ]
                ],
                
                'produtos' => [
                    'name' => 'Produtos',
                    'description' => 'Gerenciamento de produtos',
                    'base_url' => '/api/v1/produtos',
                    'methods' => [
                        [
                            'method' => 'GET',
                            'endpoint' => '/api/v1/produtos',
                            'description' => 'Lista todos os produtos',
                        ],
                        [
                            'method' => 'POST',
                            'endpoint' => '/api/v1/produtos',
                            'description' => 'Cria um novo produto',
                            'body' => [
                                'empresa_id' => ['type' => 'integer', 'required' => true],
                                'nome' => ['type' => 'string', 'required' => true],
                                'codigo' => ['type' => 'string', 'required' => false],
                                'descricao' => ['type' => 'text', 'required' => false],
                                'preco_custo' => ['type' => 'decimal', 'required' => false],
                                'preco_venda' => ['type' => 'decimal', 'required' => true],
                                'estoque_atual' => ['type' => 'integer', 'required' => false],
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
