-- Alteração da tabela grupos --
ALTER TABLE grupos ENGINE = InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE grupos MODIFY COLUMN cod_grupo integer PRIMARY KEY;

-- Alterações para a table peers
ALTER TABLE peers ENGINE = InnoDB DEFAULT CHARSET=utf8;

alter table peers modify column authenticate boolean not null default false;

alter table peers modify column `group` varchar(50) NOT NULL DEFAULT 'users';

alter table peers add FOREIGN KEY (`group`) REFERENCES groups(`name`) ON UPDATE CASCADE ON DELETE RESTRICT;

update peers set pickupgroup = null where pickupgroup=0 or pickupgroup="";

alter table peers modify column `pickupgroup` integer DEFAULT NULL;

alter table peers add FOREIGN KEY (`pickupgroup`) REFERENCES grupos(`cod_grupo`) ON UPDATE CASCADE ON DELETE SET NULL;

-- Alterações na tabela trunks

delete from trunks;
delete from peers where peer_type='T';
ALTER TABLE trunks ENGINE = InnoDB DEFAULT CHARSET=utf8;
alter table trunks add id_regex varchar(255) null;
alter table trunks modify column `type` varchar(200) default NULL;

-- Removendo tabelas antigas --
DROP TABLE agi_rules;