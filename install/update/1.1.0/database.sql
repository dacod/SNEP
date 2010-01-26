CREATE TABLE IF NOT EXISTS `contacts_group` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



-- Migrando contatos e criando chave estrangeira

INSERT INTO `contacts_group` VALUES (1, 'Default');

ALTER TABLE contacts_names ADD `group` integer NOT NULL;

UPDATE contacts_names SET `group` = 1;

ALTER TABLE contacts_names ADD CONSTRAINT contacts_group_fk FOREIGN KEY (`group`) REFERENCES contacts_group(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
