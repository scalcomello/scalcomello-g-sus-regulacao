-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para sushub
CREATE DATABASE IF NOT EXISTS `sushub` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `sushub`;

-- Copiando estrutura para tabela sushub.auditoria_solicitacao
CREATE TABLE IF NOT EXISTS `auditoria_solicitacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `solicitacao_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `usuario_id` (`usuario_id`) USING BTREE,
  KEY `solicitacao_id` (`solicitacao_id`) USING BTREE,
  CONSTRAINT `auditoria_solicitacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `auditoria_solicitacao_ibfk_2` FOREIGN KEY (`solicitacao_id`) REFERENCES `solicitacao` (`idSolicitacao`)
) ENGINE=InnoDB AUTO_INCREMENT=3643 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.competencias
CREATE TABLE IF NOT EXISTS `competencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `data_inicial` date NOT NULL,
  `data_final` date NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.exames_laboratoriais
CREATE TABLE IF NOT EXISTS `exames_laboratoriais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor_unitario` float DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.exames_laboratoriais_solicitacao
CREATE TABLE IF NOT EXISTS `exames_laboratoriais_solicitacao` (
  `solicitacao_id` int(11) NOT NULL,
  `exame_id` int(11) NOT NULL,
  `data_horario` datetime DEFAULT NULL,
  `numero_protocolo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`solicitacao_id`,`exame_id`) USING BTREE,
  KEY `exame_id` (`exame_id`) USING BTREE,
  CONSTRAINT `exames_laboratoriais_solicitacao_ibfk_1` FOREIGN KEY (`solicitacao_id`) REFERENCES `solicitacao` (`idSolicitacao`),
  CONSTRAINT `exames_laboratoriais_solicitacao_ibfk_2` FOREIGN KEY (`exame_id`) REFERENCES `exames_laboratoriais` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.medico
CREATE TABLE IF NOT EXISTS `medico` (
  `idMedico` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `especialidade` varchar(100) NOT NULL,
  `crm` varchar(20) NOT NULL,
  PRIMARY KEY (`idMedico`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.orientacoes
CREATE TABLE IF NOT EXISTS `orientacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_procedimento` varchar(255) NOT NULL,
  `orientacao` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.prestadores
CREATE TABLE IF NOT EXISTS `prestadores` (
  `id_prestador` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_prestadora` varchar(255) NOT NULL,
  `cnpj` varchar(50) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `bairro` varchar(50) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_prestador`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.procedimento
CREATE TABLE IF NOT EXISTS `procedimento` (
  `idProcedimento` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `procedimento` varchar(255) DEFAULT NULL,
  `procedimento_especifico` varchar(255) DEFAULT NULL,
  `data_ultima_atualizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`idProcedimento`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.publicar_fila_de_espera
CREATE TABLE IF NOT EXISTS `publicar_fila_de_espera` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_procedimento` int(11) DEFAULT NULL,
  `CPF` varchar(20) DEFAULT NULL,
  `CNS` varchar(20) DEFAULT NULL,
  `idade` int(11) DEFAULT NULL,
  `data_do_pedido` date DEFAULT NULL,
  `data_agendamento` date DEFAULT NULL,
  `classificacao` varchar(255) DEFAULT NULL,
  `situacao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.solicitacao
CREATE TABLE IF NOT EXISTS `solicitacao` (
  `idSolicitacao` int(11) NOT NULL AUTO_INCREMENT,
  `cidadao_id` bigint(20) unsigned DEFAULT NULL,
  `idMedico` int(11) DEFAULT NULL,
  `procedimento_id` int(11) DEFAULT NULL,
  `data_solicitacao` date DEFAULT NULL,
  `data_recebido_secretaria` datetime DEFAULT NULL,
  `classificacao` varchar(255) DEFAULT NULL,
  `status_procedimento` varchar(255) DEFAULT NULL,
  `data_encerramento` datetime DEFAULT NULL,
  `justificativa_encerramento` longtext DEFAULT NULL,
  `hora_agendamento` time DEFAULT NULL,
  `regulacao` int(11) DEFAULT NULL,
  `justificativa_regulacao` longtext DEFAULT NULL,
  `idPrestador` int(11) DEFAULT NULL,
  `data_agendamento_clinica` date DEFAULT NULL,
  `data_regulacao` datetime DEFAULT NULL,
  `numero_protocolo` varchar(255) DEFAULT NULL,
  `data_retorno_fila` datetime DEFAULT NULL,
  `tipo_procedimento` varchar(255) DEFAULT NULL,
  `retorno` int(11) DEFAULT NULL,
  `status_reagendamento` varchar(50) DEFAULT NULL,
  `total_valor` decimal(10,2) DEFAULT NULL,
  `id_acompanhante` bigint(20) DEFAULT NULL,
  `status_transporte` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idSolicitacao`),
  KEY `cidadao_id` (`cidadao_id`),
  KEY `procedimento_id` (`procedimento_id`),
  KEY `idMedico` (`idMedico`),
  KEY `fk_prestador_solicitacao` (`idPrestador`),
  CONSTRAINT `fk_prestador_solicitacao` FOREIGN KEY (`idPrestador`) REFERENCES `prestadores` (`id_prestador`),
  CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`cidadao_id`) REFERENCES `tb_cidadao` (`id_cidadao`),
  CONSTRAINT `solicitacao_ibfk_2` FOREIGN KEY (`procedimento_id`) REFERENCES `procedimento` (`idProcedimento`),
  CONSTRAINT `solicitacao_ibfk_3` FOREIGN KEY (`idMedico`) REFERENCES `medico` (`idMedico`)
) ENGINE=InnoDB AUTO_INCREMENT=5310 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.tb_cidadao
CREATE TABLE IF NOT EXISTS `tb_cidadao` (
  `id_cidadao` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dt_atualizado` varchar(500) DEFAULT NULL,
  `nu_cpf` varchar(11) DEFAULT NULL,
  `nu_cns` varchar(16) DEFAULT NULL,
  `no_cidadao` varchar(500) DEFAULT NULL,
  `no_cidadao_filtro` varchar(500) DEFAULT NULL,
  `dt_nascimento` date DEFAULT NULL,
  `dt_obito` date DEFAULT NULL,
  `no_mae` varchar(500) DEFAULT NULL,
  `no_mae_filtro` varchar(500) DEFAULT NULL,
  `no_pai` varchar(500) DEFAULT NULL,
  `st_faleceu` int(11) DEFAULT NULL,
  `ds_cep` varchar(8) DEFAULT NULL,
  `ds_complemento` varchar(255) DEFAULT NULL,
  `ds_logradouro` varchar(255) DEFAULT NULL,
  `nu_numero` varchar(20) DEFAULT NULL,
  `no_bairro` varchar(255) DEFAULT NULL,
  `no_bairro_filtro` varchar(255) DEFAULT NULL,
  `nu_telefone_residencial` varchar(255) DEFAULT NULL,
  `nu_telefone_celular` varchar(255) DEFAULT NULL,
  `nu_telefone_contato` varchar(255) DEFAULT NULL,
  `ds_email` varchar(255) DEFAULT NULL,
  `no_sexo` varchar(24) DEFAULT NULL,
  `agente_de_saude` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_cidadao`)
) ENGINE=InnoDB AUTO_INCREMENT=9993 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.transporte
CREATE TABLE IF NOT EXISTS `transporte` (
  `id_transporte` int(11) NOT NULL AUTO_INCREMENT,
  `cidadao_id` bigint(20) unsigned NOT NULL,
  `hora_transporte` time NOT NULL,
  `local_transporte` varchar(255) NOT NULL,
  `data_transporte` date NOT NULL,
  `id_acompanhante` bigint(20) unsigned DEFAULT NULL,
  `status_transporte` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_transporte`),
  KEY `cidadao_id` (`cidadao_id`),
  CONSTRAINT `transporte_ibfk_1` FOREIGN KEY (`cidadao_id`) REFERENCES `tb_cidadao` (`id_cidadao`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sushub.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `cpf` varchar(11) DEFAULT NULL,
  `cns` varchar(16) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `senha` varchar(500) DEFAULT NULL,
  `Estabelecimento` varchar(255) DEFAULT NULL,
  `CNES` varchar(50) DEFAULT NULL,
  `Contato` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
