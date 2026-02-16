<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Contas a Pagar
 */
class ContaPagar extends Model
{
    protected $table = 'contas_pagar';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as contas a pagar
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT cp.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome,
                       u.nome as usuario_cadastro_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                LEFT JOIN centros_custo cc ON cp.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cp.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cp.forma_pagamento_id = fp.id
                JOIN usuarios u ON cp.usuario_cadastro_id = u.id
                WHERE cp.deleted_at IS NULL";
        $params = [];
        
        // Filtro por empresa ou consolidação
        if (isset($filters['empresas_ids']) && is_array($filters['empresas_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['empresas_ids']), '?'));
            $sql .= " AND cp.empresa_id IN ({$placeholders})";
            $params = array_merge($params, $filters['empresas_ids']);
        } elseif (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $sql .= " AND cp.empresa_id = ?";
            $params[] = $filters['empresa_id'];
        }
        
        // Filtro por status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND cp.status = ?";
            $params[] = $filters['status'];
        }
        
        // Filtro por fornecedor (por ID ou por nome consolidado)
        if (isset($filters['fornecedor_id']) && $filters['fornecedor_id'] !== '') {
            $sql .= " AND cp.fornecedor_id = ?";
            $params[] = $filters['fornecedor_id'];
        } elseif (isset($filters['fornecedor_nome']) && $filters['fornecedor_nome'] !== '') {
            $sql .= " AND f.nome_razao_social = ?";
            $params[] = $filters['fornecedor_nome'];
        }
        
        // Filtro por categoria (por ID ou por nome consolidado)
        if (isset($filters['categoria_id']) && $filters['categoria_id'] !== '') {
            $sql .= " AND cp.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        } elseif (isset($filters['categoria_nome']) && $filters['categoria_nome'] !== '') {
            $sql .= " AND c.nome = ?";
            $params[] = $filters['categoria_nome'];
        }
        
        // Filtro por centro de custo (por ID ou por nome consolidado)
        if (isset($filters['centro_custo_id']) && $filters['centro_custo_id'] !== '') {
            $sql .= " AND cp.centro_custo_id = ?";
            $params[] = $filters['centro_custo_id'];
        } elseif (isset($filters['centro_custo_nome']) && $filters['centro_custo_nome'] !== '') {
            $sql .= " AND cc.nome = ?";
            $params[] = $filters['centro_custo_nome'];
        }
        
        // Filtro por data de competência
        if (isset($filters['data_competencia_inicio']) && $filters['data_competencia_inicio'] !== '') {
            $sql .= " AND cp.data_competencia >= ?";
            $params[] = $filters['data_competencia_inicio'];
        }
        if (isset($filters['data_competencia_fim']) && $filters['data_competencia_fim'] !== '') {
            $sql .= " AND cp.data_competencia <= ?";
            $params[] = $filters['data_competencia_fim'];
        }
        
        // Filtro por data de vencimento
        if (isset($filters['data_vencimento_inicio']) && $filters['data_vencimento_inicio'] !== '') {
            $sql .= " AND cp.data_vencimento >= ?";
            $params[] = $filters['data_vencimento_inicio'];
        }
        if (isset($filters['data_vencimento_fim']) && $filters['data_vencimento_fim'] !== '') {
            $sql .= " AND cp.data_vencimento <= ?";
            $params[] = $filters['data_vencimento_fim'];
        }
        
        // Filtro por rateio
        if (isset($filters['tem_rateio']) && $filters['tem_rateio'] !== '') {
            $sql .= " AND cp.tem_rateio = ?";
            $params[] = $filters['tem_rateio'];
        }
        
        // Busca por descrição ou número de documento
        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND (cp.descricao LIKE ? OR cp.numero_documento LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtro por tipo de custo (fixo/variável)
        if (isset($filters['tipo_custo']) && $filters['tipo_custo'] !== '') {
            $sql .= " AND cp.tipo_custo = ?";
            $params[] = $filters['tipo_custo'];
        }
        
        $sql .= " ORDER BY cp.id DESC";
        
        // Paginação
        if (isset($filters['limite'])) {
            if (isset($filters['offset'])) {
                $sql .= " LIMIT " . (int)$filters['limite'] . " OFFSET " . (int)$filters['offset'];
            } else {
                $sql .= " LIMIT " . (int)$filters['limite'];
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma conta a pagar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT cp.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome,
                       u.nome as usuario_cadastro_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                LEFT JOIN centros_custo cc ON cp.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cp.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cp.forma_pagamento_id = fp.id
                JOIN usuarios u ON cp.usuario_cadastro_id = u.id
                WHERE cp.id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova conta a pagar
     */
    public function create($data)
    {
        // Verifica quais colunas extras existem na tabela
        $extraColumns = $this->getExtraColumns();
        
        // Colunas base que sempre existem
        $columns = [
            'empresa_id', 'fornecedor_id', 'categoria_id', 'centro_custo_id', 'numero_documento',
            'descricao', 'valor_total', 'valor_pago', 'data_emissao', 'data_competencia',
            'data_vencimento', 'data_pagamento', 'status', 'forma_pagamento_id',
            'conta_bancaria_id', 'tem_rateio', 'observacoes', 'tipo_custo',
            'eh_parcelado', 'total_parcelas', 'parcela_numero', 'grupo_parcela_id',
            'usuario_cadastro_id'
        ];
        
        // Converte strings vazias para null em campos opcionais de FK
        $fornecedorId = !empty($data['fornecedor_id']) ? $data['fornecedor_id'] : null;
        $centroCustoId = !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null;
        $formaPagamentoId = !empty($data['forma_pagamento_id']) ? $data['forma_pagamento_id'] : null;
        $contaBancariaId = !empty($data['conta_bancaria_id']) ? $data['conta_bancaria_id'] : null;
        
        // Valores base
        $values = [
            $data['empresa_id'],
            $fornecedorId,
            $data['categoria_id'],
            $centroCustoId,
            $data['numero_documento'] ?? '',
            $data['descricao'],
            $data['valor_total'],
            $data['valor_pago'] ?? 0,
            $data['data_emissao'],
            $data['data_competencia'],
            $data['data_vencimento'],
            !empty($data['data_pagamento']) ? $data['data_pagamento'] : null,
            $data['status'] ?? 'pendente',
            $formaPagamentoId,
            $contaBancariaId,
            $data['tem_rateio'] ?? 0,
            $data['observacoes'] ?? null,
            $data['tipo_custo'] ?? 'variavel',
            $data['eh_parcelado'] ?? 0,
            $data['total_parcelas'] ?? null,
            $data['parcela_numero'] ?? null,
            $data['grupo_parcela_id'] ?? null,
            $data['usuario_cadastro_id']
        ];
        
        // Adiciona colunas extras se existirem na tabela
        if (in_array('pedido_id', $extraColumns)) {
            $columns[] = 'pedido_id';
            $values[] = $data['pedido_id'] ?? null;
        }
        if (in_array('cliente_id', $extraColumns)) {
            $columns[] = 'cliente_id';
            $values[] = !empty($data['cliente_id']) ? $data['cliente_id'] : null;
        }
        
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnsList = implode(', ', $columns);
        
        $sql = "INSERT INTO {$this->table} ({$columnsList}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute($values);
        
        return $success ? $this->db->lastInsertId() : false;
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
     * Cria múltiplas parcelas de uma conta a pagar
     */
    public function criarParcelas($dadosBase, $configParcelas)
    {
        $grupoParcela = $this->gerarGrupoParcelaId();
        $totalParcelas = (int) $configParcelas['quantidade'];
        $valorTotal = (float) $dadosBase['valor_total'];
        $primeiroVencimento = $configParcelas['primeiro_vencimento'];
        $intervalo = $configParcelas['intervalo']; // mensal, quinzenal, semanal, ou dias
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
            $dadosParcela['status'] = $statusInicial;
            $dadosParcela['valor_pago'] = 0;
            
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
        $sql = "SELECT cp.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                WHERE cp.grupo_parcela_id = ?
                ORDER BY cp.parcela_numero ASC";
        
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
        $valorPago = 0;
        $parcelasPagas = 0;
        $parcelasPendentes = 0;
        
        foreach ($parcelas as $parcela) {
            $valorTotal += $parcela['valor_total'];
            $valorPago += $parcela['valor_pago'];
            
            if ($parcela['status'] === 'pago') {
                $parcelasPagas++;
            } else {
                $parcelasPendentes++;
            }
        }
        
        return [
            'grupo_parcela_id' => $grupoParcela,
            'total_parcelas' => $totalParcelas,
            'parcelas_pagas' => $parcelasPagas,
            'parcelas_pendentes' => $parcelasPendentes,
            'valor_total' => $valorTotal,
            'valor_pago' => $valorPago,
            'valor_restante' => $valorTotal - $valorPago,
            'parcelas' => $parcelas
        ];
    }
    
    /**
     * Atualiza apenas a categoria de uma conta
     */
    public function updateCategoria($id, $categoriaId)
    {
        $sql = "UPDATE {$this->table} SET categoria_id = ? WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$categoriaId, $id]);
    }
    
    /**
     * Atualiza uma conta a pagar
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = ?,
                fornecedor_id = ?,
                categoria_id = ?,
                centro_custo_id = ?,
                numero_documento = ?,
                descricao = ?,
                valor_total = ?,
                data_emissao = ?,
                data_competencia = ?,
                data_vencimento = ?,
                forma_pagamento_id = ?,
                conta_bancaria_id = ?,
                observacoes = ?,
                tipo_custo = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        // Converte strings vazias para null em campos opcionais de FK
        $fornecedorId = !empty($data['fornecedor_id']) ? $data['fornecedor_id'] : null;
        $centroCustoId = !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null;
        $formaPagamentoId = !empty($data['forma_pagamento_id']) ? $data['forma_pagamento_id'] : null;
        $contaBancariaId = !empty($data['conta_bancaria_id']) ? $data['conta_bancaria_id'] : null;
        
        return $stmt->execute([
            $data['empresa_id'],
            $fornecedorId,
            $data['categoria_id'],
            $centroCustoId,
            $data['numero_documento'],
            $data['descricao'],
            $data['valor_total'],
            $data['data_emissao'],
            $data['data_competencia'],
            $data['data_vencimento'],
            $formaPagamentoId,
            $contaBancariaId,
            $data['observacoes'] ?? null,
            $data['tipo_custo'] ?? 'variavel',
            $id
        ]);
    }
    
    /**
     * Atualiza status e valor pago da conta
     */
    public function atualizarPagamento($id, $valorPago, $dataPagamento, $status)
    {
        $sql = "UPDATE {$this->table} SET 
                valor_pago = ?,
                data_pagamento = ?,
                status = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$valorPago, $dataPagamento, $status, $id]);
    }
    
    /**
     * Cancela uma conta a pagar
     */
    public function cancelar($id)
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelado' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Retorna contas vencidas
     */
    public function findVencidas($empresasIds = [])
    {
        $sql = "SELECT cp.*, e.nome_fantasia as empresa_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                WHERE cp.status IN ('pendente', 'parcial')
                  AND cp.data_vencimento < CURDATE()";
        
        $params = [];
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cp.empresa_id IN ({$placeholders})";
            $params = $empresasIds;
        }
        
        $sql .= " ORDER BY cp.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna total a pagar por status
     */
    public function getTotalPorStatus($empresasIds = [])
    {
        $sql = "SELECT status, SUM(valor_total - valor_pago) as total
                FROM {$this->table}
                WHERE status IN ('pendente', 'parcial', 'vencido')";
        
        $params = [];
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND empresa_id IN ({$placeholders})";
            $params = $empresasIds;
        }
        
        $sql .= " GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
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
            'pago' => $result['pago'] ?? 0,
            'cancelado' => $result['cancelado'] ?? 0
        ];
    }
    
    /**
     * Retorna valor total a pagar (pendente + parcial + vencido)
     */
    public function getValorTotalAPagar($empresasIds = null)
    {
        $sql = "SELECT SUM(valor_total - valor_pago) as total
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
     * Retorna valor total já pago
     */
    public function getValorTotalPago($empresasIds = null)
    {
        $sql = "SELECT SUM(valor_pago) as total
                FROM {$this->table}
                WHERE status IN ('parcial', 'pago')
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
                       SUM(valor_total - valor_pago) as valor_total
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
                       SUM(valor_total - valor_pago) as valor_total
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
            'valor_a_pagar' => $this->getValorTotalAPagar($empresasIds),
            'valor_pago' => $this->getValorTotalPago($empresasIds),
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
     * Retorna total de registros com filtros aplicados
     */
    public function countWithFilters($filters = [])
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                LEFT JOIN centros_custo cc ON cp.centro_custo_id = cc.id
                WHERE cp.deleted_at IS NULL";
        $params = [];
        
        // Aplicar os mesmos filtros do findAll
        if (isset($filters['empresas_ids']) && is_array($filters['empresas_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['empresas_ids']), '?'));
            $sql .= " AND cp.empresa_id IN ({$placeholders})";
            $params = array_merge($params, $filters['empresas_ids']);
        } elseif (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $sql .= " AND cp.empresa_id = ?";
            $params[] = $filters['empresa_id'];
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND cp.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['fornecedor_id']) && $filters['fornecedor_id'] !== '') {
            $sql .= " AND cp.fornecedor_id = ?";
            $params[] = $filters['fornecedor_id'];
        } elseif (isset($filters['fornecedor_nome']) && $filters['fornecedor_nome'] !== '') {
            $sql .= " AND f.nome_razao_social = ?";
            $params[] = $filters['fornecedor_nome'];
        }
        
        if (isset($filters['categoria_id']) && $filters['categoria_id'] !== '') {
            $sql .= " AND cp.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        } elseif (isset($filters['categoria_nome']) && $filters['categoria_nome'] !== '') {
            $sql .= " AND c.nome = ?";
            $params[] = $filters['categoria_nome'];
        }
        
        if (isset($filters['centro_custo_id']) && $filters['centro_custo_id'] !== '') {
            $sql .= " AND cp.centro_custo_id = ?";
            $params[] = $filters['centro_custo_id'];
        } elseif (isset($filters['centro_custo_nome']) && $filters['centro_custo_nome'] !== '') {
            $sql .= " AND cc.nome = ?";
            $params[] = $filters['centro_custo_nome'];
        }
        
        if (isset($filters['data_competencia_inicio']) && $filters['data_competencia_inicio'] !== '') {
            $sql .= " AND cp.data_competencia >= ?";
            $params[] = $filters['data_competencia_inicio'];
        }
        if (isset($filters['data_competencia_fim']) && $filters['data_competencia_fim'] !== '') {
            $sql .= " AND cp.data_competencia <= ?";
            $params[] = $filters['data_competencia_fim'];
        }
        
        if (isset($filters['data_vencimento_inicio']) && $filters['data_vencimento_inicio'] !== '') {
            $sql .= " AND cp.data_vencimento >= ?";
            $params[] = $filters['data_vencimento_inicio'];
        }
        if (isset($filters['data_vencimento_fim']) && $filters['data_vencimento_fim'] !== '') {
            $sql .= " AND cp.data_vencimento <= ?";
            $params[] = $filters['data_vencimento_fim'];
        }
        
        if (isset($filters['tem_rateio']) && $filters['tem_rateio'] !== '') {
            $sql .= " AND cp.tem_rateio = ?";
            $params[] = $filters['tem_rateio'];
        }
        
        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND (cp.descricao LIKE ? OR cp.numero_documento LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['tipo_custo']) && $filters['tipo_custo'] !== '') {
            $sql .= " AND cp.tipo_custo = ?";
            $params[] = $filters['tipo_custo'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Retorna soma por período
     */
    public function getSomaByPeriodo($empresaId, $dataInicio, $dataFim, $status = null)
    {
        $sql = "SELECT COALESCE(SUM(valor_total), 0) as total
                FROM {$this->table}
                WHERE data_pagamento BETWEEN :data_inicio AND :data_fim";
        
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
        $sql = "SELECT COALESCE(SUM(cp.valor_total), 0) as total
                FROM {$this->table} cp
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                WHERE cp.data_pagamento BETWEEN :data_inicio AND :data_fim
                AND c.nome LIKE :categoria_nome
                AND cp.status = 'pago'";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'categoria_nome' => "%{$categoriaNome}%"
        ];
        
        if ($empresaId) {
            $sql .= " AND cp.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Retorna despesas agrupadas por categoria
     */
    public function getDespesasPorCategoria($empresaId, $dataInicio, $dataFim)
    {
        $sql = "SELECT 
                    c.nome as categoria,
                    COALESCE(SUM(cp.valor_total), 0) as total
                FROM {$this->table} cp
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                WHERE cp.data_pagamento BETWEEN :data_inicio AND :data_fim
                AND cp.status = 'pago'";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ];
        
        if ($empresaId) {
            $sql .= " AND cp.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " GROUP BY c.id, c.nome ORDER BY total DESC";
        
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
                'contas_pagar',
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
        $dadosAntes = $this->findById($id);
        
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
                'contas_pagar',
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
     * CANCELAR PAGAMENTO - Reverte uma baixa já realizada
     */
    public function cancelarPagamento($id, $motivo = null)
    {
        $dadosAntes = $this->findById($id);
        
        // Validações
        if ($dadosAntes['status'] !== 'pago' && $dadosAntes['status'] !== 'parcial') {
            throw new \Exception('Somente contas pagas ou parcialmente pagas podem ter o pagamento cancelado');
        }
        
        $sql = "UPDATE {$this->table} SET 
                valor_pago = 0,
                data_pagamento = NULL,
                status = 'pendente'
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$id]);
        
        if ($success) {
            // Registrar na auditoria
            require_once __DIR__ . '/Auditoria.php';
            \App\Models\Auditoria::registrar(
                'contas_pagar',
                $id,
                'cancel_payment',
                $dadosAntes,
                $this->findById($id),
                $motivo ?? 'Pagamento cancelado'
            );
        }
        
        return $success;
    }
    
    /**
     * Busca incluindo registros deletados
     */
    public function findByIdWithDeleted($id)
    {
        $sql = "SELECT cp.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome,
                       u.nome as usuario_cadastro_nome,
                       ud.nome as usuario_deletou_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                LEFT JOIN centros_custo cc ON cp.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cp.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cp.forma_pagamento_id = fp.id
                JOIN usuarios u ON cp.usuario_cadastro_id = u.id
                LEFT JOIN usuarios ud ON cp.deleted_by = ud.id
                WHERE cp.id = ? LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca apenas registros deletados
     */
    public function findDeleted($empresasIds = [])
    {
        $sql = "SELECT cp.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       u.nome as usuario_deletou_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                LEFT JOIN usuarios u ON cp.deleted_by = u.id
                WHERE cp.deleted_at IS NOT NULL";
        
        $params = [];
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cp.empresa_id IN ({$placeholders})";
            $params = $empresasIds;
        }
        
        $sql .= " ORDER BY cp.deleted_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
