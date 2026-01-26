<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Permissões
 */
class Permissao extends Model
{
    // Importar LogSistema para debug
    private static function logDebug($acao, $mensagem, $dados = null)
    {
        if (class_exists('App\Models\LogSistema')) {
            \App\Models\LogSistema::debug('Permissao', $acao, $mensagem, $dados);
        }
    }

    protected $table = 'permissoes';
    protected $db;
    
    // Módulos disponíveis
    const MODULOS = [
        'empresas' => 'Empresas',
        'usuarios' => 'Usuários',
        'fornecedores' => 'Fornecedores',
        'clientes' => 'Clientes',
        'categorias' => 'Categorias Financeiras',
        'centros_custo' => 'Centros de Custo',
        'contas_bancarias' => 'Contas Bancárias',
        'formas_pagamento' => 'Formas de Pagamento',
        'contas_pagar' => 'Contas a Pagar',
        'contas_receber' => 'Contas a Receber',
        'fluxo_caixa' => 'Fluxo de Caixa',
        'conciliacao' => 'Conciliação',
        'dre' => 'DRE',
        'dfc' => 'DFC',
        'produtos' => 'Produtos',
        'pedidos' => 'Pedidos',
        'integracoes' => 'Integrações',
        'relatorios' => 'Relatórios',
        'configuracoes' => 'Configurações'
    ];
    
    // Ações disponíveis
    const ACOES = [
        'visualizar' => 'Visualizar',
        'criar' => 'Criar',
        'editar' => 'Editar',
        'excluir' => 'Excluir'
    ];
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as permissões de um usuário
     */
    public function findByUsuario($usuarioId, $empresaId = null)
    {
        // Busca todas as permissões do usuário (globais + específicas da empresa)
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = :usuario_id";
        $params = ['usuario_id' => $usuarioId];
        
        // Se empresa_id foi fornecido, busca permissões dessa empresa OU globais (NULL)
        // Se não foi fornecido, busca apenas permissões globais (NULL)
        if ($empresaId !== null && !empty($empresaId)) {
            $sql .= " AND (empresa_id = :empresa_id OR empresa_id IS NULL)";
            $params['empresa_id'] = $empresaId;
        } else {
            // Busca permissões globais (sem empresa) ou todas
            $sql .= " AND (empresa_id IS NULL OR empresa_id IS NOT NULL)"; // Busca todas
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna permissões organizadas por módulo
     */
    public function findByUsuarioGrouped($usuarioId, $empresaId = null)
    {
        $permissoes = $this->findByUsuario($usuarioId, $empresaId);
        $grouped = [];
        
        foreach ($permissoes as $permissao) {
            $modulo = $permissao['modulo'];
            if (!isset($grouped[$modulo])) {
                $grouped[$modulo] = [];
            }
            $grouped[$modulo][] = $permissao;
        }
        
        return $grouped;
    }
    
    /**
     * Verifica se usuário tem permissão
     */
    public function hasPermission($usuarioId, $modulo, $acao, $empresaId = null)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                AND modulo = :modulo 
                AND acao = :acao
                AND (empresa_id = :empresa_id OR empresa_id IS NULL)";
        
        $params = [
            'usuario_id' => $usuarioId,
            'modulo' => $modulo,
            'acao' => $acao,
            'empresa_id' => $empresaId
        ];
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Cria uma permissão
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (usuario_id, modulo, acao, empresa_id) 
                VALUES (:usuario_id, :modulo, :acao, :empresa_id)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'usuario_id' => $data['usuario_id'],
            'modulo' => $data['modulo'],
            'acao' => $data['acao'],
            'empresa_id' => $data['empresa_id'] ?? null
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Remove todas as permissões de um usuário
     */
    public function deleteByUsuario($usuarioId, $empresaId = null)
    {
        $sql = "DELETE FROM {$this->table} WHERE usuario_id = :usuario_id";
        $params = ['usuario_id' => $usuarioId];
        
        if ($empresaId !== null && !empty($empresaId)) {
            // Remove permissões específicas dessa empresa E as globais (NULL)
            $sql .= " AND (empresa_id = :empresa_id OR empresa_id IS NULL)";
            $params['empresa_id'] = $empresaId;
        }
        // Se empresa_id for null/vazio, remove TODAS as permissões do usuário
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Salva permissões em lote
     */
    public function saveBatch($usuarioId, $permissoes, $empresaId = null)
    {
        self::logDebug('saveBatch', 'INÍCIO - Parâmetros recebidos', [
            'usuario_id' => $usuarioId,
            'empresa_id_recebido' => $empresaId,
            'total_permissoes' => count($permissoes),
            'permissoes' => $permissoes
        ]);
        
        // Validar empresa_id se fornecido
        if ($empresaId !== null && !empty($empresaId)) {
            // Verificar se a empresa existe
            $stmtCheck = $this->db->prepare("SELECT id FROM empresas WHERE id = :id LIMIT 1");
            $stmtCheck->execute(['id' => $empresaId]);
            $empresaExiste = $stmtCheck->fetch();
            
            self::logDebug('saveBatch', 'Verificando empresa', [
                'empresa_id' => $empresaId,
                'existe' => $empresaExiste ? 'SIM' : 'NÃO'
            ]);
            
            if (!$empresaExiste) {
                // Empresa não existe, usar NULL (permissões globais)
                self::logDebug('saveBatch', 'Empresa não existe, usando NULL', ['empresa_id_original' => $empresaId]);
                $empresaId = null;
            }
        } else {
            // Se empresaId for 0, string vazia ou false, considerar como NULL
            self::logDebug('saveBatch', 'empresa_id vazio, usando NULL', ['empresa_id_original' => $empresaId]);
            $empresaId = null;
        }
        
        // Remove permissões existentes
        self::logDebug('saveBatch', 'Removendo permissões existentes', [
            'usuario_id' => $usuarioId,
            'empresa_id' => $empresaId
        ]);
        
        $deleteResult = $this->deleteByUsuario($usuarioId, $empresaId);
        
        self::logDebug('saveBatch', 'Resultado da remoção', ['sucesso' => $deleteResult]);
        
        // Insere novas permissões
        if (!empty($permissoes)) {
            $sql = "INSERT INTO {$this->table} (usuario_id, modulo, acao, empresa_id) VALUES ";
            $values = [];
            $params = [];
            $index = 0;
            
            foreach ($permissoes as $permissao) {
                $values[] = "(:usuario_id_{$index}, :modulo_{$index}, :acao_{$index}, :empresa_id_{$index})";
                
                $params["usuario_id_{$index}"] = $usuarioId;
                $params["modulo_{$index}"] = $permissao['modulo'];
                $params["acao_{$index}"] = $permissao['acao'];
                $params["empresa_id_{$index}"] = $empresaId;
                $index++;
            }
            
            $sql .= implode(', ', $values);
            
            self::logDebug('saveBatch', 'SQL de inserção', [
                'sql' => $sql,
                'params' => $params
            ]);
            
            try {
                $stmt = $this->db->prepare($sql);
                $resultado = $stmt->execute($params);
                
                self::logDebug('saveBatch', 'Resultado da inserção', [
                    'sucesso' => $resultado,
                    'rows_affected' => $stmt->rowCount()
                ]);
                
                return $resultado;
            } catch (\Exception $e) {
                self::logDebug('saveBatch', 'ERRO na inserção', [
                    'erro' => $e->getMessage(),
                    'sql' => $sql
                ]);
                throw $e;
            }
        } else {
            self::logDebug('saveBatch', 'Nenhuma permissão para inserir', []);
        }
        
        return true;
    }
    
    /**
     * Retorna permissões formatadas para exibição
     */
    public function getFormattedPermissions($usuarioId, $empresaId = null)
    {
        self::logDebug('getFormattedPermissions', 'Buscando permissões', [
            'usuario_id' => $usuarioId,
            'empresa_id' => $empresaId
        ]);
        
        $permissoes = $this->findByUsuarioGrouped($usuarioId, $empresaId);
        
        self::logDebug('getFormattedPermissions', 'Permissões encontradas', [
            'total_modulos' => count($permissoes),
            'permissoes' => $permissoes
        ]);
        
        $formatted = [];
        
        foreach (self::MODULOS as $moduloKey => $moduloNome) {
            $formatted[$moduloKey] = [
                'nome' => $moduloNome,
                'acoes' => []
            ];
            
            foreach (self::ACOES as $acaoKey => $acaoNome) {
                $formatted[$moduloKey]['acoes'][$acaoKey] = [
                    'nome' => $acaoNome,
                    'permitido' => false
                ];
                
                // Verifica se existe permissão
                foreach ($permissoes[$moduloKey] ?? [] as $permissao) {
                    if ($permissao['acao'] === $acaoKey) {
                        $formatted[$moduloKey]['acoes'][$acaoKey]['permitido'] = true;
                        break;
                    }
                }
            }
        }
        
        return $formatted;
    }
}

