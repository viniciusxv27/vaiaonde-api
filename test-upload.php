<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Upload de Vídeo - VaiAonde</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #FEB800 0%, #ff9500 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #FEB800;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        input[type="file"]:focus {
            outline: none;
            border-color: #FEB800;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: #FEB800;
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #ff9500;
        }
        
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .result.info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .info-box {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .progress {
            margin-top: 10px;
            height: 25px;
            background: #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            display: none;
        }
        
        .progress-bar {
            height: 100%;
            background: #FEB800;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: bold;
            font-size: 12px;
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 13px;
            display: none;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎥 Teste de Upload de Vídeo</h1>
        <p class="subtitle">VaiAonde Capixaba - Diagnóstico</p>
        
        <div class="info-box">
            <strong>ℹ️ Informações:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Este é um teste <strong>simplificado</strong> de upload</li>
                <li>Não usa Laravel, apenas PHP puro</li>
                <li>Limite atual: <?php echo ini_get('upload_max_filesize'); ?> (upload_max_filesize)</li>
                <li>POST máximo: <?php echo ini_get('post_max_size'); ?> (post_max_size)</li>
            </ul>
        </div>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo '<div class="result ';
            
            if (isset($_FILES['video'])) {
                $file = $_FILES['video'];
                
                echo 'info" style="display:block;">';
                echo '<h3>📊 Informações do Upload:</h3>';
                echo '<ul style="margin-left: 20px; margin-top: 10px;">';
                echo '<li><strong>Nome:</strong> ' . htmlspecialchars($file['name']) . '</li>';
                echo '<li><strong>Tipo:</strong> ' . htmlspecialchars($file['type']) . '</li>';
                echo '<li><strong>Tamanho:</strong> ' . number_format($file['size'] / 1024 / 1024, 2) . ' MB</li>';
                echo '<li><strong>Arquivo temporário:</strong> ' . htmlspecialchars($file['tmp_name']) . '</li>';
                echo '<li><strong>Código de erro:</strong> ' . $file['error'];
                
                // Explicar código de erro
                $errorMessages = [
                    UPLOAD_ERR_OK => 'Nenhum erro',
                    UPLOAD_ERR_INI_SIZE => 'Arquivo excede upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'Arquivo excede MAX_FILE_SIZE do formulário',
                    UPLOAD_ERR_PARTIAL => 'Upload parcial (interrompido)',
                    UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado',
                    UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever no disco',
                    UPLOAD_ERR_EXTENSION => 'Extensão PHP bloqueou o upload'
                ];
                
                echo ' (' . ($errorMessages[$file['error']] ?? 'Erro desconhecido') . ')';
                echo '</li>';
                echo '</ul>';
                echo '</div>';
                
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // Tentar mover o arquivo
                    $uploadDir = __DIR__ . '/uploads/test-videos';
                    
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $destination = $uploadDir . '/' . 'test_' . time() . '_' . basename($file['name']);
                    
                    echo '<div class="result ';
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        echo 'success" style="display:block;">';
                        echo '<h3>✅ Upload bem-sucedido!</h3>';
                        echo '<p>Arquivo salvo em: <code>' . htmlspecialchars($destination) . '</code></p>';
                        echo '<p><strong>Tamanho final:</strong> ' . number_format(filesize($destination) / 1024 / 1024, 2) . ' MB</p>';
                    } else {
                        echo 'error" style="display:block;">';
                        echo '<h3>❌ Erro ao salvar arquivo</h3>';
                        echo '<p>Não foi possível mover o arquivo de <code>' . htmlspecialchars($file['tmp_name']) . '</code> para <code>' . htmlspecialchars($destination) . '</code></p>';
                        echo '<p><strong>Possíveis causas:</strong></p>';
                        echo '<ul style="margin-left: 20px;">';
                        echo '<li>Permissões do diretório insuficientes</li>';
                        echo '<li>Espaço em disco insuficiente</li>';
                        echo '<li>SELinux ou mod_security bloqueando</li>';
                        echo '</ul>';
                    }
                    echo '</div>';
                }
            } else {
                echo 'error" style="display:block;">';
                echo '<h3>❌ Nenhum arquivo detectado</h3>';
                echo '<p>$_FILES está vazio. Possíveis causas:</p>';
                echo '<ul style="margin-left: 20px;">';
                echo '<li>Arquivo maior que post_max_size (' . ini_get('post_max_size') . ')</li>';
                echo '<li>Arquivo maior que upload_max_filesize (' . ini_get('upload_max_filesize') . ')</li>';
                echo '<li>Timeout durante upload (max_execution_time: ' . ini_get('max_execution_time') . 's)</li>';
                echo '</ul>';
            }
            
            echo '</div>';
        }
        ?>
        
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="form-group">
                <label for="video">📁 Selecione um vídeo:</label>
                <input type="file" name="video" id="video" accept="video/mp4,video/quicktime,video/x-msvideo" required>
                <div class="file-info" id="fileInfo"></div>
            </div>
            
            <button type="submit" id="submitBtn">
                🚀 Enviar Vídeo
            </button>
            
            <div class="progress" id="progressBar">
                <div class="progress-bar" id="progressBarFill">0%</div>
            </div>
        </form>
        
        <div class="warning">
            <strong>⚠️ Após o teste:</strong> Delete este arquivo (<code>test-upload.php</code>) do servidor por segurança!
        </div>
    </div>
    
    <script>
        const videoInput = document.getElementById('video');
        const fileInfo = document.getElementById('fileInfo');
        const submitBtn = document.getElementById('submitBtn');
        
        videoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                const maxSize = <?php echo return_bytes(ini_get('upload_max_filesize')) / 1024 / 1024; ?>;
                
                fileInfo.style.display = 'block';
                fileInfo.innerHTML = `
                    <strong>Arquivo selecionado:</strong><br>
                    📄 Nome: ${file.name}<br>
                    📦 Tamanho: ${sizeMB} MB<br>
                    🎬 Tipo: ${file.type}
                    ${sizeMB > maxSize ? '<br><br><strong style="color:red;">⚠️ Arquivo pode ser muito grande!</strong>' : ''}
                `;
            }
        });
        
        // Mostrar "enviando..." quando submit
        document.getElementById('uploadForm').addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Enviando...';
        });
    </script>
</body>
</html>

<?php
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}
?>
