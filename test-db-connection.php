<?php
/**
 * Script de Teste de Conexão com Banco de Dados
 * Verifica se o PDO MySQL está funcionando corretamente
 */

echo "\n========================================\n";
echo "  TESTE DE CONEXÃO COM BANCO DE DADOS  \n";
echo "========================================\n\n";

// 1. Verificar drivers PDO disponíveis
echo "1. Drivers PDO disponíveis:\n";
$drivers = PDO::getAvailableDrivers();
if (empty($drivers)) {
    echo "   ❌ ERRO: Nenhum driver PDO disponível!\n\n";
    exit(1);
}
foreach ($drivers as $driver) {
    echo "   ✅ " . $driver . "\n";
}
echo "\n";

// 2. Verificar se mysql está na lista
if (!in_array('mysql', $drivers)) {
    echo "2. Driver MySQL:\n";
    echo "   ❌ ERRO: Driver PDO MySQL não encontrado!\n";
    echo "   Solução: Habilite 'extension=pdo_mysql' no php.ini\n\n";
    exit(1);
}
echo "2. Driver MySQL:\n";
echo "   ✅ Driver PDO MySQL encontrado!\n\n";

// 3. Carregar variáveis de ambiente do Laravel
if (!file_exists(__DIR__ . '/.env')) {
    echo "3. Arquivo .env:\n";
    echo "   ❌ ERRO: Arquivo .env não encontrado!\n\n";
    exit(1);
}

// Parse .env file
$envFile = file_get_contents(__DIR__ . '/.env');
$envLines = explode("\n", $envFile);
$envVars = [];
foreach ($envLines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

echo "3. Configurações do Banco (.env):\n";
echo "   Host: " . ($envVars['DB_HOST'] ?? 'N/A') . "\n";
echo "   Porta: " . ($envVars['DB_PORT'] ?? 'N/A') . "\n";
echo "   Database: " . ($envVars['DB_DATABASE'] ?? 'N/A') . "\n";
echo "   Username: " . ($envVars['DB_USERNAME'] ?? 'N/A') . "\n";
echo "\n";

// 4. Tentar conectar ao banco
echo "4. Testando conexão com o banco...\n";
try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
        $envVars['DB_HOST'] ?? '127.0.0.1',
        $envVars['DB_PORT'] ?? '3306',
        $envVars['DB_DATABASE'] ?? ''
    );
    
    $pdo = new PDO(
        $dsn,
        $envVars['DB_USERNAME'] ?? '',
        $envVars['DB_PASSWORD'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "   ✅ CONEXÃO ESTABELECIDA COM SUCESSO!\n\n";
    
    // 5. Testar query
    echo "5. Testando query na tabela 'sessions'...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sessions");
    $result = $stmt->fetch();
    echo "   ✅ Query executada com sucesso!\n";
    echo "   Total de sessões: " . $result['total'] . "\n\n";
    
    echo "========================================\n";
    echo "  ✅ TODOS OS TESTES PASSARAM!  \n";
    echo "========================================\n\n";
    
} catch (PDOException $e) {
    echo "   ❌ ERRO DE CONEXÃO!\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n\n";
    
    echo "========================================\n";
    echo "  ❌ FALHA NOS TESTES  \n";
    echo "========================================\n\n";
    
    echo "Possíveis soluções:\n";
    echo "1. Verifique se as credenciais no .env estão corretas\n";
    echo "2. Verifique se o servidor MySQL está rodando\n";
    echo "3. Verifique se você tem permissão para acessar o banco\n";
    echo "4. Verifique o firewall/porta 3306\n\n";
    
    exit(1);
}
