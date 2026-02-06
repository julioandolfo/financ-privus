<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Contas a Receber
 */
class ContaReceber extends Model
{
    protected $table = 'contas_receber';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as contas a receber
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT cr.*, 
                       e.nome_fantasia as empresa_nome,
                       c.nome_razao_social as cliente_nome,
                       cat.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_recebimento_nome,
                       u.nome as usuario_cadastro_nome,
                       CASE 
                           WHEN cr.status IN ('pendente', 'parcial') AND cr.data_vencimento < CURDATE() THEN 'vencido'
                           ELSE cr.status
                       END as status,
                       (SELECT COUNT(*) FROM rateios_recebimentos WHERE conta_receber_id = cr.id) > 0 as tem_rateio,
                       (SELECT COUNT(*) FROM parcelas_receber WHERE conta_receber_id = cr.id) as total_parcelas_tabela,
                       (SELECT COUNT(*) FROM parcelas_receber WHERE conta_receber_id = cr.id AND status = 'recebido') as parcelas_recebidas_tabela
                FROM {$this->table} cr
                JOIN empresas e ON cr.empresa_id = e.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                JOIN categorias_financeiras cat ON cr.categoria_id = cat.id
                LEFT JOIN centros_custo cc ON cr.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cr.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cr.forma_recebimento_id = fp.id
                JOIN usuarios u ON cr.usuario_cadastro_id = u.id
                WHERE cr.deleted_at IS NULL";
        $params = [];
        
        // Filtro por empresa ou consolidação
        if (isset($filters['empresas_ids']) && is_array($filters['empresas_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['empresas_ids']), '?'));
            $sql .= " AND cr.empresa_id IN ({$placeholders})";
            $params = array_merge($params, $filters['empresas_ids']);
        } elseif (isset($filters['empresa_id'])) {
            $sql .= " AND cr.empresa_id = ?";
            $params[] = $filters['empresa_id'];
        }
        
        // Filtro por cliente (por ID ou por nome consolidado)
        if (isset($filters['cliente_id']) && $filters['cliente_id'] !== '') {
            $sql .= " AND cr.cliente_id = ?";
            $params[] = $filters['cliente_id'];
        } elseif (isset($filters['cliente_nome']) && $filters['cliente_nome'] !== '') {
            $sql .= " AND cl.nome_razao_social = ?";
            $params[] = $filters['cliente_nome'];
        }
        
        // Filtro por categoria (por ID ou por nome consolidado)
        if (isset($filters['categoria_id']) && $filters['categoria_id'] !== '') {
            $sql .= " AND cr.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        } elseif (isset($filters['categoria_nome']) && $filters['categoria_nome'] !== '') {
            $sql .= " AND c.nome = ?";
            $params[] = $filters['categoria_nome'];
        }
        
        // Filtro por centro de custo (por ID ou por nome consolidado)
        if (isset($filters['centro_custo_id']) && $filters['centro_custo_id'] !== '') {
            $sql .= " AND cr.centro_custo_id = ?";
            $params[] = $filters['centro_custo_id'];
        } elseif (isset($filters['centro_custo_nome']) && $filters['centro_custo_nome'] !== '') {
            $sql .= " AND cc.nome = ?";
            $params[] = $filters['centro_custo_nome'];
        }
        
        // Filtro por status
        if (isset($filters['status'])) {
            if ($filters['status'] == 'vencido') {
                $sql .= " AND cr.status IN ('pendente', 'parcial') AND cr.data_vencimento < CURDATE()";
            } else {
                $sql .= " AND cr.status = ?";
                $params[] = $filters['status'];
            }
        }
        
        // Filtro por data de vencimento
        if (isset($filters['data_vencimento_inicio'])) {
            $sql .= " AND cr.data_vencimento >= ?";
            $params[] = $filters['data_vencimento_inicio'];
        }
        if (isset($filters['data_vencimento_fim'])) {
            $sql .= " AND cr.data_vencimento <= ?";
            $params[] = $filters['data_vencimento_fim'];
        }
        
        // Busca por descrição ou número de documento
        if (isset($filters['search'])) {
            $sql .= " AND (cr.descricao LIKE ? OR cr.numero_documento LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY cr.data_vencimento DESC, cr.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma conta a receber por ID
     */
    public function findById($id)
    {
        $sql = "SELECT cr.*, 
                       e.nome_fantasia as empresa_nome,
                       c.nome_razao_social as cliente_nome,
                       c.codigo_cliente as cliente_codigo,
                       cat.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_recebimento_nome,
                       u.nome as usuario_cadastro_nome,
                       (SELECT COUNT(*) FROM rateios_recebimentos WHERE conta_receber_id = cr.id) > 0 as tem_rateio
                FROM {$this->table} cr
                JOIN empresas e ON cr.empresa_id = e.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                JOIN categorias_financeiras cat ON cr.categoria_id = cat.id
                LEFT JOIN centros_custo cc ON cr.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cr.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cr.forma_recebimento_id = fp.id
                JOIN usuarios u ON cr.usuario_cadastro_id = u.id
                WHERE cr.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova conta a receber
     */
    public function create($data)
    {
        // Verifica quais colunas extras existem na tabela
        $extraColumns = $this->getExtraColumns();
        
        // Colunas base que sempre existem
        $columns = [
            'empresa_id', 'cliente_id', 'categoria_id', 'centro_custo_id', 'numero_documento',
            'descricao', 'valor_total', 'valor_recebido', 'data_emissao', 'data_competencia',
            'data_vencimento', 'status', 'observacoes', 'usuario_cadastro_id', 'data_cadastro'
        ];
        
        // Valores base
        $clienteId = !empty($data['cliente_id']) ? $data['cliente_id'] : null;
        $centroCustoId = !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null;
        
        $values = [
            $data['empresa_id'],
            $clienteId,
            $data['categoria_id'],
            $centroCustoId,
            $data['numero_documento'] ?? '',
            $data['descricao'],
            $data['valor_total'],
            $data['valor_recebido'] ?? 0,
            $data['data_emissao'],
            $data['data_competencia'],
            $data['data_vencimento'],
            $data['status'] ?? 'pendente',
            $data['observacoes'] ?? null,
            $data['usuario_cadastro_id'],
            date('Y-m-d H:i:s')
        ];
        
        // Adiciona colunas extras se existirem
        if (in_array('desconto', $extraColumns)) {
            $columns[] = 'desconto';
            $values[] = $data['desconto'] ?? 0;
        }
        if (in_array('frete', $extraColumns)) {
            $columns[] = 'frete';
            $values[] = $data['frete'] ?? 0;
        }
        if (in_array('regiao', $extraColumns)) {
            $columns[] = 'regiao';
            $values[] = $data['regiao'] ?? null;
        }
        if (in_array('segmento', $extraColumns)) {
            $columns[] = 'segmento';
            $values[] = $data['segmento'] ?? null;
        }
        if (in_array('numero_parcelas', $extraColumns)) {
            $columns[] = 'numero_parcelas';
            $values[] = $data['numero_parcelas'] ?? 1;
        }
        if (in_array('parcela_atual', $extraColumns)) {
            $columns[] = 'parcela_atual';
            $values[] = $data['parcela_atual'] ?? 1;
        }
        if (in_array('conta_origem_id', $extraColumns)) {
            $columns[] = 'conta_origem_id';
            $values[] = !empty($data['conta_origem_id']) ? $data['conta_origem_id'] : null;
        }
        if (in_array('pedido_id', $extraColumns)) {
            $columns[] = 'pedido_id';
            $values[] = $data['pedido_id'] ?? null;
        }
        
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnsList = implode(', ', $columns);
        
        $sql = "INSERT INTO {$this->table} ({$columnsList}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Verifica quais colunas extras existem na tabela
     */
    private function getExtraColumns()
    {
        static $columns = null;
        
        if ($columns === null) {
            try {
                $stmt = $this->db->query("DESCRIBE {$this->table}");
                $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                $columns = $result;
            } catch (\Exception $e) {
                $columns = [];
            }
        }
        
        return $columns;
    }
    
    /**
     * Cria múltiplas parcelas de uma conta a receber
     */
    public function criarParcelas($dadosBase, $configParcelas)
    {
        $grupoParcela = $this->gerarGrupoParcelaId();
        $totalParcelas = (int) $configParcelas['quantidade'];
        $valorTotal = (float) $dadosBase['valor_total'];
        $primeiroVencimento = $configParcelas['primeiro_vencimento'];
        $intervalo = $configParcelas['intervalo']; // mensal, quinzenal, semanal, ou personalizado
        $intervaloDias = $configParcelas['intervalo_dias'] ?? 30;
        $tipoValor = $configParcelas['tipo_valor']; // total_por_parcela ou diluido
        $statusInicial = $configParcelas['status_inicial'] ?? 'pendente';
        
        // Calcular valor por parcela
        if ($tipoValor === 'diluido') {
            $valorParcela = round($valorTotal / $totalParcelas, 2);
            // Ajustar última parcela para compensar arredondamento
            $valorUltimaParcela = $valorTotal - ($valorParcela * ($totalParcelas - 1));
        } else {
            $valorParcela = $valorTotal;
            $valorUltimaParcela = $valorTotal;
        }
        
        $parcelasIds = [];
        $dataVencimento = new \DateTime($primeiroVencimento);
        
        for ($i = 1; $i <= $totalParcelas; $i++) {
            $dadosParcela = $dadosBase;
            $dadosParcela['valor_total'] = ($i == $totalParcelas) ? $valorUltimaParcela : $valorParcela;
            $dadosParcela['data_vencimento'] = $dataVencimento->format('Y-m-d');
            $dadosParcela['descricao'] = $dadosBase['descricao'] . " (Parcela {$i}/{$totalParcelas})";
            $dadosParcela['numero_documento'] = $dadosBase['numero_documento'] . "-{$i}/{$totalParcelas}";
            $dadosParcela['eh_parcelado'] = 1;
            $dadosParcela['total_parcelas'] = $totalParcelas;
            $dadosParcela['parcela_numero'] = $i;
            $dadosParcela['grupo_parcela_id'] = $grupoParcela;
            $dadosParcela['numero_parcelas'] = $totalParcelas;
            $dadosParcela['parcela_atual'] = $i;
            $dadosParcela['status'] = $statusInicial;
            $dadosParcela['valor_recebido'] = 0;
            
            $parcelaId = $this->create($dadosParcela);
            if ($parcelaId) {
                $parcelasIds[] = $parcelaId;
            }
            
            // Calcular próximo vencimento
            switch ($intervalo) {
                case 'mensal':
                    $dataVencimento->modify('+1 month');
                    break;
                case 'quinzenal':
                    $dataVencimento->modify('+15 days');
                    break;
                case 'semanal':
                    $dataVencimento->modify('+7 days');
                    break;
                case 'personalizado':
                    $dataVencimento->modify("+{$intervaloDias} days");
                    break;
                default:
                    $dataVencimento->modify('+1 month');
            }
        }
        
        return [
            'grupo_parcela_id' => $grupoParcela,
            'parcelas_ids' => $parcelasIds,
            'total_parcelas' => $totalParcelas
        ];
    }
    
    /**
     * Gera um ID único para agrupar parcelas
     */
    private function gerarGrupoParcelaId()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Busca todas as parcelas de um grupo
     */
    public function findByGrupoParcela($grupoParcela)
    {
        $sql = "SELECT cr.*, 
                       e.nome_fantasia as empresa_nome,
                       c.nome_razao_social as cliente_nome
                FROM {$this->table} cr
                JOIN empresas e ON cr.empresa_id = e.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE cr.grupo_parcela_id = ?
                ORDER BY cr.parcela_numero ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$grupoParcela]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna resumo do parcelamento
     */
    public function getResumoParcelas($grupoParcela)
    {
        $parcelas = $this->findByGrupoParcela($grupoParcela);
        
        if (empty($parcelas)) {
            return null;
        }
        
        $totalParcelas = count($parcelas);
        $valorTotal = 0;
        $valorRecebido = 0;
        $parcelasRecebidas = 0;
        $parcelasPendentes = 0;
        
        foreach ($parcelas as $parcela) {
            $valorTotal += $parcela['valor_total'];
            $valorRecebido += $parcela['valor_recebido'];
            
            if ($parcela['status'] === 'recebido') {
                $parcelasRecebidas++;
            } else {
                $parcelasPendentes++;
            }
        }
        
        return [
            'grupo_parcela_id' => $grupoParcela,
            'total_parcelas' => $totalParcelas,
            'parcelas_recebidas' => $parcelasRecebidas,
            'parcelas_pendentes' => $parcelasPendentes,
            'valor_total' => $valorTotal,
            'valor_recebido' => $valorRecebido,
            'valor_restante' => $valorTotal - $valorRecebido,
            'parcelas' => $parcelas
        ];
    }
    
    /**
     * Atualiza uma conta a receber
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                empresa_id = ?, cliente_id = ?, categoria_id = ?, centro_custo_id = ?,
                numero_documento = ?, descricao = ?, valor_total = ?, desconto = ?, frete = ?,
                data_emissao = ?, data_competencia = ?, data_vencimento = ?,
                observacoes = ?, regiao = ?, segmento = ?, numero_parcelas = ?,
                parcela_atual = ?, conta_origem_id = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        // Converte strings vazias para null em campos opcionais de FK
        $clienteId = !empty($data['cliente_id']) ? $data['cliente_id'] : null;
        $centroCustoId = !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null;
        $contaOrigemId = !empty($data['conta_origem_id']) ? $data['conta_origem_id'] : null;
        
        return $stmt->execute([
            $data['empresa_id'],
            $clienteId,
            $data['categoria_id'],
            $centroCustoId,
            $data['numero_documento'],
            $data['descricao'],
            $data['valor_total'],
            $data['desconto'] ?? 0,
            $data['frete'] ?? 0,
            $data['data_emissao'],
            $data['data_competencia'],
            $data['data_vencimento'],
            $data['observacoes'] ?? null,
            $data['regiao'] ?? null,
            $data['segmento'] ?? null,
            $data['numero_parcelas'] ?? 1,
            $data['parcela_atual'] ?? 1,
            $contaOrigemId,
            $id
        ]);
    }
    
    /**
     * Atualiza recebimento (baixa parcial ou total)
     */
    public function atualizarRecebimento($id, $valorRecebido, $dataRecebimento, $status)
    {
        $sql = "UPDATE {$this->table} SET
                valor_recebido = ?, data_recebimento = ?, status = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$valorRecebido, $dataRecebimento, $status, $id]);
    }
    
    /**
     * Cancela uma conta a receber
     */
    public function cancelar($id)
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelado', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Retorna contas vencidas
     */
    public function findVencidas($empresasIds = [])
    {
        $sql = "SELECT cr.*, e.nome_fantasia as empresa_nome
                FROM {$this->table} cr
                JOIN empresas e ON cr.empresa_id = e.id
                WHERE cr.status IN ('pendente', 'parcial')
                  AND cr.data_vencimento < CURDATE()";
        
        $params = [];
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cr.empresa_id IN ({$placeholders})";
            $params = $empresasIds;
        }
        
        $sql .= " ORDER BY cr.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Atualiza flag de rateio
     */
    public function atualizarRateio($id, $temRateio)
    {
        $sql = "UPDATE {$this->table} SET tem_rateio = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$temRateio, $id]);
    }
    
    /**
     * Métricas para o Dashboard
     */
    
    /**
     * Retorna contagem de contas por status
     */
    public function getCountPorStatus($empresasIds = null)
    {
        $sql = "SELECT status, COUNT(*) as total
                FROM {$this->table}
                WHERE deleted_at IS NULL";
        
        if ($empresasIds) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND empresa_id IN ($placeholders)";
        }
        
        $sql .= " GROUP BY status";
        
        if ($empresasIds) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($empresasIds);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return [
            'pendente' => $result['pendente'] ?? 0,
            'vencido' => $result['vencido'] ?? 0,
            'parcial' => $result['parcial'] ?? 0,
            'recebido' => $result['recebido'] ?? 0,
            'cancelado' => $result['cancelado'] ?? 0
        ];
    }
    
    /**
     * Retorna valor total a receber (pendente + parcial + vencido)
     */
    public function getValorTotalAReceber($empresasIds = null)
    {
        $sql = "SELECT SUM(valor_total - valor_recebido) as total
                FROM {$this->table}
                WHERE status IN ('pendente', 'parcial', 'vencido')
                  AND deleted_at IS NULL";
        
        if ($empresasIds) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND empresa_id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($empresasIds);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Retorna valor total já recebido
     */
    public function getValorTotalRecebido($empresasIds = null)
    {
        $sql = "SELECT SUM(valor_recebido) as total
                FROM {$this->table}
                WHERE status IN ('parcial', 'recebido')
                  AND deleted_at IS NULL";
        
        if ($empresasIds) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND empresa_id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($empresasIds);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Retorna contas vencidas (quantidade e valor)
     */
    public function getContasVencidas($empresasIds = null)
    {
        $sql = "SELECT COUNT(*) as quantidade, 
                       SUM(valor_total - valor_recebido) as valor_total
                FROM {$this->table}
                WHERE status IN ('pendente', 'parcial')
                  AND data_vencimento < CURDATE()
                  AND deleted_at IS NULL";
        
        if ($empresasIds) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND empresa_id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($empresasIds);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'quantidade' => $result['quantidade'] ?? 0,
            'valor_total' => $result['valor_total'] ?? 0
        ];
    }
    
    /**
     * Retorna contas a vencer nos próximos N dias
     */
    public function getContasAVencer($dias = 7, $empresasIds = null)
    {
        $sql = "SELECT COUNT(*) as quantidade, 
                       SUM(valor_total - valor_recebido) as valor_total
                FROM {$this->table}
                WHERE status IN ('pendente', 'parcial')
                  AND data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  AND deleted_at IS NULL";
        
        $params = [$dias];
        
        if ($empresasIds) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND empresa_id IN ($placeholders)";
            $params = array_merge($params, $empresasIds);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'quantidade' => $result['quantidade'] ?? 0,
            'valor_total' => $result['valor_total'] ?? 0
        ];
    }
    
    /**
     * Retorna resumo completo para dashboard
     */
    public function getResumo($empresasIds = null)
    {
        return [
            'total' => $this->count($empresasIds),
            'por_status' => $this->getCountPorStatus($empresasIds),
            'valor_a_receber' => $this->getValorTotalAReceber($empresasIds),
            'valor_recebido' => $this->getValorTotalRecebido($empresasIds),
            'vencidas' => $this->getContasVencidas($empresasIds),
            'a_vencer_7d' => $this->getContasAVencer(7, $empresasIds),
            'a_vencer_30d' => $this->getContasAVencer(30, $empresasIds)
        ];
    }
    
    /**
     * Retorna total de registros
     */
    public function count($empresasIds = null)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL";
        
        if ($empresasIds) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND empresa_id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($empresasIds);
            return $stmt->fetchColumn();
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn();
    }

    /**
     * Retorna soma por período
     */
    public function getSomaByPeriodo($empresaId, $dataInicio, $dataFim, $status = null)
    {
        $sql = "SELECT COALESCE(SUM(valor_total), 0) as total
                FROM {$this->table}
                WHERE data_recebimento BETWEEN :data_inicio AND :data_fim";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Retorna soma por categoria
     */
    public function getSomaByCategoria($empresaId, $dataInicio, $dataFim, $categoriaNome)
    {
        $sql = "SELECT COALESCE(SUM(cr.valor_total), 0) as total
                FROM {$this->table} cr
                JOIN categorias_financeiras c ON cr.categoria_id = c.id
                WHERE cr.data_recebimento BETWEEN :data_inicio AND :data_fim
                AND c.nome LIKE :categoria_nome
                AND cr.status = 'recebido'";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'categoria_nome' => "%{$categoriaNome}%"
        ];
        
        if ($empresaId) {
            $sql .= " AND cr.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Retorna receitas agrupadas por categoria
     */
    public function getReceitasPorCategoria($empresaId, $dataInicio, $dataFim)
    {
        $sql = "SELECT 
                    c.nome as categoria,
                    COALESCE(SUM(cr.valor_total), 0) as total
                FROM {$this->table} cr
                JOIN categorias_financeiras c ON cr.categoria_id = c.id
                WHERE cr.data_recebimento BETWEEN :data_inicio AND :data_fim
                AND cr.status = 'recebido'";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ];
        
        if ($empresaId) {
            $sql .= " AND cr.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " GROUP BY c.id, c.nome ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retorna lista detalhada de contas vencidas
     */
    public function getContasVencidasDetalhadas($empresaId = null)
    {
        $sql = "SELECT cr.*, c.nome_razao_social as cliente_nome
                FROM {$this->table} cr
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                WHERE cr.status = 'pendente'
                AND cr.data_vencimento < CURDATE()";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND cr.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " ORDER BY cr.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retorna a conexão do banco (para uso em outros lugares)
     */
    public function getDb()
    {
        return $this->db;
    }
    
    /**
     * Cria conta com parcelas
     * @param array $data Dados da conta
     * @param array $parcelas Array de parcelas com data_vencimento e valor
     * @return array ['conta_id' => int, 'parcelas_ids' => array]
     */
    public function createWithParcelas($data, $parcelas)
    {
        $this->db->beginTransaction();
        
        try {
            // Define número de parcelas
            $data['numero_parcelas'] = count($parcelas);
            $data['parcela_atual'] = 1;
            
            // Calcula valor total se não informado
            if (empty($data['valor_total'])) {
                $data['valor_total'] = array_sum(array_column($parcelas, 'valor'));
            }
            
            // Cria a conta principal
            $contaId = $this->create($data);
            
            // Cria as parcelas
            $parcelaModel = new ParcelaReceber();
            $parcelasIds = [];
            
            foreach ($parcelas as $index => $parcela) {
                $parcelaId = $parcelaModel->create([
                    'conta_receber_id' => $contaId,
                    'empresa_id' => $data['empresa_id'],
                    'numero_parcela' => $index + 1,
                    'valor_parcela' => $parcela['valor'],
                    'data_vencimento' => $parcela['data_vencimento'],
                    'desconto' => $parcela['desconto'] ?? 0,
                    'observacoes' => $parcela['observacoes'] ?? null
                ]);
                $parcelasIds[] = $parcelaId;
            }
            
            $this->db->commit();
            
            return [
                'conta_id' => $contaId,
                'parcelas_ids' => $parcelasIds
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Gera parcelas automaticamente para uma conta existente
     */
    public function gerarParcelas($id, $numeroParcelas, $intervalo = 30)
    {
        $conta = $this->findById($id);
        if (!$conta) {
            return false;
        }
        
        $parcelaModel = new ParcelaReceber();
        
        // Excluir parcelas existentes
        $parcelaModel->deleteByContaReceber($id);
        
        // Gerar novas parcelas
        $ids = $parcelaModel->gerarParcelas(
            $id,
            $conta['empresa_id'],
            $conta['valor_total'] - ($conta['desconto'] ?? 0),
            $numeroParcelas,
            $conta['data_vencimento'],
            $intervalo
        );
        
        // Atualizar número de parcelas na conta
        $this->execute("UPDATE {$this->table} SET numero_parcelas = ? WHERE id = ?", [$numeroParcelas, $id]);
        
        return $ids;
    }
    
    /**
     * Retorna parcelas de uma conta
     */
    public function getParcelas($id)
    {
        $parcelaModel = new ParcelaReceber();
        return $parcelaModel->findByContaReceber($id);
    }
    
    /**
     * Executa uma query SQL
     */
    private function execute($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * SOFT DELETE - Marca registro como deletado sem remover do banco
     */
    public function softDelete($id, $motivo = null)
    {
        $sql = "UPDATE {$this->table} SET 
                deleted_at = NOW(), 
                deleted_by = ?,
                deleted_reason = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        $success = $stmt->execute([$usuarioId, $motivo, $id]);
        
        if ($success) {
            // Registrar na auditoria
            require_once __DIR__ . '/Auditoria.php';
            \App\Models\Auditoria::registrar(
                'contas_receber',
                $id,
                'delete',
                $this->findById($id),
                null,
                $motivo
            );
        }
        
        return $success;
    }
    
    /**
     * RESTAURAR - Remove a marcação de deletado
     */
    public function restore($id)
    {
        $dadosAntes = $this->findByIdWithDeleted($id);
        
        $sql = "UPDATE {$this->table} SET 
                deleted_at = NULL, 
                deleted_by = NULL,
                deleted_reason = NULL
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$id]);
        
        if ($success) {
            // Registrar na auditoria
            require_once __DIR__ . '/Auditoria.php';
            \App\Models\Auditoria::registrar(
                'contas_receber',
                $id,
                'restore',
                $dadosAntes,
                $this->findById($id),
                'Registro restaurado'
            );
        }
        
        return $success;
    }
    
    /**
     * CANCELAR RECEBIMENTO - Reverte uma baixa já realizada
     */
    public function cancelarRecebimento($id, $motivo = null)
    {
        $dadosAntes = $this->findById($id);
        
        // Validações
        if ($dadosAntes['status'] !== 'recebido' && $dadosAntes['status'] !== 'parcial') {
            throw new \Exception('Somente contas recebidas ou parcialmente recebidas podem ter o recebimento cancelado');
        }
        
        $sql = "UPDATE {$this->table} SET 
                valor_recebido = 0,
                data_recebimento = NULL,
                status = 'pendente'
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$id]);
        
        if ($success) {
            // Registrar na auditoria
            require_once __DIR__ . '/Auditoria.php';
            \App\Models\Auditoria::registrar(
                'contas_receber',
                $id,
                'cancel_receipt',
                $dadosAntes,
                $this->findById($id),
                $motivo ?? 'Recebimento cancelado'
            );
        }
        
        return $success;
    }
    
    /**
     * Busca incluindo registros deletados
     */
    public function findByIdWithDeleted($id)
    {
        $sql = "SELECT cr.*, 
                       e.nome_fantasia as empresa_nome,
                       c.nome_razao_social as cliente_nome,
                       cat.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_recebimento_nome,
                       u.nome as usuario_cadastro_nome,
                       ud.nome as usuario_deletou_nome,
                       (SELECT COUNT(*) FROM rateios_recebimentos WHERE conta_receber_id = cr.id) > 0 as tem_rateio
                FROM {$this->table} cr
                JOIN empresas e ON cr.empresa_id = e.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                JOIN categorias_financeiras cat ON cr.categoria_id = cat.id
                LEFT JOIN centros_custo cc ON cr.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cr.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cr.forma_recebimento_id = fp.id
                JOIN usuarios u ON cr.usuario_cadastro_id = u.id
                LEFT JOIN usuarios ud ON cr.deleted_by = ud.id
                WHERE cr.id = ? LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca apenas registros deletados
     */
    public function findDeleted($empresasIds = [])
    {
        $sql = "SELECT cr.*, 
                       e.nome_fantasia as empresa_nome,
                       c.nome_razao_social as cliente_nome,
                       u.nome as usuario_deletou_nome
                FROM {$this->table} cr
                JOIN empresas e ON cr.empresa_id = e.id
                LEFT JOIN clientes c ON cr.cliente_id = c.id
                LEFT JOIN usuarios u ON cr.deleted_by = u.id
                WHERE cr.deleted_at IS NOT NULL";
        
        $params = [];
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cr.empresa_id IN ({$placeholders})";
            $params = $empresasIds;
        }
        
        $sql .= " ORDER BY cr.deleted_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
