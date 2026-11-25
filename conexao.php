<?php
/**
 * Conexão com o MySQL (XAMPP).
 * - Cria o banco e as tabelas automaticamente se ainda não existirem.
 * - Sempre responde em JSON, mesmo quando ocorre erro.
 * Para ver o erro técnico real, deixe DEBUG = true (volte para false em produção).
 */
const DEBUG = true;

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;          // Se o MySQL do XAMPP estiver em outra porta (ex.: 3307), altere aqui
const DB_NAME = 'constructs_bass';
const DB_USER = 'root';
const DB_PASS = '';            // Senha do MySQL, se houver

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function responderErro(int $codigo, string $mensagem, ?Throwable $e = null): void
{
    http_response_code($codigo);
    $resp = ['erro' => $mensagem];
    if (DEBUG && $e) {
        $resp['detalhe'] = $e->getMessage();
    }
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
    exit;
}

// Qualquer erro não tratado (ex.: tabela inexistente) vira JSON em vez de "erro fatal" em HTML
set_exception_handler(function (Throwable $e) {
    error_log('[constructs_bass] ' . $e->getMessage());
    responderErro(500, 'Erro interno no servidor.', $e);
});

if (!extension_loaded('pdo_mysql')) {
    responderErro(500, 'Extensão pdo_mysql não está ativa. Ative "extension=pdo_mysql" no php.ini do XAMPP e reinicie o Apache.');
}

$opcoes = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$dsnServidor = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', DB_HOST, DB_PORT);

try {
    $pdo = new PDO($dsnServidor . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $opcoes);
} catch (PDOException $e) {
    if ((int)$e->getCode() === 1049) {
        // Banco ainda não existe: cria automaticamente
        try {
            $pdo = new PDO($dsnServidor, DB_USER, DB_PASS, $opcoes);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . DB_NAME . '`');
        } catch (PDOException $e2) {
            responderErro(500, 'Falha ao criar o banco de dados.', $e2);
        }
    } else {
        $dica = match ((int)$e->getCode()) {
            2002    => 'MySQL parado ou em outra porta. Inicie o MySQL no painel do XAMPP e confira DB_PORT.',
            1045    => 'Usuário ou senha do MySQL incorretos. Confira DB_USER e DB_PASS.',
            default => 'Falha na conexão com o banco de dados.',
        };
        responderErro(500, $dica, $e);
    }
}

// Garante que as tabelas existam (mesma estrutura de banco/constructs_bass.sql)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS orcamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_completo VARCHAR(150) NOT NULL,
        telefone_whatsapp VARCHAR(25) NOT NULL,
        email VARCHAR(150) DEFAULT NULL,
        tipo_servico ENUM('Construção Residencial','Reforma e Retrofit','Gerenciamento de Obras','Outro') DEFAULT 'Outro',
        detalhes_projeto TEXT NOT NULL,
        status ENUM('Novo','Em Análise','Orçamento Enviado','Aprovado','Recusado') DEFAULT 'Novo',
        criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$pdo->exec("
    CREATE TABLE IF NOT EXISTS agendamentos_visita (
        id INT AUTO_INCREMENT PRIMARY KEY,
        orcamento_id INT DEFAULT NULL,
        nome_cliente VARCHAR(150) NOT NULL,
        telefone_whatsapp VARCHAR(25) NOT NULL,
        email VARCHAR(150) DEFAULT NULL,
        endereco_obra VARCHAR(255) NOT NULL,
        bairro_cidade VARCHAR(120) DEFAULT 'Chapecó - SC',
        tipo_servico ENUM('Construção Residencial','Reforma e Retrofit','Gerenciamento de Obras') NOT NULL,
        data_visita DATE NOT NULL,
        horario_visita TIME NOT NULL,
        observacoes TEXT DEFAULT NULL,
        status ENUM('Pendente','Confirmado','Realizado','Cancelado','Reagendado') DEFAULT 'Pendente',
        criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY fk_agendamento_orcamento (orcamento_id),
        CONSTRAINT fk_agendamento_orcamento FOREIGN KEY (orcamento_id)
            REFERENCES orcamentos (id) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$pdo->exec("
    CREATE TABLE IF NOT EXISTS comentarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_cliente VARCHAR(120) NOT NULL,
        empresa_ou_projeto VARCHAR(120) DEFAULT NULL,
        comentario TEXT NOT NULL,
        avaliacao TINYINT UNSIGNED DEFAULT 5,
        data_publicacao DATE NOT NULL,
        aprovado TINYINT(1) DEFAULT 0,
        criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
