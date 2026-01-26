<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Padrões de Importação de Extrato
 */
class PadraoImportacaoExtrato extends Model
{
    protected $table = 'padroes_importacao_extrato';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca padrão por descrição normalizada
     */
    public function findByDescricao($descricaoNormalizada, $usuarioId, $empresaId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE descricao_padrao = :descricao 
                AND usuario_id = :usuario_id 
                AND empresa_id = :empresa_id 
                AND ativo = 1 
                ORDER BY usos DESC, ultimo_uso_em DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'descricao' => $descricaoNormalizada,
            'usuario_id' => $usuarioId,
            'empresa_id' => $empresaId
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Busca padrão similar (usando LIKE)
     */
    public function findSimilar($descricao, $usuarioId, $empresaId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (descricao_padrao LIKE :descricao_padrao OR descricao_original LIKE :descricao_original)
                AND usuario_id = :usuario_id 
                AND empresa_id = :empresa_id 
                AND ativo = 1 
                ORDER BY usos DESC, ultimo_uso_em DESC 
                LIMIT 1";
        
        $descricaoLike = '%' . $descricao . '%';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'descricao_padrao' => $descricaoLike,
            'descricao_original' => $descricaoLike,
            'usuario_id' => $usuarioId,
            'empresa_id' => $empresaId
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Cria ou atualiza padrão
     */
    public function saveOrUpdate($data)
    {
        $padrao = $this->findByDescricao($data['descricao_padrao'], $data['usuario_id'], $data['empresa_id']);
        
        if ($padrao) {
            // Atualiza padrão existente
            $data['usos'] = ($padrao['usos'] ?? 0) + 1;
            $data['ultimo_uso_em'] = date('Y-m-d H:i:s');
            return $this->update($padrao['id'], $data);
        } else {
            // Cria novo padrão
            $data['usos'] = 1;
            $data['ultimo_uso_em'] = date('Y-m-d H:i:s');
            return $this->create($data);
        }
    }
    
    /**
     * Cria novo padrão
     */
    public function create($data)
    {
        $columns = [
            'usuario_id', 'empresa_id', 'descricao_padrao', 'descricao_original', 'tipo_padrao',
            'categoria_id', 'centro_custo_id', 'fornecedor_id', 'conta_bancaria_id',
            'forma_pagamento_id', 'tem_rateio', 'observacoes_padrao', 'usos', 'ultimo_uso_em'
        ];
        
        $insertData = [];
        $placeholders = [];
        
        foreach ($columns as $column) {
            if (array_key_exists($column, $data)) {
                $insertData[$column] = $data[$column];
                $placeholders[] = ":{$column}";
            }
        }
        
        $columnNames = implode(', ', array_keys($insertData));
        $placeholderStr = implode(', ', $placeholders);
        
        $sql = "INSERT INTO {$this->table} ({$columnNames}) VALUES ({$placeholderStr})";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($insertData);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza padrão existente
     */
    public function update($id, $data)
    {
        $columns = [
            'categoria_id', 'centro_custo_id', 'fornecedor_id', 'conta_bancaria_id',
            'forma_pagamento_id', 'tem_rateio', 'observacoes_padrao', 'usos', 'ultimo_uso_em'
        ];
        
        $updateData = ['id' => $id];
        $setParts = [];
        
        foreach ($columns as $column) {
            if (array_key_exists($column, $data)) {
                $updateData[$column] = $data[$column];
                $setParts[] = "{$column} = :{$column}";
            }
        }
        
        if (empty($setParts)) {
            return $id; // Nada para atualizar
        }
        
        $setStr = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setStr} WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($updateData);
        
        return $id;
    }
    
    /**
     * Normaliza descrição para busca de padrões
     */
    public static function normalizarDescricao($descricao)
    {
        // Remove caracteres especiais, espaços extras, converte para maiúsculas
        $normalizada = mb_strtoupper($descricao);
        $normalizada = preg_replace('/[^A-Z0-9\s]/', '', $normalizada);
        $normalizada = preg_replace('/\s+/', ' ', $normalizada);
        $normalizada = trim($normalizada);
        
        // Remove palavras comuns muito curtas
        $palavrasComuns = ['DE', 'DA', 'DO', 'EM', 'PARA', 'COM', 'POR', 'A', 'O', 'E'];
        $palavras = explode(' ', $normalizada);
        $palavras = array_filter($palavras, function($palavra) use ($palavrasComuns) {
            return strlen($palavra) > 2 && !in_array($palavra, $palavrasComuns);
        });
        
        return implode(' ', $palavras);
    }
    
    /**
     * Busca padrão por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Lista todos os padrões do usuário/empresa
     */
    public function findAll($usuarioId, $empresaId)
    {
        $sql = "SELECT p.*, 
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       f.nome_razao_social as fornecedor_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome
                FROM {$this->table} p
                LEFT JOIN categorias_financeiras c ON p.categoria_id = c.id
                LEFT JOIN centros_custo cc ON p.centro_custo_id = cc.id
                LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
                LEFT JOIN contas_bancarias cb ON p.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON p.forma_pagamento_id = fp.id
                WHERE p.usuario_id = :usuario_id 
                AND p.empresa_id = :empresa_id 
                AND p.ativo = 1
                ORDER BY p.usos DESC, p.ultimo_uso_em DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'empresa_id' => $empresaId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
