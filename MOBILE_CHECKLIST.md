# ✅ Checklist de Implementação - VaiAonde Mobile App

## 📱 Configuração Inicial

- [ ] Configurar URL base da API: `https://vaiaondecapixaba.com.br/api`
- [ ] Implementar gerenciador de requisições HTTP (Axios, Fetch, etc)
- [ ] Configurar interceptor para adicionar JWT token automaticamente
- [ ] Implementar storage local para JWT token
- [ ] Configurar timeout de requisições (30 segundos recomendado)
- [ ] Implementar tratamento de erros global

---

## 🔐 Autenticação

### Tela de Login
- [ ] Campo: Email
- [ ] Campo: Senha
- [ ] Botão: Entrar
- [ ] Link: Esqueci minha senha
- [ ] Link: Criar conta
- [ ] Integração: `POST /api/auth/login`
- [ ] Salvar JWT token no storage
- [ ] Redirecionar para home após login

### Tela de Registro
- [ ] Campo: Nome completo
- [ ] Campo: Email
- [ ] Campo: Telefone
- [ ] Campo: Senha
- [ ] Campo: Confirmar senha
- [ ] Botão: Cadastrar
- [ ] Integração: `POST /api/auth/register`
- [ ] Salvar JWT token no storage
- [ ] Redirecionar para home após registro

### Recuperação de Senha
- [ ] Campo: Email
- [ ] Botão: Enviar link de recuperação
- [ ] Integração: `POST /api/auth/recover-password`
- [ ] Tela de confirmação/sucesso

---

## 🏠 Home / Feed Principal

### Componentes
- [ ] Carrossel de banners (topo)
  - Integração: `GET /api/banners`
- [ ] Seção "Em Destaque"
  - Integração: `GET /api/highlights`
- [ ] Feed de vídeos (scroll infinito, estilo TikTok)
  - Integração: `GET /api/videos/feed`
  - Implementar paginação
  - Auto-play quando vídeo entra na tela
  - Pause quando sai da tela

### Interações no Feed
- [ ] Botão: Like/Unlike
  - Integração: `POST /api/videos/{id}/like`
- [ ] Contador de views
  - Integração: `POST /api/videos/{id}/view` (automático)
- [ ] Botão: Compartilhar
  - Integração: `POST /api/videos/{id}/share`
- [ ] Botão: Ver perfil do influenciador
- [ ] Botão: Ver estabelecimento

---

## 📍 Estabelecimentos

### Tela de Listagem
- [ ] Lista de estabelecimentos (cards)
  - Integração: `GET /api/places`
- [ ] Filtro por categoria
  - Integração: `GET /api/categories`
  - Integração: `GET /api/places/category/{id}`
- [ ] Filtro por cidade
  - Integração: `GET /api/cities`
  - Integração: `GET /api/places/city/{id}`
- [ ] Busca por nome
- [ ] Implementar paginação (20 por página)

### Card do Estabelecimento
- [ ] Foto principal (card_image)
- [ ] Logo
- [ ] Nome
- [ ] Categoria
- [ ] Avaliação (estrelas)
- [ ] Distância (se geolocalização ativada)
- [ ] Botão: Favoritar

### Tela de Detalhes
- [ ] Galeria de fotos
- [ ] Logo
- [ ] Nome
- [ ] Descrição completa
- [ ] Avaliação média
- [ ] Botão: Instagram
  - Abrir URL: `instagram_url`
- [ ] Botão: Localização (Google Maps)
  - Abrir URL: `location_url`
- [ ] Botão: Chamar Uber
  - Abrir URL: `uber_url`
- [ ] Botão: Favoritar/Desfavoritar
  - Integração: `POST /api/favorites/{id}`
  - Integração: `DELETE /api/favorites/{id}`
- [ ] Seção de avaliações
- [ ] Formulário de avaliação
  - Integração: `POST /api/places/{id}/rate`
  - Campo: Nota (1-5 estrelas)
  - Campo: Comentário

---

## ⭐ Favoritos

### Tela de Favoritos
- [ ] Lista de estabelecimentos favoritos
  - Integração: `GET /api/favorites`
- [ ] Botão: Remover favorito
  - Integração: `DELETE /api/favorites/{id}`
- [ ] Estado vazio: "Você ainda não tem favoritos"

---

## 🎭 Influenciadores

### Tela de Listagem
- [ ] Lista de influenciadores (cards)
  - Integração: `GET /api/influencers`
- [ ] Filtro por categoria
  - Integração: `GET /api/influencers/category/{id}`
- [ ] Seção "Top Influencers"
  - Integração: `GET /api/influencers/top`

### Card do Influenciador
- [ ] Avatar
- [ ] Nome
- [ ] Categoria
- [ ] Número de seguidores
- [ ] Número de vídeos
- [ ] Avaliação

### Perfil do Influenciador
- [ ] Avatar grande
- [ ] Nome
- [ ] Bio
- [ ] Estatísticas (seguidores, vídeos, avaliação)
- [ ] Grid de vídeos
  - Integração: `GET /api/videos/influencer/{id}`
- [ ] Botão: Contatar
  - Integração: `POST /api/influencers/{id}/contact`

---

## 🎪 Clube de Benefícios

### Tela Principal
- [ ] Informações do clube
  - Integração: `GET /api/club/info`
- [ ] Lista de benefícios
  - Integração: `GET /api/club/benefits`
- [ ] Planos disponíveis
  - Integração: `GET /api/subscription/plans`

### Detalhes do Plano
- [ ] Nome do plano
- [ ] Preço
- [ ] Descrição completa
- [ ] Lista de benefícios inclusos
- [ ] Botão: Assinar
  - Integração: `POST /api/club/subscribe`
- [ ] Se já assinante: Botão: Cancelar
  - Integração: `POST /api/club/cancel`

---

## 🎡 Roleta (Gamificação)

### Tela da Roleta
- [ ] Animação da roleta girando
- [ ] Exibir prêmios disponíveis
  - Integração: `GET /api/roulette/prizes`
- [ ] Saldo de moedas/créditos do usuário
- [ ] Botão: Girar (pago)
  - Integração: `POST /api/roulette/spin`
- [ ] Botão: Girada grátis (se disponível)
  - Integração: `POST /api/roulette/daily-spin`
- [ ] Modal de prêmio ganho
- [ ] Botão: Resgatar prêmio
  - Integração: `POST /api/roulette/plays/{id}/claim`

### Histórico
- [ ] Lista de jogadas anteriores
  - Integração: `GET /api/roulette/history`
- [ ] Status: Resgatado / Pendente

---

## 🎟️ Cupons

### Tela de Cupons
- [ ] Lista de cupons disponíveis
  - Integração: `GET /api/vouchers/{id}`
- [ ] Card do cupom:
  - Nome do estabelecimento
  - Desconto/oferta
  - Validade
  - Código (QR ou texto)
- [ ] Botão: Usar cupom
  - Integração: `POST /api/vouchers/{id}`
- [ ] Modal de confirmação de uso

---

## 💬 Chat

### Listagem de Conversas
- [ ] Lista de conversas ativas
  - Integração: `GET /api/chats`
- [ ] Card da conversa:
  - Avatar do contato
  - Nome
  - Última mensagem
  - Badge de mensagens não lidas
  - Timestamp

### Tela de Conversa
- [ ] Histórico de mensagens
  - Integração: `GET /api/chats/{id}/messages`
- [ ] Campo de texto para nova mensagem
- [ ] Botão: Enviar
  - Integração: `POST /api/chats/{id}/send`
- [ ] Auto-scroll para mensagem mais recente
- [ ] Marcar como lida ao abrir
  - Integração: `POST /api/chats/{id}/mark-read`
- [ ] Implementar polling ou WebSocket para mensagens em tempo real

---

## 💰 Carteira Digital

### Tela Principal
- [ ] Exibir saldo atual
  - Integração: `GET /api/wallet/balance`
- [ ] Botão: Depositar
- [ ] Botão: Sacar
- [ ] Histórico de transações
  - Integração: `GET /api/wallet/transactions`

### Depósito via PIX
- [ ] Campo: Valor a depositar
- [ ] Botão: Gerar QR Code
  - Integração: `POST /api/wallet/deposit/pix`
- [ ] Exibir QR Code gerado
- [ ] Botão: Copiar código PIX
- [ ] Timer de expiração
- [ ] Polling para verificar pagamento
  - Integração: `POST /api/wallet/deposit/pix/{id}/confirm`

### Depósito via Cartão
- [ ] Campos do cartão:
  - Número
  - Nome
  - Validade
  - CVV
- [ ] Campo: Valor
- [ ] Botão: Depositar
  - Integração: `POST /api/wallet/deposit/card`

### Saque
- [ ] Campo: Valor a sacar
- [ ] Campo: Chave PIX (se não cadastrada)
  - Integração: `PUT /api/wallet/pix-key`
- [ ] Botão: Solicitar saque
  - Integração: `POST /api/wallet/withdraw`
- [ ] Confirmação/feedback

---

## 📝 Propostas (Para Influenciadores)

### Criar Proposta
- [ ] Seletor: Estabelecimento
- [ ] Campo: Descrição da proposta
- [ ] Campo: Valor solicitado
- [ ] Campo: Prazo de entrega
- [ ] Botão: Enviar proposta
  - Integração: `POST /api/proposals`

### Minhas Propostas
- [ ] Lista de propostas enviadas
  - Integração: `GET /api/proposals/my-proposals`
- [ ] Card da proposta:
  - Estabelecimento
  - Status (Pendente/Aceita/Rejeitada)
  - Valor
  - Botão: Marcar como concluída (se aceita)
    - Integração: `POST /api/proposals/{id}/complete`

---

## 📝 Propostas (Para Proprietários)

### Propostas Recebidas
- [ ] Lista de propostas recebidas
  - Integração: `GET /api/proposals/place/{placeId}`
- [ ] Card da proposta:
  - Influenciador
  - Descrição
  - Valor
  - Status
  - Botão: Aceitar
    - Integração: `POST /api/proposals/{id}/accept`
  - Botão: Rejeitar
    - Integração: `POST /api/proposals/{id}/reject`

---

## 👤 Perfil do Usuário

### Tela de Perfil
- [ ] Avatar (com opção de alterar)
- [ ] Nome
- [ ] Email
- [ ] Telefone
- [ ] Botão: Editar perfil
- [ ] Botão: Configurações
- [ ] Botão: Sair
  - Integração: `POST /api/user/logout`
  - Limpar JWT token do storage

### Editar Perfil
- [ ] Integração: `GET /api/user/profile` (carregar dados)
- [ ] Campo: Nome
- [ ] Campo: Telefone
- [ ] Campo: Bio (se influenciador)
- [ ] Upload de foto
- [ ] Botão: Salvar
  - Integração: `PUT /api/user/profile`

### Configurações
- [ ] Toggle: Notificações push
- [ ] Toggle: Notificações de email
- [ ] Seletor: Idioma
- [ ] Botão: Alterar senha
- [ ] Botão: Deletar conta
  - Integração: `DELETE /api/user/profile`
  - Confirmação com modal

---

## 🔔 Notificações Push

### Configuração
- [ ] Solicitar permissão de notificações
- [ ] Enviar token FCM/APNs para backend
- [ ] Listener para notificações recebidas

### Tipos de Notificações
- [ ] Nova mensagem no chat
- [ ] Proposta aceita/rejeitada
- [ ] Prêmio da roleta disponível
- [ ] Novo vídeo de influenciador seguido
- [ ] Cupom prestes a expirar

---

## 🎨 Design & UX

### Identidade Visual
- [ ] Aplicar cor primária: `#FEB800` (amarelo)
- [ ] Aplicar cor secundária: `#000000` (preto)
- [ ] Aplicar cor de texto: `#ffffff` (branco em fundos escuros)
- [ ] Criar tema dark/light (opcional)

### Componentes Reutilizáveis
- [ ] Card de estabelecimento
- [ ] Card de vídeo
- [ ] Card de influenciador
- [ ] Botão primário
- [ ] Botão secundário
- [ ] Input de texto
- [ ] Modal
- [ ] Loading spinner
- [ ] Placeholder para imagens
- [ ] Toast de sucesso/erro

---

## 🚀 Performance & Otimizações

- [ ] Implementar cache de imagens
- [ ] Implementar lazy loading de listas
- [ ] Implementar pull-to-refresh
- [ ] Otimizar vídeos para mobile (qualidade adaptativa)
- [ ] Implementar offline mode (básico)
- [ ] Comprimir uploads antes de enviar

---

## 🧪 Testes

- [ ] Testar fluxo completo de login/registro
- [ ] Testar todas as requisições autenticadas
- [ ] Testar renovação de token expirado
- [ ] Testar upload de vídeos
- [ ] Testar pagamentos (sandbox)
- [ ] Testar em dispositivos de baixa performance
- [ ] Testar em conexões lentas
- [ ] Testar modo offline

---

## 📦 Deploy

- [ ] Configurar ambiente de produção
- [ ] Configurar ambiente de staging/teste
- [ ] Implementar analytics (Google Analytics, Firebase)
- [ ] Implementar crash reporting (Sentry, Firebase Crashlytics)
- [ ] Configurar deep links
- [ ] Publicar na App Store (iOS)
- [ ] Publicar na Play Store (Android)

---

## 📞 Contatos do Backend

**Base URL:** `https://vaiaondecapixaba.com.br/api`  
**Documentação:** Ver arquivos `API_DOCUMENTATION.md`, `API_STRUCTURE.md`, `API_EXAMPLES.md`

---

## 📊 Status do Projeto

**Total de Itens:** ~180  
**Prioridade Alta:** Autenticação, Feed, Estabelecimentos, Carteira  
**Prioridade Média:** Chat, Propostas, Roleta, Clube  
**Prioridade Baixa:** Configurações avançadas, Notificações

---

**Boa sorte no desenvolvimento! 🚀**  
*VaiAonde Capixaba - Conectando pessoas e lugares*
