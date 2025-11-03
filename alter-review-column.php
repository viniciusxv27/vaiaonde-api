<?php
/**
 * Script para alterar a coluna 'review' da tabela 'place' de VARCHAR para TEXT
 * Suba este arquivo para a raiz do site via File Manager da Hostinger
 * Acesse: https://vaiaondecapixaba.com.br/alter-review-column.php
 */

// Configurações do banco (use as mesmas do .env)
$host = 'vaiaondecapixaba.com.br';
$db = 'u847695711_api';
$user = 'u847695711_vaiaondeadmin';
$pass = 'Azfty1009!'; // Substitua pela senha correta do .env

try {
    // Conectar ao banco
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Alterando coluna 'review' da tabela 'place'</h1>";
    echo "<p>Conexão com banco estabelecida com sucesso!</p>";
    
    // Executar a alteração
    $sql = "ALTER TABLE `place` MODIFY COLUMN `review` TEXT";
    $pdo->exec($sql);
    
    echo "<p style='color:green; font-weight:bold;'>✅ Coluna 'review' alterada com sucesso para TEXT!</p>";
    echo "<p>Agora você pode salvar descrições/reviews com qualquer tamanho.</p>";
    
    // Verificar a mudança
    $result = $pdo->query("SHOW COLUMNS FROM `place` LIKE 'review'");
    $column = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Detalhes da coluna após alteração:</h2>";
    echo "<pre>";
    print_r($column);
    echo "</pre>";
    
    echo "<p><strong>⚠️ IMPORTANTE:</strong> Delete este arquivo do servidor após a execução!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    if ($e->getCode() == 2002) {
        echo "<p><strong>Sugestão:</strong> Verifique se o host do banco está correto. Tente usar 'localhost' no lugar de 'vaiaondecapixaba.com.br' se o banco estiver no mesmo servidor.</p>";
    }
}
?>
