
CREATE TABLE IF NOT EXISTS `usuario` (
    `id_usuario`      INT            NOT NULL AUTO_INCREMENT,
    `nome`            VARCHAR(60)    NOT NULL,
    `email`           VARCHAR(60)    NOT NULL,
    `senha_usuario`   VARCHAR(255)   NOT NULL,
    `usuario_banco`   VARCHAR(60)    NOT NULL,
    `servidor`        VARCHAR(60)    NOT NULL,
    `tipo_perfil`     ENUM('admin', 'usuario') NOT NULL,
    PRIMARY KEY (`id_usuario`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `projeto` (
`id_projeto` INT NOT NULL AUTO_INCREMENT,
`id_usuario` INT NOT NULL,
`fk_banco` INT NOT NULL,
`fk_estilo` INT NULL,
`nome_projeto` VARCHAR(60) NOT NULL,
`data_criacao` DATETIME NOT NULL,
`status_permanencia` INT NOT NULL,
`caminho_armazenamento` VARCHAR(255) NOT NULL,
`comentarios` TINYINT NOT NULL,
`views` TINYINT NOT NULL,
`ultimo_download` INT NULL,
PRIMARY KEY (`id_projeto`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;



-- Usuário administrador padrão para primeiro acesso
-- Senha: admin123
INSERT INTO `usuario` (`nome`, `email`, `senha_usuario`, `usuario_banco`, `servidor`, `tipo_perfil`)
VALUES ('Administrador', 'admin@devstudio.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'root', 'localhost', 'admin');
