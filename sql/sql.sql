INSERT INTO `tb_recurso` (`id`, `nome`, `descricao`) VALUES
(45, 'salvarMenuHidden', 'Salvar o menu hidden');

INSERT INTO `tb_usuario_recurso` (`id`, `usuario_tipo`, `recurso`) VALUES
(101, 4, 45),
(100, 3, 45),
(99, 2, 45),
(98, 1, 45);
ALTER TABLE `tb_usuario` ADD `menu_hidden` CHAR(1) NULL DEFAULT 'N' AFTER `template`;