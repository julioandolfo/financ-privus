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
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = :usuario_id";
        $params = ['usuario_id' => $usuarioId];
        
        if ($empresaId !== null) {
            $sql .= " AND (empresa_id = :empresa_id OR empresa_id IS NULL)";
            $params['empresa_id'] = $empresaId;
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
        
        if ($empresaId !== null) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Salva permissões em lote
     */
    public function saveBatch($usuarioId, $permissoes, $empresaId = null)
    {
        // Validar empresa_id se fornecido
        if ($empresaId !== null && !empty($empresaId)) {
            // Verificar se a empresa existe
            $stmtCheck = $this->db->prepare("SELECT id FROM empresas WHERE id = :id LIMIT 1");
            $stmtCheck->execute(['id' => $empresaId]);
            if (!$stmtCheck->fetch()) {
                // Empresa não existe, usar NULL (permissões globais)
                error_log("AVISO: empresa_id {$empresaId} não existe, usando permissões globais (NULL)");
                $empresaId = null;
            }
        } else {
            // Se empresaId for 0, string vazia ou false, considerar como NULL
            $empresaId = null;
        }
        
        // Remove permissões existentes
        $this->deleteByUsuario($usuarioId, $empresaId);
        
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
                $params["empresa_id_{$index}"] = $empresaId; // Agora é NULL se inválido
                $index++;
            }
            
            $sql .= implode(', ', $values);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
        
        return true;
    }
    
    /**
     * Retorna permissões formatadas para exibição
     */
    public function getFormattedPermissions($usuarioId, $empresaId = null)
    {
        $permissoes = $this->findByUsuarioGrouped($usuarioId, $empresaId);
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

