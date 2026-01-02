#!/bin/bash
# Teste via CURL - simula navegador enviando formulário

echo "========================================"
echo "TESTE VIA CURL (simula navegador)"
echo "========================================"
echo ""

# Configurar URL (ajuste se necessário)
URL="http://localhost/configuracoes/salvar"

echo "1. Estado atual no banco:"
php check_configuracoes.php | grep -A 5 "CATEGORIAS"
echo ""

echo "2. Enviando POST via CURL (marcando codigo_auto_gerado)..."
curl -X POST "$URL" \
  -d "grupo=categorias" \
  -d "categorias.codigo_auto_gerado=true" \
  -L -s -o /dev/null -w "Status HTTP: %{http_code}\n"
echo ""

echo "3. Aguardando 1 segundo..."
sleep 1
echo ""

echo "4. Verificando no banco:"
php check_configuracoes.php | grep -A 5 "CATEGORIAS"
echo ""

echo "✅ Teste concluído!"
