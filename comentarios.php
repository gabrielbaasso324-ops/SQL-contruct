<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('
        SELECT id, nome_cliente, empresa_ou_projeto, comentario, avaliacao, data_publicacao
        FROM comentarios
        WHERE aprovado = TRUE
        ORDER BY data_publicacao DESC, id DESC
    ');
    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $nome = trim($dados['nome_cliente'] ?? '');
    $empresa = trim($dados['empresa_ou_projeto'] ?? '');
    $comentario = trim($dados['comentario'] ?? '');
    $avaliacao = (int)($dados['avaliacao'] ?? 5);

    if ($nome === '' || $comentario === '') {
        http_response_code(400);
        echo json_encode(['erro' => 'Nome e comentário são obrigatórios.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($avaliacao < 1 || $avaliacao > 5) {
        $avaliacao = 5;
    }

    $stmt = $pdo->prepare('
        INSERT INTO comentarios (nome_cliente, empresa_ou_projeto, comentario, avaliacao, data_publicacao, aprovado)
        VALUES (:nome, :empresa, :comentario, :avaliacao, CURDATE(), FALSE)
    ');
    $stmt->execute([
        ':nome' => $nome,
        ':empresa' => $empresa !== '' ? $empresa : null,
        ':comentario' => $comentario,
        ':avaliacao' => $avaliacao
    ]);

    http_response_code(201);
    echo json_encode([
        'sucesso' => true,
        'id' => $pdo->lastInsertId(),
        'mensagem' => 'Comentário enviado para aprovação!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);