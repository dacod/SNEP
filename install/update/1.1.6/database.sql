CREATE TABLE IF NOT EXISTS expr_alias (
    `aliasid` INTEGER PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS expr_alias_expression (
    `aliasid` INTEGER NOT NULL,
    `expression` VARCHAR(200) NOT NULL,
    CONSTRAINT fk_expression_alias FOREIGN KEY (`aliasid`) REFERENCES expr_alias(`aliasid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `time_history`;
CREATE TABLE IF NOT EXISTS `time_history` (
  `id` integer NOT NULL auto_increment,
  `owner` integer NOT NULL,
  `year` integer NOT NULL,
  `month` integer,
  `day` integer,
  `used` integer NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `owner_type` char(1) NOT NULL default 'T',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
