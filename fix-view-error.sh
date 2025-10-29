#!/bin/bash

# ==========================================
# SOLUÇÃO EMERGENCIAL - View [login] not found
# ==========================================
# Execute este script NO SERVIDOR DE PRODUÇÃO
# chmod +x fix-view-error.sh
# ./fix-view-error.sh
# ==========================================

echo "🚨 Corrigindo erro: View [login] not found"
echo ""

# 1. Limpar cache de views (PRINCIPAL)
echo "1️⃣ Limpando cache de views..."
php artisan view:clear
echo "✅ Cache de views limpo"
echo ""

# 2. Limpar cache de configuração
echo "2️⃣ Limpando cache de configuração..."
php artisan config:clear
echo "✅ Cache de configuração limpo"
echo ""

# 3. Limpar cache geral
echo "3️⃣ Limpando cache da aplicação..."
php artisan cache:clear
echo "✅ Cache da aplicação limpo"
echo ""

# 4. Limpar rotas
echo "4️⃣ Limpando cache de rotas..."
php artisan route:clear
echo "✅ Cache de rotas limpo"
echo ""

# 5. Recriar cache de views
echo "5️⃣ Recriando cache de views..."
php artisan view:cache
echo "✅ Cache de views recriado"
echo ""

# 6. Otimizar autoloader
echo "6️⃣ Otimizando autoloader..."
composer dump-autoload --optimize --no-dev
echo "✅ Autoloader otimizado"
echo ""

echo "✨ PRONTO! O erro deve estar corrigido!"
echo "🔄 Teste acessando a página de login novamente"
echo ""
