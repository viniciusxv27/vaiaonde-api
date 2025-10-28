# 🔧 Fix: PDO MySQL Driver Error

## ❌ Erro
```
could not find driver
select * from `sessions` where `id` = ... limit 1
```

## ✅ Solução

O driver **pdo_mysql** não está habilitado no seu PHP. Siga os passos abaixo:

---

### **Método 1: Edição Manual do php.ini** ⭐ RECOMENDADO

1. **Abra o arquivo `php.ini` como Administrador:**
   ```
   C:\php\php.ini
   ```

2. **Procure pela linha** (use Ctrl+F):
   ```ini
   ;extension=pdo_mysql
   ```

3. **Remova o ponto e vírgula (;)** no início da linha:
   ```ini
   extension=pdo_mysql
   ```

4. **Salve o arquivo** (Ctrl+S)

5. **Reinicie o servidor PHP** (feche o terminal e inicie novamente)

---

### **Método 2: PowerShell Automático** ⚡

**Execute como ADMINISTRADOR** no PowerShell:

```powershell
# 1. Habilitar pdo_mysql
(Get-Content C:\php\php.ini) -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Set-Content C:\php\php.ini

# 2. Verificar se foi habilitado
php -m | Select-String -Pattern "pdo_mysql"
```

---

### **Verificação**

Após editar o `php.ini`, execute:

```bash
php -m | findstr pdo
```

Você deve ver:
```
PDO
pdo_mysql
```

---

### **Reiniciar Servidor**

Se estiver usando:

- **Artisan Serve:**
  ```bash
  # Pare o servidor (Ctrl+C) e reinicie
  php artisan serve
  ```

- **Apache/XAMPP:**
  ```bash
  # Reinicie o Apache no painel do XAMPP
  ```

- **Laragon:**
  ```bash
  # Clique em "Stop All" e depois "Start All"
  ```

---

## 🎯 Extensões Úteis para Laravel (opcional)

Enquanto estiver editando o `php.ini`, considere habilitar essas extensões também:

```ini
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=zip
```

Remova o `;` no início de cada linha que você quiser habilitar.

---

## ✅ Teste Final

Execute após as alterações:

```bash
# 1. Verificar extensões
php -m

# 2. Limpar cache do Laravel
php artisan config:clear
php artisan cache:clear

# 3. Testar conexão
php artisan migrate:status
```

Se ainda houver problemas, verifique se:
- O arquivo `php.ini` correto foi editado (`php --ini`)
- O servidor foi reiniciado completamente
- Não há outro `php.ini` sobrescrevendo as configurações
