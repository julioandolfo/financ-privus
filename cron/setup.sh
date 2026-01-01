#!/bin/bash

###############################################################################
# Script de Configuração Automática de CRONs
# Sistema Financeiro Empresarial
###############################################################################

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║   🔧 CONFIGURAÇÃO DE CRONs - SISTEMA FINANCEIRO EMPRESARIAL   ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Detectar diretório do projeto
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

echo "📁 Diretório do Projeto: $PROJECT_DIR"
echo ""

# Verificar PHP
PHP_PATH=$(which php)
if [ -z "$PHP_PATH" ]; then
    echo "❌ PHP não encontrado! Instale o PHP primeiro."
    exit 1
fi
echo "✅ PHP encontrado: $PHP_PATH"

# Verificar permissões
echo ""
echo "🔐 Verificando permissões..."
chmod +x "$SCRIPT_DIR"/*.php
echo "✅ Permissões de execução configuradas"

# Criar diretórios necessários
echo ""
echo "📂 Criando diretórios..."

# Diretório de logs
LOG_DIR="/var/log/financeiro"
if [ ! -d "$LOG_DIR" ]; then
    if sudo mkdir -p "$LOG_DIR" 2>/dev/null; then
        sudo chown www-data:www-data "$LOG_DIR" 2>/dev/null || chown $(whoami):$(whoami) "$LOG_DIR"
        sudo chmod 755 "$LOG_DIR"
        echo "✅ Diretório de logs criado: $LOG_DIR"
    else
        LOG_DIR="$PROJECT_DIR/logs"
        mkdir -p "$LOG_DIR"
        echo "⚠️  Usando logs locais: $LOG_DIR"
    fi
else
    echo "✅ Diretório de logs já existe: $LOG_DIR"
fi

# Diretório de backups
BACKUP_DIR="$PROJECT_DIR/backups"
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    chmod 755 "$BACKUP_DIR"
    echo "✅ Diretório de backups criado: $BACKUP_DIR"
else
    echo "✅ Diretório de backups já existe: $BACKUP_DIR"
fi

# Testar scripts
echo ""
echo "🧪 Testando scripts CRON..."
echo ""

test_script() {
    local script=$1
    local name=$2
    
    echo -n "   Testando $name... "
    if $PHP_PATH "$SCRIPT_DIR/$script" > /dev/null 2>&1; then
        echo "✅"
    else
        echo "❌ (verifique o arquivo)"
    fi
}

test_script "sync_bancaria.php" "Sincronização Bancária"
test_script "integracoes.php" "Integrações"
test_script "lembretes_vencimento.php" "Lembretes"
test_script "backup_database.php" "Backup"
test_script "limpeza_sistema.php" "Limpeza"

# Gerar configuração do crontab
echo ""
echo "📝 Gerando configuração do crontab..."
echo ""

CRON_CONFIG="$SCRIPT_DIR/crontab.txt"

cat > "$CRON_CONFIG" << EOF
# ╔════════════════════════════════════════════════════════════════╗
# ║        CRONs do Sistema Financeiro Empresarial                ║
# ║        Gerado automaticamente em $(date +"%Y-%m-%d %H:%M:%S")        ║
# ╚════════════════════════════════════════════════════════════════╝

# Sincronização Bancária (a cada 10 minutos)
*/10 * * * * $PHP_PATH $SCRIPT_DIR/sync_bancaria.php >> $LOG_DIR/cron_sync_bancaria.log 2>&1

# Integrações (a cada 15 minutos)
*/15 * * * * $PHP_PATH $SCRIPT_DIR/integracoes.php >> $LOG_DIR/cron_integracoes.log 2>&1

# Lembretes de Vencimento (diário às 08:00)
0 8 * * * $PHP_PATH $SCRIPT_DIR/lembretes_vencimento.php >> $LOG_DIR/cron_lembretes.log 2>&1

# Backup do Banco de Dados (diário às 03:00)
0 3 * * * $PHP_PATH $SCRIPT_DIR/backup_database.php >> $LOG_DIR/cron_backup.log 2>&1

# Limpeza do Sistema (diário às 02:00)
0 2 * * * $PHP_PATH $SCRIPT_DIR/limpeza_sistema.php >> $LOG_DIR/cron_limpeza.log 2>&1
EOF

echo "✅ Configuração gerada em: $CRON_CONFIG"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo ""
echo "1️⃣  Revisar a configuração gerada:"
echo "    cat $CRON_CONFIG"
echo ""
echo "2️⃣  Instalar no crontab:"
echo "    crontab $CRON_CONFIG"
echo ""
echo "3️⃣  Verificar instalação:"
echo "    crontab -l"
echo ""
echo "4️⃣  Monitorar logs:"
echo "    tail -f $LOG_DIR/cron_sync_bancaria.log"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "❓ Deseja instalar os CRONs agora? (s/N)"
read -r response

if [[ "$response" =~ ^([sS][iI][mM]|[sS])$ ]]; then
    echo ""
    echo "📥 Instalando CRONs..."
    
    # Backup do crontab atual
    crontab -l > "$SCRIPT_DIR/crontab.backup.txt" 2>/dev/null
    
    # Instalar novo crontab
    crontab "$CRON_CONFIG"
    
    echo "✅ CRONs instalados com sucesso!"
    echo ""
    echo "📋 CRONs ativos:"
    crontab -l
else
    echo ""
    echo "ℹ️  Instalação cancelada. Você pode instalar manualmente depois:"
    echo "   crontab $CRON_CONFIG"
fi

echo ""
echo "✅ Configuração concluída!"
echo ""
