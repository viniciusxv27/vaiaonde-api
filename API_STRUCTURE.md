# 🗺️ Mapa Visual da API - VaiAonde

```
📱 VaiAonde API
│
├── 🔐 AUTENTICAÇÃO (Público)
│   ├── POST /auth/register ..................... Registrar
│   ├── POST /auth/login ........................ Login (retorna JWT)
│   ├── POST /auth/recover-password ............. Recuperar senha
│   └── POST /auth/reset-password ............... Resetar senha
│
├── 🏠 DESTAQUES & BANNERS (Público)
│   ├── GET /banners ............................ Lista banners
│   ├── GET /highlights ......................... Estabelecimentos top
│   └── GET /featured ........................... Estabelecimentos featured
│
├── 📍 ESTABELECIMENTOS
│   │
│   ├── 👁️ Público (sem auth)
│   │   ├── GET /places ......................... Lista todos
│   │   └── GET /places/{id} .................... Detalhes
│   │
│   └── 🔒 Protegido (com JWT)
│       ├── GET /places/category/{id} ........... Por categoria
│       ├── GET /places/city/{id} ............... Por cidade
│       └── POST /places/{id}/rate .............. Avaliar
│
├── ⭐ FAVORITOS (Protegido)
│   ├── GET /favorites .......................... Meus favoritos
│   ├── POST /favorites/{id} .................... Adicionar
│   └── DELETE /favorites/{id} .................. Remover
│
├── 🎥 FEED DE VÍDEOS (TikTok-style)
│   │
│   ├── 👁️ Público
│   │   ├── GET /videos/feed .................... Feed principal
│   │   └── GET /videos/influencer/{id} ......... Vídeos do influencer
│   │
│   └── 🔒 Protegido
│       ├── POST /videos/{id}/view .............. Registrar view
│       ├── POST /videos/{id}/like .............. Like/Unlike
│       ├── POST /videos/{id}/share ............. Compartilhar
│       ├── POST /videos/upload ................. Upload (influencer)
│       ├── GET /videos/my-videos ............... Meus vídeos
│       └── DELETE /videos/{id} ................. Deletar vídeo
│
├── 🎭 INFLUENCIADORES
│   │
│   ├── 👁️ Público
│   │   ├── GET /influencers .................... Lista
│   │   ├── GET /influencers/top ................ Ranking
│   │   ├── GET /influencers/category/{id} ...... Por categoria
│   │   └── GET /influencers/{id} ............... Perfil
│   │
│   └── 🔒 Protegido
│       └── POST /influencers/{id}/contact ...... Iniciar contato
│
├── 🎪 CLUBE DE BENEFÍCIOS
│   │
│   ├── 👁️ Público
│   │   ├── GET /club/info ...................... Informações
│   │   └── GET /club/benefits .................. Benefícios
│   │
│   └── 🔒 Protegido
│       ├── POST /club/subscribe ................ Assinar
│       └── POST /club/cancel ................... Cancelar
│
├── 🎡 ROLETA (Gamificação)
│   │
│   ├── 👁️ Público
│   │   └── GET /roulette/prizes ................ Prêmios
│   │
│   └── 🔒 Protegido
│       ├── POST /roulette/spin ................. Girar
│       ├── POST /roulette/daily-spin ........... Girada grátis
│       ├── GET /roulette/history ............... Histórico
│       └── POST /roulette/plays/{id}/claim ..... Resgatar
│
├── 🎟️ CUPONS (Protegido)
│   ├── GET /vouchers/{id} ...................... Lista cupons
│   └── POST /vouchers/{id} ..................... Usar cupom
│
├── 📝 PROPOSTAS (Protegido)
│   ├── 💼 Influenciador
│   │   ├── POST /proposals ..................... Criar proposta
│   │   ├── GET /proposals/my-proposals ......... Minhas propostas
│   │   └── POST /proposals/{id}/complete ....... Marcar concluída
│   │
│   └── 🏢 Proprietário
│       ├── GET /proposals/place/{placeId} ...... Propostas recebidas
│       ├── POST /proposals/{id}/accept ......... Aceitar
│       └── POST /proposals/{id}/reject ......... Rejeitar
│
├── 💬 CHAT (Protegido)
│   ├── GET /chats .............................. Lista conversas
│   ├── POST /chats ............................. Criar conversa
│   ├── GET /chats/{id}/messages ................ Ver mensagens
│   ├── POST /chats/{id}/send ................... Enviar mensagem
│   └── POST /chats/{id}/mark-read .............. Marcar como lida
│
├── 💰 CARTEIRA (Protegido)
│   ├── 📊 Consulta
│   │   ├── GET /wallet/balance ................. Ver saldo
│   │   └── GET /wallet/transactions ............ Histórico
│   │
│   ├── 💳 Depósitos
│   │   ├── POST /wallet/deposit/card ........... Via cartão
│   │   ├── POST /wallet/deposit/pix ............ Gerar QR PIX
│   │   └── POST /wallet/deposit/pix/{id}/confirm Confirmar PIX
│   │
│   └── 💸 Saques
│       ├── POST /wallet/withdraw ............... Solicitar saque
│       └── PUT /wallet/pix-key ................. Atualizar chave PIX
│
├── 👤 PERFIL (Protegido)
│   ├── GET /user/profile ....................... Ver perfil
│   ├── PUT /user/profile ....................... Atualizar
│   ├── DELETE /user/profile .................... Deletar conta
│   └── POST /user/logout ....................... Logout
│
├── 📂 RECURSOS AUXILIARES (Público)
│   ├── GET /categories ......................... Categorias
│   ├── GET /cities ............................. Cidades
│   ├── GET /subscription/plans ................. Planos
│   └── GET /subscription/plans/{slug} .......... Detalhes do plano
│
└── 🔗 WEBHOOKS (Sem auth)
    └── POST /webhook/abacatepay ................ Webhook pagamentos

```

## 📊 Estatísticas da API

- **Total de Endpoints:** ~70+
- **Endpoints Públicos:** ~20
- **Endpoints Protegidos (JWT):** ~50
- **Controllers:** 17

## 🎯 Fluxo Principal do Usuário

```
1. 📱 Abertura do App
   └─> GET /videos/feed (Feed de vídeos)
   └─> GET /banners (Banners promocionais)
   └─> GET /highlights (Estabelecimentos em destaque)

2. 🔐 Autenticação
   └─> POST /auth/register ou /auth/login
   └─> Recebe JWT token
   └─> Armazena token localmente

3. 🎥 Interação com Vídeos
   └─> POST /videos/{id}/view (auto ao visualizar)
   └─> POST /videos/{id}/like (ao curtir)
   └─> POST /videos/{id}/share (ao compartilhar)

4. 📍 Explorar Estabelecimentos
   └─> GET /places (ou /places/category/{id})
   └─> GET /places/{id} (detalhes)
   └─> POST /favorites/{id} (favoritar)
   └─> POST /places/{id}/rate (avaliar)

5. 🎡 Gamificação (Roleta)
   └─> POST /roulette/daily-spin (girada grátis diária)
   └─> POST /roulette/spin (girada paga)
   └─> POST /roulette/plays/{id}/claim (resgatar prêmio)

6. 🎪 Clube de Benefícios
   └─> GET /club/info
   └─> GET /club/benefits
   └─> POST /club/subscribe (assinatura)

7. 💰 Carteira Digital
   └─> GET /wallet/balance
   └─> POST /wallet/deposit/pix (depositar)
   └─> POST /wallet/withdraw (sacar)

8. 💬 Chat & Propostas
   └─> GET /chats (conversas)
   └─> POST /proposals (criar proposta)
   └─> GET /proposals/my-proposals (acompanhar)
```

## 🏗️ Arquitetura

```
App Mobile
    ↓
JWT Auth Middleware
    ↓
Laravel Routes (api.php)
    ↓
Controllers (17 controllers)
    ↓
Models & Database
    ↓
Services (AbacatePay, Kirvano)
    ↓
Storage (Cloudflare R2 / Local)
```

## 🎨 Identidade Visual

- **Cor Primária:** `#FEB800` (Amarelo VaiAonde)
- **Cor Secundária:** `#000000` (Preto)
- **Cor de Texto:** `#ffffff` (Branco sobre fundos escuros)

## 🔧 Tecnologias

- **Backend:** Laravel 11
- **Auth:** JWT (tymon/jwt-auth)
- **Database:** MySQL
- **Storage:** Cloudflare R2 + Local Fallback
- **Payments:** AbacatePay + Stripe
- **Hosting:** Hostinger

---

**VaiAonde Capixaba** - Conectando pessoas e lugares 🚀
