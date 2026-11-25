<?php
// Abra http://localhost/constructs_bass/api/teste.php para diagnosticar a conexão.
require 'conexao.php';
echo json_encode([
    'conexao' => 'OK',
    'banco' => DB_NAME,
    'tabelas' => $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
