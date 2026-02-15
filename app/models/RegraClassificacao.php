<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para regras de classificação automática de transações.
 * 
 * Permite que o usuário defina regras fixas como:
 * - "NETFLIX" contém -> Categoria: Assinaturas
 * - "ALUGUEL" contém -> Categoria: Despesas Fixas, Centro: Operacional
 * 
 * Essas regras são verificadas ANTES da IA, economizando custos de API.
 */
class RegraClassificacao extends Model
{
    protected $table = 'regras_classificacao';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca todas as regras ativas de uma empresa, ordenadas por prioridade.
     */
    public function findByEmpresa($empresaId)
    {
        $sql = "SELECT rc.*, 
                       cf.nome as categoria_nome, cf.codigo as categoria_codigo,
                       cc.nome as centro_custo_nome,
                       f.nome_razao_social as fornecedor_nome,
                       cli.nome_razao_social as cliente_nome
                FROM {$this->table} rc
                LEFT JOIN categorias_financeiras cf ON rc.categoria_id = cf.id
                LEFT JOIN centros_custo cc ON rc.centro_custo_id = cc.id
                LEFT JOIN fornecedores f ON rc.fornecedor_id = f.id
                LEFT JOIN clientes cli ON rc.cliente_id = cli.id
                WHERE rc.empresa_id = :empresa_id AND rc.ativo = 1
                ORDER BY rc.prioridade DESC, rc.vezes_aplicada DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Tenta classificar uma transação usando regras fixas.
     * Retorna a classificação ou null se nenhuma regra casar.
     * 
     * @param string $descricao   Descrição da transação
     * @param string $tipo        'debito' ou 'credito'
     * @param int    $empresaId   ID da empresa
     * @return array|null Classificação encontrada ou null
     */
    public function classificar(string $descricao, string $tipo, int $empresaId): ?array
    {
        $regras = $this->findByEmpresa($empresaId);
        $descricaoUpper = mb_strtoupper($descricao);

        foreach ($regras as $regra) {
            // Verificar tipo de transação
            if ($regra['tipo_transacao'] !== 'ambos' && $regra['tipo_transacao'] !== $tipo) {
                continue;
            }

            $padrao = mb_strtoupper($regra['padrao_descricao']);
            $match = false;

            switch ($regra['tipo_match']) {
                case 'contem':
                    $match = mb_strpos($descricaoUpper, $padrao) !== false;
                    break;
                case 'exato':
                    $match = $descricaoUpper === $padrao;
                    break;
                case 'comeca_com':
                    $match = mb_strpos($descricaoUpper, $padrao) === 0;
                    break;
                case 'termina_com':
                    $match = mb_substr($descricaoUpper, -mb_strlen($padrao)) === $padrao;
                    break;
                case 'regex':
                    $match = @preg_match('/' . $regra['padrao_descricao'] . '/i', $descricao) === 1;
                    break;
            }

            if ($match) {
                // Incrementar contador
                $this->incrementarContador($regra['id']);

                return [
                    'categoria_id' => $regra['categoria_id'],
                    'centro_custo_id' => $regra['centro_custo_id'],
                    'fornecedor_id' => $regra['fornecedor_id'],
                    'cliente_id' => $regra['cliente_id'],
                    'confianca' => 95, // Alta confiança para regras manuais
                    'justificativa' => "Regra automática: \"{$regra['padrao_descricao']}\" ({$regra['tipo_match']})",
                    'regra_id' => $regra['id']
                ];
            }
        }

        return null;
    }

    /**
     * Cria uma nova regra.
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, padrao_descricao, tipo_match, tipo_transacao,
                 categoria_id, centro_custo_id, fornecedor_id, cliente_id,
                 prioridade, ativo, criado_por)
                VALUES 
                (:empresa_id, :padrao_descricao, :tipo_match, :tipo_transacao,
                 :categoria_id, :centro_custo_id, :fornecedor_id, :cliente_id,
                 :prioridade, :ativo, :criado_por)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'padrao_descricao' => $data['padrao_descricao'],
            'tipo_match' => $data['tipo_match'] ?? 'contem',
            'tipo_transacao' => $data['tipo_transacao'] ?? 'ambos',
            'categoria_id' => $data['categoria_id'] ?? null,
            'centro_custo_id' => $data['centro_custo_id'] ?? null,
            'fornecedor_id' => $data['fornecedor_id'] ?? null,
            'cliente_id' => $data['cliente_id'] ?? null,
            'prioridade' => $data['prioridade'] ?? 0,
            'ativo' => $data['ativo'] ?? 1,
            'criado_por' => $data['criado_por'] ?? null
        ]) ? $this->db->lastInsertId() : false;
    }

    /**
     * Atualiza uma regra.
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];
        $allowed = ['padrao_descricao', 'tipo_match', 'tipo_transacao', 'categoria_id',
                     'centro_custo_id', 'fornecedor_id', 'cliente_id', 'prioridade', 'ativo'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Remove (soft delete) uma regra.
     */
    public function desativar($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Busca regra por ID.
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Incrementa o contador de vezes aplicada.
     */
    private function incrementarContador($id)
    {
        $sql = "UPDATE {$this->table} SET vezes_aplicada = vezes_aplicada + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Cria regra automaticamente a partir de uma aprovação do usuário.
     * Chamado quando o usuário corrige a categoria de uma transação.
     */
    public function criarRegraDeAprovacao(int $empresaId, string $descricao, string $tipo, array $classificacao, int $usuarioId): ?int
    {
        // Extrair palavras-chave da descrição (ignorar números, datas, etc.)
        $palavrasChave = $this->extrairPalavrasChave($descricao);

        if (empty($palavrasChave)) {
            return null;
        }

        // Verificar se já existe regra similar
        $existente = $this->findByPadrao($empresaId, $palavrasChave);
        if ($existente) {
            return null; // Já existe
        }

        return $this->create([
            'empresa_id' => $empresaId,
            'padrao_descricao' => $palavrasChave,
            'tipo_match' => 'contem',
            'tipo_transacao' => $tipo,
            'categoria_id' => $classificacao['categoria_id'] ?? null,
            'centro_custo_id' => $classificacao['centro_custo_id'] ?? null,
            'fornecedor_id' => $classificacao['fornecedor_id'] ?? null,
            'cliente_id' => $classificacao['cliente_id'] ?? null,
            'prioridade' => 0,
            'criado_por' => $usuarioId
        ]);
    }

    /**
     * Extrai palavras-chave relevantes de uma descrição bancária.
     */
    private function extrairPalavrasChave(string $descricao): string
    {
        // Remover caracteres especiais, números de documento, datas
        $limpo = preg_replace('/\d{2}\/\d{2}\/\d{4}/', '', $descricao); // Datas
        $limpo = preg_replace('/\d{2}\.\d{3}\.\d{3}[\/ ]\d{4}[-]?\d{2}/', '', $limpo); // CNPJ
        $limpo = preg_replace('/\d{3}\.\d{3}\.\d{3}[-]?\d{2}/', '', $limpo); // CPF
        $limpo = preg_replace('/\b\d{10,}\b/', '', $limpo); // Números longos
        $limpo = preg_replace('/\s+/', ' ', trim($limpo));

        // Pegar as primeiras palavras significativas
        $palavras = explode(' ', $limpo);
        $significativas = array_filter($palavras, function ($p) {
            return mb_strlen($p) > 2 && !in_array(mb_strtoupper($p), [
                'PIX', 'TED', 'DOC', 'RECEBIDO', 'ENVIADO', 'PAGTO', 'PAGAMENTO',
                'REF', 'NUM', 'CPF', 'CNPJ', 'DE', 'DO', 'DA', 'DOS', 'DAS',
                'PARA', 'COM', 'POR', 'EM', 'NO', 'NA'
            ]);
        });

        // Pegar até 3 palavras-chave
        $chaves = array_slice(array_values($significativas), 0, 3);
        return implode(' ', $chaves);
    }

    /**
     * Busca regra existente por padrão similar.
     */
    private function findByPadrao(int $empresaId, string $padrao)
    {
        $sql = "SELECT id FROM {$this->table} 
                WHERE empresa_id = :empresa_id AND padrao_descricao = :padrao AND ativo = 1 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'padrao' => $padrao]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
