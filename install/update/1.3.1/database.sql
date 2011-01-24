update groups set name='administrator' where name = 'admin';
ALTER TABLE  `trunks` ADD  `domain` VARCHAR( 250 ) NOT NULL;

CREATE TABLE IF NOT EXISTS ars_operadora (
    `id` integer primary key,
    `name` varchar(30) not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ars_estado (
    `cod` char(2) primary key,
    `name` varchar(30) not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ars_cidade (
    `id` integer primary key auto_increment,
    `name` varchar(30) not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ars_ddd (
    `cod` char(2),
    `estado` char(2),
    `cidade` integer,
    primary key (`cod`,`estado`,`cidade`),
    foreign key (`estado`) references ars_estado(`cod`) on update cascade on delete restrict,
    foreign key (`cidade`) references ars_cidade(`id`) on update cascade on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ars_prefixo (
    `prefixo` integer,
    `cidade` integer,
    `operadora` integer,
    primary key (`prefixo`,`cidade`,`operadora`),
    foreign key (`operadora`) references ars_operadora(`id`) on update cascade on delete restrict,
    foreign key (`cidade`) references ars_cidade(`id`) on update cascade on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

