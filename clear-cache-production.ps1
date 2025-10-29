# Script PowerShell para limpar cache em produção
# Execute este script no servidor Windows

Write-Host "🧹 Limpando caches do Laravel..." -ForegroundColor Yellow
Write-Host ""

# Limpar cache de configuração
php artisan config:clear
Write-Host "✅ Cache de configuração limpo" -ForegroundColor Green

# Limpar cache de rotas
php artisan route:clear
Write-Host "✅ Cache de rotas limpo" -ForegroundColor Green

# Limpar cache de views
php artisan view:clear
Write-Host "✅ Cache de views limpo" -ForegroundColor Green

# Limpar cache da aplicação
php artisan cache:clear
Write-Host "✅ Cache da aplicação limpo" -ForegroundColor Green

# Limpar cache compilado
php artisan clear-compiled
Write-Host "✅ Cache compilado limpo" -ForegroundColor Green

Write-Host ""
Write-Host "🔨 Recriando caches otimizados..." -ForegroundColor Yellow

# Recriar caches otimizados
php artisan config:cache
Write-Host "✅ Cache de configuração criado" -ForegroundColor Green

php artisan route:cache
Write-Host "✅ Cache de rotas criado" -ForegroundColor Green

php artisan view:cache
Write-Host "✅ Cache de views criado" -ForegroundColor Green

Write-Host ""
Write-Host "✨ Todos os caches foram limpos e otimizados!" -ForegroundColor Cyan
Write-Host "🚀 A aplicação está pronta para produção!" -ForegroundColor Cyan
