<?php
namespace Includes\Services;

use App\Models\Notificacao;
use App\Models\NotificacaoConfig;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Core\Database;
use PDO;

/**
 * Service para gerenciamento de notificações
 */
class NotificacaoService
{
    private $notificacaoModel;
    private $configModel;
    private $db;
    
    public function __construct()
    {
        $this->notificacaoModel = new Notificacao();
        $this->configModel = new NotificacaoConfig();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria uma notificação
     */
    public function criar($usuarioId, $tipo, $titulo, $mensagem, $opcoes = [])
    {
        $data = [
            'usuario_id' => $usuarioId,
            'empresa_id' => $opcoes['empresa_id'] ?? null,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'icone' => $opcoes['icone'] ?? $this->getIconePorTipo($tipo),
            'cor' => $opcoes['cor'] ?? $this->getCorPorTipo($tipo),
            'link_url' => $opcoes['link_url'] ?? null,
            'link_texto' => $opcoes['link_texto'] ?? null,
            'dados_extras' => $opcoes['dados_extras'] ?? null
        ];
        
        return $this->notificacaoModel->create($data);
    }
    
    /**
     * Cria notificação para múltiplos usuários
     */
    public function criarParaUsuarios($usuarioIds, $tipo, $titulo, $mensagem, $opcoes = [])
    {
        $ids = [];
        foreach ($usuarioIds as $usuarioId) {
            $id = $this->criar($usuarioId, $tipo, $titulo, $mensagem, $opcoes);
            if ($id) {
                $ids[] = $id;
            }
        }
        return $ids;
    }
    
    /**
     * Busca notificações do usuário para o dropdown
     */
    public function buscarParaDropdown($usuarioId)
    {
        $notificacoes = $this->notificacaoModel->buscarRecentes($usuarioId, 10);
        $naoLidas = $this->notificacaoModel->contarNaoLidas($usuarioId);
        
        return [
            'notificacoes' => $notificacoes,
            'nao_lidas' => $naoLidas
        ];
    }
    
    /**
     * Gera notificações de vencimento
     */
    public function gerarNotificacoesVencimento()
    {
        // Busca configurações de todos os usuários
        $sql = "SELECT DISTINCT nc.usuario_id, nc.antecedencia_vencimento, ue.empresa_id
                FROM notificacoes_config nc
                JOIN usuario_empresa ue ON nc.usuario_id = ue.usuario_id
                WHERE nc.notificar_vencimentos = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($configs as $config) {
            $this->notificarVencimentosUsuario(
                $config['usuario_id'], 
                $config['empresa_id'],
                $config['antecedencia_vencimento']
            );
        }
    }
    
    /**
     * Notifica vencimentos para um usuário específico
     */
    private function notificarVencimentosUsuario($usuarioId, $empresaId, $antecedencia)
    {
        $dataLimite = date('Y-m-d', strtotime("+{$antecedencia} days"));
        
        // Contas a pagar
        $sql = "SELECT COUNT(*) as total, SUM(valor_total - valor_pago) as valor
                FROM contas_pagar 
                WHERE empresa_id = ? 
                AND status = 'pendente' 
                AND data_vencimento <= ?
                AND data_vencimento >= CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $dataLimite]);
        $contasPagar = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contasPagar['total'] > 0) {
            // Verifica se já notificou hoje
            $jaNotificou = $this->verificarNotificacaoHoje($usuarioId, 'vencimento', 'contas_pagar');
            
            if (!$jaNotificou) {
                $this->criar($usuarioId, 'vencimento', 
                    "{$contasPagar['total']} contas a pagar vencem em breve",
                    "Total de R$ " . number_format($contasPagar['valor'], 2, ',', '.') . " nos próximos {$antecedencia} dias",
                    [
                        'empresa_id' => $empresaId,
                        'link_url' => '/contas-pagar?status=pendente',
                        'link_texto' => 'Ver contas',
                        'dados_extras' => ['tipo_conta' => 'contas_pagar', 'total' => $contasPagar['total']]
                    ]
                );
            }
        }
        
        // Contas a receber
        $sql = "SELECT COUNT(*) as total, SUM(valor_total - valor_recebido) as valor
                FROM contas_receber 
                WHERE empresa_id = ? 
                AND status = 'pendente' 
                AND data_vencimento <= ?
                AND data_vencimento >= CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $dataLimite]);
        $contasReceber = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contasReceber['total'] > 0) {
            $jaNotificou = $this->verificarNotificacaoHoje($usuarioId, 'recebimento', 'contas_receber');
            
            if (!$jaNotificou) {
                $this->criar($usuarioId, 'recebimento', 
                    "{$contasReceber['total']} recebimentos previstos",
                    "Total de R$ " . number_format($contasReceber['valor'], 2, ',', '.') . " nos próximos {$antecedencia} dias",
                    [
                        'empresa_id' => $empresaId,
                        'link_url' => '/contas-receber?status=pendente',
                        'link_texto' => 'Ver contas',
                        'dados_extras' => ['tipo_conta' => 'contas_receber', 'total' => $contasReceber['total']]
                    ]
                );
            }
        }
    }
    
    /**
     * Gera notificações de contas vencidas
     */
    public function gerarNotificacoesVencidas()
    {
        $sql = "SELECT DISTINCT nc.usuario_id, ue.empresa_id
                FROM notificacoes_config nc
                JOIN usuario_empresa ue ON nc.usuario_id = ue.usuario_id
                WHERE nc.notificar_vencidas = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($configs as $config) {
            $this->notificarVencidasUsuario($config['usuario_id'], $config['empresa_id']);
        }
    }
    
    /**
     * Notifica contas vencidas para um usuário
     */
    private function notificarVencidasUsuario($usuarioId, $empresaId)
    {
        // Contas a pagar vencidas
        $sql = "SELECT COUNT(*) as total, SUM(valor_total - valor_pago) as valor
                FROM contas_pagar 
                WHERE empresa_id = ? 
                AND status = 'pendente' 
                AND data_vencimento < CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId]);
        $vencidas = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($vencidas['total'] > 0) {
            $jaNotificou = $this->verificarNotificacaoHoje($usuarioId, 'vencida', 'contas_pagar_vencidas');
            
            if (!$jaNotificou) {
                $this->criar($usuarioId, 'vencida', 
                    "{$vencidas['total']} contas estão vencidas!",
                    "Total em atraso: R$ " . number_format($vencidas['valor'], 2, ',', '.'),
                    [
                        'empresa_id' => $empresaId,
                        'cor' => 'red',
                        'link_url' => '/contas-pagar?status=pendente&vencidas=1',
                        'link_texto' => 'Ver contas vencidas',
                        'dados_extras' => ['tipo_conta' => 'contas_pagar_vencidas', 'total' => $vencidas['total']]
                    ]
                );
            }
        }
    }
    
    /**
     * Notifica sobre recorrência gerada
     */
    public function notificarRecorrenciaGerada($usuarioId, $tipo, $descricao, $valor, $contaId)
    {
        $config = $this->configModel->findByUsuario($usuarioId);
        
        if (!$config['notificar_recorrencias']) {
            return false;
        }
        
        $tipoTexto = $tipo === 'despesa' ? 'Despesa' : 'Receita';
        $urlBase = $tipo === 'despesa' ? '/contas-pagar/' : '/contas-receber/';
        
        return $this->criar($usuarioId, 'recorrencia',
            "{$tipoTexto} recorrente gerada",
            "{$descricao} - R$ " . number_format($valor, 2, ',', '.'),
            [
                'icone' => 'refresh',
                'cor' => $tipo === 'despesa' ? 'orange' : 'green',
                'link_url' => $urlBase . $contaId,
                'link_texto' => 'Ver detalhes'
            ]
        );
    }
    
    /**
     * Verifica se já notificou hoje
     */
    private function verificarNotificacaoHoje($usuarioId, $tipo, $identificador)
    {
        $sql = "SELECT id FROM notificacoes 
                WHERE usuario_id = ? 
                AND tipo = ? 
                AND DATE(created_at) = CURDATE()
                AND JSON_EXTRACT(dados_extras, '$.tipo_conta') = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId, $tipo, $identificador]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Retorna ícone baseado no tipo
     */
    private function getIconePorTipo($tipo)
    {
        $icones = [
            'vencimento' => 'clock',
            'vencida' => 'exclamation-circle',
            'recorrencia' => 'refresh',
            'recebimento' => 'cash',
            'sistema' => 'cog',
            'fluxo_caixa' => 'chart-line',
            'alerta' => 'bell'
        ];
        
        return $icones[$tipo] ?? 'bell';
    }
    
    /**
     * Retorna cor baseada no tipo
     */
    private function getCorPorTipo($tipo)
    {
        $cores = [
            'vencimento' => 'yellow',
            'vencida' => 'red',
            'recorrencia' => 'indigo',
            'recebimento' => 'green',
            'sistema' => 'gray',
            'fluxo_caixa' => 'blue',
            'alerta' => 'orange'
        ];
        
        return $cores[$tipo] ?? 'blue';
    }
    
    /**
     * Limpa notificações antigas
     */
    public function limparAntigas($dias = 30)
    {
        return $this->notificacaoModel->limparAntigas($dias);
    }
    
    /**
     * Envia Web Push para notificações pendentes
     */
    public function enviarWebPush()
    {
        $notificacoes = $this->notificacaoModel->buscarParaPush();
        
        foreach ($notificacoes as $notificacao) {
            $enviado = $this->enviarPush(
                $notificacao['web_push_endpoint'],
                $notificacao['web_push_p256dh'],
                $notificacao['web_push_auth'],
                $notificacao['titulo'],
                $notificacao['mensagem'],
                $notificacao['link_url']
            );
            
            if ($enviado) {
                $this->notificacaoModel->marcarComoEnviadaPush($notificacao['id']);
            }
        }
    }
    
    /**
     * Envia push notification individual
     */
    private function enviarPush($endpoint, $p256dh, $auth, $titulo, $mensagem, $url = null)
    {
        // Implementação simplificada - em produção usar biblioteca como web-push-php
        // Por enquanto, apenas marca como enviado
        // A implementação real requer VAPID keys e biblioteca de Web Push
        return true;
    }
    
    /**
     * Formata tempo relativo
     */
    public static function tempoRelativo($data)
    {
        $agora = new \DateTime();
        $dataNotif = new \DateTime($data);
        $diff = $agora->diff($dataNotif);
        
        if ($diff->days == 0) {
            if ($diff->h == 0) {
                if ($diff->i == 0) {
                    return 'agora';
                }
                return "há {$diff->i} min";
            }
            return "há {$diff->h}h";
        } elseif ($diff->days == 1) {
            return 'ontem';
        } elseif ($diff->days < 7) {
            return "há {$diff->days} dias";
        } else {
            return $dataNotif->format('d/m/Y');
        }
    }
}
