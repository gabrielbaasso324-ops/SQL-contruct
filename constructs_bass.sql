-- Constructs Bass — banco de dados
-- Importe no phpMyAdmin (aba "Importar") OU deixe o api/conexao.php criar tudo sozinho.
-- Este arquivo recria o banco do zero (apaga as tabelas se já existirem).

CREATE DATABASE IF NOT EXISTS `constructs_bass`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `constructs_bass`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `agendamentos_visita`;
DROP TABLE IF EXISTS `orcamentos`;
DROP TABLE IF EXISTS `comentarios`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `orcamentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome_completo` VARCHAR(150) NOT NULL,
  `telefone_whatsapp` VARCHAR(25) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `tipo_servico` ENUM('Construção Residencial','Reforma e Retrofit','Gerenciamento de Obras','Outro') DEFAULT 'Outro',
  `detalhes_projeto` TEXT NOT NULL,
  `status` ENUM('Novo','Em Análise','Orçamento Enviado','Aprovado','Recusado') DEFAULT 'Novo',
  `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agendamentos_visita` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `orcamento_id` INT DEFAULT NULL,
  `nome_cliente` VARCHAR(150) NOT NULL,
  `telefone_whatsapp` VARCHAR(25) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `endereco_obra` VARCHAR(255) NOT NULL,
  `bairro_cidade` VARCHAR(120) DEFAULT 'Chapecó - SC',
  `tipo_servico` ENUM('Construção Residencial','Reforma e Retrofit','Gerenciamento de Obras') NOT NULL,
  `data_visita` DATE NOT NULL,
  `horario_visita` TIME NOT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `status` ENUM('Pendente','Confirmado','Realizado','Cancelado','Reagendado') DEFAULT 'Pendente',
  `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_agendamento_orcamento` (`orcamento_id`),
  CONSTRAINT `fk_agendamento_orcamento` FOREIGN KEY (`orcamento_id`)
    REFERENCES `orcamentos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comentarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome_cliente` VARCHAR(120) NOT NULL,
  `empresa_ou_projeto` VARCHAR(120) DEFAULT NULL,
  `comentario` TEXT NOT NULL,
  `avaliacao` TINYINT UNSIGNED DEFAULT 5,
  `data_publicacao` DATE NOT NULL,
  `aprovado` TINYINT(1) DEFAULT 0,
  `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comentarios` (`nome_cliente`, `empresa_ou_projeto`, `comentario`, `avaliacao`, `data_publicacao`, `aprovado`) VALUES
('Juliana Andrade', 'Cliente residencial', 'Fechamos o contrato com prazo de seis meses e a casa ficou pronta em seis meses. Depois de duas experiências ruins com outras empresas, foi a primeira vez que isso aconteceu de verdade.', 5, '2025-04-14', 1),
('Roberto Salles', 'Padaria Trigo Nosso', 'Reformamos a padaria inteira sem fechar as portas por mais de dois dias seguidos. O Tiago organizou tudo em etapas para não atrapalhar o funcionamento, e o orçamento não mudou.', 5, '2025-09-02', 1),
('Denise Marchetti', 'Clínica Boa Vista', 'Contratamos só o gerenciamento. A Constructs Bass organizou o cronograma e resolveu conflitos com fornecedores que a gente nem sabia que existiam.', 5, '2026-01-20', 1);
