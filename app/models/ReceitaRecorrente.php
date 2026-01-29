<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Receitas Recorrentes
 */
class ReceitaRecorrente extends Model
{
    protected $table = 'receitas_recorrentes';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca todas as receitas recorrentes
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT rr.*, 
                       e.nome_fantasia as empresa_nome,
                       cl.nome_razao_social as cliente_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       fp.nome as forma_pagamento_nome,
                       cb.banco_nome
                FROM {$this->table} rr
                JOIN empresas e ON rr.empresa_id = e.id
                LEFT JOIN clientes cl ON rr.cliente_id = cl.id
                JOIN categorias_financeiras c ON rr.categoria_id = c.id
                LEFT JOIN centros_custo cc ON rr.centro_custo_id = cc.id
                LEFT JOIN formas_pagamento fp ON rr.forma_pagamento_id = fp.id
                LEFT JOIN contas_bancarias cb ON rr.conta_bancaria_id = cb.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['empresa_id']) && $filters['empresa_id']) {
            $sql .= " AND rr.empresa_id = ?";
            $params[] = $filters['empresa_id'];
        }
        
        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $sql .= " AND rr.ativo = ?";
            $params[] = $filters['ativo'];
        }
        
        if (isset($filters['frequencia']) && $filters['frequencia']) {
            $sql .= " AND rr.frequencia = ?";
            $params[] = $filters['frequencia'];
        }
        
        $sql .= " ORDER BY rr.descricao ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca por ID
     */
    public function findById($id)
    {
        $sql = "SELECT rr.*, 
                       e.nome_fantasia as empresa_nome,
                       cl.nome_razao_social as cliente_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       fp.nome as forma_pagamento_nome,
                       cb.banco_nome
                FROM {$this->table} rr
                JOIN empresas e ON rr.empresa_id = e.id
                LEFT JOIN clientes cl ON rr.cliente_id = cl.id
                JOIN categorias_financeiras c ON rr.categoria_id = c.id
                LEFT JOIN centros_custo cc ON rr.centro_custo_id = cc.id
                LEFT JOIN formas_pagamento fp ON rr.forma_pagamento_id = fp.id
                LEFT JOIN contas_bancarias cb ON rr.conta_bancaria_id = cb.id
                WHERE rr.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova receita recorrente
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, cliente_id, categoria_id, centro_custo_id,
                 descricao, valor, observacoes,
                 frequencia, dia_mes, dia_semana, intervalo_dias,
                 data_inicio, data_fim, max_ocorrencias,
                 antecedencia_dias, status_inicial, criar_automaticamente,
                 ajuste_fim_semana, reajuste_ativo, reajuste_tipo, reajuste_valor, reajuste_mes,
                 valor_original, forma_pagamento_id, conta_bancaria_id,
                 ativo, proxima_geracao, usuario_cadastro_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        // Calcula próxima geração
        $proximaGeracao = $this->calcularProximaGeracao($data);
        
        $stmt->execute([
            $data['empresa_id'],
            !empty($data['cliente_id']) ? $data['cliente_id'] : null,
            $data['categoria_id'],
            !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null,
            $data['descricao'],
            $data['valor'],
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
     * Atualiza receita recorrente
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = ?, cliente_id = ?, categoria_id = ?, centro_custo_id = ?,
                descricao = ?, valor = ?, observacoes = ?,
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
            !empty($data['cliente_id']) ? $data['cliente_id'] : null,
            $data['categoria_id'],
            !empty($data['centro_custo_id']) ? $data['centro_custo_id'] : null,
            $data['descricao'],
            $data['valor'],
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
     * Ativa/Desativa receita recorrente
     */
    public function toggleAtivo($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = NOT ativo WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Exclui receita recorrente
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Busca receitas que precisam ser geradas
     */
    public function buscarParaGerar()
    {
        $sql = "SELECT rr.*, 
                       e.nome_fantasia as empresa_nome
                FROM {$this->table} rr
                JOIN empresas e ON rr.empresa_id = e.id
                WHERE rr.ativo = 1 
                AND rr.criar_automaticamente = 1
                AND rr.proxima_geracao <= DATE_ADD(CURDATE(), INTERVAL rr.antecedencia_dias DAY)
                AND (rr.data_fim IS NULL OR rr.data_fim >= CURDATE())
                AND (rr.max_ocorrencias IS NULL OR rr.ocorrencias_geradas < rr.max_ocorrencias)";
        
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
        $receita = $this->findById($id);
        if (!$receita || !$receita['reajuste_ativo']) {
            return false;
        }
        
        $novoValor = $receita['valor'];
        
        if ($receita['reajuste_tipo'] === 'percentual') {
            $novoValor = $receita['valor'] * (1 + ($receita['reajuste_valor'] / 100));
        } else {
            $novoValor = $receita['valor'] + $receita['reajuste_valor'];
        }
        
        $sql = "UPDATE {$this->table} SET 
                valor = ?, 
                ultimo_reajuste = CURDATE()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([round($novoValor, 2), $id]);
    }
    
    /**
     * Busca receitas que precisam de reajuste
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
        
        if ($dataInicio > $hoje) {
            return $this->ajustarDataVencimento($dataInicio, $data);
        }
        
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
    
    private function calcularProximoMes(\DateTime $data, $dia, $meses = 1)
    {
        $data->modify("+{$meses} month");
        
        if ($dia == 0) {
            $data->modify('last day of this month');
        } else {
            $ultimoDia = (int) $data->format('t');
            $diaFinal = min($dia, $ultimoDia);
            $data->setDate($data->format('Y'), $data->format('m'), $diaFinal);
        }
        
        return $data;
    }
    
    private function ajustarDataVencimento(\DateTime $data, $config)
    {
        $ajuste = $config['ajuste_fim_semana'] ?? 'manter';
        $diaSemana = (int) $data->format('w');
        
        if ($ajuste === 'manter' || ($diaSemana != 0 && $diaSemana != 6)) {
            return $data->format('Y-m-d');
        }
        
        if ($ajuste === 'antecipar') {
            if ($diaSemana === 6) {
                $data->modify('-1 day');
            } else {
                $data->modify('-2 days');
            }
        } else {
            if ($diaSemana === 6) {
                $data->modify('+2 days');
            } else {
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
        $sql = "SELECT COUNT(*) as total FROM contas_receber WHERE receita_recorrente_id = ?";
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
        $sql = "SELECT cr.*, 
                       CASE 
                           WHEN cr.status = 'recebido' THEN 'Recebido'
                           WHEN cr.data_vencimento < CURDATE() THEN 'Vencido'
                           ELSE 'Pendente'
                       END as status_texto
                FROM contas_receber cr
                WHERE cr.receita_recorrente_id = ?
                ORDER BY cr.data_vencimento DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
