<?php
/**
 * Script de diagnóstico para upload de vídeos
 * Suba para a raiz do site e acesse via navegador
 * https://vaiaondecapixaba.com.br/check-video-upload.php
 */

echo "<h1>Diagnóstico de Upload de Vídeos - VaiAonde</h1>";

echo "<h2>1. Configurações PHP</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Configuração</th><th>Valor Atual</th><th>Status</th></tr>";

$configs = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit'),
    'file_uploads' => ini_get('file_uploads') ? 'On' : 'Off',
];

foreach ($configs as $key => $value) {
    $status = '✅';
    $recommendation = '';
    
    if ($key === 'upload_max_filesize') {
        $bytes = return_bytes($value);
        if ($bytes < 100 * 1024 * 1024) { // menos de 100MB
            $status = '⚠️';
            $recommendation = '<br><small>Recomendado: 100M ou mais para vídeos</small>';
        }
    }
    
    if ($key === 'post_max_size') {
        $bytes = return_bytes($value);
        if ($bytes < 100 * 1024 * 1024) {
            $status = '⚠️';
            $recommendation = '<br><small>Recomendado: 100M ou mais para vídeos</small>';
        }
    }
    
    if ($key === 'max_execution_time' && $value < 300) {
        $status = '⚠️';
        $recommendation = '<br><small>Recomendado: 300 segundos ou mais</small>';
    }
    
    if ($key === 'file_uploads' && $value === 'Off') {
        $status = '❌';
        $recommendation = '<br><small>CRÍTICO: Upload de arquivos desabilitado!</small>';
    }
    
    echo "<tr><td><strong>{$key}</strong></td><td>{$value}{$recommendation}</td><td>{$status}</td></tr>";
}

echo "</table>";

echo "<h2>2. Permissões de Diretórios</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Diretório</th><th>Existe</th><th>Gravável</th><th>Status</th></tr>";

$dirs = [
    'storage/app' => __DIR__ . '/storage/app',
    'storage/logs' => __DIR__ . '/storage/logs',
    'storage/framework' => __DIR__ . '/storage/framework',
    'storage/framework/cache' => __DIR__ . '/storage/framework/cache',
    'bootstrap/cache' => __DIR__ . '/bootstrap/cache',
];

foreach ($dirs as $name => $path) {
    $exists = file_exists($path);
    $writable = is_writable($path);
    $status = ($exists && $writable) ? '✅' : '❌';
    
    echo "<tr>";
    echo "<td><strong>{$name}</strong><br><small>{$path}</small></td>";
    echo "<td>" . ($exists ? 'Sim' : 'Não') . "</td>";
    echo "<td>" . ($writable ? 'Sim' : 'Não') . "</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>3. Variáveis de Ambiente (R2/Cloudflare)</h2>";

// Carregar .env
$envFile = __DIR__ . '/.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $envVars[$key] = $value;
        }
    }
}

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Variável</th><th>Configurada</th><th>Status</th></tr>";

$r2Vars = [
    'R2_ACCESS_KEY_ID',
    'R2_SECRET_ACCESS_KEY',
    'R2_BUCKET',
    'R2_ENDPOINT',
    'R2_PUBLIC_URL',
];

foreach ($r2Vars as $var) {
    $value = $envVars[$var] ?? '';
    $configured = !empty($value);
    $status = $configured ? '✅' : '❌';
    $display = $configured ? '(configurado)' : '(NÃO configurado)';
    
    // Ocultar valores sensíveis
    if (in_array($var, ['R2_ACCESS_KEY_ID', 'R2_SECRET_ACCESS_KEY']) && $configured) {
        $display = substr($value, 0, 8) . '***' . substr($value, -4);
    } elseif ($configured) {
        $display = $value;
    }
    
    echo "<tr><td><strong>{$var}</strong></td><td>{$display}</td><td>{$status}</td></tr>";
}

echo "</table>";

echo "<h2>4. Extensões PHP Necessárias</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Extensão</th><th>Status</th></tr>";

$extensions = [
    'fileinfo' => 'Identificação de tipos MIME',
    'curl' => 'Upload para R2/S3',
    'openssl' => 'Conexões HTTPS',
    'simplexml' => 'Parse de respostas S3',
];

foreach ($extensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅ Instalada' : '❌ NÃO instalada';
    echo "<tr><td><strong>{$ext}</strong><br><small>{$description}</small></td><td>{$status}</td></tr>";
}

echo "</table>";

echo "<h2>5. Pacotes Laravel/Composer Necessários</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Pacote</th><th>Status</th><th>Ação</th></tr>";

// Verificar se o pacote AWS S3 está instalado
$awsS3Installed = class_exists('League\Flysystem\AwsS3V3\AwsS3V3Adapter');
$awsS3Status = $awsS3Installed ? '✅ Instalado' : '❌ NÃO instalado';
$awsS3Action = $awsS3Installed 
    ? '<span style="color:green;">Pronto para usar R2/S3</span>' 
    : '<span style="color:red;">Execute: <code>composer require league/flysystem-aws-s3-v3</code></span>';

echo "<tr>";
echo "<td><strong>league/flysystem-aws-s3-v3</strong><br><small>Necessário para R2/Cloudflare e S3</small></td>";
echo "<td>{$awsS3Status}</td>";
echo "<td>{$awsS3Action}</td>";
echo "</tr>";

// Verificar Storage facade
$storageFacadeExists = class_exists('Illuminate\Support\Facades\Storage');
$storageFacadeStatus = $storageFacadeExists ? '✅ OK' : '❌ Laravel não instalado';

echo "<tr>";
echo "<td><strong>Laravel Storage Facade</strong><br><small>Sistema de arquivos do Laravel</small></td>";
echo "<td>{$storageFacadeStatus}</td>";
echo "<td>" . ($storageFacadeExists ? 'OK' : 'Reinstale Laravel') . "</td>";
echo "</tr>";

echo "</table>";

if (!$awsS3Installed) {
    echo "<div style='background:#fff3cd; border-left:4px solid #ffc107; padding:15px; margin:20px 0; border-radius:4px;'>";
    echo "<h3 style='margin-top:0; color:#856404;'>⚠️ AÇÃO NECESSÁRIA</h3>";
    echo "<p><strong>O pacote AWS S3 NÃO está instalado!</strong></p>";
    echo "<p>O sistema agora usa <strong>armazenamento local</strong> como fallback, mas para usar R2/Cloudflare você precisa instalar o pacote:</p>";
    echo "<ol>";
    echo "<li>Via SSH/Terminal: <code>composer require league/flysystem-aws-s3-v3:^3.0</code></li>";
    echo "<li>Ou localmente, depois suba os arquivos <code>vendor/</code> e <code>composer.lock</code></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>6. Teste de Escrita em Disco</h2>";

$testFile = __DIR__ . '/storage/app/test_upload_' . time() . '.txt';
$testContent = 'Teste de escrita - ' . date('Y-m-d H:i:s');

try {
    $written = file_put_contents($testFile, $testContent);
    if ($written !== false) {
        echo "<p style='color:green;'>✅ Escrita em disco: <strong>OK</strong></p>";
        echo "<p>Arquivo de teste criado: {$testFile}</p>";
        
        // Tentar ler o arquivo
        $read = file_get_contents($testFile);
        if ($read === $testContent) {
            echo "<p style='color:green;'>✅ Leitura do disco: <strong>OK</strong></p>";
        } else {
            echo "<p style='color:red;'>❌ Leitura falhou</p>";
        }
        
        // Deletar arquivo de teste
        @unlink($testFile);
    } else {
        echo "<p style='color:red;'>❌ Não foi possível escrever no disco</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erro ao testar escrita: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</table>";

echo "<h2>7. Logs Recentes</h2>";

$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "<p>Tamanho do log: " . number_format($logSize / 1024, 2) . " KB</p>";
    
    // Ler últimas 50 linhas
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    
    echo "<div style='background:#f5f5f5; padding:10px; border:1px solid #ccc; max-height:400px; overflow:auto;'>";
    echo "<pre style='font-size:11px;'>";
    
    foreach ($lastLines as $line) {
        // Destacar erros
        if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
            echo "<span style='color:red; font-weight:bold;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    
    echo "</pre>";
    echo "</div>";
} else {
    echo "<p style='color:orange;'>⚠️ Arquivo de log não encontrado</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Delete este arquivo após o diagnóstico!</p>";
echo "<p><em>Gerado em: " . date('Y-m-d H:i:s') . "</em></p>";

// Função auxiliar para converter valores de bytes
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
