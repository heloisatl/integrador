SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

DROP SCHEMA IF EXISTS `mvc_creator`;

CREATE SCHEMA `mvc_creator`;
USE `mvc_creator`;

-- -----------------------------------------------------
-- Table usuario
-- -----------------------------------------------------
CREATE TABLE `usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(60) NOT NULL,
  `email` VARCHAR(60) NOT NULL,
  `senha_usuario` VARCHAR(255) NOT NULL,
  `tipo_perfil` ENUM('admin', 'usuario') NOT NULL,
  PRIMARY KEY (`id_usuario`)
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table banco
-- -----------------------------------------------------
CREATE TABLE `banco` (
  `id_banco` INT NOT NULL AUTO_INCREMENT,
  `fk_usuario` INT NOT NULL,
  `nome_banco` VARCHAR(60) NOT NULL,
  `usuario_banco` VARCHAR(60) NOT NULL,
  `senha_banco` VARCHAR(255) NULL,
  `host` VARCHAR(20) NOT NULL,
  `porta` VARCHAR(10) NOT NULL,

  PRIMARY KEY (`id_banco`),

  INDEX `idx_banco_usuario` (`fk_usuario`),

  CONSTRAINT `fk_banco_usuario`
    FOREIGN KEY (`fk_usuario`)
    REFERENCES `usuario` (`id_usuario`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table estilo
-- -----------------------------------------------------
CREATE TABLE `estilo` (
  `id_estilo` INT NOT NULL AUTO_INCREMENT,
  `css_customizado` MEDIUMTEXT NULL,
  `conteudo_principal` MEDIUMTEXT NULL,
  `cabecalho` VARCHAR(45) NULL,
  `links` TEXT NULL,
  `cor_primaria` VARCHAR(45) NOT NULL,
  `cor_secundaria` VARCHAR(45) NOT NULL,
  `tamanho_fonte` INT NOT NULL,

  PRIMARY KEY (`id_estilo`)
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table log
-- -----------------------------------------------------
CREATE TABLE `log` (
  `id_log` INT NOT NULL AUTO_INCREMENT,
  `fk_usuario` INT NOT NULL,
  `acao` VARCHAR(255) NOT NULL,
  `data` DATETIME NOT NULL,

  PRIMARY KEY (`id_log`),

  INDEX `idx_log_usuario` (`fk_usuario`),

  CONSTRAINT `fk_log_usuario`
    FOREIGN KEY (`fk_usuario`)
    REFERENCES `usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table projeto
-- -----------------------------------------------------
CREATE TABLE `projeto` (
  `id_projeto` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `fk_banco` INT NOT NULL,
  `fk_estilo` INT NULL,
  `ultimo_download` INT NULL,
  `nome_projeto` VARCHAR(60) NOT NULL,
  `data_criacao` DATETIME NOT NULL,
  `prazo_de_vida` INT NOT NULL,
  `caminho_armazenamento` VARCHAR(255) NOT NULL,
  `comentarios` TINYINT NOT NULL,
  `views` TINYINT NOT NULL,

  PRIMARY KEY (`id_projeto`),

  INDEX `idx_projeto_usuario` (`id_usuario`),
  INDEX `idx_projeto_banco` (`fk_banco`),
  INDEX `idx_projeto_estilo` (`fk_estilo`),
  INDEX `idx_projeto_log` (`ultimo_download`),

  UNIQUE INDEX `uq_projeto_estilo` (`fk_estilo`),

  CONSTRAINT `fk_projeto_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,

  CONSTRAINT `fk_projeto_banco`
    FOREIGN KEY (`fk_banco`)
    REFERENCES `banco` (`id_banco`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,

  CONSTRAINT `fk_projeto_estilo`
    FOREIGN KEY (`fk_estilo`)
    REFERENCES `estilo` (`id_estilo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,

  CONSTRAINT `fk_projeto_log`
    FOREIGN KEY (`ultimo_download`)
    REFERENCES `log` (`id_log`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table tabela
-- -----------------------------------------------------
CREATE TABLE `tabela` (
  `id_tabela` INT NOT NULL AUTO_INCREMENT,
  `fk_banco` INT NOT NULL,
  `nome_tabela` VARCHAR(60) NOT NULL,

  PRIMARY KEY (`id_tabela`),

  INDEX `idx_tabela_banco` (`fk_banco`),

  CONSTRAINT `fk_tabela_banco`
    FOREIGN KEY (`fk_banco`)
    REFERENCES `banco` (`id_banco`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table atributo
-- -----------------------------------------------------
CREATE TABLE `atributo` (
  `id_atributo` INT NOT NULL AUTO_INCREMENT,
  `fk_tabela` INT NOT NULL,
  `fk_atributo` INT DEFAULT NULL,
  `nome_atributo` VARCHAR(60) NOT NULL,
  `tipo` TINYTEXT NOT NULL,
  `PK` TINYINT NOT NULL,
  `NN` TINYINT NOT NULL,
  `AI` TINYINT NOT NULL,
  `UQ` TINYINT NOT NULL,

  PRIMARY KEY (`id_atributo`),

  INDEX `idx_atributo_tabela` (`fk_tabela`),
  INDEX `idx_atributo_atributo` (`fk_atributo`),

  CONSTRAINT `fk_atributo_tabela`
    FOREIGN KEY (`fk_tabela`)
    REFERENCES `tabela` (`id_tabela`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,

  CONSTRAINT `fk_atributo_atributo`
    FOREIGN KEY (`fk_atributo`)
    REFERENCES `atributo` (`id_atributo`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT
) ENGINE = InnoDB;


SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET SQL_MODE=@OLD_SQL_MODE;



-- Usuário administrador padrão para primeiro acesso
-- Senha: admin123
INSERT INTO `usuario` (`nome`, `email`, `senha_usuario`, `tipo_perfil`)
VALUES ('Administrador', 'admin@devstudio.com', '$2y$10$xB4P7YzuYdTGUTGgao9Qce6kvm7x/QuWmB1umLASZXE8myB1bmPhS', 'admin');
