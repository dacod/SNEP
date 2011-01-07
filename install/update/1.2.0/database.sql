INSERT INTO `rotinas` VALUES (114, 'Diagnóstico do Sistema');
INSERT INTO `permissoes` VALUES (114, 1, 'S');

/* Criação do campo para senhas dos ramais. Independentes da senha do SIP/IAX2*/
ALTER TABLE `peers` ADD `password` VARCHAR(12) NOT NULL;
UPDATE `peers` SET `password`=`secret`;

INSERT INTO `rotinas` VALUES (105,'Erros Links Khomp');

/* Tabela de Alertas de filas */
CREATE TABLE `alertas` (
  `recurso` VARCHAR( 20 ) NOT NULL ,
  `tipo` VARCHAR( 10 ) NOT NULL ,
  `tme` INT( 10 ) NOT NULL ,
  `sla` INT( 10 ) NOT NULL ,
  `item` VARCHAR( 20 ) NOT NULL ,
  `alerta` VARCHAR( 255 ) NOT NULL ,
  `destino` VARCHAR( 255 ) NOT NULL ,
  `ativo` TINYINT( 1 ) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
