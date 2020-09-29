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













INSERT INTO `tb_recurso` (`id`, `nome`, `descricao`) VALUES
(40, 'alterarUsuarioClienteCliente', 'Permite ao cliente alterar um usuário');

INSERT INTO `tb_usuario_recurso` (`id`, `usuario_tipo`, `recurso`) VALUES
(91, 3, 40);

INSERT INTO `tb_recurso` (`id`, `nome`, `descricao`) VALUES
(44, 'usuarioDeletarCliente', 'Permite ao cliente deletar um usuário'),
(43, 'usuarioAlterarCliente', 'Permite ao cliente alterar um usuário'),
(42, 'usuarioNovoCliente', 'Permite ao cliente inserir usuários'),
(41, 'usuarioCliente', 'Listar usuários para cliente');

INSERT INTO `tb_usuario_recurso` (`id`, `usuario_tipo`, `recurso`) VALUES
(95, 3, 44),
(94, 3, 43),
(93, 3, 42),
(92, 3, 41);



INSERT INTO `tb_usuario_recurso` (`id`, `usuario_tipo`, `recurso`) VALUES
(97, 2, 37),
(96, 3, 37);








ALTER TABLE `tb_dashboard` ADD `pagina_power_bi` INT(2) NULL AFTER `report_id`;
