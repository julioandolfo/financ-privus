<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Parcelas de Contas a Receber
 */
class ParcelaReceber extends Model
{
    protected $table = 'parcelas_receber';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as parcelas de uma conta a receber
     */
    public function findByContaReceber($contaReceberId)
    {
        $sql = "SELECT pr.*, 
                       fp.nome as forma_recebimento_nome,
                       cb.banco_nome
                FROM {$this->table} pr
                LEFT JOIN formas_pagamento fp ON pr.forma_recebimento_id = fp.id
                LEFT JOIN contas_bancarias cb ON pr.conta_bancaria_id = cb.id
                WHERE pr.conta_receber_id = ?
                ORDER BY pr.numero_parcela ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contaReceberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma parcela por ID
     */
    public function findById($id)
    {
        $sql = "SELECT pr.*, 
                       fp.nome as forma_recebimento_nome,
                       cb.banco_nome,
                       cr.descricao as conta_descricao,
                       cr.numero_documento
                FROM {$this->table} pr
                LEFT JOIN formas_pagamento fp ON pr.forma_recebimento_id = fp.id
                LEFT JOIN contas_bancarias cb ON pr.conta_bancaria_id = cb.id
                LEFT JOIN contas_receber cr ON pr.conta_receber_id = cr.id
                WHERE pr.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova parcela
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (conta_receber_id, empresa_id, numero_parcela, valor_parcela, valor_recebido, 
                 desconto, frete, juros, multa, data_vencimento, status, observacoes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['conta_receber_id'],
            $data['empresa_id'],
            $data['numero_parcela'],
            $data['valor_parcela'],
            $data['valor_recebido'] ?? 0,
            $data['desconto'] ?? 0,
            $data['frete'] ?? 0,
            $data['juros'] ?? 0,
            $data['multa'] ?? 0,
            $data['data_vencimento'],
            $data['status'] ?? 'pendente',
            $data['observacoes'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Cria múltiplas parcelas de uma vez
     */
    public function createMultiple($contaReceberId, $empresaId, $parcelas)
    {
        $ids = [];
        
        foreach ($parcelas as $index => $parcela) {
            $id = $this->create([
                'conta_receber_id' => $contaReceberId,
                'empresa_id' => $empresaId,
                'numero_parcela' => $index + 1,
                'valor_parcela' => $parcela['valor'],
                'data_vencimento' => $parcela['data_vencimento'],
                'desconto' => $parcela['desconto'] ?? 0,
                'observacoes' => $parcela['observacoes'] ?? null
            ]);
            $ids[] = $id;
        }
        
        return $ids;
    }
    
    /**
     * Gera parcelas automaticamente
     */
    public function gerarParcelas($contaReceberId, $empresaId, $valorTotal, $numeroParcelas, $dataVencimentoInicial, $intervalo = 30)
    {
        $valorParcela = round($valorTotal / $numeroParcelas, 2);
        $resto = $valorTotal - ($valorParcela * $numeroParcelas);
        
        $ids = [];
        $dataVencimento = new \DateTime($dataVencimentoInicial);
        
        for ($i = 1; $i <= $numeroParcelas; $i++) {
            $valor = $valorParcela;
            
            // Adicionar resto na última parcela
            if ($i === $numeroParcelas) {
                $valor += $resto;
            }
            
            $id = $this->create([
                'conta_receber_id' => $contaReceberId,
                'empresa_id' => $empresaId,
                'numero_parcela' => $i,
                'valor_parcela' => $valor,
                'data_vencimento' => $dataVencimento->format('Y-m-d')
            ]);
            $ids[] = $id;
            
            // Próxima data de vencimento
            $dataVencimento->modify("+{$intervalo} days");
        }
        
        return $ids;
    }
    
    /**
     * Atualiza uma parcela
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['valor_parcela', 'valor_recebido', 'desconto', 'juros', 'multa', 
                          'data_vencimento', 'data_recebimento', 'status', 
                          'forma_recebimento_id', 'conta_bancaria_id', 'observacoes'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Registrar recebimento de parcela
     * @param bool $sobrescrever Se true, substitui o valor_recebido; se false, soma ao existente
     */
    public function registrarRecebimento($id, $valorRecebido, $dataRecebimento, $formaRecebimentoId = null, $contaBancariaId = null, $sobrescrever = false)
    {
        $parcela = $this->findById($id);
        if (!$parcela) {
            return false;
        }
        
        // Se sobrescrever, usa o valor diretamente; senão, soma ao existente
        if ($sobrescrever) {
            $novoValorRecebido = $valorRecebido;
        } else {
            $novoValorRecebido = ($parcela['valor_recebido'] ?? 0) + $valorRecebido;
        }
        
        $valorEsperado = $parcela['valor_parcela'] - ($parcela['desconto'] ?? 0) + ($parcela['juros'] ?? 0) + ($parcela['multa'] ?? 0);
        
        $status = 'parcial';
        if ($novoValorRecebido >= $valorEsperado) {
            $status = 'recebido';
        }
        
        return $this->update($id, [
            'valor_recebido' => $novoValorRecebido,
            'data_recebimento' => $dataRecebimento,
            'status' => $status,
            'forma_recebimento_id' => $formaRecebimentoId,
            'conta_bancaria_id' => $contaBancariaId
        ]);
    }
    
    /**
     * Cancelar parcela
     */
    public function cancelar($id)
    {
        return $this->update($id, ['status' => 'cancelado']);
    }
    
    /**
     * Excluir parcelas de uma conta
     */
    public function deleteByContaReceber($contaReceberId)
    {
        $sql = "DELETE FROM {$this->table} WHERE conta_receber_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$contaReceberId]);
    }
    
    /**
     * Retorna resumo das parcelas de uma conta
     */
    public function getResumoByContaReceber($contaReceberId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_parcelas,
                    SUM(valor_parcela) as valor_total,
                    SUM(valor_recebido) as total_recebido,
                    SUM(desconto) as total_desconto,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as parcelas_pendentes,
                    SUM(CASE WHEN status = 'recebido' THEN 1 ELSE 0 END) as parcelas_recebidas,
                    SUM(CASE WHEN status = 'vencido' OR (status = 'pendente' AND data_vencimento < CURDATE()) THEN 1 ELSE 0 END) as parcelas_vencidas
                FROM {$this->table}
                WHERE conta_receber_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contaReceberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna parcelas vencidas
     */
    public function findVencidas($empresaId = null)
    {
        $sql = "SELECT pr.*, 
                       cr.descricao as conta_descricao,
                       cr.numero_documento,
                       c.nome_razao_social as cliente_nome
                FROM {$this->table} pr
                JOIN contas_receber cr ON pr.conta_receber_id = cr.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE pr.status IN ('pendente', 'parcial')
                  AND pr.data_vencimento < CURDATE()";
        
        $params = [];
        if ($empresaId) {
            $sql .= " AND pr.empresa_id = ?";
            $params[] = $empresaId;
        }
        
        $sql .= " ORDER BY pr.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
