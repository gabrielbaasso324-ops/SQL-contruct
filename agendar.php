<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$nome = trim($dados['nome_cliente'] ?? '');
$telefone = trim($dados['telefone_whatsapp'] ?? '');
$email = trim($dados['email'] ?? '');
$endereco = trim($dados['endereco_obra'] ?? '');
$bairroCidade = trim($dados['bairro_cidade'] ?? 'Chapecó - SC');
$tipoServico = trim($dados['tipo_servico'] ?? '');
$dataVisita = trim($dados['data_visita'] ?? '');
$horarioVisita = trim($dados['horario_visita'] ?? '');
$observacoes = trim($dados['observacoes'] ?? '');

$servicosValidos = [
    'Construção Residencial',
    'Reforma e Retrofit',
    'Gerenciamento de Obras'
];

if (
    $nome === '' ||
    $telefone === '' ||
    $endereco === '' ||
    !in_array($tipoServico, $servicosValidos, true) ||
    $dataVisita === '' ||
    $horarioVisita === ''
) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados obrigatórios inválidos ou ausentes para o agendamento.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($horarioVisita) === 5) {
    $horarioVisita .= ':00';
}

$stmt = $pdo->prepare('
    INSERT INTO agendamentos_visita (
        nome_cliente, telefone_whatsapp, email, endereco_obra,
        bairro_cidade, tipo_servico, data_visita, horario_visita, observacoes
    ) VALUES (
        :nome, :telefone, :email, :endereco,
        :bairro_cidade, :tipo_servico, :data_visita, :horario_visita, :observacoes
    )
');
$stmt->execute([
    ':nome' => $nome,
    ':telefone' => $telefone,
    ':email' => $email !== '' ? $email : null,
    ':endereco' => $endereco,
    ':bairro_cidade' => $bairroCidade !== '' ? $bairroCidade : 'Chapecó - SC',
    ':tipo_servico' => $tipoServico,
    ':data_visita' => $dataVisita,
    ':horario_visita' => $horarioVisita,
    ':observacoes' => $observacoes !== '' ? $observacoes : null
]);

http_response_code(201);
echo json_encode([
    'sucesso' => true,
    'id' => $pdo->lastInsertId(),
    'mensagem' => 'Visita técnica agendada com sucesso!'
], JSON_UNESCAPED_UNICODE);