#!/bin/bash

###############################################################################
# Script de Monitoramento de CRONs
# Sistema Financeiro Empresarial
###############################################################################

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Detectar diretório de logs
if [ -d "/var/log/financeiro" ]; then
    LOG_DIR="/var/log/financeiro"
else
    SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
    LOG_DIR="$PROJECT_DIR/logs"
fi

clear
echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║      📊 MONITOR DE CRONs - SISTEMA FINANCEIRO                 ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "📁 Diretório de logs: ${YELLOW}$LOG_DIR${NC}"
echo ""

# Função para verificar última execução
check_log() {
    local log_file=$1
    local name=$2
    local max_hours=$3
    
    if [ ! -f "$log_file" ]; then
        echo -e "   ${RED}❌ Nunca executado${NC}"
        return
    fi
    
    # Última modificação
    local last_mod=$(stat -c %Y "$log_file" 2>/dev/null || stat -f %m "$log_file" 2>/dev/null)
    local now=$(date +%s)
    local diff=$((now - last_mod))
    local hours=$((diff / 3600))
    local minutes=$(((diff % 3600) / 60))
    
    # Status baseado no tempo
    if [ $hours -gt $max_hours ]; then
        echo -e "   ${RED}⚠️  Última execução há ${hours}h ${minutes}min${NC}"
    elif [ $hours -gt 0 ]; then
        echo -e "   ${YELLOW}⏰ Última execução há ${hours}h ${minutes}min${NC}"
    else
        echo -e "   ${GREEN}✅ Última execução há ${minutes}min${NC}"
    fi
    
    # Tamanho do log
    local size=$(du -h "$log_file" 2>/dev/null | cut -f1)
    echo -e "   📦 Tamanho do log: $size"
    
    # Últimas 3 linhas
    echo -e "   ${BLUE}📄 Últimas linhas:${NC}"
    tail -n 3 "$log_file" 2>/dev/null | while IFS= read -r line; do
        echo "      $line"
    done
}

# Verificar cada CRON
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}1. 🏦 SINCRONIZAÇÃO BANCÁRIA${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
check_log "$LOG_DIR/cron_sync_bancaria.log" "Sincronização Bancária" 2
echo ""

echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}2. 🔄 INTEGRAÇÕES${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
check_log "$LOG_DIR/cron_integracoes.log" "Integrações" 1
echo ""

echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}3. 📧 LEMBRETES DE VENCIMENTO${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
check_log "$LOG_DIR/cron_lembretes.log" "Lembretes" 25
echo ""

echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}4. 💾 BACKUP DO BANCO${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
check_log "$LOG_DIR/cron_backup.log" "Backup" 25
echo ""

echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}5. 🧹 LIMPEZA DO SISTEMA${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
check_log "$LOG_DIR/cron_limpeza.log" "Limpeza" 25
echo ""

# Verificar crontab
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}📋 CRONTAB ATIVO:${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
if crontab -l > /dev/null 2>&1; then
    cron_count=$(crontab -l | grep -v "^#" | grep -v "^$" | wc -l)
    echo -e "   ${GREEN}✅ $cron_count CRON(s) configurado(s)${NC}"
    echo ""
    crontab -l | grep "financeiro" | grep -v "^#" | while IFS= read -r line; do
        echo "   $line"
    done
else
    echo -e "   ${RED}❌ Nenhum crontab configurado${NC}"
fi
echo ""

# Estatísticas gerais
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}📊 ESTATÍSTICAS:${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"

# Total de logs
total_logs=$(find "$LOG_DIR" -name "cron_*.log" 2>/dev/null | wc -l)
echo -e "   Total de arquivos de log: $total_logs"

# Tamanho total
if [ -d "$LOG_DIR" ]; then
    total_size=$(du -sh "$LOG_DIR" 2>/dev/null | cut -f1)
    echo -e "   Tamanho total dos logs: $total_size"
fi

# Backups
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_DIR/backups"

if [ -d "$BACKUP_DIR" ]; then
    backup_count=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" 2>/dev/null | wc -l)
    if [ $backup_count -gt 0 ]; then
        backup_size=$(du -sh "$BACKUP_DIR" 2>/dev/null | cut -f1)
        last_backup=$(ls -t "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | head -1)
        last_backup_date=$(stat -c %y "$last_backup" 2>/dev/null | cut -d'.' -f1 || stat -f "%Sm" "$last_backup" 2>/dev/null)
        echo -e "   Backups disponíveis: $backup_count"
        echo -e "   Tamanho dos backups: $backup_size"
        echo -e "   Último backup: $last_backup_date"
    else
        echo -e "   ${YELLOW}⚠️  Nenhum backup encontrado${NC}"
    fi
fi

echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ Monitoramento concluído!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "💡 ${BLUE}Dicas:${NC}"
echo -e "   - Use ${YELLOW}tail -f $LOG_DIR/cron_[nome].log${NC} para acompanhar em tempo real"
echo -e "   - Execute ${YELLOW}bash $0${NC} para atualizar este monitor"
echo -e "   - Logs são limpos automaticamente após 90 dias"
echo ""
