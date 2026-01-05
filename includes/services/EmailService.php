<?php
namespace Includes\Services;

use App\Models\Configuracao;

/**
 * Serviço para envio de emails
 */
class EmailService
{
    private $config;
    
    public function __construct()
    {
        $this->loadConfig();
    }
    
    /**
     * Carrega configurações de email
     */
    private function loadConfig()
    {
        $this->config = [
            'host' => Configuracao::get('email.smtp_host', 'smtp.gmail.com'),
            'port' => Configuracao::get('email.smtp_port', 587),
            'username' => Configuracao::get('email.smtp_usuario', ''),
            'password' => Configuracao::get('email.senha', ''),
            'from' => Configuracao::get('email.remetente_email', ''),
            'from_name' => Configuracao::get('email.remetente_nome', 'Sistema Financeiro'),
            'encryption' => Configuracao::get('email.smtp_seguranca', 'tls'),
        ];
    }
    
    /**
     * Envia email de teste
     */
    public function enviarEmailTeste($emailDestino)
    {
        $assunto = 'Teste de Configuração de Email - ' . date('d/m/Y H:i');
        $mensagem = $this->getMensagemTeste();
        
        return $this->enviar($emailDestino, $assunto, $mensagem);
    }
    
    /**
     * Envia email usando PHPMailer ou mail() nativo
     */
    public function enviar($para, $assunto, $mensagem, $nomeDestinatario = '')
    {
        // Verificar se as configurações básicas estão preenchidas
        if (empty($this->config['host']) || empty($this->config['username'])) {
            throw new \Exception('Configurações de email não estão completas. Configure SMTP Host e Usuário.');
        }
        
        // Verificar se PHPMailer está disponível via Composer
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            return $this->enviarComPHPMailer($para, $assunto, $mensagem, $nomeDestinatario);
        }
        
        // Fallback: usar mail() nativo do PHP
        return $this->enviarComMailNativo($para, $assunto, $mensagem);
    }
    
    /**
     * Envia email usando PHPMailer
     */
    private function enviarComPHPMailer($para, $assunto, $mensagem, $nomeDestinatario = '')
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = $this->config['encryption'];
            $mail->Port = $this->config['port'];
            $mail->CharSet = 'UTF-8';
            
            // Remetente
            $mail->setFrom($this->config['from'], $this->config['from_name']);
            
            // Destinatário
            $mail->addAddress($para, $nomeDestinatario);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $mensagem;
            $mail->AltBody = strip_tags($mensagem);
            
            $mail->send();
            return [
                'success' => true,
                'message' => 'Email enviado com sucesso!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $mail->ErrorInfo
            ];
        }
    }
    
    /**
     * Envia email usando mail() nativo do PHP
     */
    private function enviarComMailNativo($para, $assunto, $mensagem)
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->config['from_name'] . ' <' . $this->config['from'] . '>',
            'Reply-To: ' . $this->config['from'],
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $resultado = mail($para, $assunto, $mensagem, implode("\r\n", $headers));
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Email enviado com sucesso usando mail() nativo!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao enviar email. Verifique as configurações do servidor e do PHP.'
            ];
        }
    }
    
    /**
     * Retorna mensagem de teste em HTML
     */
    private function getMensagemTeste()
    {
        $data = date('d/m/Y H:i:s');
        $config = $this->config;
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #3b82f6; border-radius: 4px; }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                .success { color: #10b981; font-weight: bold; }
                h1 { margin: 0; font-size: 24px; }
                h2 { color: #3b82f6; margin-top: 0; }
                .label { font-weight: bold; color: #6b7280; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Email de Teste</h1>
                    <p style='margin: 10px 0 0 0;'>Sistema Financeiro Empresarial</p>
                </div>
                <div class='content'>
                    <p class='success'>🎉 Parabéns! Seu servidor de email está configurado corretamente.</p>
                    
                    <div class='info-box'>
                        <h2>📧 Informações do Teste</h2>
                        <p><span class='label'>Data/Hora:</span> {$data}</p>
                        <p><span class='label'>Servidor SMTP:</span> {$config['host']}:{$config['port']}</p>
                        <p><span class='label'>Segurança:</span> " . strtoupper($config['encryption']) . "</p>
                        <p><span class='label'>Remetente:</span> {$config['from']}</p>
                    </div>
                    
                    <div class='info-box'>
                        <h2>✨ O que isso significa?</h2>
                        <ul>
                            <li>✅ As configurações de SMTP estão corretas</li>
                            <li>✅ A autenticação foi bem-sucedida</li>
                            <li>✅ O servidor está pronto para enviar emails</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 20px;'><strong>Próximos passos:</strong></p>
                    <ul>
                        <li>Configure os lembretes automáticos de vencimento</li>
                        <li>Ative as notificações por email</li>
                        <li>Configure alertas de movimentações importantes</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>Este é um email automático de teste do Sistema Financeiro Empresarial.</p>
                    <p>Se você não solicitou este teste, pode ignorar esta mensagem.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Valida configurações de email
     */
    public function validarConfiguracao()
    {
        $erros = [];
        
        if (empty($this->config['host'])) {
            $erros[] = 'Servidor SMTP não configurado';
        }
        
        if (empty($this->config['username'])) {
            $erros[] = 'Usuário SMTP não configurado';
        }
        
        if (empty($this->config['password'])) {
            $erros[] = 'Senha SMTP não configurada';
        }
        
        if (empty($this->config['from'])) {
            $erros[] = 'Email remetente não configurado';
        }
        
        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }
    
    /**
     * Retorna informações da configuração atual
     */
    public function getInfo()
    {
        return [
            'servidor' => $this->config['host'] . ':' . $this->config['port'],
            'seguranca' => strtoupper($this->config['encryption']),
            'usuario' => $this->config['username'],
            'remetente' => $this->config['from'],
            'remetente_nome' => $this->config['from_name'],
            'phpmailer_disponivel' => class_exists('\PHPMailer\PHPMailer\PHPMailer')
        ];
    }
}
