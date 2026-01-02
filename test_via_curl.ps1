# Teste via PowerShell - simula navegador enviando formulário

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "TESTE VIA POWERSHELL (simula navegador)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configurar URL (ajuste se necessário)
$URL = "http://localhost/configuracoes/salvar"

Write-Host "1. Estado atual no banco:" -ForegroundColor Yellow
php check_configuracoes.php | Select-String -Pattern "CATEGORIAS" -Context 0,5
Write-Host ""

Write-Host "2. Enviando POST (marcando codigo_auto_gerado)..." -ForegroundColor Yellow
$body = @{
    grupo = "categorias"
    "categorias.codigo_auto_gerado" = "true"
}

try {
    $response = Invoke-WebRequest -Uri $URL -Method POST -Body $body -UseBasicParsing
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Erro: $_" -ForegroundColor Red
}
Write-Host ""

Write-Host "3. Aguardando 1 segundo..." -ForegroundColor Yellow
Start-Sleep -Seconds 1
Write-Host ""

Write-Host "4. Verificando no banco:" -ForegroundColor Yellow
php check_configuracoes.php | Select-String -Pattern "CATEGORIAS" -Context 0,5
Write-Host ""

Write-Host "✅ Teste concluído!" -ForegroundColor Green
