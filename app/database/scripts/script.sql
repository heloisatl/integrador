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
  `nome_arquivo` VARCHAR(60) NULL DEFAULT 'index.html',
  `texto_cabecalho` VARCHAR(100) NULL DEFAULT 'Meu Projeto',
  `titulo_aba` VARCHAR(100) NULL DEFAULT 'Meu Projeto',
  `cor_fundo` VARCHAR(20) NOT NULL DEFAULT '#ffffff',
  `cor_fonte` VARCHAR(20) NOT NULL DEFAULT '#000000',
  `tamanho_fonte` VARCHAR(20) NOT NULL DEFAULT 'medium',
  `tipo_layout` VARCHAR(10) NOT NULL DEFAULT '1',
  `alinhamento_horizontal` VARCHAR(20) NOT NULL DEFAULT 'flex-start',
  `alinhamento_vertical` VARCHAR(20) NOT NULL DEFAULT 'flex-start',
  `margem_pagina` INT NOT NULL DEFAULT 24,
  `preenchimento_pagina` INT NOT NULL DEFAULT 24,
  `cabecalho_fixo` TINYINT(1) NOT NULL DEFAULT 0,
  `url_fonte` VARCHAR(255) NULL,
  `peso_fonte` VARCHAR(10) NOT NULL DEFAULT '400',
  `altura_linha` DECIMAL(3,1) NOT NULL DEFAULT 1.5,
  `raio_borda` INT NOT NULL DEFAULT 8,
  `tipo_sombra` VARCHAR(20) NOT NULL DEFAULT 'none',
  `imagem_fundo` VARCHAR(255) NULL,
  `cor_gradiente_inicial` VARCHAR(20) NOT NULL DEFAULT '#ffffff',
  `cor_gradiente_final` VARCHAR(20) NOT NULL DEFAULT '#e8e8ff',
  `opacidade_fundo` INT NOT NULL DEFAULT 100,
  `sem_navegacao` TINYINT(1) NOT NULL DEFAULT 0,
  `cor_links` VARCHAR(20) NOT NULL DEFAULT '#0000ff',
  `estilo_links` TINYINT(1) NOT NULL DEFAULT 1,
  `links` TEXT NULL,
  `texto_pagina` MEDIUMTEXT NULL,
  `nome_imagem` VARCHAR(100) NULL,
  `url_imagem` VARCHAR(255) NULL,
  `largura_imagem` INT NULL,
  `alt_imagem` VARCHAR(100) NULL,
  `alt_text_imagem` VARCHAR(100) NULL,
  `incluir_lista` TINYINT(1) NOT NULL DEFAULT 0,
  `incluir_tabela` TINYINT(1) NOT NULL DEFAULT 0,
  `incluir_rodape` TINYINT(1) NOT NULL DEFAULT 0,
  `incluir_card` TINYINT(1) NOT NULL DEFAULT 0,
  `incluir_formulario` TINYINT(1) NOT NULL DEFAULT 0,
  `texto_botao` VARCHAR(60) NOT NULL DEFAULT 'Saiba mais',
  `link_botao` VARCHAR(255) NOT NULL DEFAULT 'https://exemplo.com',
  `cor_botao` VARCHAR(20) NOT NULL DEFAULT '#5b6af0',
  `cor_botao_hover` VARCHAR(20) NOT NULL DEFAULT '#4a59df',
  `ordem_elementos` VARCHAR(50) NOT NULL DEFAULT 'texto-imagem-lista-tabela',
  `css_customizado` MEDIUMTEXT NULL,
  `html_customizado` MEDIUMTEXT NULL,

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


-- -----------------------------------------------------
-- SCRIPT DE UPDATE / MIGRAÇÃO DA TABELA estilo (PageMaker)
-- Execute este bloco caso você já possua um banco de dados
-- criado anteriormente e deseje atualizá-lo sem recriar o schema:
-- -----------------------------------------------------
-- ALTER TABLE `estilo`
--   ADD COLUMN IF NOT EXISTS `nome_arquivo` VARCHAR(60) NULL DEFAULT 'index.html',
--   ADD COLUMN IF NOT EXISTS `texto_cabecalho` VARCHAR(100) NULL DEFAULT 'Meu Projeto',
--   ADD COLUMN IF NOT EXISTS `titulo_aba` VARCHAR(100) NULL DEFAULT 'Meu Projeto',
--   ADD COLUMN IF NOT EXISTS `cor_fundo` VARCHAR(20) NOT NULL DEFAULT '#ffffff',
--   ADD COLUMN IF NOT EXISTS `cor_fonte` VARCHAR(20) NOT NULL DEFAULT '#000000',
--   MODIFY COLUMN `tamanho_fonte` VARCHAR(20) NOT NULL DEFAULT 'medium',
--   ADD COLUMN IF NOT EXISTS `tipo_layout` VARCHAR(10) NOT NULL DEFAULT '1',
--   ADD COLUMN IF NOT EXISTS `alinhamento_horizontal` VARCHAR(20) NOT NULL DEFAULT 'flex-start',
--   ADD COLUMN IF NOT EXISTS `alinhamento_vertical` VARCHAR(20) NOT NULL DEFAULT 'flex-start',
--   ADD COLUMN IF NOT EXISTS `margem_pagina` INT NOT NULL DEFAULT 24,
--   ADD COLUMN IF NOT EXISTS `preenchimento_pagina` INT NOT NULL DEFAULT 24,
--   ADD COLUMN IF NOT EXISTS `cabecalho_fixo` TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN IF NOT EXISTS `url_fonte` VARCHAR(255) NULL,
--   ADD COLUMN IF NOT EXISTS `peso_fonte` VARCHAR(10) NOT NULL DEFAULT '400',
--   ADD COLUMN IF NOT EXISTS `altura_linha` DECIMAL(3,1) NOT NULL DEFAULT 1.5,
--   ADD COLUMN IF NOT EXISTS `raio_borda` INT NOT NULL DEFAULT 8,
--   ADD COLUMN IF NOT EXISTS `tipo_sombra` VARCHAR(20) NOT NULL DEFAULT 'none',
--   ADD COLUMN IF NOT EXISTS `imagem_fundo` VARCHAR(255) NULL,
--   ADD COLUMN IF NOT EXISTS `cor_gradiente_inicial` VARCHAR(20) NOT NULL DEFAULT '#ffffff',
--   ADD COLUMN IF NOT EXISTS `cor_gradiente_final` VARCHAR(20) NOT NULL DEFAULT '#e8e8ff',
--   ADD COLUMN IF NOT EXISTS `opacidade_fundo` INT NOT NULL DEFAULT 100,
--   ADD COLUMN IF NOT EXISTS `sem_navegacao` TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN IF NOT EXISTS `cor_links` VARCHAR(20) NOT NULL DEFAULT '#0000ff',
--   ADD COLUMN IF NOT EXISTS `estilo_links` TINYINT(1) NOT NULL DEFAULT 1,
--   ADD COLUMN IF NOT EXISTS `texto_pagina` MEDIUMTEXT NULL,
--   ADD COLUMN IF NOT EXISTS `nome_imagem` VARCHAR(100) NULL,
--   ADD COLUMN IF NOT EXISTS `url_imagem` VARCHAR(255) NULL,
--   ADD COLUMN IF NOT EXISTS `largura_imagem` INT NULL,
--   ADD COLUMN IF NOT EXISTS `alt_imagem` VARCHAR(100) NULL,
--   ADD COLUMN IF NOT EXISTS `alt_text_imagem` VARCHAR(100) NULL,
--   ADD COLUMN IF NOT EXISTS `incluir_lista` TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN IF NOT EXISTS `incluir_tabela` TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN IF NOT EXISTS `incluir_rodape` TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN IF NOT EXISTS `incluir_card` TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN IF NOT EXISTS `incluir_formulario` TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN IF NOT EXISTS `texto_botao` VARCHAR(60) NOT NULL DEFAULT 'Saiba mais',
--   ADD COLUMN IF NOT EXISTS `link_botao` VARCHAR(255) NOT NULL DEFAULT 'https://exemplo.com',
--   ADD COLUMN IF NOT EXISTS `cor_botao` VARCHAR(20) NOT NULL DEFAULT '#5b6af0',
--   ADD COLUMN IF NOT EXISTS `cor_botao_hover` VARCHAR(20) NOT NULL DEFAULT '#4a59df',
--   ADD COLUMN IF NOT EXISTS `ordem_elementos` VARCHAR(50) NOT NULL DEFAULT 'texto-imagem-lista-tabela',
--   ADD COLUMN IF NOT EXISTS `html_customizado` MEDIUMTEXT NULL;

