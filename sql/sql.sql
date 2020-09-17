INSERT INTO `tb_usuario_recurso` (`id`, `usuario_tipo`, `recurso`) VALUES
(86, 4, 28),
(85, 3, 28);


INSERT INTO `tb_recurso` (`id`, `nome`, `descricao`) VALUES
(39, 'salvarTemplate', 'Salvar template selecionado (dark/light) ');

INSERT INTO `tb_usuario_recurso` (`id`, `usuario_tipo`, `recurso`) VALUES
(90, 4, 39),
(89, 3, 39),
(88, 2, 39),
(87, 1, 39);

ALTER TABLE `tb_usuario` ADD `template` VARCHAR(10) NOT NULL DEFAULT 'escuro' AFTER `token_ativacao`;

ALTER TABLE `tb_invoice` CHANGE `arquivo` `arquivo` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

