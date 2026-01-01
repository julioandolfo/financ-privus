<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class RegraBancaria extends Model
{
    protected $table = 'regras_classificacao_bancaria';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as regras de uma empresa
     */
    public function findByEmpresa($empresaId, $apenasAtivas = true)
    {
        $sql = "SELECT r.*, 
                       u.nome as criador_nome,
                       cat.nome as categoria_nome,
                       cc.nome as centro_custo_nome
                FROM {$this->table} r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                LEFT JOIN categorias_financeiras cat ON r.categoria_destino_id = cat.id
                LEFT JOIN centros_custo cc ON r.centro_custo_destino_id = cc.id
                WHERE r.empresa_id = :empresa_id";
        
        if ($apenasAtivas) {
            $sql .= " AND r.ativo = 1";
        }
        
        $sql .= " ORDER BY r.prioridade DESC, r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar regra por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar nova regra
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, usuario_id, nome, descricao, tipo_condicao, valor_busca,
                 banco_especifico, tipo_origem, valor_minimo, valor_maximo,
                 categoria_destino_id, centro_custo_destino_id, 
                 fornecedor_destino_id, cliente_destino_id,
                 aprovar_automaticamente, prioridade, ativo) 
                VALUES 
                (:empresa_id, :usuario_id, :nome, :descricao, :tipo_condicao, :valor_busca,
                 :banco_especifico, :tipo_origem, :valor_minimo, :valor_maximo,
                 :categoria_destino_id, :centro_custo_destino_id,
                 :fornecedor_destino_id, :cliente_destino_id,
                 :aprovar_automaticamente, :prioridade, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'usuario_id' => $data['usuario_id'],
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'tipo_condicao' => $data['tipo_condicao'] ?? 'contem',
            'valor_busca' => $data['valor_busca'],
            'banco_especifico' => $data['banco_especifico'] ?? null,
            'tipo_origem' => $data['tipo_origem'] ?? null,
            'valor_minimo' => $data['valor_minimo'] ?? null,
            'valor_maximo' => $data['valor_maximo'] ?? null,
            'categoria_destino_id' => $data['categoria_destino_id'] ?? null,
            'centro_custo_destino_id' => $data['centro_custo_destino_id'] ?? null,
            'fornecedor_destino_id' => $data['fornecedor_destino_id'] ?? null,
            'cliente_destino_id' => $data['cliente_destino_id'] ?? null,
            'aprovar_automaticamente' => $data['aprovar_automaticamente'] ?? 0,
            'prioridade' => $data['prioridade'] ?? 0,
            'ativo' => $data['ativo'] ?? 1
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar regra
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];
        
        $allowed = ['nome', 'descricao', 'tipo_condicao', 'valor_busca',
                   'banco_especifico', 'tipo_origem', 'valor_minimo', 'valor_maximo',
                   'categoria_destino_id', 'centro_custo_destino_id',
                   'fornecedor_destino_id', 'cliente_destino_id',
                   'aprovar_automaticamente', 'prioridade', 'ativo'];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Deletar regra (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Aplicar regras a uma transação
     */
    public function aplicarRegras($transacao, $empresaId)
    {
        $regras = $this->findByEmpresa($empresaId, true);
        
        foreach ($regras as $regra) {
            if ($this->regraSeAplica($regra, $transacao)) {
                // Incrementar contador
                $this->incrementarContador($regra['id']);
                
                return [
                    'regra_id' => $regra['id'],
                    'regra_nome' => $regra['nome'],
                    'categoria_id' => $regra['categoria_destino_id'],
                    'centro_custo_id' => $regra['centro_custo_destino_id'],
                    'fornecedor_id' => $regra['fornecedor_destino_id'],
                    'cliente_id' => $regra['cliente_destino_id'],
                    'aprovar_automaticamente' => $regra['aprovar_automaticamente']
                ];
            }
        }
        
        return null; // Nenhuma regra se aplica
    }
    
    /**
     * Verificar se regra se aplica à transação
     */
    private function regraSeAplica($regra, $transacao)
    {
        // Verificar banco específico
        if ($regra['banco_especifico'] && $regra['banco_especifico'] != $transacao['banco']) {
            return false;
        }
        
        // Verificar tipo de origem
        if ($regra['tipo_origem'] && $regra['tipo_origem'] != $transacao['origem']) {
            return false;
        }
        
        // Verificar valor mínimo
        if ($regra['valor_minimo'] && abs($transacao['valor']) < $regra['valor_minimo']) {
            return false;
        }
        
        // Verificar valor máximo
        if ($regra['valor_maximo'] && abs($transacao['valor']) > $regra['valor_maximo']) {
            return false;
        }
        
        // Verificar condição de texto
        $descricao = strtolower($transacao['descricao_original']);
        $valorBusca = strtolower($regra['valor_busca']);
        
        switch ($regra['tipo_condicao']) {
            case 'contem':
                return strpos($descricao, $valorBusca) !== false;
            case 'igual':
                return $descricao === $valorBusca;
            case 'comeca_com':
                return strpos($descricao, $valorBusca) === 0;
            case 'termina_com':
                return substr($descricao, -strlen($valorBusca)) === $valorBusca;
            case 'regex':
                return preg_match('/' . $valorBusca . '/i', $descricao);
            default:
                return false;
        }
    }
    
    /**
     * Incrementar contador de uso da regra
     */
    private function incrementarContador($regraId)
    {
        $sql = "UPDATE {$this->table} 
                SET vezes_aplicada = vezes_aplicada + 1,
                    ultima_aplicacao = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $regraId]);
    }
}
