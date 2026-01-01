# 📅 Configuração de CRONs do Sistema Financeiro

## 📋 Índice
- [CRONs Disponíveis](#crons-disponíveis)
- [Como Configurar](#como-configurar)
- [Exemplos de Configuração](#exemplos-de-configuração)
- [Monitoramento](#monitoramento)

---

## 🔄 CRONs Disponíveis

### 1. **Sincronização Bancária** (`sync_bancaria.php`)
**Função:** Busca automaticamente transações dos bancos conectados via Open Banking.

**Frequência Recomendada:** A cada 10 minutos (para suportar todas as opções de frequência)
**Comando:**
```bash
*/10 * * * * /usr/bin/php /caminho/completo/para/projeto/cron/sync_bancaria.php >> /var/log/cron_sync_bancaria.log 2>&1
```

**O que faz:**
- Busca transações de contas correntes, poupanças e cartões de crédito
- Renova tokens OAuth expirados automaticamente
- Classifica transações usando IA
- Detecta duplicatas
- Respeita frequência configurada para cada conexão:
  - **A cada 10 minutos**: Para acompanhamento em tempo real
  - **A cada 30 minutos**: Atualização frequente
  - **A cada hora**: Sincronização regular
  - **Diária**: Uma vez por dia
  - **Semanal**: Às segundas-feiras
  - **Manual**: Apenas quando solicitado manualmente

---

### 2. **Integrações** (`integracoes.php`)
**Função:** Sincroniza dados de integrações como WooCommerce, Banco de Dados externos, etc.

**Frequência Recomendada:** A cada 15 minutos  
**Comando:**
```bash
*/15 * * * * /usr/bin/php /caminho/completo/para/projeto/cron/integracoes.php >> /var/log/cron_integracoes.log 2>&1
```

**O que faz:**
- Sincroniza pedidos do WooCommerce
- Importa dados de bancos de dados externos
- Registra logs de todas as sincronizações
- Trata erros automaticamente

---

### 3. **Lembretes de Vencimento** (`lembretes_vencimento.php`)
**Função:** Notifica sobre contas a vencer nos próximos 3 dias.

**Frequência Recomendada:** Diário às 08:00  
**Comando:**
```bash
0 8 * * * /usr/bin/php /caminho/completo/para/projeto/cron/lembretes_vencimento.php >> /var/log/cron_lembretes.log 2>&1
```

**O que faz:**
- Verifica contas a pagar vencendo em 3 dias
- Verifica contas a receber vencendo em 3 dias
- Agrupa por empresa
- Prepara dados para envio de e-mail (você precisa implementar o envio)

**⚠️ TODO:** Integrar com sistema de e-mail (PHPMailer, SendGrid, etc.)

---

### 4. **Backup do Banco de Dados** (`backup_database.php`)
**Função:** Cria backup automático do banco de dados.

**Frequência Recomendada:** Diário às 03:00  
**Comando:**
```bash
0 3 * * * /usr/bin/php /caminho/completo/para/projeto/cron/backup_database.php >> /var/log/cron_backup.log 2>&1
```

**O que faz:**
- Executa mysqldump
- Comprime backup em .gz
- Remove backups com mais de 30 dias
- Calcula estatísticas de compressão

**Requisito:** `mysqldump` instalado no servidor

---

### 5. **Limpeza do Sistema** (`limpeza_sistema.php`)
**Função:** Remove dados antigos e otimiza o banco de dados.

**Frequência Recomendada:** Diário às 02:00  
**Comando:**
```bash
0 2 * * * /usr/bin/php /caminho/completo/para/projeto/cron/limpeza_sistema.php >> /var/log/cron_limpeza.log 2>&1
```

**O que faz:**
- Remove sessões PHP expiradas (> 24h)
- Remove logs de integrações antigos (> 90 dias)
- Remove logs de API antigos (> 60 dias)
- Remove transações pendentes ignoradas (> 30 dias)
- Remove arquivos temporários (> 24h)
- Otimiza tabelas do banco de dados
- Gera estatísticas de uso de espaço

---

## 🔧 Como Configurar

### Método 1: Crontab (Linux/Unix)

1. **Abrir o editor de crontab:**
```bash
crontab -e
```

2. **Adicionar todas as linhas de CRON:**
```bash
# Sincronização Bancária (a cada 10 minutos - para suportar todas as frequências)
*/10 * * * * /usr/bin/php /var/www/financeiro/cron/sync_bancaria.php >> /var/log/financeiro/cron_sync_bancaria.log 2>&1

# Integrações (a cada 15 minutos)
*/15 * * * * /usr/bin/php /var/www/financeiro/cron/integracoes.php >> /var/log/financeiro/cron_integracoes.log 2>&1

# Lembretes de Vencimento (diário às 08:00)
0 8 * * * /usr/bin/php /var/www/financeiro/cron/lembretes_vencimento.php >> /var/log/financeiro/cron_lembretes.log 2>&1

# Backup do Banco (diário às 03:00)
0 3 * * * /usr/bin/php /var/www/financeiro/cron/backup_database.php >> /var/log/financeiro/cron_backup.log 2>&1

# Limpeza do Sistema (diário às 02:00)
0 2 * * * /usr/bin/php /var/www/financeiro/cron/limpeza_sistema.php >> /var/log/financeiro/cron_limpeza.log 2>&1
```

3. **Salvar e sair** (Ctrl+X, depois Y, depois Enter)

4. **Verificar se foi salvo:**
```bash
crontab -l
```

---

### Método 2: cPanel / Painel de Hospedagem

1. Acesse **Tarefas Cron** no cPanel
2. Para cada CRON, adicione:
   - **Minuto, Hora, Dia, Mês, Dia da Semana** conforme tabela acima
   - **Comando:** Cole o comando completo de cada CRON

---

### Método 3: Plesk

1. Vá em **Ferramentas e Configurações** → **Tarefas Agendadas**
2. Clique em **Adicionar Tarefa**
3. Configure cada CRON com:
   - Script: Caminho completo do arquivo PHP
   - Horário: Conforme recomendação acima

---

## 📊 Monitoramento

### Verificar Logs

Cada CRON gera seu próprio arquivo de log. Para visualizar:

```bash
# Últimas 50 linhas do log de sincronização bancária
tail -n 50 /var/log/financeiro/cron_sync_bancaria.log

# Logs em tempo real
tail -f /var/log/financeiro/cron_sync_bancaria.log
```

### Criar Diretório de Logs

```bash
sudo mkdir -p /var/log/financeiro
sudo chown www-data:www-data /var/log/financeiro
sudo chmod 755 /var/log/financeiro
```

### Script de Monitoramento

Crie um arquivo `monitor_crons.sh`:

```bash
#!/bin/bash
echo "=== STATUS DOS CRONs DO SISTEMA FINANCEIRO ==="
echo ""

for log in /var/log/financeiro/cron_*.log; do
    echo "📄 $(basename $log)"
    echo "   Última execução: $(stat -c %y "$log" 2>/dev/null | cut -d'.' -f1 || echo 'Nunca executado')"
    echo "   Tamanho: $(du -h "$log" 2>/dev/null | cut -f1 || echo '0')"
    echo "   Últimas 3 linhas:"
    tail -n 3 "$log" 2>/dev/null | sed 's/^/     /'
    echo ""
done
```

Execute: `bash monitor_crons.sh`

---

## ⚙️ Configurações Importantes

### Variáveis de Ambiente (.env)

Certifique-se de que seu `.env` está configurado:

```env
# Banco de Dados
DB_HOST=localhost
DB_NAME=financeiro
DB_USER=root
DB_PASSWORD=sua_senha_segura

# OpenAI (para classificação IA)
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini

# Criptografia (para tokens bancários)
ENCRYPTION_KEY=chave_secreta_minimo_32_caracteres
```

### Permissões

```bash
# Dar permissão de execução aos scripts
chmod +x /var/www/financeiro/cron/*.php

# Criar diretório de backups
mkdir -p /var/www/financeiro/backups
chmod 755 /var/www/financeiro/backups
```

---

## 🔍 Troubleshooting

### CRON não está executando?

1. **Verificar se o crontab está ativo:**
```bash
sudo systemctl status cron  # Debian/Ubuntu
sudo systemctl status crond  # CentOS/RHEL
```

2. **Ver logs do sistema:**
```bash
sudo grep CRON /var/log/syslog  # Debian/Ubuntu
sudo grep CRON /var/log/cron    # CentOS/RHEL
```

3. **Testar manualmente:**
```bash
/usr/bin/php /var/www/financeiro/cron/sync_bancaria.php
```

4. **Verificar caminho do PHP:**
```bash
which php
# Use o caminho retornado nos comandos CRON
```

### Erro de permissões?

```bash
# Dar permissões ao usuário do servidor web
sudo chown -R www-data:www-data /var/www/financeiro/cron
sudo chmod -R 755 /var/www/financeiro/cron
```

---

## 📈 Frequências Personalizadas

Você pode ajustar as frequências conforme necessário:

| Frequência | Sintaxe | Exemplo |
|-----------|---------|---------|
| A cada 5 minutos | `*/5 * * * *` | Integrações críticas |
| A cada 30 minutos | `*/30 * * * *` | Verificações médias |
| A cada 2 horas | `0 */2 * * *` | Sincronizações leves |
| Às 14:30 | `30 14 * * *` | Relatórios diários |
| Segunda às 09:00 | `0 9 * * 1` | Início de semana |
| Todo dia 1 às 00:00 | `0 0 1 * *` | Mensal |

---

## ✅ Checklist de Configuração

- [ ] Todos os 5 CRONs adicionados ao crontab
- [ ] Diretório de logs criado (`/var/log/financeiro`)
- [ ] Diretório de backups criado (`/var/www/financeiro/backups`)
- [ ] Permissões corretas nos arquivos PHP
- [ ] Variáveis de ambiente configuradas (`.env`)
- [ ] `mysqldump` instalado (para backups)
- [ ] Testado manualmente cada script
- [ ] Logs sendo gerados corretamente
- [ ] Sistema de e-mail configurado (lembretes)

---

## 📞 Suporte

Se precisar de ajuda, verifique:
1. Logs do CRON
2. Logs do servidor web
3. Permissões de arquivos
4. Configurações do .env

**Desenvolvido por:** Sistema Financeiro Empresarial  
**Última atualização:** 2025-01-01
