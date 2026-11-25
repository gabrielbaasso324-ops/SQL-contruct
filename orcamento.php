<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$nome = trim($dados['nome_completo'] ?? '');
$telefone = trim($dados['telefone_whatsapp'] ?? '');
$email = trim($dados['email'] ?? '');
$tipoServico = trim($dados['tipo_servico'] ?? 'Outro');
$detalhes = trim($dados['detalhes_projeto'] ?? '');

$servicosValidos = [
    'Construção Residencial',
    'Reforma e Retrofit',
    'Gerenciamento de Obras',
    'Outro'
];
if (!in_array($tipoServico, $servicosValidos, true)) {
    $tipoServico = 'Outro';
}

if ($nome === '' || $telefone === '' || $detalhes === '') {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha nome, telefone e detalhes do projeto.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO orcamentos (nome_completo, telefone_whatsapp, email, tipo_servico, detalhes_projeto)
    VALUES (:nome, :telefone, :email, :tipo_servico, :detalhes)
');
$stmt->execute([
    ':nome' => $nome,
    ':telefone' => $telefone,
    ':email' => $email !== '' ? $email : null,
    ':tipo_servico' => $tipoServico,
    ':detalhes' => $detalhes
]);

http_response_code(201);
echo json_encode([
    'sucesso' => true,
    'id' => $pdo->lastInsertId(),
    'mensagem' => 'Solicitação de orçamento salva com sucesso!'
], JSON_UNESCAPED_UNICODE);