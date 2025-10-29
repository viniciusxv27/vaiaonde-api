# 🚀 Deploy para Produção - VaiAonde API

## ⚠️ ERRO: "View [login] not found"

Este erro ocorre quando os caches do Laravel estão desatualizados em produção.

### Solução Rápida

**No servidor de produção, execute:**

#### Linux/Unix:
```bash
chmod +x clear-cache-production.sh
./clear-cache-production.sh
```

#### Windows:
```powershell
.\clear-cache-production.ps1
```

#### Ou manualmente:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan clear-compiled

# Recriar caches otimizados
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📋 Checklist de Deploy

Sempre que fizer deploy em produção:

- [ ] 1. **Fazer backup do banco de dados**
  ```bash
  mysqldump -u usuario -p nome_banco > backup_$(date +%Y%m%d_%H%M%S).sql
  ```

- [ ] 2. **Atualizar código via Git**
  ```bash
  git pull origin main
  ```

- [ ] 3. **Instalar/Atualizar dependências**
  ```bash
  composer install --no-dev --optimize-autoloader
  ```

- [ ] 4. **Executar migrações**
  ```bash
  php artisan migrate --force
  ```

- [ ] 5. **Limpar e recriar caches**
  ```bash
  ./clear-cache-production.sh
  # ou
  .\clear-cache-production.ps1
  ```

- [ ] 6. **Verificar permissões**
  ```bash
  chmod -R 775 storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache
  ```

- [ ] 7. **Otimizar autoloader**
  ```bash
  composer dump-autoload --optimize
  ```

- [ ] 8. **Reiniciar serviços (se necessário)**
  ```bash
  sudo systemctl restart php8.2-fpm
  sudo systemctl restart nginx
  # ou
  sudo service apache2 restart
  ```

---

## 🔧 Variáveis de Ambiente (.env)

Certifique-se que o `.env` em produção tem:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Cache de configuração deve estar habilitado em produção
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

---

## 📁 Estrutura de Pastas que Precisam de Permissão

```
storage/
├── app/
├── framework/
│   ├── cache/
│   ├── sessions/
│   └── views/
└── logs/

public/
└── uploads/
    └── places/
        └── logos/

bootstrap/
└── cache/
```

**Permissões corretas:**
```bash
chmod -R 775 storage bootstrap/cache public/uploads
```

---

## 🐛 Problemas Comuns

### View not found
- **Causa:** Cache de views desatualizado
- **Solução:** `php artisan view:clear && php artisan view:cache`

### Route not found
- **Causa:** Cache de rotas desatualizado
- **Solução:** `php artisan route:clear && php artisan route:cache`

### Configuration cached
- **Causa:** Cache de configuração desatualizado
- **Solução:** `php artisan config:clear && php artisan config:cache`

### Permission denied
- **Causa:** Permissões incorretas
- **Solução:** `chmod -R 775 storage bootstrap/cache`

### 500 Internal Server Error
- **Causa:** Múltiplas possíveis
- **Solução:** 
  1. Verificar logs: `tail -f storage/logs/laravel.log`
  2. Ativar debug temporariamente: `APP_DEBUG=true`
  3. Verificar permissões
  4. Limpar todos os caches

---

## 📝 Logs

**Verificar erros em tempo real:**
```bash
tail -f storage/logs/laravel.log
```

**Últimas 100 linhas:**
```bash
tail -n 100 storage/logs/laravel.log
```

---

## 🔐 Segurança

Em produção, SEMPRE:
- ✅ `APP_DEBUG=false`
- ✅ `APP_ENV=production`
- ✅ Senha forte no `.env`
- ✅ HTTPS habilitado
- ✅ Permissões corretas (775 para storage, 644 para .env)
- ✅ `.env` fora do controle de versão

---

## 📞 Suporte

Em caso de dúvidas ou problemas, verifique:
1. Logs do Laravel: `storage/logs/laravel.log`
2. Logs do servidor web (nginx/apache)
3. Logs do PHP-FPM

---

**Última atualização:** 29/10/2025
