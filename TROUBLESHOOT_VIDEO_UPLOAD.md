# 🎥 Guia de Troubleshooting - Upload de Vídeos

## Erro Atual
```
Erro ao enviar vídeo: Server Error
Failed to load resource: the server responded with a status of 500 ()
```

## ✅ Melhorias Implementadas

### 1. **Sistema de Fallback R2 → Local**
Agora o sistema tenta:
1. **Primeiro**: Upload para R2/Cloudflare (se configurado)
2. **Se falhar**: Faz upload para o disco local automaticamente

### 2. **Mensagens de Erro Detalhadas**
- Identificação automática do tipo de erro
- Mensagens específicas para cada problema
- Logs mais detalhados em `storage/logs/laravel.log`

### 3. **Tratamento Robusto de Exceções**
- Rollback de transações em caso de erro
- Captura de erros de conexão, disco, tamanho, etc.

---

## 🔧 Passo a Passo para Resolver

### **PASSO 1: Executar Script de Diagnóstico**

1. Suba o arquivo `check-video-upload.php` para a **raiz do site**
2. Acesse: `https://vaiaondecapixaba.com.br/check-video-upload.php`
3. Verifique os itens marcados com ❌ ou ⚠️

**O script verifica:**
- ✅ Configurações PHP (upload_max_filesize, post_max_size, etc.)
- ✅ Permissões de diretórios (storage/app, storage/logs, etc.)
- ✅ Variáveis de ambiente R2
- ✅ Extensões PHP necessárias
- ✅ Logs de erros recentes

### **PASSO 2: Verificar/Corrigir Permissões**

Conecte via **File Manager** da Hostinger e defina permissões:

```
📁 storage/
   └─ 📁 app/          → 755 ou 775
   └─ 📁 logs/         → 755 ou 775
   └─ 📁 framework/    → 755 ou 775
      └─ 📁 cache/     → 755 ou 775
      └─ 📁 sessions/  → 755 ou 775
      └─ 📁 views/     → 755 ou 775

📁 bootstrap/
   └─ 📁 cache/        → 755 ou 775

📁 public/
   └─ 📁 uploads/      → 755 ou 775 (criar se não existir)
      └─ 📁 videos/    → 755 ou 775 (criar se não existir)
      └─ 📁 thumbnails/ → 755 ou 775 (criar se não existir)
```

**Como mudar permissões no File Manager:**
- Clique com botão direito no diretório
- "Permissions" ou "Permissões"
- Marque: `Read`, `Write`, `Execute` para Owner e Group
- Aplique recursivamente

### **PASSO 3: Verificar Configuração PHP**

Se o `.htaccess` não estiver aplicando os limites, crie/edite `php.ini` na raiz:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
file_uploads = On
```

### **PASSO 4: Testar Upload Simples**

Crie um arquivo `test-upload.php` na raiz:

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    echo "<h2>Informações do Upload</h2>";
    echo "<pre>";
    print_r($_FILES['video']);
    echo "</pre>";
    
    if ($_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $destino = __DIR__ . '/uploads/test_' . time() . '.mp4';
        if (move_uploaded_file($_FILES['video']['tmp_name'], $destino)) {
            echo "<p style='color:green;'>✅ Upload bem-sucedido!</p>";
            echo "<p>Arquivo salvo em: $destino</p>";
        } else {
            echo "<p style='color:red;'>❌ Erro ao mover arquivo</p>";
        }
    } else {
        echo "<p style='color:red;'>Erro no upload: " . $_FILES['video']['error'] . "</p>";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="video" accept="video/*" required>
    <button type="submit">Testar Upload</button>
</form>
```

Acesse: `https://vaiaondecapixaba.com.br/test-upload.php`

---

## 🐛 Erros Comuns e Soluções

### Erro: "The file was not uploaded"
**Causa**: Arquivo muito grande ou configuração PHP
**Solução**: 
- Verificar `upload_max_filesize` e `post_max_size`
- Aumentar em `.htaccess` ou `php.ini`

### Erro: "Disk not found" ou "Storage exception"
**Causa**: Configuração R2 incorreta ou permissões
**Solução**:
- Verificar variáveis R2 no `.env`
- Sistema agora usa fallback local automático

### Erro: "Permission denied"
**Causa**: Diretório sem permissão de escrita
**Solução**:
- Ajustar permissões conforme PASSO 2

### Erro: "Connection timeout"
**Causa**: Upload muito lento ou max_execution_time baixo
**Solução**:
- Aumentar `max_execution_time` para 300 ou 600

---

## 📋 Checklist de Verificação

Após aplicar as melhorias, verifique:

- [ ] Script de diagnóstico executado e analisado
- [ ] Permissões dos diretórios storage/ e public/ ajustadas
- [ ] Arquivo `php.ini` criado/editado (se necessário)
- [ ] Teste de upload simples funcionando
- [ ] Logs em `storage/logs/laravel.log` sendo gerados
- [ ] Diretórios `public/uploads/videos` e `thumbnails` criados
- [ ] Variáveis R2 no `.env` corretas (ou vazias para usar local)

---

## 🚀 Testando o Upload

1. Acesse: `https://vaiaondecapixaba.com.br/influencer/videos/create`
2. Preencha o formulário
3. Selecione um vídeo **pequeno** primeiro (5-10MB)
4. Envie e verifique:
   - Se aparecer erro, veja os logs em `storage/logs/laravel.log`
   - Se funcionar, tente vídeos maiores gradualmente

---

## 📝 Logs para Análise

Os logs agora mostram:
```
[timestamp] INFO: === INÍCIO DO UPLOAD DE VÍDEO ===
[timestamp] INFO: Dados do request: {has_video: true, video_size: 12345678}
[timestamp] INFO: Validação passou com sucesso
[timestamp] INFO: Tentando upload para R2/Cloudflare
[timestamp] WARNING: Falha no upload para R2, tentando armazenamento local
[timestamp] INFO: Upload do vídeo para disco local concluído
```

---

## ⚠️ IMPORTANTE

**Após resolver o problema:**
1. Delete `check-video-upload.php`
2. Delete `test-upload.php`
3. Configure credenciais R2 no `.env` para usar armazenamento em nuvem
4. Teste upload de vídeos de diferentes tamanhos

**Suporte Hostinger:**
- Se nada funcionar, abra ticket mencionando "limite de upload de vídeos"
- Peça para verificar `php.ini` global e `mod_security`

---

**Desenvolvido para**: VaiAonde Capixaba  
**Data**: 03/11/2025
