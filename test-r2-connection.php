<?php
/**
 * Teste de Conexão com R2/Cloudflare
 * Suba este arquivo para a raiz e acesse via navegador
 */

require __DIR__ . '/vendor/autoload.php';

// Carregar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
} catch (Exception $e) {
    echo "<p style='color:red;'>Erro ao carregar .env: " . $e->getMessage() . "</p>";
}

echo "<h1>🧪 Teste de Conexão R2/Cloudflare</h1>";

echo "<h2>1. Variáveis de Ambiente</h2>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>Variável</th><th>Configurada</th><th>Valor (oculto)</th></tr>";

$r2Vars = [
    'R2_ACCESS_KEY_ID' => $_ENV['R2_ACCESS_KEY_ID'] ?? '',
    'R2_SECRET_ACCESS_KEY' => $_ENV['R2_SECRET_ACCESS_KEY'] ?? '',
    'R2_BUCKET' => $_ENV['R2_BUCKET'] ?? '',
    'R2_ENDPOINT' => $_ENV['R2_ENDPOINT'] ?? '',
    'R2_PUBLIC_URL' => $_ENV['R2_PUBLIC_URL'] ?? '',
];

foreach ($r2Vars as $key => $value) {
    $configured = !empty($value);
    $status = $configured ? '✅ Sim' : '❌ Não';
    
    // Mostrar apenas parte do valor para segurança
    if ($configured && in_array($key, ['R2_ACCESS_KEY_ID', 'R2_SECRET_ACCESS_KEY'])) {
        $display = substr($value, 0, 8) . '***' . substr($value, -4);
    } elseif ($configured) {
        $display = htmlspecialchars($value);
    } else {
        $display = '<em>não configurado</em>';
    }
    
    echo "<tr>";
    echo "<td><strong>{$key}</strong></td>";
    echo "<td>{$status}</td>";
    echo "<td>{$display}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>2. Verificar Pacote AWS S3</h2>";
$s3ClassExists = class_exists('League\Flysystem\AwsS3V3\AwsS3V3Adapter');
if ($s3ClassExists) {
    echo "<p style='color:green; font-weight:bold;'>✅ Pacote league/flysystem-aws-s3-v3 instalado corretamente!</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>❌ Pacote league/flysystem-aws-s3-v3 NÃO encontrado!</p>";
    echo "<p>Execute: <code>composer require league/flysystem-aws-s3-v3</code></p>";
}

echo "<h2>3. Teste de Conexão com R2</h2>";

if (!$s3ClassExists) {
    echo "<p style='color:orange;'>⚠️ Não é possível testar sem o pacote AWS S3 instalado.</p>";
} elseif (empty($r2Vars['R2_ACCESS_KEY_ID']) || empty($r2Vars['R2_SECRET_ACCESS_KEY']) || empty($r2Vars['R2_BUCKET'])) {
    echo "<p style='color:orange;'>⚠️ Configure as variáveis R2 no arquivo .env primeiro.</p>";
} else {
    try {
        echo "<p>Tentando conectar ao R2/Cloudflare...</p>";
        
        $s3Client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => $r2Vars['R2_ENDPOINT'],
            'use_path_style_endpoint' => false,
            'credentials' => [
                'key' => $r2Vars['R2_ACCESS_KEY_ID'],
                'secret' => $r2Vars['R2_SECRET_ACCESS_KEY'],
            ],
        ]);
        
        // Testar listagem de objetos no bucket
        $result = $s3Client->listObjectsV2([
            'Bucket' => $r2Vars['R2_BUCKET'],
            'MaxKeys' => 5,
        ]);
        
        echo "<p style='color:green; font-weight:bold;'>✅ Conexão com R2 bem-sucedida!</p>";
        echo "<p><strong>Bucket:</strong> " . htmlspecialchars($r2Vars['R2_BUCKET']) . "</p>";
        echo "<p><strong>Objetos encontrados:</strong> " . (isset($result['KeyCount']) ? $result['KeyCount'] : 0) . "</p>";
        
        if (isset($result['Contents']) && count($result['Contents']) > 0) {
            echo "<h4>📁 Últimos arquivos no bucket:</h4>";
            echo "<ul>";
            foreach (array_slice($result['Contents'], 0, 5) as $object) {
                $size = number_format($object['Size'] / 1024 / 1024, 2);
                $date = $object['LastModified']->format('Y-m-d H:i:s');
                echo "<li><strong>" . htmlspecialchars($object['Key']) . "</strong> ({$size} MB) - {$date}</li>";
            }
            echo "</ul>";
        }
        
        // Teste de upload
        echo "<h4>🧪 Teste de Upload (arquivo pequeno)</h4>";
        
        $testContent = "Teste de upload - " . date('Y-m-d H:i:s');
        $testKey = 'test-uploads/test-' . time() . '.txt';
        
        $uploadResult = $s3Client->putObject([
            'Bucket' => $r2Vars['R2_BUCKET'],
            'Key' => $testKey,
            'Body' => $testContent,
            'ContentType' => 'text/plain',
        ]);
        
        if ($uploadResult['@metadata']['statusCode'] == 200) {
            $testUrl = rtrim($r2Vars['R2_PUBLIC_URL'], '/') . '/' . $testKey;
            echo "<p style='color:green; font-weight:bold;'>✅ Upload de teste bem-sucedido!</p>";
            echo "<p><strong>Arquivo:</strong> {$testKey}</p>";
            echo "<p><strong>URL:</strong> <a href='{$testUrl}' target='_blank'>{$testUrl}</a></p>";
            
            // Deletar arquivo de teste
            $s3Client->deleteObject([
                'Bucket' => $r2Vars['R2_BUCKET'],
                'Key' => $testKey,
            ]);
            echo "<p><em>Arquivo de teste deletado automaticamente.</em></p>";
        }
        
    } catch (Aws\S3\Exception\S3Exception $e) {
        echo "<p style='color:red; font-weight:bold;'>❌ Erro de S3: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Código:</strong> " . $e->getAwsErrorCode() . "</p>";
        
        if ($e->getAwsErrorCode() === 'InvalidAccessKeyId') {
            echo "<p>⚠️ Access Key inválida. Verifique R2_ACCESS_KEY_ID</p>";
        } elseif ($e->getAwsErrorCode() === 'SignatureDoesNotMatch') {
            echo "<p>⚠️ Secret Key inválida. Verifique R2_SECRET_ACCESS_KEY</p>";
        } elseif ($e->getAwsErrorCode() === 'NoSuchBucket') {
            echo "<p>⚠️ Bucket não encontrado. Verifique R2_BUCKET</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red; font-weight:bold;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre style='background:#f5f5f5; padding:10px; overflow:auto;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
    }
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Delete este arquivo após o teste!</p>";
echo "<p><em>Gerado em: " . date('Y-m-d H:i:s') . "</em></p>";
?>
