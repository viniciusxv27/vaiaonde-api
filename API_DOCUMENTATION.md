# 📱 VaiAonde API - Documentação Completa

**Base URL:** `https://vaiaondecapixaba.com.br/api`  
**Versão:** 1.0  
**Autenticação:** JWT Bearer Token

---

## 🔐 Autenticação

Todas as rotas protegidas requerem um token JWT no header:

```
Authorization: Bearer {seu_token_aqui}
```

### Endpoints Públicos (sem autenticação)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/auth/register` | Registrar novo usuário |
| POST | `/auth/login` | Login (retorna token JWT) |
| POST | `/auth/recover-password` | Recuperação de senha |
| POST | `/auth/reset-password` | Reset de senha |

---

## 🏠 Destaques & Banners

### Banners
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/banners` | ❌ | Lista todos os banners ativos |

### Destaques
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/highlights` | ❌ | Estabelecimentos em destaque |
| GET | `/featured` | ❌ | Estabelecimentos featured |

---

## 📍 Estabelecimentos (Places)

### Rotas Públicas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/places` | ❌ | Lista todos os estabelecimentos |
| GET | `/places/{id}` | ❌ | Detalhes de um estabelecimento |

### Rotas Autenticadas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/places/category/{id}` | ✅ | Estabelecimentos por categoria |
| GET | `/places/city/{id}` | ✅ | Estabelecimentos por cidade |
| POST | `/places/{id}/rate` | ✅ | Avaliar estabelecimento |

---

## ⭐ Favoritos

| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/favorites` | ✅ | Listar meus favoritos |
| POST | `/favorites/{id}` | ✅ | Adicionar aos favoritos |
| DELETE | `/favorites/{id}` | ✅ | Remover dos favoritos |

---

## 🎥 Feed de Vídeos (TikTok-style)

### Rotas Públicas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/videos/feed` | ❌ | Feed principal de vídeos |
| GET | `/videos/influencer/{id}` | ❌ | Vídeos de um influenciador específico |

### Rotas Autenticadas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/videos/{id}/view` | ✅ | Registrar visualização |
| POST | `/videos/{id}/like` | ✅ | Like/Unlike no vídeo |
| POST | `/videos/{id}/share` | ✅ | Compartilhar vídeo |
| POST | `/videos/upload` | ✅ | Upload de vídeo (influenciador) |
| GET | `/videos/my-videos` | ✅ | Meus vídeos enviados |
| DELETE | `/videos/{id}` | ✅ | Deletar meu vídeo |

---

## 🎭 Influenciadores

### Rotas Públicas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/influencers` | ❌ | Listar influenciadores |
| GET | `/influencers/top` | ❌ | Ranking de influenciadores |
| GET | `/influencers/category/{id}` | ❌ | Influenciadores por categoria |
| GET | `/influencers/{id}` | ❌ | Perfil do influenciador |

### Rotas Autenticadas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/influencers/{id}/contact` | ✅ | Iniciar contato/chat |

---

## 🎪 Clube de Benefícios

### Rotas Públicas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/club/info` | ❌ | Informações do clube |
| GET | `/club/benefits` | ❌ | Lista de benefícios |

### Rotas Autenticadas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/club/subscribe` | ✅ | Assinar plano do clube |
| POST | `/club/cancel` | ✅ | Cancelar assinatura |

---

## 🎡 Roleta

### Rotas Públicas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/roulette/prizes` | ❌ | Prêmios disponíveis |

### Rotas Autenticadas
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/roulette/spin` | ✅ | Girar roleta |
| POST | `/roulette/daily-spin` | ✅ | Girada diária grátis |
| GET | `/roulette/history` | ✅ | Histórico de jogadas |
| POST | `/roulette/plays/{id}/claim` | ✅ | Resgatar prêmio |

---

## 🎟️ Cupons (Vouchers)

| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/vouchers/{id}` | ✅ | Listar cupons de um estabelecimento |
| POST | `/vouchers/{id}` | ✅ | Usar/resgatar cupom |

---

## 📝 Propostas (Influenciador ↔ Proprietário)

### Influenciador
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/proposals` | ✅ | Criar proposta para estabelecimento |
| GET | `/proposals/my-proposals` | ✅ | Minhas propostas enviadas |
| POST | `/proposals/{id}/complete` | ✅ | Marcar proposta como concluída |

### Proprietário
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/proposals/place/{placeId}` | ✅ | Propostas recebidas no meu estabelecimento |
| POST | `/proposals/{id}/accept` | ✅ | Aceitar proposta |
| POST | `/proposals/{id}/reject` | ✅ | Rejeitar proposta |

---

## 💬 Chat

| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/chats` | ✅ | Listar minhas conversas |
| POST | `/chats` | ✅ | Criar nova conversa |
| GET | `/chats/{id}/messages` | ✅ | Ver mensagens da conversa |
| POST | `/chats/{id}/send` | ✅ | Enviar mensagem |
| POST | `/chats/{id}/mark-read` | ✅ | Marcar como lida |

---

## 💰 Carteira (Depósitos & Saques)

### Consulta
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/wallet/balance` | ✅ | Ver saldo |
| GET | `/wallet/transactions` | ✅ | Histórico de transações |

### Depósitos
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/wallet/deposit/card` | ✅ | Depositar via cartão |
| POST | `/wallet/deposit/pix` | ✅ | Gerar QR Code PIX |
| POST | `/wallet/deposit/pix/{id}/confirm` | ✅ | Confirmar pagamento PIX |

### Saques
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/wallet/withdraw` | ✅ | Solicitar saque |
| PUT | `/wallet/pix-key` | ✅ | Atualizar chave PIX |

---

## 👤 Perfil do Usuário

| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/user/profile` | ✅ | Ver meu perfil |
| PUT | `/user/profile` | ✅ | Atualizar perfil |
| DELETE | `/user/profile` | ✅ | Deletar conta |
| POST | `/user/logout` | ✅ | Logout |

---

## 📂 Recursos Auxiliares

### Categorias e Cidades
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/categories` | ❌ | Lista de categorias |
| GET | `/cities` | ❌ | Lista de cidades |

### Planos de Assinatura
| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| GET | `/subscription/plans` | ❌ | Planos disponíveis |
| GET | `/subscription/plans/{slug}` | ❌ | Detalhes do plano |

---

## 🔗 Webhook

| Método | Endpoint | Auth | Descrição |
|--------|----------|------|-----------|
| POST | `/webhook/abacatepay` | ❌ | Webhook de pagamentos AbacatePay |

---

## 📊 Exemplo de Resposta

### Sucesso (200)
```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "Example"
  },
  "message": "Operação realizada com sucesso"
}
```

### Erro (400/401/404/500)
```json
{
  "success": false,
  "error": "Mensagem de erro",
  "code": "ERROR_CODE"
}
```

---

## 🚀 Fluxos Principais do App

### 1️⃣ Login & Registro
1. **POST** `/auth/register` → Criar conta
2. **POST** `/auth/login` → Receber token JWT
3. Guardar token para requisições autenticadas

### 2️⃣ Feed de Conteúdo
1. **GET** `/videos/feed` → Carregar vídeos
2. **GET** `/banners` → Banners do topo
3. **GET** `/highlights` → Estabelecimentos em destaque

### 3️⃣ Explorar Estabelecimentos
1. **GET** `/places` → Lista de places
2. **GET** `/categories` → Filtrar por categoria
3. **GET** `/cities` → Filtrar por cidade
4. **POST** `/favorites/{id}` → Adicionar favorito
5. **POST** `/places/{id}/rate` → Avaliar

### 4️⃣ Interagir com Vídeos
1. **GET** `/videos/feed` → Carregar feed
2. **POST** `/videos/{id}/view` → Registrar view
3. **POST** `/videos/{id}/like` → Curtir
4. **POST** `/videos/{id}/share` → Compartilhar

### 5️⃣ Roleta (Gamificação)
1. **GET** `/roulette/prizes` → Ver prêmios
2. **POST** `/roulette/daily-spin` → Girada grátis
3. **POST** `/roulette/spin` → Girar (pago)
4. **POST** `/roulette/plays/{id}/claim` → Resgatar

### 6️⃣ Clube de Benefícios
1. **GET** `/club/info` → Informações
2. **GET** `/club/benefits` → Benefícios
3. **POST** `/club/subscribe` → Assinar

### 7️⃣ Carteira Digital
1. **GET** `/wallet/balance` → Ver saldo
2. **POST** `/wallet/deposit/pix` → Depositar
3. **POST** `/wallet/withdraw` → Sacar

---

## 🎨 Cores VaiAonde
- **Primária:** `#FEB800` (amarelo)
- **Secundária:** `#000000` (preto)
- **Texto:** `#ffffff` (branco sobre preto)

---

## 📝 Notas Importantes

1. **JWT Token:** Todos os endpoints autenticados precisam do header `Authorization: Bearer {token}`
2. **Rate Limiting:** Implementar throttle no app para evitar spam
3. **Paginação:** Endpoints de lista suportam `?page=1&per_page=20`
4. **Upload de Vídeos:** Usar multipart/form-data com campo `video` e `thumbnail`
5. **Notificações Push:** Implementar para chat, propostas aceitas, prêmios ganhos

---

**Desenvolvido para VaiAonde Capixaba** 🇧🇷  
*Conectando influenciadores e estabelecimentos no Espírito Santo*
