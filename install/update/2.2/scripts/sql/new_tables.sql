--
-- Table structure for table `groups`
--

CREATE TABLE groups (
    name varchar(50) PRIMARY KEY,
    inherit varchar(50),
    FOREIGN KEY (inherit) REFERENCES groups(name) ON UPDATE CASCADE
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Grupos padrões do sistema
INSERT INTO groups VALUES ('all',null);
INSERT INTO groups VALUES ('admin','all');
INSERT INTO groups VALUES ('users','all');
--
-- Table structure for table `regras_negocio`
--

CREATE TABLE regras_negocio (
  id integer PRIMARY KEY auto_increment,
  prio integer NOT NULL default 0,
  `desc` varchar(255) default NULL,
  origem text NOT NULL,
  destino text NOT NULL,
  validade text NOT NULL,
  record boolean NOT NULL default false,
  ativa boolean NOT NULL default true
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE regras_negocio_actions (
  regra_id integer NOT NULL,
  prio integer NOT NULL,
  `action` varchar(250) NOT NULL,
  PRIMARY KEY(regra_id, prio),
  FOREIGN KEY (regra_id) REFERENCES regras_negocio(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE regras_negocio_actions_config (
  regra_id integer NOT NULL,
  prio integer NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY(regra_id,prio,`key`),
  FOREIGN KEY (regra_id, prio) REFERENCES regras_negocio_actions (regra_id, prio) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;