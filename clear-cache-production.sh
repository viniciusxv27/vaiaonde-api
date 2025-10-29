#!/bin/bash

# Script para limpar cache em produção
# Execute este script no servidor de produção

echo "🧹 Limpando caches do Laravel..."

# Limpar cache de configuração
php artisan config:clear
echo "✅ Cache de configuração limpo"

# Limpar cache de rotas
php artisan route:clear
echo "✅ Cache de rotas limpo"

# Limpar cache de views
php artisan view:clear
echo "✅ Cache de views limpo"

# Limpar cache da aplicação
php artisan cache:clear
echo "✅ Cache da aplicação limpo"

# Limpar cache compilado
php artisan clear-compiled
echo "✅ Cache compilado limpo"

# Recriar caches otimizados
echo ""
echo "🔨 Recriando caches otimizados..."

php artisan config:cache
echo "✅ Cache de configuração criado"

php artisan route:cache
echo "✅ Cache de rotas criado"

php artisan view:cache
echo "✅ Cache de views criado"

echo ""
echo "✨ Todos os caches foram limpos e otimizados!"
echo "🚀 A aplicação está pronta para produção!"
