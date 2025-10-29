# ==========================================
# SOLUÇÃO EMERGENCIAL - View [login] not found
# ==========================================
# Execute este script NO SERVIDOR DE PRODUÇÃO (Windows)
# .\fix-view-error.ps1
# ==========================================

Write-Host "🚨 Corrigindo erro: View [login] not found" -ForegroundColor Red
Write-Host ""

# 1. Limpar cache de views (PRINCIPAL)
Write-Host "1️⃣ Limpando cache de views..." -ForegroundColor Yellow
php artisan view:clear
Write-Host "✅ Cache de views limpo" -ForegroundColor Green
Write-Host ""

# 2. Limpar cache de configuração
Write-Host "2️⃣ Limpando cache de configuração..." -ForegroundColor Yellow
php artisan config:clear
Write-Host "✅ Cache de configuração limpo" -ForegroundColor Green
Write-Host ""

# 3. Limpar cache geral
Write-Host "3️⃣ Limpando cache da aplicação..." -ForegroundColor Yellow
php artisan cache:clear
Write-Host "✅ Cache da aplicação limpo" -ForegroundColor Green
Write-Host ""

# 4. Limpar rotas
Write-Host "4️⃣ Limpando cache de rotas..." -ForegroundColor Yellow
php artisan route:clear
Write-Host "✅ Cache de rotas limpo" -ForegroundColor Green
Write-Host ""

# 5. Recriar cache de views
Write-Host "5️⃣ Recriando cache de views..." -ForegroundColor Yellow
php artisan view:cache
Write-Host "✅ Cache de views recriado" -ForegroundColor Green
Write-Host ""

# 6. Otimizar autoloader
Write-Host "6️⃣ Otimizando autoloader..." -ForegroundColor Yellow
composer dump-autoload --optimize --no-dev
Write-Host "✅ Autoloader otimizado" -ForegroundColor Green
Write-Host ""

Write-Host "✨ PRONTO! O erro deve estar corrigido!" -ForegroundColor Cyan
Write-Host "🔄 Teste acessando a página de login novamente" -ForegroundColor Cyan
Write-Host ""
