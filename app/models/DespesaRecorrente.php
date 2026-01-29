<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Despesas Recorrentes
 */
class DespesaRecorrente extends Model
{
    protected $table = 'despesas_recorrentes';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca todas as despesas recorrentes
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT dr.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       fp.nome as forma_pagamento_nome,
                       cb.banco_nome
                FROM {$this->table} dr
                JOIN empresas e ON dr.empresa_id = e.id
                LEFT JOIN fornecedores f ON dr.fornecedor_id = f.id
                JOIN categorias_financeiras c ON dr.categoria_id = c.id
                LEFT JOIN centros_custo cc ON dr.centro_custo_id = cc.id
                LEFT JOIN formas_pagamento fp ON dr.forma_pagamento_id = fp.id
                LEFT JOIN contas_bancarias cb ON dr.conta_bancaria_id = cb.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['empresa_id']) && $filters['empresa_id']) {
            $sql .= " AND dr.empresa_id = ?";
            $params[] = $filters['empresa_id'];
        }
        
        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $sql .= " AND dr.ativo = ?";
            $params[] = $filters['ativo'];
        }
        
        if (isset($filters['frequencia']) && $filters['frequencia']) {
            $sql .= " AND dr.frequencia = ?";
            $params[] = $filters['frequencia'];
        }
        
        $sql .= " ORDER BY dr.descricao ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca por ID
     */
    public function findById($id)
    {
        $sql = "SELECT dr.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       fp.nome as forma_pagamento_nome,
                       cb.banco_nome
                FROM {$this->table} dr
                JOIN empresas e ON dr.empresa_id = e.id
                LEFT JOIN fornecedores f ON dr.fornecedor_id = f.id
                JOIN categorias_financeiras c ON dr.categoria_id = c.id
                LEFT JOIN centros_custo cc ON dr.centro_custo_id = cc.id
                LEFT JOIN formas_pagamento fp ON dr.forma_pagamento_id = fp.id
                LEFT JOIN contas_bancarias cb ON dr.conta_bancaria_id = cb.id
                WHERE dr.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova despesa recorrente
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, fornecedor_id, categoria_id, centro_custo_id,
                 descricao, valor, tipo_custo, observacoes,
                 frequencia, dia_mes, dia_semana, intervalo_dias,
                 data_inicio, data_fim, max_ocorrencias,
                 antecedencia_dias, status_inicial, criar_automaticamente,
                 ajuste_fim_semana, reajuste_ativo, reajuste_tipo, reajuste_valor, reajuste_mes,
                 valor_original, forma_pagamento_id, conta_bancaria_id,
                 ativo, proxima_geracao, usuario_cadastro_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        // Calcula próxima geração
        $proximaGeracao = $this->calcularProximaGeracao($data);
        
        $stmt->execute([
            $data['empresa_id'],
            !empty($data['fornecedor_id']) ? $data['fornecedor_id'] : null,
            $data['categoria_id'],
            !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null,
            $data['descricao'],
            $data['valor'],
            $data['tipo_custo'] ?? 'fixo',
            $data['observacoes'] ?? null,
            $data['frequencia'] ?? 'mensal',
            $data['dia_mes'] ?? null,
            $data['dia_semana'] ?? null,
            $data['intervalo_dias'] ?? null,
            $data['data_inicio'],
            !empty($data['data_fim']) ? $data['data_fim'] : null,
            !empty($data['max_ocorrencias']) ? $data['max_ocorrencias'] : null,
            $data['antecedencia_dias'] ?? 5,
            $data['status_inicial'] ?? 'pendente',
            $data['criar_automaticamente'] ?? 1,
            $data['ajuste_fim_semana'] ?? 'manter',
            $data['reajuste_ativo'] ?? 0,
            $data['reajuste_tipo'] ?? 'percentual',
            $data['reajuste_valor'] ?? null,
            $data['reajuste_mes'] ?? null,
            $data['valor'],
            !empty($data['forma_pagamento_id']) ? $data['forma_pagamento_id'] : null,
            !empty($data['conta_bancaria_id']) ? $data['conta_bancaria_id'] : null,
            1,
            $proximaGeracao,
            $data['usuario_cadastro_id']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza despesa recorrente
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = ?, fornecedor_id = ?, categoria_id = ?, centro_custo_id = ?,
                descricao = ?, valor = ?, tipo_custo = ?, observacoes = ?,
                frequencia = ?, dia_mes = ?, dia_semana = ?, intervalo_dias = ?,
                data_inicio = ?, data_fim = ?, max_ocorrencias = ?,
                antecedencia_dias = ?, status_inicial = ?, criar_automaticamente = ?,
                ajuste_fim_semana = ?, reajuste_ativo = ?, reajuste_tipo = ?, reajuste_valor = ?, reajuste_mes = ?,
                forma_pagamento_id = ?, conta_bancaria_id = ?,
                proxima_geracao = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        // Recalcula próxima geração
        $data['id'] = $id;
        $proximaGeracao = $this->calcularProximaGeracao($data);
        
        return $stmt->execute([
            $data['empresa_id'],
            !empty($data['fornecedor_id']) ? $data['fornecedor_id'] : null,
            $data['categoria_id'],
            !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null,
            $data['descricao'],
            $data['valor'],
            $data['tipo_custo'] ?? 'fixo',
            $data['observacoes'] ?? null,
            $data['frequencia'] ?? 'mensal',
            $data['dia_mes'] ?? null,
            $data['dia_semana'] ?? null,
            $data['intervalo_dias'] ?? null,
            $data['data_inicio'],
            !empty($data['data_fim']) ? $data['data_fim'] : null,
            !empty($data['max_ocorrencias']) ? $data['max_ocorrencias'] : null,
            $data['antecedencia_dias'] ?? 5,
            $data['status_inicial'] ?? 'pendente',
            $data['criar_automaticamente'] ?? 1,
            $data['ajuste_fim_semana'] ?? 'manter',
            $data['reajuste_ativo'] ?? 0,
            $data['reajuste_tipo'] ?? 'percentual',
            $data['reajuste_valor'] ?? null,
            $data['reajuste_mes'] ?? null,
            !empty($data['forma_pagamento_id']) ? $data['forma_pagamento_id'] : null,
            !empty($data['conta_bancaria_id']) ? $data['conta_bancaria_id'] : null,
            $proximaGeracao,
            $id
        ]);
    }
    
    /**
     * Ativa/Desativa despesa recorrente
     */
    public function toggleAtivo($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = NOT ativo WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Exclui despesa recorrente
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Busca despesas que precisam ser geradas
     */
    public function buscarParaGerar()
    {
        $sql = "SELECT dr.*, 
                       e.nome_fantasia as empresa_nome
                FROM {$this->table} dr
                JOIN empresas e ON dr.empresa_id = e.id
                WHERE dr.ativo = 1 
                AND dr.criar_automaticamente = 1
                AND dr.proxima_geracao <= DATE_ADD(CURDATE(), INTERVAL dr.antecedencia_dias DAY)
                AND (dr.data_fim IS NULL OR dr.data_fim >= CURDATE())
                AND (dr.max_ocorrencias IS NULL OR dr.ocorrencias_geradas < dr.max_ocorrencias)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Atualiza após gerar uma ocorrência
     */
    public function atualizarAposGeracao($id, $proximaGeracao)
    {
        $sql = "UPDATE {$this->table} SET 
                ocorrencias_geradas = ocorrencias_geradas + 1,
                ultima_geracao = CURDATE(),
                proxima_geracao = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$proximaGeracao, $id]);
    }
    
    /**
     * Aplica reajuste anual
     */
    public function aplicarReajuste($id)
    {
        $despesa = $this->findById($id);
        if (!$despesa || !$despesa['reajuste_ativo']) {
            return false;
        }
        
        $novoValor = $despesa['valor'];
        
        if ($despesa['reajuste_tipo'] === 'percentual') {
            $novoValor = $despesa['valor'] * (1 + ($despesa['reajuste_valor'] / 100));
        } else {
            $novoValor = $despesa['valor'] + $despesa['reajuste_valor'];
        }
        
        $sql = "UPDATE {$this->table} SET 
                valor = ?, 
                ultimo_reajuste = CURDATE()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([round($novoValor, 2), $id]);
    }
    
    /**
     * Busca despesas que precisam de reajuste
     */
    public function buscarParaReajuste()
    {
        $mesAtual = date('n');
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE ativo = 1 
                AND reajuste_ativo = 1 
                AND reajuste_mes = ?
                AND (ultimo_reajuste IS NULL OR YEAR(ultimo_reajuste) < YEAR(CURDATE()))";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mesAtual]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Calcula próxima data de geração
     */
    public function calcularProximaGeracao($data)
    {
        $dataInicio = new \DateTime($data['data_inicio']);
        $hoje = new \DateTime();
        
        // Se data início é no futuro, usa ela
        if ($dataInicio > $hoje) {
            return $this->ajustarDataVencimento($dataInicio, $data);
        }
        
        // Calcula próxima data baseado na frequência
        $proximaData = clone $hoje;
        
        switch ($data['frequencia']) {
            case 'diaria':
                $proximaData->modify('+1 day');
                break;
                
            case 'semanal':
                if (isset($data['dia_semana'])) {
                    $diasSemana = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $proximaData->modify('next ' . $diasSemana[$data['dia_semana']]);
                } else {
                    $proximaData->modify('+1 week');
                }
                break;
                
            case 'quinzenal':
                $proximaData->modify('+15 days');
                break;
                
            case 'mensal':
                $proximaData = $this->calcularProximoMes($proximaData, $data['dia_mes'] ?? 1);
                break;
                
            case 'bimestral':
                $proximaData = $this->calcularProximoMes($proximaData, $data['dia_mes'] ?? 1, 2);
                break;
                
            case 'trimestral':
                $proximaData = $this->calcularProximoMes($proximaData, $data['dia_mes'] ?? 1, 3);
                break;
                
            case 'semestral':
                $proximaData = $this->calcularProximoMes($proximaData, $data['dia_mes'] ?? 1, 6);
                break;
                
            case 'anual':
                $proximaData = $this->calcularProximoMes($proximaData, $data['dia_mes'] ?? 1, 12);
                break;
                
            case 'personalizado':
                $dias = $data['intervalo_dias'] ?? 30;
                $proximaData->modify("+{$dias} days");
                break;
        }
        
        return $this->ajustarDataVencimento($proximaData, $data);
    }
    
    /**
     * Calcula próximo mês com dia específico
     */
    private function calcularProximoMes(\DateTime $data, $dia, $meses = 1)
    {
        $data->modify("+{$meses} month");
        
        // Dia 0 = último dia do mês
        if ($dia == 0) {
            $data->modify('last day of this month');
        } else {
            // Ajusta para o dia correto
            $ultimoDia = (int) $data->format('t');
            $diaFinal = min($dia, $ultimoDia);
            $data->setDate($data->format('Y'), $data->format('m'), $diaFinal);
        }
        
        return $data;
    }
    
    /**
     * Ajusta data para fim de semana
     */
    private function ajustarDataVencimento(\DateTime $data, $config)
    {
        $ajuste = $config['ajuste_fim_semana'] ?? 'manter';
        $diaSemana = (int) $data->format('w'); // 0 = domingo, 6 = sábado
        
        if ($ajuste === 'manter' || ($diaSemana != 0 && $diaSemana != 6)) {
            return $data->format('Y-m-d');
        }
        
        if ($ajuste === 'antecipar') {
            // Antecipa para sexta-feira
            if ($diaSemana === 6) { // Sábado
                $data->modify('-1 day');
            } else { // Domingo
                $data->modify('-2 days');
            }
        } else { // postergar
            // Posterga para segunda-feira
            if ($diaSemana === 6) { // Sábado
                $data->modify('+2 days');
            } else { // Domingo
                $data->modify('+1 day');
            }
        }
        
        return $data->format('Y-m-d');
    }
    
    /**
     * Conta contas geradas por esta recorrência
     */
    public function contarContasGeradas($id)
    {
        $sql = "SELECT COUNT(*) as total FROM contas_pagar WHERE despesa_recorrente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }
    
    /**
     * Busca contas geradas por esta recorrência
     */
    public function buscarContasGeradas($id, $limite = 10)
    {
        $sql = "SELECT cp.*, 
                       CASE 
                           WHEN cp.status = 'pago' THEN 'Pago'
                           WHEN cp.data_vencimento < CURDATE() THEN 'Vencido'
                           ELSE 'Pendente'
                       END as status_texto
                FROM contas_pagar cp
                WHERE cp.despesa_recorrente_id = ?
                ORDER BY cp.data_vencimento DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
