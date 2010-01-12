CREATE TABLE IF NOT EXISTS `contacts_group` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Grupo incial dos contatos

INSERT INTO `contacts_group` VALUES (1, 'Default');

ALTER TABLE contacts_names ADD `group` integer NOT NULL REFERENCES contacts_group(id);
