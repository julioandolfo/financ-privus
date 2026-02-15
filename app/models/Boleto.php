<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Boleto extends Model
{
    protected $table = 'boletos';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Cria um novo boleto.
     */
    public function criar(array $data): ?int
    {
        $campos = [
            'empresa_id', 'conexao_bancaria_id', 'cliente_id', 'pedido_vinculado_id', 'conta_receber_id',
            'nosso_numero', 'seu_numero', 'identificacao_boleto_empresa', 'codigo_barras', 'linha_digitavel', 'qr_code_pix',
            'valor', 'valor_abatimento', 'valor_multa', 'valor_juros_mora', 'valor_desconto', 'valor_recebido',
            'data_emissao', 'data_vencimento', 'data_limite_pagamento', 'data_pagamento',
            'numero_cliente_banco', 'codigo_modalidade', 'numero_conta_corrente', 'especie_documento',
            'numero_parcela', 'aceite',
            'tipo_desconto', 'data_primeiro_desconto', 'valor_primeiro_desconto',
            'data_segundo_desconto', 'valor_segundo_desconto',
            'data_terceiro_desconto', 'valor_terceiro_desconto',
            'tipo_multa', 'data_multa', 'tipo_juros_mora', 'data_juros_mora',
            'codigo_protesto', 'dias_protesto', 'codigo_negativacao', 'dias_negativacao',
            'pagador_cpf_cnpj', 'pagador_nome', 'pagador_endereco', 'pagador_bairro',
            'pagador_cidade', 'pagador_cep', 'pagador_uf', 'pagador_email',
            'beneficiario_final_cpf_cnpj', 'beneficiario_final_nome',
            'mensagens_instrucao', 'codigo_cadastrar_pix', 'numero_contrato_cobranca',
            'situacao', 'situacao_banco', 'pdf_boleto',
            'emissao_banco', 'distribuicao_banco', 'banco_referencia_id', 'rateio_creditos', 'criado_por'
        ];

        $inserir = [];
        $valores = [];
        foreach ($campos as $campo) {
            if (array_key_exists($campo, $data)) {
                $inserir[] = $campo;
                $val = $data[$campo];
                if (in_array($campo, ['mensagens_instrucao', 'rateio_creditos']) && is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $valores[$campo] = $val;
            }
        }

        $colunas = implode(', ', $inserir);
        $placeholders = implode(', ', array_map(fn($c) => ":{$c}", $inserir));

        $sql = "INSERT INTO {$this->table} ({$colunas}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($valores);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza um boleto.
     */
    public function atualizar(int $id, array $data): bool
    {
        $sets = [];
        $valores = [];
        foreach ($data as $campo => $val) {
            if (in_array($campo, ['mensagens_instrucao', 'rateio_creditos']) && is_array($val)) {
                $val = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
            $sets[] = "{$campo} = :{$campo}";
            $valores[$campo] = $val;
        }
        $valores['id'] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($valores);
    }

    /**
     * Busca um boleto por ID.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT b.*, 
                       cb.banco, cb.identificacao as conexao_nome,
                       cli.nome_razao_social as cliente_nome_completo,
                       cli.cpf_cnpj as cliente_cpf_cnpj_cadastro,
                       pv.numero as pedido_numero, pv.descricao as pedido_descricao
                FROM {$this->table} b
                LEFT JOIN conexoes_bancarias cb ON b.conexao_bancaria_id = cb.id
                LEFT JOIN clientes cli ON b.cliente_id = cli.id
                LEFT JOIN pedidos_vinculados pv ON b.pedido_vinculado_id = pv.id
                WHERE b.id = :id AND b.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row = $this->decodificarJson($row);
        }
        return $row ?: null;
    }

    /**
     * Busca boletos por empresa com filtros.
     */
    public function findByEmpresa(int $empresaId, array $filtros = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT b.*, 
                       cb.banco, cb.identificacao as conexao_nome,
                       cli.nome_razao_social as cliente_nome_completo
                FROM {$this->table} b
                LEFT JOIN conexoes_bancarias cb ON b.conexao_bancaria_id = cb.id
                LEFT JOIN clientes cli ON b.cliente_id = cli.id
                WHERE b.empresa_id = :empresa_id AND b.deleted_at IS NULL";
        $params = ['empresa_id' => $empresaId];

        if (!empty($filtros['situacao'])) {
            $sql .= " AND b.situacao = :situacao";
            $params['situacao'] = $filtros['situacao'];
        }
        if (!empty($filtros['conexao_bancaria_id'])) {
            $sql .= " AND b.conexao_bancaria_id = :conexao_id";
            $params['conexao_id'] = $filtros['conexao_bancaria_id'];
        }
        if (!empty($filtros['cliente_id'])) {
            $sql .= " AND b.cliente_id = :cliente_id";
            $params['cliente_id'] = $filtros['cliente_id'];
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND b.data_vencimento >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND b.data_vencimento <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['busca'])) {
            $sql .= " AND (b.pagador_nome LIKE :busca OR b.pagador_cpf_cnpj LIKE :busca2 OR b.nosso_numero LIKE :busca3 OR b.seu_numero LIKE :busca4)";
            $params['busca'] = '%' . $filtros['busca'] . '%';
            $params['busca2'] = '%' . $filtros['busca'] . '%';
            $params['busca3'] = '%' . $filtros['busca'] . '%';
            $params['busca4'] = '%' . $filtros['busca'] . '%';
        }

        $sql .= " ORDER BY b.data_vencimento DESC, b.created_at DESC";
        $sql .= " LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($results as &$row) {
            $row = $this->decodificarJson($row);
        }

        return $results;
    }

    /**
     * Conta total para paginação.
     */
    public function countByEmpresa(int $empresaId, array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} b
                WHERE b.empresa_id = :empresa_id AND b.deleted_at IS NULL";
        $params = ['empresa_id' => $empresaId];

        if (!empty($filtros['situacao'])) {
            $sql .= " AND b.situacao = :situacao";
            $params['situacao'] = $filtros['situacao'];
        }
        if (!empty($filtros['conexao_bancaria_id'])) {
            $sql .= " AND b.conexao_bancaria_id = :conexao_id";
            $params['conexao_id'] = $filtros['conexao_bancaria_id'];
        }
        if (!empty($filtros['cliente_id'])) {
            $sql .= " AND b.cliente_id = :cliente_id";
            $params['cliente_id'] = $filtros['cliente_id'];
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND b.data_vencimento >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND b.data_vencimento <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['busca'])) {
            $sql .= " AND (b.pagador_nome LIKE :busca OR b.pagador_cpf_cnpj LIKE :busca2)";
            $params['busca'] = '%' . $filtros['busca'] . '%';
            $params['busca2'] = '%' . $filtros['busca'] . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Retorna estatísticas por situação (contadores).
     */
    public function getEstatisticas(int $empresaId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN situacao = 'em_aberto' THEN 1 ELSE 0 END) as em_aberto,
                    SUM(CASE WHEN situacao = 'liquidado' THEN 1 ELSE 0 END) as liquidados,
                    SUM(CASE WHEN situacao = 'baixado' THEN 1 ELSE 0 END) as baixados,
                    SUM(CASE WHEN situacao = 'protestado' THEN 1 ELSE 0 END) as protestados,
                    SUM(CASE WHEN situacao = 'negativado' THEN 1 ELSE 0 END) as negativados,
                    SUM(CASE WHEN situacao = 'vencido' THEN 1 ELSE 0 END) as vencidos,
                    SUM(CASE WHEN situacao = 'em_aberto' THEN valor ELSE 0 END) as valor_em_aberto,
                    SUM(CASE WHEN situacao = 'liquidado' THEN COALESCE(valor_recebido, valor) ELSE 0 END) as valor_liquidado,
                    SUM(CASE WHEN situacao = 'vencido' THEN valor ELSE 0 END) as valor_vencido,
                    SUM(CASE WHEN situacao IN ('em_aberto','vencido') AND data_vencimento < CURDATE() THEN valor ELSE 0 END) as valor_inadimplente,
                    COUNT(CASE WHEN situacao IN ('em_aberto','vencido') AND data_vencimento < CURDATE() THEN 1 END) as qtd_inadimplente
                FROM {$this->table}
                WHERE empresa_id = :empresa_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Analytics completo para o dashboard.
     */
    public function getAnalytics(int $empresaId, ?string $periodoInicio = null, ?string $periodoFim = null, ?int $conexaoId = null): array
    {
        $params = ['empresa_id' => $empresaId];
        $where = "empresa_id = :empresa_id AND deleted_at IS NULL";

        if ($periodoInicio) {
            $where .= " AND data_emissao >= :periodo_inicio";
            $params['periodo_inicio'] = $periodoInicio;
        }
        if ($periodoFim) {
            $where .= " AND data_emissao <= :periodo_fim";
            $params['periodo_fim'] = $periodoFim;
        }
        if ($conexaoId) {
            $where .= " AND conexao_bancaria_id = :conexao_id";
            $params['conexao_id'] = $conexaoId;
        }

        // KPIs
        $sqlKpi = "SELECT 
            COUNT(*) as total_emitidos,
            SUM(valor) as valor_total_emitido,
            SUM(CASE WHEN situacao = 'liquidado' THEN COALESCE(valor_recebido, valor) ELSE 0 END) as valor_total_recebido,
            COUNT(CASE WHEN situacao = 'liquidado' THEN 1 END) as total_liquidados,
            AVG(valor) as ticket_medio,
            AVG(CASE WHEN situacao = 'liquidado' AND data_pagamento IS NOT NULL 
                THEN DATEDIFF(data_pagamento, data_emissao) END) as prazo_medio_recebimento,
            COUNT(CASE WHEN situacao IN ('em_aberto','vencido') AND data_vencimento < CURDATE() THEN 1 END) as total_inadimplentes,
            SUM(CASE WHEN situacao IN ('em_aberto','vencido') AND data_vencimento < CURDATE() THEN valor ELSE 0 END) as valor_inadimplente
            FROM {$this->table} WHERE {$where}";
        $stmt = $this->db->prepare($sqlKpi);
        $stmt->execute($params);
        $kpis = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Taxa de inadimplência
        $kpis['taxa_inadimplencia'] = ($kpis['total_emitidos'] > 0)
            ? round(($kpis['total_inadimplentes'] / $kpis['total_emitidos']) * 100, 2)
            : 0;

        // Evolução mensal (últimos 12 meses)
        $sqlMensal = "SELECT 
            DATE_FORMAT(data_emissao, '%Y-%m') as mes,
            COUNT(*) as emitidos,
            SUM(valor) as valor_emitido,
            SUM(CASE WHEN situacao = 'liquidado' THEN 1 ELSE 0 END) as liquidados,
            SUM(CASE WHEN situacao = 'liquidado' THEN COALESCE(valor_recebido, valor) ELSE 0 END) as valor_recebido,
            SUM(CASE WHEN situacao IN ('em_aberto','vencido') AND data_vencimento < CURDATE() THEN 1 ELSE 0 END) as inadimplentes
            FROM {$this->table} WHERE {$where}
            GROUP BY DATE_FORMAT(data_emissao, '%Y-%m')
            ORDER BY mes DESC LIMIT 12";
        $stmt = $this->db->prepare($sqlMensal);
        $stmt->execute($params);
        $evolucaoMensal = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Distribuição por situação
        $sqlDist = "SELECT situacao, COUNT(*) as quantidade, SUM(valor) as valor_total
            FROM {$this->table} WHERE {$where}
            GROUP BY situacao ORDER BY quantidade DESC";
        $stmt = $this->db->prepare($sqlDist);
        $stmt->execute($params);
        $distribuicao = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Top inadimplentes
        $sqlInad = "SELECT pagador_cpf_cnpj, pagador_nome,
            COUNT(*) as qtd_boletos,
            SUM(valor) as valor_total,
            MIN(data_vencimento) as vencimento_mais_antigo
            FROM {$this->table}
            WHERE {$where} AND situacao IN ('em_aberto','vencido') AND data_vencimento < CURDATE()
            GROUP BY pagador_cpf_cnpj, pagador_nome
            ORDER BY valor_total DESC LIMIT 10";
        $stmt = $this->db->prepare($sqlInad);
        $stmt->execute($params);
        $topInadimplentes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'kpis' => $kpis,
            'evolucao_mensal' => array_reverse($evolucaoMensal),
            'distribuicao_situacao' => $distribuicao,
            'top_inadimplentes' => $topInadimplentes,
        ];
    }

    /**
     * Boletos vencidos (inadimplentes).
     */
    public function getInadimplentes(int $empresaId, int $limit = 50): array
    {
        $sql = "SELECT b.*, cli.nome_razao_social as cliente_nome_completo,
                       DATEDIFF(CURDATE(), b.data_vencimento) as dias_atraso
                FROM {$this->table} b
                LEFT JOIN clientes cli ON b.cliente_id = cli.id
                WHERE b.empresa_id = :empresa_id 
                  AND b.deleted_at IS NULL
                  AND b.situacao IN ('em_aberto','vencido')
                  AND b.data_vencimento < CURDATE()
                ORDER BY b.data_vencimento ASC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Busca por nosso_numero + conexão.
     */
    public function findByNossoNumero(int $nossoNumero, int $conexaoId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE nosso_numero = :nn AND conexao_bancaria_id = :cid AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nn' => $nossoNumero, 'cid' => $conexaoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->decodificarJson($row) : null;
    }

    /**
     * Soft delete.
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Atualiza situação de boletos vencidos (cron).
     */
    public function atualizarVencidos(int $empresaId): int
    {
        $sql = "UPDATE {$this->table} SET situacao = 'vencido'
                WHERE empresa_id = :empresa_id AND situacao = 'em_aberto' 
                AND data_vencimento < CURDATE() AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->rowCount();
    }

    private function decodificarJson(array $row): array
    {
        if (!empty($row['mensagens_instrucao'])) {
            $row['mensagens_instrucao'] = json_decode($row['mensagens_instrucao'], true) ?: [];
        }
        if (!empty($row['rateio_creditos'])) {
            $row['rateio_creditos'] = json_decode($row['rateio_creditos'], true) ?: [];
        }
        return $row;
    }
}
