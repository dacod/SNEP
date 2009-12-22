-- MySQL dump 10.11
--
-- Host: localhost    Database: snep
-- ------------------------------------------------------
-- Server version	5.0.45-Debian_1ubuntu3.3-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


CREATE USER 'snep'@'localhost' IDENTIFIED BY 'sneppass';

GRANT ALL PRIVILEGES ON * . * TO 'snep'@'localhost' IDENTIFIED BY 'sneppass' WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

CREATE DATABASE IF NOT EXISTS `snep25` ; 

GRANT ALL PRIVILEGES ON `snep25` . * TO 'snep'@'localhost'; 

FLUSH PRIVILEGES ; 

CREATE DATABASE IF NOT EXISTS `snep25` ; 
GRANT ALL PRIVILEGES ON `snep25` . * TO 'snep'@'localhost'; 
USE snep25;

--
-- Table structure for table `agentes`
--

DROP TABLE IF EXISTS `agentes`;
CREATE TABLE `agentes` (
  `agentid` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `agentpassword` varchar(50) NOT NULL default '',
  `horario` int(11) NOT NULL default '0',
  PRIMARY KEY  (`agentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `regras_negocio`
--

DROP TABLE IF EXISTS `regras_negocio`;
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

DROP TABLE IF EXISTS `regras_negocio_actions`;
CREATE TABLE regras_negocio_actions (
  regra_id integer NOT NULL,
  prio integer NOT NULL,
  `action` varchar(250) NOT NULL,
  PRIMARY KEY(regra_id, prio),
  FOREIGN KEY (regra_id) REFERENCES regras_negocio(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `regras_negocio_actions_config`;
CREATE TABLE regras_negocio_actions_config (
  regra_id integer NOT NULL,
  prio integer NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY(regra_id,prio,`key`),
  FOREIGN KEY (regra_id, prio) REFERENCES regras_negocio_actions (regra_id, prio) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `ccustos`
--

DROP TABLE IF EXISTS `ccustos`;
CREATE TABLE `ccustos` (
  `codigo` char(7) NOT NULL,
  `tipo` char(1) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `descricao` varchar(250) default NULL,
  PRIMARY KEY  (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ccustos`
--

LOCK TABLES `ccustos` WRITE;
/*!40000 ALTER TABLE `ccustos` DISABLE KEYS */;
INSERT INTO `ccustos` VALUES ('5','O','FUNCIONALIDADES','Funcionalidades do Sistema'),('5.01','O','Conferencias','Ligacoes para Salas de de Conferencias'),('2','S','SAIDAS','Ligacoes de Saida'),('1','E','ENTRADAS','Ligacoes de Entrada'),('5.02','O','Logon de Agentes','Logon de Agentes na Fila (*01)'),('5.03','O','Logoff de Agentes','Logoff de Agentes na Fila (*02)'),('5.04','O','Pausa de Agentes - Inicio','Pausa de Agente na Fila (*03)'),('5.05','O','Pausa de Agente - Fim','Pausa de Agente na Fila - Fim (*04)'),('5.10','O','Emergencias','Ligacoes para telefones de Emergencia (190, 192, 191, etc)'),('9','O','Internas','Ligacoes Internas entre Ramais');
/*!40000 ALTER TABLE `ccustos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cdr`
--

DROP TABLE IF EXISTS `cdr`;
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

--
-- Table structure for table `cdr_compactado`
--

DROP TABLE IF EXISTS `cdr_compactado`;
CREATE TABLE `cdr_compactado` (
  `userfield` varchar(255) default NULL,
  `arquivo` varchar(255) default NULL,
  `data` date default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `cnl`
--

DROP TABLE IF EXISTS `cnl`;
CREATE TABLE `cnl` (
  `uf` char(2) NOT NULL default '',
  `municipio` varchar(50) default NULL,
  `prefixo` varchar(7) NOT NULL default '',
  `operadora` varchar(30) default NULL,
  PRIMARY KEY  (`uf`,`prefixo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `event` longtext,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `grupos`
--

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE `grupos` (
  `cod_grupo` integer NOT NULL auto_increment,
  `nome` varchar(30) NOT NULL,
  UNIQUE KEY `cod_grupo` (`cod_grupo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Table structure for table `oper_ccustos`
--

DROP TABLE IF EXISTS `oper_ccustos`;
CREATE TABLE `oper_ccustos` (
  `operadora` int(11) NOT NULL,
  `ccustos` char(7) NOT NULL,
  PRIMARY KEY  (`operadora`,`ccustos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `oper_contas`
--

DROP TABLE IF EXISTS `oper_contas`;
CREATE TABLE `oper_contas` (
  `operadora` int(11) NOT NULL,
  `conta` int(11) NOT NULL,
  PRIMARY KEY  (`operadora`,`conta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `operadoras`
--

DROP TABLE IF EXISTS `operadoras`;
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


--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE groups (
    name varchar(50) PRIMARY KEY,
    inherit varchar(50),
    FOREIGN KEY (inherit) REFERENCES groups(name) ON UPDATE CASCADE
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;;

-- Grupos padrões do sistema
INSERT INTO groups VALUES ('all',null);
INSERT INTO groups VALUES ('admin','all');
INSERT INTO groups VALUES ('users','all');

--
-- Table structure for table `peers`
--

DROP TABLE IF EXISTS `peers`;
CREATE TABLE `peers` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
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
  `vinculo` varchar(100) NOT NULL default '',
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

--
-- Dumping data for table `peers`
--

LOCK TABLES `peers` WRITE;
/*!40000 ALTER TABLE `peers` DISABLE KEYS */;
INSERT INTO `peers` VALUES (1,'admin','','','','Administrator <0>','no','','','','admin','','','dynamic','','br','admin','','','','','','','','','','','','admin123','friend','admin','',';;;;','',0,'','','','','','',';;;','0','0','0','','R',NULL,'','','no','admin',null,null,null, false, null);
/*!40000 ALTER TABLE `peers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Estrutura da tabela `services_log`
--

CREATE TABLE IF NOT EXISTS `services_log` (
  `date` datetime NOT NULL,
  `peer` varchar(80) NOT NULL,
  `service` varchar(50) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permissoes`
--

DROP TABLE IF EXISTS `permissoes`;
CREATE TABLE `permissoes` (
  `cod_rotina` int(11) NOT NULL default '0',
  `cod_usuario` int(11) NOT NULL default '0',
  `permissao` char(1) NOT NULL default 'S',
  PRIMARY KEY  (`cod_rotina`,`cod_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permissoes`
--

LOCK TABLES `permissoes` WRITE;
/*!40000 ALTER TABLE `permissoes` DISABLE KEYS */;
INSERT INTO `permissoes` VALUES (1,1,'S'),(10,1,'S'),(11,1,'S'),(12,1,'S'),(13,1,'S'),(14,1,'S'),(15,1,'S'),(16,1,'S'),(20,1,'S'),(21,1,'S'),(22,1,'S'),(23,1,'S'),(24,1,'S'),(40,1,'S'),(41,1,'S'),(42,1,'S'),(43,1,'S'),(60,1,'S'),(61,1,'S'),(62,1,'S'),(63,1,'S'),(99,1,'S'),(81,1,'S'),(25,1,'S'),(26,1,'S'),(17,1,'S'),(27,1,'S'),(28,1,'S'),(18,1,'S'),(19,1,'S'),(30,1,'S'),(31,1,'S'),(33,1,'S'),(34,1,'S'),(32,1,'S'),(35,1,'S'),(37,1,'S'),(38,1,'S'),(45,1,'S'),(46,1,'S'),(70,1,'S'),(47,1,'S'),(29,1,'S'),(48,1,'S'),(49,1,'S'),(50,1,'S'),(51,1,'S'),(53,1,'S'),(66,1,'S'),(65,1,'S'),(64,1,'S'),(57,1,'S'),(59,1,'S'),(52,1,'S'),(101,1,'S'),(102,1,'S'),(100,1,'S'),(103,1,'S'),(104,1,'S');
/*!40000 ALTER TABLE `permissoes` ENABLE KEYS */;
UNLOCK TABLES;



--
-- Table structure for table `queue_log`
--

DROP TABLE IF EXISTS `queue_log`;
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

--
-- Table structure for table `queue_members`
--

DROP TABLE IF EXISTS `queue_members`;
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

--
-- Table structure for table `queue_peers`
--

DROP TABLE IF EXISTS `queue_peers`;
CREATE TABLE `queue_peers` (
  `fila` varchar(80) NOT NULL default '',
  `ramal` int(11) NOT NULL,
  PRIMARY KEY  (`ramal`,`fila`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `queues`
--

DROP TABLE IF EXISTS `queues`;
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

--
-- Table structure for table `queues_agent`
--

DROP TABLE IF EXISTS `queues_agent`;
CREATE TABLE `queues_agent` (
  `agent_id` int(11) NOT NULL,
  `queue` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `rotinas`
--

DROP TABLE IF EXISTS `rotinas`;
CREATE TABLE `rotinas` (
  `cod_rotina` int(11) NOT NULL default '0',
  `desc_rotina` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`cod_rotina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rotinas`
--

LOCK TABLES `rotinas` WRITE;
/*!40000 ALTER TABLE `rotinas` DISABLE KEYS */;
INSERT INTO `rotinas` VALUES (81,'Permitir OUVIR gravacoes das chamadas'),(1,'Ver Painel'),(11,'Grupos: Relacao de Grupos'),(12,'Grupos: Cadastro Usuarios (I/A/E)'),(13,'Contas: Cadastro de Contas (I/A/E)'),(14,'Agentes (I,A,E)'),(15,'Ramais: Relacao de Ramais'),(16,'Ramais: Cadastro de Ramais (I/A/E)'),(10,'Cadastros'),(20,'RelatÃ³rios'),(40,'Graficos'),(60,'Configuracoes'),(21,'Relatorio: Registro de Chamadas'),(22,'Relatorio: Registro de Login/Logout de Agentes'),(23,'Relatorio: Faxes Transmitidos'),(24,'Relatorio: Faxes Recebidos'),(41,'Grafico: Registro de Chamadas'),(42,'Grafico: EstatiÂ­sticas de Tempo'),(43,'Grafico: Taxas de Ocupacao'),(61,'Configuracoes: Parametros do Sistema'),(62,'Configuracoes: Manutencao do Sistema'),(63,'Configuracoes: URA'),(99,'Usuarios: Permissao de Acesso'),(25,'Relatorio: Estatisticas do Operador'),(26,'Filas e Agentes'),(27,'Contas: Relacao de Contas'),(28,'Relatorio: Usuarios'),(17,'Ramais: Cadastro de Varios Ramais'),(19,'Filas: Cadastro de Filas (I/A/E)'),(18,'Filas: Relacao de Filas'),(31,'Troncos: Cadastro de Troncos (I/A/E)'),(30,'Troncos: Relacao de Troncos'),(33,'Agentes: Cadastro (I/A/E)'),(32,'Conferencias: Cadastro (I/A/E)'),(35,'Status dos Links'),(37,'Operadoras: Cadastrar Operadoras (I/A/E)'),(38,'Operadoras: Relacao de Operadoras '),(45,'Tarifas: Cadastrod e Tarifas (I/A/C)'),(46,'Tarifas: Relacao de Tarifas'),(70,'Tarifas'),(47,'Tarifas: Gerar Tarifacao'),(29,'Relatorios: Ranking das Ligacoes'),(48,'Regras de Dialplan: Relacao de Regras'),(49,'Regras de Dialplan: Cadastrar Regras (I/A/C)'),(50,'Sons - Relacao de Sons'),(51,'Sons - Cadastro de Sons (I/A/E)'),(53,'Musicas em Espera'),(64,'Configuracoes: Aliases de Troncos'),(65,'Configuracoes: Relacao de Aliases de Troncos'),(59, 'Contatos: Relacao de contatos'),(57, 'Contatos: (A/I/E)'),(82, 'Permitir EXCLUIR gravacoes das chamadas'),(100, 'Relatório Filas de Atendimento'),(101, 'Relatório Loguin Logoff em Filas'),(102, 'Relatório Serviços Utilizados'),(103, 'Logs do Sistema'), (104, 'Relatório de Fax');
/*!40000 ALTER TABLE `rotinas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sounds`
--

DROP TABLE IF EXISTS `sounds`;
CREATE TABLE `sounds` (
  `arquivo` varchar(50) NOT NULL,
  `descricao` varchar(80) NOT NULL,
  `data` datetime default NULL,
  `tipo` char(3) NOT NULL default 'AST',
  `secao` varchar(30) NOT NULL,
  PRIMARY KEY  (`arquivo`,`tipo`,`secao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sounds`
--

LOCK TABLES `sounds` WRITE;
/*!40000 ALTER TABLE `sounds` DISABLE KEYS */;
INSERT INTO `sounds` VALUES ('fpm-calm-river.wav','Som de Musica em Espera - Calm River','2008-07-25 10:51:42','MOH','default'),('fpm-sunshine.wav','Som de Musica em Espera - Sunshine','2008-07-25 10:51:56','MOH','default'),('fpm-world-mix.wav','Som de Musica em Espera - World Mix','2008-07-25 10:52:13','MOH','default'),('Acre.wav','Acre','2008-08-11 14:14:35','AST',''),('Alagoas.wav','Alagoas','2008-08-11 14:14:40','AST',''),('Amapa.wav','Amapá','2008-08-11 14:14:45','AST',''),('Amazonas.wav','Amazonas','2008-08-11 14:14:49','AST',''),('Aracaju.wav','Aracaju','2008-08-11 14:14:54','AST',''),('Bahia.wav','Bahia','2008-08-11 14:14:57','AST',''),('Belem.wav','Belém','2008-08-11 14:15:01','AST',''),('Belo-Horizonte.wav','Belo Horizonte','2008-08-11 14:15:22','AST',''),('Boa-Vista.wav','Boa Vista','2008-08-11 14:15:31','AST',''),('Brasilia.wav','Brasilia','2008-08-11 14:15:38','AST',''),('Campo-Grande.wav','Campo Grande','2008-08-11 14:15:46','AST',''),('Ceara.wav','Ceara','2008-08-11 14:15:50','AST',''),('Cuiaba.wav','Cuiaba','2008-08-11 14:15:57','AST',''),('Curitiba.wav','Curitiba','2008-08-11 14:16:01','AST',''),('Distrito-Federal.wav','Distrito Federal','2008-08-11 14:16:14','AST',''),('Espirito-Santo.wav','Espirito Santo','2008-08-11 14:16:25','AST',''),('Florianopolis.wav','Florianopolis','2008-08-11 14:17:03','AST',''),('Fortaleza.wav','Fortaleza','2008-08-11 14:17:10','AST',''),('Goiania.wav','Goiania','2008-08-11 14:17:15','AST',''),('Goias.wav','Goais','2008-08-11 14:17:19','AST',''),('Joao-Pessoa.wav','Joao pessoa','2008-08-11 14:17:22','AST',''),('Macapa.wav','Macapa','2008-08-11 14:26:06','AST',''),('Maceio.wav','Maceio','2008-08-11 14:17:32','AST',''),('Manaus.wav','Manaus','2008-08-11 14:17:35','AST',''),('Maranhao.wav','Maranhão','2008-08-11 14:17:39','AST',''),('Mato-Grosso-do-Sul.wav','Mato grosso do Sul','2008-08-11 14:17:44','AST',''),('Mato-Grosso.wav','Mato Grosso','2008-08-11 14:17:51','AST',''),('Minas-Gerais.wav','Minas Gerais','2008-08-11 14:17:55','AST',''),('Natal.wav','Natal','2008-08-11 14:17:59','AST',''),('Palmas.wav','Palmas','2008-08-11 14:18:02','AST',''),('Para.wav','Para','2008-08-11 14:18:25','AST',''),('Paraiba.wav','Paraiba','2008-08-11 14:18:33','AST',''),('Parana.wav','Paraná','2008-08-11 14:18:47','AST',''),('Pernambuco.wav','Pernambuco','2008-08-11 14:18:57','AST',''),('Piaui.wav','Piaui','2008-08-11 14:19:01','AST',''),('Porto-Alegre.wav','Porto Alegre','2008-08-11 14:19:09','AST',''),('Porto-Velho.wav','Porto velho','2008-08-11 14:19:15','AST',''),('Real.wav','Real','2008-08-11 14:19:21','AST',''),('Recife.wav','Recife','2008-08-11 14:19:26','AST',''),('Rio-Branco.wav','Rio Branco','2008-08-11 14:19:30','AST',''),('Rio-Grande-do-Norte.wav','Rio Grande do Norte','2008-08-11 14:19:36','AST',''),('Rio-Grande-do-Sul.wav','Rio Grande do Sul','2008-08-11 14:19:42','AST',''),('Rio-de-Janeiro.wav','Rio de Janeiro','2008-08-11 14:19:46','AST',''),('Rondonia.wav','Rondonia','2008-08-11 14:19:51','AST',''),('Roraima.wav','Roraima','2008-08-11 14:19:55','AST',''),('Salvador.wav','Salvador','2008-08-11 14:19:59','AST',''),('Santa-Catarina.wav','Santa Catarina','2008-08-11 14:20:03','AST',''),('Sao-Luis.wav','São Luiz','2008-08-11 14:20:10','AST',''),('Sao-Paulo.wav','São Paulo','2008-08-11 14:20:13','AST',''),('Sergipe.wav','Sergipe','2008-08-11 14:20:16','AST',''),('Teresina.wav','Teresina','2008-08-11 14:20:20','AST',''),('Tocantins.wav','Tocantins','2008-08-11 14:20:46','AST',''),('Vitoria.wav','Vitória','2008-08-11 14:20:53','AST',''),('access-password.wav','Digite a senha de acesso e pressione cerca','2008-08-11 14:21:30','AST',''),('activated.wav','Ativado','2008-08-11 14:21:42','AST',''),('afternoon.wav','Tarde','2008-08-11 14:21:53','AST',''),('agent-alreadyon.wav','Atendentes apresente, digite seu número e pressione cerca','2008-08-11 14:22:29','AST',''),('agent-incorrect.wav','Numero incorreto, digite seu numero e pressione cerca','2008-08-11 14:22:59','AST',''),('agent-loggedoff.wav','Atendente ausente','2008-08-11 14:23:14','AST',''),('agent-loginok.wav','Atendente presente','2008-08-11 14:23:29','AST',''),('agent-newlocation.wav','Digite seu ramal e pressione cerca','2008-08-11 14:26:25','AST',''),('agent-pass.wav','Digite sua senha e pressione cerca','2008-08-11 14:26:40','AST',''),('agent-user.wav','Digite seu numero e pressione cerca','2008-08-11 14:26:51','AST',''),('all-circuits-busy-now.wav','Aguarde, todas as linhas ocupadas no momento','2008-08-11 14:25:35','AST',''),('an-error-has-occured.wav','Ocorreu um erro','2008-08-11 15:48:50','AST',''),('astcc-accountnum.gsm','Digite o numero do seu carto seguido de #','2008-08-11 15:50:04','AST',''),('astcc-badaccount.gsm','Cartão inválido','2008-08-11 15:50:50','AST',''),('astcc-badphone.gsm','Número inválido','2008-08-11 15:51:02','AST',''),('astcc-cents.gsm','Centavos','2008-08-11 15:51:17','AST',''),('astcc-connectcharge.gsm','Uma caixa de conexão de','2008-08-11 15:51:39','AST',''),('astcc-dollar.gsm','Real','2008-08-11 15:52:02','AST',''),('astcc-dollars.gsm','Reais','2008-08-11 15:52:06','AST',''),('astcc-down.gsm','Não está disponível no momento','2008-08-11 15:52:34','AST',''),('astcc-forfirst.gsm','PAra os primeiros','2008-08-11 15:52:48','AST',''),('astcc-isbusy.gsm','O número está ocupado no momento','2008-08-11 15:53:09','AST',''),('astcc-minute.gsm','Minuto','2008-08-11 15:53:23','AST',''),('astcc-minutes.gsm','Minutos','2008-08-11 15:53:26','AST',''),('astcc-noanswer.gsm','O número chamado não atende','2008-08-11 15:53:46','AST',''),('astcc-notenough.gsm','Sem créditos suficientes p/ efetuar a chamada','2008-08-11 15:54:16','AST',''),('astcc-nothing.gsm','Nada','2008-08-11 15:54:26','AST',''),('astcc-perminute.gsm','Centavos por minuto','2008-08-11 15:54:41','AST',''),('astcc-phonenum.gsm','Disque o número a ser chamado seguido de #','2008-08-11 15:55:09','AST',''),('astcc-pleasewait.gsm','Aguarde enquanto efetuamos sua chamada','2008-08-11 15:55:31','AST',''),('astcc-point.gsm','Ponto','2008-08-11 15:55:47','AST',''),('astcc-remaining.gsm','Está sobrando','2008-08-11 15:55:59','AST',''),('astcc-secounds.gsm','Segundos','2008-08-11 15:56:22','AST',''),('astcc-unavail.gsm','Número não disponível no momento','2008-08-11 15:57:12','AST',''),('astcc-welcome.gsm','Bem-vindo','2008-08-11 15:57:23','AST',''),('astcc-willapply.gsm','Será debitada','2008-08-11 15:57:39','AST',''),('astcc-willcost.gsm','Chamada vai custar','2008-08-11 15:57:55','AST',''),('astcc-youhave.gsm','Você tem','2008-08-11 15:58:08','AST',''),('at-tone-time-exactly.wav','Quando houvir o tom a hora exata será','2008-08-11 15:58:36','AST',''),('auth-incorrect.wav','Senha incorreta','2008-08-11 15:58:54','AST',''),('auth-thankyou.wav','Obrigado','2008-08-11 15:59:11','AST',''),('call-fwd-no-ans.wav','Redicionar ligação quando não atende','2008-08-11 15:59:43','AST',''),('call-fwd-on-busy.wav','Redicionar ligação quando ocupado','2008-08-11 15:59:51','AST',''),('call-fwd-unconditional.wav','Redicionar ligação sempre','2008-08-11 16:00:10','AST',''),('conf-adminmenu.wav','Conferência - Tecle 1 p/ lig/des microfone ou 2 para bloq/desbl a Sala de Conf','2008-08-12 08:30:34','AST',''),('conf-enteringno.wav','Conferência - Sala de Conferência número','2008-08-12 08:30:57','AST',''),('conf-errormenu.wav','Conferência - Opção inválida','2008-08-12 08:31:11','AST',''),('conf-getchannel.wav','Conferência - Digite o canal da Sala de conferência seguido de #','2008-08-12 08:31:47','AST',''),('conf-getpin.wav','Conferência - Digite a senha da sala de conferência','2008-08-12 08:32:15','AST',''),('conf-hasjoin.wav','Conferência - Entrou na sala de conferência','2008-08-12 08:32:28','AST',''),('conf-hasleft.wav','Conferência - Saiu da Sala de Conferência','2008-08-12 08:32:42','AST',''),('conf-invalid.wav','Conferência - Sala de Conferência inválida','2008-08-12 08:32:55','AST',''),('conf-invalidpin.wav','Conferência - Senha da Sala de Conferência inválida','2008-08-12 08:33:06','AST',''),('conf-kicked.wav','Conferência - Você foi excluido desta Sala de Conferência','2008-08-12 08:33:16','AST',''),('conf-leaderhasleft.wav','Conferência - Líder saiu da sala de conferência','2008-08-12 08:33:29','AST',''),('conf-locked.wav','Conferência - Sala de Conferência Bloqueada','2008-08-12 08:33:46','AST',''),('conf-muted.wav','Conferência - Microfone desativado','2008-08-12 08:34:08','AST',''),('conf-noempty.wav','Conferência - Todos Canais da Sala de Conferência estão ocupados','2008-08-12 08:34:46','AST',''),('de-activated.wav','Desativado','2008-08-11 17:02:25','AST',''),('queue-callswaiting.wav','Filas - Aguarde para falar com um atendente','2008-08-12 08:52:07','AST',''),('queue-holdtime.wav','Filas - O tempo estimado de espera é de','2008-08-12 08:52:16','AST',''),('queue-periodic-announce.wav','Filas - Atendentes ocupados, por favor aguarde ...','2008-08-12 08:52:26','AST',''),('queue-thankyou.wav','Filas - Aguarde ser atendido','2008-08-12 08:52:36','AST',''),('conf-getconfno.wav','Conferência - Digite o número da Sala de Conferência e pressione #','2008-08-12 08:32:01','AST',''),('conf-lockednow.wav','Conferência - Sala de Conferência Bloqueada','2008-08-12 08:33:58','AST',''),('conf-onlyone.wav','Conferência - Existe apenas 1 participante na Sala','2008-08-12 08:24:47','AST',''),('conf-onlyperson.wav','Conferência - Você é a única pessoa nesta Sala de Conferência','2008-08-12 08:25:18','AST',''),('conf-otherinparty.wav','Conferência - Outros participantes na Sala de Conferência','2008-08-12 08:25:49','AST',''),('conf-placeintoconf.wav','Conferência - Você entrará agora na Sala de Conferência','2008-08-12 08:26:23','AST',''),('conf-thereare.wav','Conferência - Existem atualmente','2008-08-12 08:26:49','AST',''),('conf-unlockednow.wav','Conferência - Sala de Conferência desbloqueada','2008-08-12 08:27:14','AST',''),('conf-unmuted.wav','Conferência - Microfone ativado','2008-08-12 08:27:36','AST',''),('conf-usermenu.wav','Conferência - Pressione 1 para Ligar ou Desligar o Microfone','2008-08-12 08:28:09','AST',''),('conf-userswilljoin.wav','Conferência - Algumas pessoas entrarão na Sala de Conferência','2008-08-12 08:28:46','AST',''),('conf-userwilljoin.wav','Conferência - Uma pessoa entrara na Sala de Conferência','2008-08-12 08:35:23','AST',''),('conf-waitforleader.wav','Conferência - Sala de Conferência iniciará quando líder chegar','2008-08-12 08:35:55','AST',''),('do-not-disturb.wav','Não perturbe','2008-08-12 08:36:51','AST',''),('ent-target-attendant.wav','Entre com o número do','2008-08-12 08:37:19','AST',''),('ext-disabled.wav','Ramal não habilitado para receber chamadas','2008-08-12 08:37:38','AST',''),('hour.wav','Hora','2008-08-12 08:38:11','AST',''),('im-sorry.wav','Desculpe','2008-08-12 08:38:20','AST',''),('info-about-last-call.wav','Informação sobre a última chamada','2008-08-12 08:38:43','AST',''),('incorrect-password.wav','Senha incorreta','2008-08-12 08:39:00','AST',''),('invalid.wav','Número inválido, tente novamente','2008-08-12 08:39:25','AST',''),('is-in-use.wav','Está em uso','2008-08-12 08:39:42','AST',''),('location.wav','Posição','2008-08-12 08:40:05','AST',''),('is.wav','É','2008-08-12 08:40:09','AST',''),('minute.wav','Minuto','2008-08-12 08:40:22','AST',''),('is-set-to.wav','Está marcado como','2008-08-12 08:40:43','AST',''),('morning.wav','Manhã','2008-08-12 08:41:04','AST',''),('night.wav','Noite','2008-08-12 08:41:11','AST',''),('no-rights.wav','Você não tem direito de acesso à rota sainte','2008-08-12 08:41:33','AST',''),('number.wav','Número','2008-08-12 08:41:44','AST',''),('one-moment-please.wav','Um momento , por favor','2008-08-12 08:42:03','AST',''),('pbx-invalid.wav','Ramal inválido, por favor tente novamente','2008-08-12 08:42:30','AST',''),('pbx-transfer.wav','Transferência','2008-08-12 08:42:46','AST',''),('pbx-invalidpark.wav','Não existe chamada estacionada neste ramal','2008-08-12 08:43:11','AST',''),('pls-try-call-later.wav','Tente mais tarde','2008-08-12 08:43:38','AST',''),('pm-invalid-option.wav','Você escolheu uma opção inválida','2008-08-12 08:43:58','AST',''),('press-1.wav','Pressione 1','2008-08-12 08:44:16','AST',''),('press-2.wav','Pressione 2','2008-08-12 08:44:24','AST',''),('press-3.wav','Pressione 3','2008-08-12 08:44:30','AST',''),('press-star.wav','Pressione estrela','2008-08-12 08:44:44','AST',''),('queue-less-than.wav','Filas - Menos que','2008-08-12 08:45:03','AST',''),('queue-minutes.wav','Filas - Minutos','2008-08-12 08:45:23','AST',''),('queue-reporthold.wav','Filas - Tempo de espera','2008-08-12 08:45:42','AST',''),('queue-seconds.wav','Filas - Segundos','2008-08-12 08:45:50','AST',''),('queue-thereare.wav','Filas - Sua chamada é a','2008-08-12 08:48:21','AST',''),('queue-youarenext.wav','Filas - Sua chamada é a primeira da fila','2008-08-12 08:48:44','AST','');
/*!40000 ALTER TABLE `sounds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarifas`
--

DROP TABLE IF EXISTS `tarifas`;
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


--
-- Table structure for table `tarifas_valores`
--

DROP TABLE IF EXISTS `tarifas_valores`;
CREATE TABLE `tarifas_valores` (
  `codigo` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `vcel` float NOT NULL default '0',
  `vfix` float NOT NULL default '0',
  `vpf` float NOT NULL default '0',
  `vpc` float NOT NULL default '0',
  PRIMARY KEY  (`codigo`,`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `trunks`
--

DROP TABLE IF EXISTS `trunks`;
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for view `trunks_redund`
--

DROP VIEW IF EXISTS `trunks_redund`;
CREATE VIEW `trunks_redund` AS
    SELECT `trunks`.`id` AS `trunk_redund` , `trunks`.`channel` AS `channel_redund` , `trunks`.`name` AS `name` , `trunks`.`dialmethod` AS `red_dialmethod`
FROM `trunks` ;


--
-- Table structure for view `time_history`
--
DROP TABLE IF EXISTS `time_history`;
CREATE TABLE IF NOT EXISTS `time_history` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  `used` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `owner_type` char(1) NOT NULL default 'T',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `vinculos`
--

DROP TABLE IF EXISTS `vinculos`;
CREATE TABLE `vinculos` (
  `ramal` varchar(80) default NULL,
  `cod_usuario` varchar(80) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `voicemail_messages`
--

DROP TABLE IF EXISTS `voicemail_messages`;
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


--
-- Table structure for table `voicemail_users`
--

DROP TABLE IF EXISTS `voicemail_users`;
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


--
-- Estrutura da tabela `contacts_names`
--

CREATE TABLE IF NOT EXISTS `contacts_names` (
  `id` char(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(2) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `phone_1` varchar(15) NOT NULL,
  `cell_1` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Estrutura da tabela `lista_abandono`
--

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


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2008-08-12 16:58:45
