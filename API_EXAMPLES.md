# 🧪 VaiAonde API - Exemplos de Requisições HTTP

## 🔐 Autenticação

### Registrar Novo Usuário
```http
POST https://vaiaondecapixaba.com.br/api/auth/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123",
  "phone": "27999999999",
  "type": "user"
}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "João Silva",
      "email": "joao@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  },
  "message": "Usuário registrado com sucesso"
}
```

---

### Login
```http
POST https://vaiaondecapixaba.com.br/api/auth/login
Content-Type: application/json

{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 123,
      "name": "João Silva",
      "email": "joao@example.com",
      "type": "user"
    }
  }
}
```

---

### Recuperar Senha
```http
POST https://vaiaondecapixaba.com.br/api/auth/recover-password
Content-Type: application/json

{
  "email": "joao@example.com"
}
```

---

## 🏠 Destaques & Banners

### Listar Banners
```http
GET https://vaiaondecapixaba.com.br/api/banners
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Promoção de Verão",
      "image_url": "https://vaiaondecapixaba.com.br/uploads/banners/banner1.jpg",
      "link": "https://example.com/promo",
      "active": true
    }
  ]
}
```

---

### Estabelecimentos em Destaque
```http
GET https://vaiaondecapixaba.com.br/api/highlights
```

---

## 📍 Estabelecimentos

### Listar Todos os Estabelecimentos
```http
GET https://vaiaondecapixaba.com.br/api/places?page=1&per_page=20
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "name": "Restaurante do Mar",
      "description": "Melhor frutos do mar da região",
      "logo": "https://vaiaondecapixaba.com.br/uploads/logo.jpg",
      "card_image": "https://vaiaondecapixaba.com.br/uploads/card.jpg",
      "rating": 4.5,
      "category": "Restaurante",
      "city": "Vitória",
      "instagram_url": "https://instagram.com/restaurante",
      "location_url": "https://maps.google.com/...",
      "uber_url": "https://uber.com/..."
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150
  }
}
```

---

### Detalhes de um Estabelecimento
```http
GET https://vaiaondecapixaba.com.br/api/places/45
```

---

### Estabelecimentos por Categoria (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/places/category/3
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Avaliar Estabelecimento (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/places/45/rate
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "rating": 5,
  "comment": "Excelente atendimento e comida deliciosa!"
}
```

---

## ⭐ Favoritos

### Listar Favoritos (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/favorites
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Adicionar aos Favoritos (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/favorites/45
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Remover dos Favoritos (Autenticado)
```http
DELETE https://vaiaondecapixaba.com.br/api/favorites/45
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 🎥 Feed de Vídeos

### Feed Principal (Público)
```http
GET https://vaiaondecapixaba.com.br/api/videos/feed?page=1
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "video_url": "https://r2.vaiaonde.com/videos/video1.mp4",
      "thumbnail_url": "https://r2.vaiaonde.com/thumbnails/thumb1.jpg",
      "title": "Conhecendo o Restaurante X",
      "description": "Melhor comida da cidade!",
      "views": 1523,
      "likes": 245,
      "influencer": {
        "id": 12,
        "name": "Maria Influencer",
        "avatar": "https://..."
      },
      "place": {
        "id": 45,
        "name": "Restaurante do Mar"
      }
    }
  ]
}
```

---

### Vídeos de um Influenciador (Público)
```http
GET https://vaiaondecapixaba.com.br/api/videos/influencer/12
```

---

### Registrar Visualização (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/videos/789/view
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Curtir Vídeo (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/videos/789/like
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Compartilhar Vídeo (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/videos/789/share
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "platform": "whatsapp"
}
```

---

### Upload de Vídeo (Influenciador Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/videos/upload
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: multipart/form-data

video: [arquivo_video.mp4]
thumbnail: [arquivo_thumb.jpg]
title: "Meu novo vídeo"
description: "Descrição do vídeo"
place_id: 45
```

---

## 🎭 Influenciadores

### Listar Influenciadores (Público)
```http
GET https://vaiaondecapixaba.com.br/api/influencers?page=1
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "name": "Maria Influencer",
      "bio": "Criadora de conteúdo gastronômico",
      "avatar": "https://...",
      "followers": 15420,
      "videos_count": 234,
      "rating": 4.8,
      "category": "Gastronomia"
    }
  ]
}
```

---

### Ranking de Influenciadores (Público)
```http
GET https://vaiaondecapixaba.com.br/api/influencers/top
```

---

### Perfil do Influenciador (Público)
```http
GET https://vaiaondecapixaba.com.br/api/influencers/12
```

---

### Contatar Influenciador (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/influencers/12/contact
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "message": "Olá, gostaria de fazer uma parceria!"
}
```

---

## 🎪 Clube de Benefícios

### Informações do Clube (Público)
```http
GET https://vaiaondecapixaba.com.br/api/club/info
```

---

### Benefícios Disponíveis (Público)
```http
GET https://vaiaondecapixaba.com.br/api/club/benefits
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "10% de desconto em restaurantes",
      "description": "Desconto válido em todos os restaurantes parceiros",
      "image": "https://..."
    }
  ]
}
```

---

### Assinar Clube (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/club/subscribe
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "plan_id": 1,
  "payment_method": "credit_card"
}
```

---

### Cancelar Assinatura (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/club/cancel
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 🎡 Roleta

### Prêmios Disponíveis (Público)
```http
GET https://vaiaondecapixaba.com.br/api/roulette/prizes
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "R$ 10 em créditos",
      "description": "Ganhe R$ 10 para usar no app",
      "image_url": "https://...",
      "probability": 15
    }
  ]
}
```

---

### Girar Roleta (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/roulette/spin
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "cost": 5
}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "prize": {
      "id": 1,
      "name": "R$ 10 em créditos",
      "image_url": "https://..."
    },
    "play_id": 456
  }
}
```

---

### Girada Diária Grátis (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/roulette/daily-spin
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Histórico de Jogadas (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/roulette/history
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Resgatar Prêmio (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/roulette/plays/456/claim
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 💬 Chat

### Listar Conversas (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/chats
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Ver Mensagens (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/chats/789/messages
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Enviar Mensagem (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/chats/789/send
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "message": "Olá, tudo bem?"
}
```

---

## 💰 Carteira

### Ver Saldo (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/wallet/balance
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "balance": 125.50,
    "currency": "BRL"
  }
}
```

---

### Histórico de Transações (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/wallet/transactions?page=1
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Depositar via PIX (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/wallet/deposit/pix
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "amount": 50.00
}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "transaction_id": 12345,
    "qr_code": "00020126580014br.gov.bcb.pix...",
    "qr_code_image": "data:image/png;base64,...",
    "amount": 50.00,
    "expires_at": "2024-01-15 18:30:00"
  }
}
```

---

### Solicitar Saque (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/wallet/withdraw
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "amount": 100.00,
  "pix_key": "27999999999"
}
```

---

## 👤 Perfil

### Ver Meu Perfil (Autenticado)
```http
GET https://vaiaondecapixaba.com.br/api/user/profile
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### Atualizar Perfil (Autenticado)
```http
PUT https://vaiaondecapixaba.com.br/api/user/profile
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "name": "João Silva Atualizado",
  "phone": "27999999999",
  "bio": "Minha nova bio"
}
```

---

### Logout (Autenticado)
```http
POST https://vaiaondecapixaba.com.br/api/user/logout
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 📂 Recursos Auxiliares

### Listar Categorias (Público)
```http
GET https://vaiaondecapixaba.com.br/api/categories
```

---

### Listar Cidades (Público)
```http
GET https://vaiaondecapixaba.com.br/api/cities
```

---

## ⚠️ Tratamento de Erros

### Erro de Autenticação (401)
```json
{
  "success": false,
  "error": "Token inválido ou expirado",
  "code": "UNAUTHORIZED"
}
```

### Erro de Validação (422)
```json
{
  "success": false,
  "error": "Dados inválidos",
  "errors": {
    "email": ["O campo email é obrigatório"],
    "password": ["A senha deve ter no mínimo 6 caracteres"]
  }
}
```

### Erro de Servidor (500)
```json
{
  "success": false,
  "error": "Erro interno do servidor",
  "code": "INTERNAL_ERROR"
}
```

---

## 🔧 Headers Comuns

### Requisições Autenticadas
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
Accept: application/json
```

### Upload de Arquivos
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: multipart/form-data
Accept: application/json
```

---

**Desenvolvido para VaiAonde Capixaba** 🚀  
Base URL: `https://vaiaondecapixaba.com.br/api`
