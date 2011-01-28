/*
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */

CREATE TABLE `agentes` (
  `agentid` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `agentpassword` varchar(50) NOT NULL default '',
  `horario` int(11) NOT NULL default '0',
  PRIMARY KEY  (`agentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE expr_alias (
    `aliasid` INTEGER PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE expr_alias_expression (
    `aliasid` INTEGER NOT NULL,
    `expression` VARCHAR(200) NOT NULL,
    FOREIGN KEY (`aliasid`) REFERENCES expr_alias(`aliasid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE regras_negocio (
  id integer PRIMARY KEY auto_increment,
  prio integer NOT NULL default 0,
  `desc` varchar(255) default NULL,
  origem text NOT NULL,
  destino text NOT NULL,
  validade text NOT NULL,
  diasDaSemana varchar(30) NOT NULL DEFAULT "sun,mon,tue,wed,thu,fri,sat",
  record boolean NOT NULL default false,
  ativa boolean NOT NULL default true
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE regras_negocio_actions (
  regra_id integer NOT NULL,
  prio integer NOT NULL,
  `action` varchar(250) NOT NULL,
  PRIMARY KEY(regra_id, prio),
  FOREIGN KEY (regra_id) REFERENCES regras_negocio(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE regras_negocio_actions_config (
  regra_id integer NOT NULL,
  prio integer NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY(regra_id,prio,`key`),
  FOREIGN KEY (regra_id, prio) REFERENCES regras_negocio_actions (regra_id, prio) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `registry` (
    `context` VARCHAR(50),
    `key` VARCHAR(30),
    `value` VARCHAR(250),
    PRIMARY KEY (`context`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ccustos` (
  `codigo` char(7) NOT NULL,
  `tipo` char(1) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `descricao` varchar(250) default NULL,
  PRIMARY KEY  (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cdr` (
  `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `clid` varchar(80) NOT NULL default '',
  `src` varchar(80) NOT NULL default '',
  `dst` varchar(80) NOT NULL default '',
  `dcontext` varchar(80) NOT NULL default '',
  `channel` varchar(80) NOT NULL default '',
  `dstchannel` varchar(80) NOT NULL default '',
  `lastapp` varchar(80) NOT NULL default '',
  `lastdata` varchar(80) NOT NULL default '',
  `duration` int(11) NOT NULL default '0',
  `billsec` int(11) NOT NULL default '0',
  `disposition` varchar(45) NOT NULL default '',
  `amaflags` int(20) NOT NULL default '0',
  `accountcode` varchar(20) NOT NULL default '',
  `uniqueid` varchar(32) NOT NULL default '',
  `userfield` varchar(255) NOT NULL default '',
  KEY `calldate` (`calldate`),
  KEY `dst` (`dst`),
  KEY `accountcode` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cdr_compactado` (
  `userfield` varchar(255) default NULL,
  `arquivo` varchar(255) default NULL,
  `data` date default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `event` longtext,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `grupos` (
  `cod_grupo` integer NOT NULL auto_increment,
  `nome` varchar(30) NOT NULL,
  UNIQUE KEY `cod_grupo` (`cod_grupo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `oper_ccustos` (
  `operadora` int(11) NOT NULL,
  `ccustos` char(7) NOT NULL,
  PRIMARY KEY  (`operadora`,`ccustos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `oper_contas` (
  `operadora` int(11) NOT NULL,
  `conta` int(11) NOT NULL,
  PRIMARY KEY  (`operadora`,`conta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `operadoras` (
  `codigo` bigint(20) unsigned NOT NULL auto_increment,
  `nome` varchar(50) NOT NULL,
  `tpm` int(11) default '0',
  `tdm` int(11) default '0',
  `tbf` float default '0',
  `tbc` float default '0',
  `vpf` float NOT NULL default '0',
  `vpc` float NOT NULL default '0',
  PRIMARY KEY  (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE groups (
    name varchar(50) PRIMARY KEY,
    inherit varchar(50),
    FOREIGN KEY (inherit) REFERENCES groups(name) ON UPDATE CASCADE
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;;

CREATE TABLE `peers` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `password` VARCHAR(12) NOT NULL,
  `accountcode` varchar(20) default NULL,
  `amaflags` varchar(13) default NULL,
  `callgroup` varchar(10) default NULL,
  `callerid` varchar(80) default NULL,
  `canreinvite` char(3) default 'no',
  `context` varchar(80) default NULL,
  `defaultip` varchar(15) default NULL,
  `dtmfmode` varchar(7) default NULL,
  `fromuser` varchar(80) default NULL,
  `fromdomain` varchar(80) default NULL,
  `fullcontact` varchar(80) default NULL,
  `host` varchar(31) NOT NULL default '',
  `insecure` varchar(4) default NULL,
  `language` char(2) default 'br',
  `mailbox` varchar(50) default NULL,
  `md5secret` varchar(80) default '',
  `nat` varchar(5) NOT NULL default 'no',
  `deny` varchar(95) default NULL,
  `permit` varchar(95) default NULL,
  `mask` varchar(95) default NULL,
  `pickupgroup` integer default NULL,
  `port` varchar(5) NOT NULL default '',
  `qualify` char(5) default NULL,
  `restrictcid` char(1) default NULL,
  `rtptimeout` char(3) default NULL,
  `rtpholdtimeout` char(3) default NULL,
  `secret` varchar(80) default NULL,
  `type` varchar(6) NOT NULL default 'friend',
  `username` varchar(80) NOT NULL default '',
  `disallow` varchar(100) default 'all',
  `allow` varchar(100) default 'ulaw;alaw;gsm',
  `musiconhold` varchar(100) default NULL,
  `regseconds` int(11) NOT NULL default '0',
  `ipaddr` varchar(15) NOT NULL default '',
  `regexten` varchar(80) NOT NULL default '',
  `cancallforward` char(3) default 'yes',
  `setvar` varchar(100) NOT NULL default '',
  `vinculo` varchar(255) NOT NULL default '',
  `email` varchar(255) default NULL,
  `canal` varchar(255) default NULL,
  `call-limit` varchar(4) default NULL,
  `incominglimit` varchar(4) default NULL,
  `outgoinglimit` varchar(4) default NULL,
  `usa_vc` varchar(4) NOT NULL default 'no',
  `peer_type` char(1) NOT NULL default 'R',
  `credits` int(11) default NULL,
  `authenticate` boolean not null default false,
  `subscribecontext` varchar(40) default NULL,
  `trunk` varchar(3) NOT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'users',
  `time_total` int(11) default NULL,
  `time_chargeby` char(1) default NULL,
  `regserver` int(250) default NULL,
  `dnd` BOOL NOT NULL DEFAULT '0',
  `sigame` VARCHAR( 20 ) NULL ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `name_2` (`name`),
  FOREIGN KEY (`group`) REFERENCES groups(`name`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`pickupgroup`) REFERENCES grupos(`cod_grupo`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `services_log` (
  `date` datetime NOT NULL,
  `peer` varchar(80) NOT NULL,
  `service` varchar(50) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `permissoes` (
  `cod_rotina` int(11) NOT NULL default '0',
  `cod_usuario` int(11) NOT NULL default '0',
  `permissao` char(1) NOT NULL default 'S',
  PRIMARY KEY  (`cod_rotina`,`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `queue_log` (
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `callid` varchar(20) NOT NULL default '',
  `queuename` varchar(20) NOT NULL default '',
  `agent` varchar(20) NOT NULL default '',
  `event` varchar(20) NOT NULL default '',
  `arg1` varchar(100) NOT NULL default '',
  `arg2` varchar(100) NOT NULL default '',
  `arg3` varchar(100) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `queue_members` (
  `uniqueid` int(10) unsigned NOT NULL auto_increment,
  `membername` varchar(40) default NULL,
  `queue_name` varchar(128) default NULL,
  `interface` varchar(128) default NULL,
  `penalty` int(11) default NULL,
  `paused` tinyint(1) default NULL,
  PRIMARY KEY  (`uniqueid`),
  UNIQUE KEY `queue_interface` (`queue_name`,`interface`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `queue_peers` (
  `fila` varchar(80) NOT NULL default '',
  `ramal` int(11) NOT NULL,
  PRIMARY KEY  (`ramal`,`fila`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `queues` (
  `name` varchar(128) NOT NULL,
  `musiconhold` varchar(128) default NULL,
  `announce` varchar(128) default NULL,
  `context` varchar(128) default NULL,
  `timeout` int(11) default NULL,
  `monitor_type` tinyint(1) default NULL,
  `monitor_format` varchar(128) default NULL,
  `queue_youarenext` varchar(128) default NULL,
  `queue_thereare` varchar(128) default NULL,
  `queue_callswaiting` varchar(128) default NULL,
  `queue_holdtime` varchar(128) default NULL,
  `queue_minutes` varchar(128) default NULL,
  `queue_seconds` varchar(128) default NULL,
  `queue_lessthan` varchar(128) default NULL,
  `queue_thankyou` varchar(128) default NULL,
  `queue_reporthold` varchar(128) default NULL,
  `announce_frequency` int(11) default NULL,
  `announce_round_seconds` int(11) default NULL,
  `announce_holdtime` varchar(128) default NULL,
  `retry` int(11) default NULL,
  `wrapuptime` int(11) default NULL,
  `maxlen` int(11) default NULL,
  `servicelevel` int(11) default NULL,
  `strategy` varchar(128) default NULL,
  `joinempty` varchar(128) default NULL,
  `leavewhenempty` varchar(128) default NULL,
  `eventmemberstatus` tinyint(1) default NULL,
  `eventwhencalled` tinyint(1) default NULL,
  `reportholdtime` tinyint(1) default NULL,
  `memberdelay` int(11) default NULL,
  `weight` int(11) default NULL,
  `timeoutrestart` tinyint(1) default NULL,
  `periodic_announce` varchar(50) default NULL,
  `periodic_announce_frequency` int(11) default NULL,
  `max_call_queue` int(11) default '0',
  `max_time_call` int(11) default '0',
  `alert_mail` varchar(80) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `queues_agent` (
  `agent_id` int(11) NOT NULL,
  `queue` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `rotinas` (
  `cod_rotina` int(11) NOT NULL default '0',
  `desc_rotina` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`cod_rotina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sounds` (
  `arquivo` varchar(50) NOT NULL,
  `descricao` varchar(80) NOT NULL,
  `data` datetime default NULL,
  `tipo` char(3) NOT NULL default 'AST',
  `secao` varchar(30) NOT NULL,
  PRIMARY KEY  (`arquivo`,`tipo`,`secao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tarifas` (
  `operadora` int(11) NOT NULL default '0',
  `ddi` smallint(6) NOT NULL default '0',
  `pais` varchar(30) NOT NULL default '',
  `ddd` smallint(6) NOT NULL default '0',
  `cidade` varchar(30) NOT NULL default '',
  `estado` char(2) NOT NULL default '',
  `prefixo` varchar(6) NOT NULL default '',
  `codigo` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`codigo`),
  UNIQUE KEY `operadora` (`operadora`,`ddi`,`ddd`,`prefixo`,`cidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tarifas_valores` (
  `codigo` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `vcel` float NOT NULL default '0',
  `vfix` float NOT NULL default '0',
  `vpf` float NOT NULL default '0',
  `vpc` float NOT NULL default '0',
  PRIMARY KEY  (`codigo`,`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `trunks` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `accountcode` varchar(20) default NULL,
  `callerid` varchar(80) default NULL,
  `context` varchar(80) default NULL,
  `dtmfmode` varchar(7) default NULL,
  `insecure` varchar(4) default NULL,
  `secret` varchar(80) default NULL,
  `username` varchar(80) default NULL,
  `allow` varchar(100) default 'g729;ilbc;gsm;ulaw;alaw',
  `channel` varchar(255) default NULL,
  `type` varchar(200) default NULL,
  `trunktype` char(1) NOT NULL,
  `host` varchar(31) default NULL,
  `trunk_redund` int(11) default NULL,
  `time_total` int(11) default NULL,
  `time_chargeby` char(1) default NULL,
  `dialmethod` VARCHAR(6) NOT NULL DEFAULT 'NORMAL',
  `id_regex` VARCHAR(255) NULL,
  `map_extensions` BOOLEAN DEFAULT FALSE,
  `reverse_auth` BOOLEAN DEFAULT TRUE,
  `dtmf_dial` BOOLEAN NOT NULL DEFAULT FALSE,
  `dtmf_dial_number` VARCHAR(50) DEFAULT NULL,
  `domain` VARCHAR( 250 ) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `vinculos` (
  `ramal` varchar(80) default NULL,
  `cod_usuario` varchar(80) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `voicemail_messages` (
  `id` int(11) NOT NULL auto_increment,
  `msgnum` int(11) NOT NULL default '0',
  `dir` varchar(80) default '',
  `context` varchar(80) default '',
  `macrocontext` varchar(80) default '',
  `callerid` varchar(40) default '',
  `origtime` varchar(40) default '',
  `duration` varchar(20) default '',
  `mailboxuser` varchar(80) default '',
  `mailboxcontext` varchar(80) default '',
  `recording` longblob,
  PRIMARY KEY  (`id`),
  KEY `dir` (`dir`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `voicemail_users` (
  `uniqueid` int(11) NOT NULL auto_increment,
  `customer_id` varchar(11) NOT NULL default '0',
  `context` varchar(50) default '',
  `mailbox` varchar(11) NOT NULL default '0',
  `password` varchar(5) NOT NULL default '0',
  `fullname` varchar(150) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `pager` varchar(50) default '',
  `tz` varchar(10) NOT NULL default 'central24',
  `attach` varchar(4) NOT NULL default 'yes',
  `saycid` varchar(4) NOT NULL default 'yes',
  `dialout` varchar(10) default '',
  `callback` varchar(10) default '',
  `review` varchar(4) NOT NULL default 'no',
  `operator` varchar(4) NOT NULL default 'no',
  `envelope` varchar(4) NOT NULL default 'no',
  `sayduration` varchar(4) NOT NULL default 'no',
  `saydurationm` tinyint(4) NOT NULL default '1',
  `sendvoicemail` varchar(4) NOT NULL default 'no',
  `delete` varchar(4) NOT NULL default 'no',
  `nextaftercmd` varchar(4) NOT NULL default 'yes',
  `forcename` varchar(4) NOT NULL default 'no',
  `forcegreetings` varchar(4) NOT NULL default 'no',
  `hidefromdir` varchar(4) NOT NULL default 'yes',
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`uniqueid`),
  KEY `mailbox_context` (`mailbox`,`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lista_abandono` (
  `time` int(20) NOT NULL,
  `data` varchar(150) NOT NULL,
  `fila` varchar(150) NOT NULL,
  `canal` varchar(150) NOT NULL,
  `evento` varchar(150) NOT NULL,
  `par1` varchar(30) NOT NULL,
  `par2` varchar(30) NOT NULL,
  `par3` varchar(30) NOT NULL,
  `date` datetime NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `permissoes_vinculos` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `id_peer` VARCHAR( 100 ) NOT NULL ,
  `tipo` CHAR( 1 ) NOT NULL,
  `id_vinculado` VARCHAR( 100 ) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE ars_operadora (
    `id` integer primary key auto_increment,
    `name` varchar(30) not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ars_estado (
    `cod` char(2) primary key,
    `name` varchar(30) not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ars_cidade (
    `id` integer primary key auto_increment,
    `name` varchar(30) not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ars_ddd (
    `cod` char(2),
    `estado` char(2),
    `cidade` integer,
    primary key (`cod`,`estado`,`cidade`),
    foreign key (`estado`) references ars_estado(`cod`) on update cascade on delete restrict,
    foreign key (`cidade`) references ars_cidade(`id`) on update cascade on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ars_prefixo (
    `prefixo` integer,
    `cidade` integer,
    `operadora` integer,
    primary key (`prefixo`,`cidade`,`operadora`),
    foreign key (`operadora`) references ars_operadora(`id`) on update cascade on delete restrict,
    foreign key (`cidade`) references ars_cidade(`id`) on update cascade on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Estrutura da tabela `contacts_group`
--

DROP TABLE IF EXISTS `contacts_group`;
CREATE TABLE IF NOT EXISTS `contacts_group` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Estrutura da tabela `contacts_names`
--
DROP TABLE IF EXISTS `contacts_names`;
CREATE TABLE IF NOT EXISTS `contacts_names` (
  `id` char(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(2) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `phone_1` varchar(15) NOT NULL,
  `cell_1` varchar(15) NOT NULL,
  `group` integer NOT NULL,
  CONSTRAINT contacts_group_fk FOREIGN KEY (`group`) REFERENCES contacts_group(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

