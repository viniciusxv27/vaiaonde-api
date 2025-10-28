#!/bin/bash

echo "========================================="
echo "Configuração da API Vá!Aonde"
echo "========================================="
echo ""

# Verifica se o composer está instalado
if ! command -v composer &> /dev/null; then
    echo "❌ Composer não encontrado. Por favor, instale o Composer primeiro."
    exit 1
fi

# Verifica se o PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP não encontrado. Por favor, instale o PHP primeiro."
    exit 1
fi

echo "✅ Dependências encontradas"
echo ""

# Instala dependências do Composer
echo "📦 Instalando dependências do Composer..."
composer install

# Copia arquivo .env se não existir
if [ ! -f .env ]; then
    echo "📝 Criando arquivo .env..."
    cp .env.example .env
    echo "⚠️  Configure as variáveis de ambiente no arquivo .env"
fi

# Gera chave da aplicação
echo "🔑 Gerando chave da aplicação..."
php artisan key:generate

# Gera JWT secret
echo "🔑 Gerando JWT secret..."
php artisan jwt:secret

# Executa migrations
echo "🗄️  Executando migrations..."
php artisan migrate

# Executa seeders
echo "🌱 Executando seeders..."
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=ClubBenefitSeeder

echo ""
echo "========================================="
echo "✅ Configuração concluída!"
echo "========================================="
echo ""
echo "Usuário admin criado:"
echo "Email: vinicius8cm@gmail.com"
echo "Senha: senha123"
echo ""
echo "Para iniciar o servidor de desenvolvimento:"
echo "php artisan serve"
echo ""
echo "As rotas da API estarão disponíveis em:"
echo "http://localhost:8000/api"
echo ""
echo "Painel administrativo:"
echo "http://localhost:8000/api/admin/dashboard"
echo ""
