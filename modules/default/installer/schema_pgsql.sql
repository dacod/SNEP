--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: alias_expression_id_alias_expression_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE alias_expression_id_alias_expression_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.alias_expression_id_alias_expression_seq OWNER TO postgres;

--
-- Name: alias_expression_id_alias_expression_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('alias_expression_id_alias_expression_seq', 1, false);


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: alias_expression; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE alias_expression (
    id_alias_expression bigint DEFAULT nextval('alias_expression_id_alias_expression_seq'::regclass) NOT NULL,
    ds_name character varying(60) NOT NULL
);


ALTER TABLE public.alias_expression OWNER TO postgres;

--
-- Name: TABLE alias_expression; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE alias_expression IS 'Alias de Expressão';


--
-- Name: audit; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE audit (
    id_audit bigint NOT NULL,
    id_resource bigint NOT NULL,
    id_user bigint NOT NULL,
    dt_action timestamp with time zone NOT NULL,
    ds_ipuser character(15)
);


ALTER TABLE public.audit OWNER TO postgres;

--
-- Name: TABLE audit; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE audit IS 'Utilizado para auditoria do sistema';


--
-- Name: billing_id_billing_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE billing_id_billing_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.billing_id_billing_seq OWNER TO postgres;

--
-- Name: billing_id_billing_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('billing_id_billing_seq', 1, false);


--
-- Name: billing; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE billing (
    id_billing bigint DEFAULT nextval('billing_id_billing_seq'::regclass) NOT NULL,
    vl_celphone numeric(10,2) NOT NULL,
    vl_phone numeric(10,2) NOT NULL,
    dt_vigency date NOT NULL,
    id_citty bigint,
    id_carrier bigint
);


ALTER TABLE public.billing OWNER TO postgres;

--
-- Name: TABLE billing; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE billing IS 'tarifas';


--
-- Name: business_expression_id_bussiness_expression_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE business_expression_id_bussiness_expression_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.business_expression_id_bussiness_expression_seq OWNER TO postgres;

--
-- Name: business_expression_id_bussiness_expression_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('business_expression_id_bussiness_expression_seq', 1, false);


--
-- Name: business_expression; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE business_expression (
    id_business_role bigint,
    id_alias_expression bigint,
    id_bussiness_expression bigint DEFAULT nextval('business_expression_id_bussiness_expression_seq'::regclass) NOT NULL
);


ALTER TABLE public.business_expression OWNER TO postgres;

--
-- Name: TABLE business_expression; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE business_expression IS 'Expressões da regra';


--
-- Name: business_rule_id_business_role_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE business_rule_id_business_role_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.business_rule_id_business_role_seq OWNER TO postgres;

--
-- Name: business_rule_id_business_role_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('business_rule_id_business_role_seq', 1, false);


--
-- Name: business_rule; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE business_rule (
    id_business_role bigint DEFAULT nextval('business_rule_id_business_role_seq'::regclass) NOT NULL,
    vl_priority numeric(10,0) NOT NULL,
    ds_name character varying(100) NOT NULL,
    ds_source text NOT NULL,
    ds_destination text NOT NULL,
    ds_validate text NOT NULL,
    ds_days_week character varying(30) NOT NULL,
    vl_record smallint NOT NULL,
    fg_active smallint NOT NULL
);


ALTER TABLE public.business_rule OWNER TO postgres;

--
-- Name: TABLE business_rule; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE business_rule IS 'Regras de negocio';


--
-- Name: business_rule_actions_id_business_rule_actions_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE business_rule_actions_id_business_rule_actions_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.business_rule_actions_id_business_rule_actions_seq OWNER TO postgres;

--
-- Name: business_rule_actions_id_business_rule_actions_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('business_rule_actions_id_business_rule_actions_seq', 1, false);


--
-- Name: business_rule_actions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE business_rule_actions (
    id_business_role bigint,
    id_business_rule_actions bigint DEFAULT nextval('business_rule_actions_id_business_rule_actions_seq'::regclass) NOT NULL,
    vl_priority numeric(10,0) NOT NULL,
    ds_action character varying(150) NOT NULL
);


ALTER TABLE public.business_rule_actions OWNER TO postgres;

--
-- Name: TABLE business_rule_actions; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE business_rule_actions IS 'Ações de regra de negócio';


--
-- Name: call_center_queues_id_call_center_queues_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE call_center_queues_id_call_center_queues_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.call_center_queues_id_call_center_queues_seq OWNER TO postgres;

--
-- Name: call_center_queues_id_call_center_queues_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('call_center_queues_id_call_center_queues_seq', 1, false);


--
-- Name: call_center_queues; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE call_center_queues (
    id_call_center_queues bigint DEFAULT nextval('call_center_queues_id_call_center_queues_seq'::regclass) NOT NULL,
    cd_queue bigint NOT NULL,
    vl_source numeric(10,0) NOT NULL,
    vl_destination numeric(10,0) NOT NULL,
    dt_event date NOT NULL,
    ds_event character(45) NOT NULL,
    vl_duration numeric(10,0) NOT NULL,
    fg_type numeric NOT NULL,
    id_cdr bigint,
    name character varying(128),
    id_queue bigint
);


ALTER TABLE public.call_center_queues OWNER TO postgres;

--
-- Name: TABLE call_center_queues; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE call_center_queues IS 'Filas do callcenter';


--
-- Name: carrier; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE carrier (
    id_costcenter bigint,
    id_carrier bigint NOT NULL
);


ALTER TABLE public.carrier OWNER TO postgres;

--
-- Name: TABLE carrier; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE carrier IS 'Operadoras';


--
-- Name: carrier_id_carrier_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE carrier_id_carrier_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.carrier_id_carrier_seq OWNER TO postgres;

--
-- Name: carrier_id_carrier_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('carrier_id_carrier_seq', 1, false);


--
-- Name: carrier_prefix; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE carrier_prefix (
    id_carrier bigint,
    id_prefix bigint NOT NULL,
    id_citty bigint
);


ALTER TABLE public.carrier_prefix OWNER TO postgres;

--
-- Name: TABLE carrier_prefix; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE carrier_prefix IS 'Prefixo da operadora';


--
-- Name: cdr_id_cdr_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cdr_id_cdr_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cdr_id_cdr_seq OWNER TO postgres;

--
-- Name: cdr_id_cdr_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('cdr_id_cdr_seq', 1, false);


--
-- Name: cdr; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cdr (
    id_cdr bigint DEFAULT nextval('cdr_id_cdr_seq'::regclass) NOT NULL,
    userfield character varying(255) NOT NULL,
    calldate timestamp with time zone DEFAULT now() NOT NULL,
    clid character varying(80) NOT NULL,
    src character varying(80) NOT NULL,
    dst character varying(80) NOT NULL,
    dcontext character varying(80) NOT NULL,
    channel character varying(80) NOT NULL,
    dstchannel character varying(80) NOT NULL,
    lastapp character varying(80) NOT NULL,
    lastdata character varying(80) NOT NULL,
    duration bigint NOT NULL,
    billsec bigint NOT NULL,
    disposition character varying(45) NOT NULL,
    amaflags bigint NOT NULL,
    accountcode character varying(20) NOT NULL,
    uniqueid character varying(32) NOT NULL
);


ALTER TABLE public.cdr OWNER TO postgres;

--
-- Name: city; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE city (
    id_citty bigint NOT NULL,
    ds_name character(30) NOT NULL
);


ALTER TABLE public.city OWNER TO postgres;

--
-- Name: TABLE city; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE city IS 'Cidade do ARS';


--
-- Name: city_code_id_city_code_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE city_code_id_city_code_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.city_code_id_city_code_seq OWNER TO postgres;

--
-- Name: city_code_id_city_code_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('city_code_id_city_code_seq', 1, false);


--
-- Name: city_code; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE city_code (
    id_city_code bigint DEFAULT nextval('city_code_id_city_code_seq'::regclass) NOT NULL,
    id_state bigint,
    id_citty bigint,
    ds_name character(4) NOT NULL
);


ALTER TABLE public.city_code OWNER TO postgres;

--
-- Name: TABLE city_code; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE city_code IS 'DDD';


--
-- Name: contact_id_contact_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE contact_id_contact_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contact_id_contact_seq OWNER TO postgres;

--
-- Name: contact_id_contact_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('contact_id_contact_seq', 1, false);


--
-- Name: contact; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE contact (
    id_contact bigint DEFAULT nextval('contact_id_contact_seq'::regclass) NOT NULL,
    ds_name character(80) NOT NULL,
    ds_address character(100) NOT NULL,
    ds_cep character(8) NOT NULL,
    ds_phone character(15) NOT NULL,
    ds_cell_phone character(15) NOT NULL,
    id_citty bigint,
    id_state bigint,
    id_contact_group bigint
);


ALTER TABLE public.contact OWNER TO postgres;

--
-- Name: TABLE contact; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE contact IS 'tabela de contatos';


--
-- Name: contact_group_id_contact_group_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE contact_group_id_contact_group_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contact_group_id_contact_group_seq OWNER TO postgres;

--
-- Name: contact_group_id_contact_group_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('contact_group_id_contact_group_seq', 1, false);


--
-- Name: contact_group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE contact_group (
    id_contact_group bigint DEFAULT nextval('contact_group_id_contact_group_seq'::regclass) NOT NULL,
    ds_name character(20) NOT NULL
);


ALTER TABLE public.contact_group OWNER TO postgres;

--
-- Name: TABLE contact_group; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE contact_group IS 'Grupo de contatos';


--
-- Name: cost_center; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cost_center (
    id_costcenter bigint NOT NULL,
    ds_description character(255),
    cd_type character(1),
    ds_name character(32),
    CONSTRAINT "COST_CENTER_type" CHECK ((cd_type = ANY (ARRAY['E'::bpchar, 'S'::bpchar, 'O'::bpchar])))
);


ALTER TABLE public.cost_center OWNER TO postgres;

--
-- Name: expression_id_expression_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE expression_id_expression_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.expression_id_expression_seq OWNER TO postgres;

--
-- Name: expression_id_expression_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('expression_id_expression_seq', 1, false);


--
-- Name: expression; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE expression (
    id_alias_expression bigint,
    ds_expression character varying(200) NOT NULL,
    id_expression bigint DEFAULT nextval('expression_id_expression_seq'::regclass) NOT NULL
);


ALTER TABLE public.expression OWNER TO postgres;

--
-- Name: TABLE expression; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE expression IS 'Expressão';


--
-- Name: extension; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE extension (
    id_extension bigint NOT NULL,
    fg_canreinvite boolean,
    fg_usevoicemail boolean,
    fg_dontdisturb boolean,
    fg_followme boolean,
    id_extensiongroup bigint,
    id_pickupgroup bigint,
    id_user bigint,
    id_peer bigint NOT NULL
);

CREATE SEQUENCE pickup_group_id_pickupgroup_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.extension OWNER TO postgres;

--
-- Name: extension_group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE extension_group (
    id_extensiongroup bigint NOT NULL,
    ds_name character(35)
);


ALTER TABLE public.extension_group OWNER TO postgres;

--
-- Name: group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "group" (
    id_group bigint NOT NULL,
    ds_group character(32) NOT NULL,
    id_profile bigint
);


ALTER TABLE public."group" OWNER TO postgres;

--
-- Name: TABLE "group"; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE "group" IS 'Grupo de usuários';


--
-- Name: peer; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE peer (
    id_peer bigint NOT NULL,
    ds_name character(32) NOT NULL,
    ds_callerid character(60),
    ds_context character(80),
    ds_dtmfmode character(7),
    ds_host character(31) NOT NULL,
    ds_insecure character(4),
    fg_nat boolean NOT NULL,
    fg_qualify boolean NOT NULL,
    cd_secret character(60),
    cd_type character(7) NOT NULL,
    ds_username character(32) NOT NULL,
    ds_codec_disallow character(60),
    ds_codec_allow character(60),
    ds_channel character(255),
    vl_call_limit numeric(4,0),
    cd_peer_type character(1) NOT NULL,
    vl_time_total numeric(11,0),
    vl_time_chargeby numeric(1,0),
    CONSTRAINT "PEER_type" CHECK ((cd_peer_type = ANY (ARRAY['O'::bpchar, 'T'::bpchar, 'R'::bpchar])))
);


ALTER TABLE public.peer OWNER TO postgres;

--
-- Name: TABLE peer; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE peer IS 'Tabela que guarda informações genéricas de peers (ramais ou troncos)';


--
-- Name: COLUMN peer.id_peer; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.id_peer IS 'id do peer - campo único';


--
-- Name: COLUMN peer.ds_name; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_name IS 'Nome do peer';


--
-- Name: COLUMN peer.ds_callerid; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_callerid IS 'Identificador de chamada';


--
-- Name: COLUMN peer.ds_context; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_context IS 'Descrição do contexto';


--
-- Name: COLUMN peer.ds_dtmfmode; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_dtmfmode IS 'String do dtmfmode';


--
-- Name: COLUMN peer.ds_host; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_host IS 'Identificação do host';


--
-- Name: COLUMN peer.fg_nat; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.fg_nat IS 'Usa NAT (0=não / 1=sim)';


--
-- Name: COLUMN peer.fg_qualify; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.fg_qualify IS 'Qualificar (0=não / 1=sim)';


--
-- Name: COLUMN peer.cd_secret; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.cd_secret IS 'Senha de autenticação no asterisk';


--
-- Name: COLUMN peer.cd_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.cd_type IS 'Indica se é peer ou friend';


--
-- Name: COLUMN peer.ds_username; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_username IS 'Username de autenticação no asterisk';


--
-- Name: COLUMN peer.ds_codec_disallow; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_codec_disallow IS 'Codecs não permitidos';


--
-- Name: COLUMN peer.ds_codec_allow; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_codec_allow IS 'Codecs permitidos';


--
-- Name: COLUMN peer.ds_channel; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.ds_channel IS 'Descrição dos canais';


--
-- Name: COLUMN peer.vl_call_limit; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.vl_call_limit IS 'Limite de chamadas';


--
-- Name: COLUMN peer.cd_peer_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.cd_peer_type IS 'Tipo de peer (R=Ramal / T=Tronco / O=Outros)';


--
-- Name: COLUMN peer.vl_time_total; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.vl_time_total IS 'Tempo total';


--
-- Name: COLUMN peer.vl_time_chargeby; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN peer.vl_time_chargeby IS 'Tempo de carga';


--
-- Name: pickup_group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pickup_group (
    id_pickupgroup bigint NOT NULL,
    ds_name character(35)
);


ALTER TABLE public.pickup_group OWNER TO postgres;

--
-- Name: profile; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE profile (
    id_profile bigint NOT NULL,
    ds_profile character(32) NOT NULL
);


ALTER TABLE public.profile OWNER TO postgres;

--
-- Name: TABLE profile; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE profile IS 'Perfis de grupos e usuários';


--
-- Name: profile_resource; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE profile_resource (
    id_profile bigint NOT NULL,
    id_resource bigint NOT NULL
);


ALTER TABLE public.profile_resource OWNER TO postgres;

--
-- Name: TABLE profile_resource; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE profile_resource IS 'Relacionamento perfil/recursos.';


--
-- Name: queue; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE queue (
    name character varying(128) NOT NULL,
    musiconhold character varying(128),
    announce character varying(128),
    context character varying(128),
    timeout bigint,
    monitor_join boolean,
    monitor_format character varying(128),
    queue_youarenext character varying(128),
    queue_thereare character varying(128),
    queue_callswaiting character varying(128),
    queue_holdtime character varying(128),
    queue_minutes character varying(128),
    queue_seconds character varying(128),
    queue_lessthan character varying(128),
    queue_thankyou character varying(128),
    queue_reporthold character varying(128),
    announce_frequency bigint,
    announce_round_seconds bigint,
    announce_holdtime character varying(128),
    retry bigint,
    wrapuptime bigint,
    maxlen bigint,
    servicelevel bigint,
    strategy character varying(128),
    joinempty character varying(128),
    leavewhenempty character varying(128),
    eventmemberstatus boolean,
    eventwhencalled boolean,
    reportholdtime boolean,
    memberdelay bigint,
    weight bigint,
    timeoutrestart boolean,
    queue_name character varying(128),
    interface character varying(128),
    id_queue bigint NOT NULL
);


ALTER TABLE public.queue OWNER TO postgres;

--
-- Name: queue_member_table; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE queue_member_table (
    queue_name character varying(128) NOT NULL,
    interface character varying(128) NOT NULL,
    penalty bigint,
    id_extension bigint,
    name character varying(128),
    id_queue bigint
);


ALTER TABLE public.queue_member_table OWNER TO postgres;

--
-- Name: registry_id_registry_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE registry_id_registry_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.registry_id_registry_seq OWNER TO postgres;

--
-- Name: registry_id_registry_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('registry_id_registry_seq', 1, false);


--
-- Name: registry; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE registry (
    id_registry bigint DEFAULT nextval('registry_id_registry_seq'::regclass) NOT NULL,
    ds_context character(50) NOT NULL,
    ds_key character(30) NOT NULL,
    ds_value character(200) NOT NULL
);


ALTER TABLE public.registry OWNER TO postgres;

--
-- Name: resource; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE resource (
    id_resource bigint NOT NULL,
    ds_resource character(256) NOT NULL,
    cd_resource bigint NOT NULL,
    fg_active boolean NOT NULL,
    id_parent bigint
);


ALTER TABLE public.resource OWNER TO postgres;

--
-- Name: TABLE resource; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE resource IS 'ACL dos recursos disponíveis';


--
-- Name: state_id_state_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE state_id_state_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.state_id_state_seq OWNER TO postgres;

--
-- Name: state_id_state_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('state_id_state_seq', 1, false);


--
-- Name: state; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE state (
    id_state bigint DEFAULT nextval('state_id_state_seq'::regclass) NOT NULL,
    ds_name character(30) NOT NULL
);


ALTER TABLE public.state OWNER TO postgres;

--
-- Name: TABLE state; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE state IS 'Estado do ARS';


--
-- Name: time_history_id_time_history_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE time_history_id_time_history_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.time_history_id_time_history_seq OWNER TO postgres;

--
-- Name: time_history_id_time_history_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('time_history_id_time_history_seq', 1, false);


--
-- Name: time_history; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE time_history (
    id_time_history bigint DEFAULT nextval('time_history_id_time_history_seq'::regclass) NOT NULL,
    cd_owner numeric(10,0) NOT NULL,
    vl_year numeric(4,0) NOT NULL,
    vl_month numeric(2,0) NOT NULL,
    vl_day numeric(2,0) NOT NULL,
    fg_used boolean NOT NULL,
    dt_changed timestamp without time zone NOT NULL,
    cd_owner_type character(1) NOT NULL
);


ALTER TABLE public.time_history OWNER TO postgres;

--
-- Name: trunk; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE trunk (
    id_trunk bigint NOT NULL,
    ds_trunktype character(1) NOT NULL,
    cd_trunkredund integer,
    ds_dialmethod character(6) NOT NULL,
    id_regex character(255),
    cd_mapextensions bigint,
    cd_reverseauth bigint,
    cd_dtmfdial bigint NOT NULL,
    ds_dtmfdialnumber character(50),
    ds_domain character(250) NOT NULL,
    id_peer bigint NOT NULL
);


ALTER TABLE public.trunk OWNER TO postgres;

--
-- Name: user; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "user" (
    id_user bigint NOT NULL,
    ds_login character(32) NOT NULL,
    cd_password character(64) NOT NULL,
    dt_lastlogin timestamp with time zone,
    ds_mail character(64),
    fg_active boolean NOT NULL,
    id_profile bigint
);


ALTER TABLE public."user" OWNER TO postgres;

--
-- Name: TABLE "user"; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE "user" IS 'Usuários cadastrados no sistema';


--
-- Name: user_group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_group (
    id_user bigint NOT NULL,
    id_group bigint NOT NULL
);


ALTER TABLE public.user_group OWNER TO postgres;

--
-- Name: TABLE user_group; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE user_group IS 'Relacionamento usuário/grupo';


--
-- Name: voicemail_messages_id_voicemail_message_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE voicemail_messages_id_voicemail_message_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.voicemail_messages_id_voicemail_message_seq OWNER TO postgres;

--
-- Name: voicemail_messages_id_voicemail_message_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('voicemail_messages_id_voicemail_message_seq', 1, false);


--
-- Name: voicemail_messages; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE voicemail_messages (
    id_voicemail_message bigint DEFAULT nextval('voicemail_messages_id_voicemail_message_seq'::regclass) NOT NULL,
    vl_number_message numeric(11,0) NOT NULL,
    ds_directory character(80) NOT NULL,
    ds_context character(80) NOT NULL,
    ds_macro_context character(80) NOT NULL,
    cd_callerid character(40) NOT NULL,
    vl_original_time numeric(10,0) NOT NULL,
    vl_duration numeric(11,0) NOT NULL,
    ds_mailbox_user character(80) NOT NULL,
    ds_mailbox_context character(80) NOT NULL,
    bl_recording bytea NOT NULL
);


ALTER TABLE public.voicemail_messages OWNER TO postgres;

--
-- Name: voicemail_users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE voicemail_users (
    id integer NOT NULL,
    customer_id bigint NOT NULL,
    context character varying(50) DEFAULT ''::character varying NOT NULL,
    mailbox bigint NOT NULL,
    "“password”" character varying(4) DEFAULT '0'::character varying NOT NULL,
    fullname character varying(50) DEFAULT ''::character varying NOT NULL,
    email character varying(50) DEFAULT ''::character varying NOT NULL,
    pager character varying(50) DEFAULT ''::character varying NOT NULL,
    stamp timestamp(6) without time zone NOT NULL
);


ALTER TABLE public.voicemail_users OWNER TO postgres;

--
-- Name: voicemail_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE voicemail_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.voicemail_users_id_seq OWNER TO postgres;

--
-- Name: voicemail_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE voicemail_users_id_seq OWNED BY voicemail_users.id;


--
-- Name: voicemail_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('voicemail_users_id_seq', 1, false);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE voicemail_users ALTER COLUMN id SET DEFAULT nextval('voicemail_users_id_seq'::regclass);


--
-- Data for Name: alias_expression; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY alias_expression (id_alias_expression, ds_name) FROM stdin;
\.


--
-- Data for Name: audit; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY audit (id_audit, id_resource, id_user, dt_action, ds_ipuser) FROM stdin;
\.


--
-- Data for Name: billing; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY billing (id_billing, vl_celphone, vl_phone, dt_vigency, id_citty, id_carrier) FROM stdin;
\.


--
-- Data for Name: business_expression; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY business_expression (id_business_role, id_alias_expression, id_bussiness_expression) FROM stdin;
\.


--
-- Data for Name: business_rule; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY business_rule (id_business_role, vl_priority, ds_name, ds_source, ds_destination, ds_validate, ds_days_week, vl_record, fg_active) FROM stdin;
\.


--
-- Data for Name: business_rule_actions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY business_rule_actions (id_business_role, id_business_rule_actions, vl_priority, ds_action) FROM stdin;
\.


--
-- Data for Name: call_center_queues; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY call_center_queues (id_call_center_queues, cd_queue, vl_source, vl_destination, dt_event, ds_event, vl_duration, fg_type, id_cdr, name, id_queue) FROM stdin;
\.


--
-- Data for Name: carrier; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY carrier (id_costcenter, id_carrier) FROM stdin;
\.


--
-- Data for Name: carrier_prefix; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY carrier_prefix (id_carrier, id_prefix, id_citty) FROM stdin;
\.


--
-- Data for Name: cdr; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cdr (id_cdr, userfield, calldate, clid, src, dst, dcontext, channel, dstchannel, lastapp, lastdata, duration, billsec, disposition, amaflags, accountcode, uniqueid) FROM stdin;
\.


--
-- Data for Name: city; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY city (id_citty, ds_name) FROM stdin;
\.


--
-- Data for Name: city_code; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY city_code (id_city_code, id_state, id_citty, ds_name) FROM stdin;
\.


--
-- Data for Name: contact; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY contact (id_contact, ds_name, ds_address, ds_cep, ds_phone, ds_cell_phone, id_citty, id_state, id_contact_group) FROM stdin;
\.


--
-- Data for Name: contact_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY contact_group (id_contact_group, ds_name) FROM stdin;
\.


--
-- Data for Name: cost_center; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cost_center (id_costcenter, ds_description, cd_type, ds_name) FROM stdin;
\.


--
-- Data for Name: expression; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY expression (id_alias_expression, ds_expression, id_expression) FROM stdin;
\.


--
-- Data for Name: extension; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY extension (id_extension, fg_canreinvite, fg_usevoicemail, fg_dontdisturb, fg_followme, id_extensiongroup, id_pickupgroup, id_user, id_peer) FROM stdin;
\.


--
-- Data for Name: extension_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY extension_group (id_extensiongroup, ds_name) FROM stdin;
\.


--
-- Data for Name: group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY "group" (id_group, ds_group, id_profile) FROM stdin;
\.


--
-- Data for Name: peer; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY peer (id_peer, ds_name, ds_callerid, ds_context, ds_dtmfmode, ds_host, ds_insecure, fg_nat, fg_qualify, cd_secret, cd_type, ds_username, ds_codec_disallow, ds_codec_allow, ds_channel, vl_call_limit, cd_peer_type, vl_time_total, vl_time_chargeby) FROM stdin;
\.


--
-- Data for Name: pickup_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY pickup_group (id_pickupgroup, ds_name) FROM stdin;
\.


--
-- Data for Name: profile; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY profile (id_profile, ds_profile) FROM stdin;
\.


--
-- Data for Name: profile_resource; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY profile_resource (id_profile, id_resource) FROM stdin;
\.


--
-- Data for Name: queue; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY queue (name, musiconhold, announce, context, timeout, monitor_join, monitor_format, queue_youarenext, queue_thereare, queue_callswaiting, queue_holdtime, queue_minutes, queue_seconds, queue_lessthan, queue_thankyou, queue_reporthold, announce_frequency, announce_round_seconds, announce_holdtime, retry, wrapuptime, maxlen, servicelevel, strategy, joinempty, leavewhenempty, eventmemberstatus, eventwhencalled, reportholdtime, memberdelay, weight, timeoutrestart, queue_name, interface, id_queue) FROM stdin;
\.


--
-- Data for Name: queue_member_table; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY queue_member_table (queue_name, interface, penalty, id_extension, name, id_queue) FROM stdin;
\.


--
-- Data for Name: registry; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY registry (id_registry, ds_context, ds_key, ds_value) FROM stdin;
\.


--
-- Data for Name: resource; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY resource (id_resource, ds_resource, cd_resource, fg_active, id_parent) FROM stdin;
\.


--
-- Data for Name: state; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY state (id_state, ds_name) FROM stdin;
\.


--
-- Data for Name: time_history; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY time_history (id_time_history, cd_owner, vl_year, vl_month, vl_day, fg_used, dt_changed, cd_owner_type) FROM stdin;
\.


--
-- Data for Name: trunk; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY trunk (id_trunk, ds_trunktype, cd_trunkredund, ds_dialmethod, id_regex, cd_mapextensions, cd_reverseauth, cd_dtmfdial, ds_dtmfdialnumber, ds_domain, id_peer) FROM stdin;
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY "user" (id_user, ds_login, cd_password, dt_lastlogin, ds_mail, fg_active, id_profile) FROM stdin;
1	admin                           	admin123                                                        	\N	rafael@opens.com.br                                             	t	\N
\.


--
-- Data for Name: user_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY user_group (id_user, id_group) FROM stdin;
\.


--
-- Data for Name: voicemail_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY voicemail_messages (id_voicemail_message, vl_number_message, ds_directory, ds_context, ds_macro_context, cd_callerid, vl_original_time, vl_duration, ds_mailbox_user, ds_mailbox_context, bl_recording) FROM stdin;
\.


--
-- Data for Name: voicemail_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY voicemail_users (id, customer_id, context, mailbox, "“password”", fullname, email, pager, stamp) FROM stdin;
\.


--
-- Name: PEER_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY peer
    ADD CONSTRAINT "PEER_pkey" PRIMARY KEY (id_peer);


--
-- Name: audit_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY audit
    ADD CONSTRAINT audit_pkey PRIMARY KEY (id_audit);


--
-- Name: carrier_id_carrier_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY carrier
    ADD CONSTRAINT carrier_id_carrier_key UNIQUE (id_carrier);


--
-- Name: carrier_prefix_id_prefix_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY carrier_prefix
    ADD CONSTRAINT carrier_prefix_id_prefix_key UNIQUE (id_prefix);


--
-- Name: city_id_citty_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY city
    ADD CONSTRAINT city_id_citty_key UNIQUE (id_citty);


--
-- Name: cost_center_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cost_center
    ADD CONSTRAINT cost_center_pkey PRIMARY KEY (id_costcenter);


--
-- Name: extension_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY extension_group
    ADD CONSTRAINT extension_group_pkey PRIMARY KEY (id_extensiongroup);


--
-- Name: extension_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY extension
    ADD CONSTRAINT extension_pkey PRIMARY KEY (id_extension);


--
-- Name: group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "group"
    ADD CONSTRAINT group_pkey PRIMARY KEY (id_group);


--
-- Name: pickup_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pickup_group
    ADD CONSTRAINT pickup_group_pkey PRIMARY KEY (id_pickupgroup);


--
-- Name: pkalias_expression; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY alias_expression
    ADD CONSTRAINT pkalias_expression PRIMARY KEY (id_alias_expression);


--
-- Name: pkbilling; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY billing
    ADD CONSTRAINT pkbilling PRIMARY KEY (id_billing);


--
-- Name: pkbusiness_expression; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY business_expression
    ADD CONSTRAINT pkbusiness_expression PRIMARY KEY (id_bussiness_expression);


--
-- Name: pkbusiness_rule; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY business_rule
    ADD CONSTRAINT pkbusiness_rule PRIMARY KEY (id_business_role);


--
-- Name: pkbusiness_rule_actions; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY business_rule_actions
    ADD CONSTRAINT pkbusiness_rule_actions PRIMARY KEY (id_business_rule_actions);


--
-- Name: pkcall_center_queues; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY call_center_queues
    ADD CONSTRAINT pkcall_center_queues PRIMARY KEY (id_call_center_queues);


--
-- Name: pkcarrier; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY carrier
    ADD CONSTRAINT pkcarrier PRIMARY KEY (id_carrier);


--
-- Name: pkcarrier_prefix; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY carrier_prefix
    ADD CONSTRAINT pkcarrier_prefix PRIMARY KEY (id_prefix);


--
-- Name: pkcdr; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cdr
    ADD CONSTRAINT pkcdr PRIMARY KEY (id_cdr);


--
-- Name: pkcity; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY city
    ADD CONSTRAINT pkcity PRIMARY KEY (id_citty);


--
-- Name: pkcity_code; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY city_code
    ADD CONSTRAINT pkcity_code PRIMARY KEY (id_city_code);


--
-- Name: pkcontact; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT pkcontact PRIMARY KEY (id_contact);


--
-- Name: pkcontact_group; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY contact_group
    ADD CONSTRAINT pkcontact_group PRIMARY KEY (id_contact_group);


--
-- Name: pkexpression; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY expression
    ADD CONSTRAINT pkexpression PRIMARY KEY (id_expression);


--
-- Name: pkqueue; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY queue
    ADD CONSTRAINT pkqueue PRIMARY KEY (id_queue);


--
-- Name: pkregistry; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY registry
    ADD CONSTRAINT pkregistry PRIMARY KEY (id_registry);


--
-- Name: pkstate; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY state
    ADD CONSTRAINT pkstate PRIMARY KEY (id_state);


--
-- Name: pktime_history; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY time_history
    ADD CONSTRAINT pktime_history PRIMARY KEY (id_time_history);


--
-- Name: pkvoicemail_messages; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY voicemail_messages
    ADD CONSTRAINT pkvoicemail_messages PRIMARY KEY (id_voicemail_message);


--
-- Name: profile_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY profile
    ADD CONSTRAINT profile_pkey PRIMARY KEY (id_profile);


--
-- Name: profile_resource_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY profile_resource
    ADD CONSTRAINT profile_resource_pkey PRIMARY KEY (id_profile, id_resource);


--
-- Name: resource_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY resource
    ADD CONSTRAINT resource_pkey PRIMARY KEY (id_resource);


--
-- Name: trunk_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY trunk
    ADD CONSTRAINT trunk_pkey PRIMARY KEY (id_trunk);


--
-- Name: user_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_group
    ADD CONSTRAINT user_group_pkey PRIMARY KEY (id_user, id_group);


--
-- Name: user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id_user);


--
-- Name: fki_peer_fkey; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX fki_peer_fkey ON trunk USING btree (id_peer);


--
-- Name: extension_group_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY extension
    ADD CONSTRAINT extension_group_fkey FOREIGN KEY (id_extensiongroup) REFERENCES extension_group(id_extensiongroup);


--
-- Name: fk_billing_carrier; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY billing
    ADD CONSTRAINT fk_billing_carrier FOREIGN KEY (id_carrier) REFERENCES carrier(id_carrier);


--
-- Name: fk_billing_city; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY billing
    ADD CONSTRAINT fk_billing_city FOREIGN KEY (id_citty) REFERENCES city(id_citty);


--
-- Name: fk_business_expression_alias_expression; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY business_expression
    ADD CONSTRAINT fk_business_expression_alias_expression FOREIGN KEY (id_alias_expression) REFERENCES alias_expression(id_alias_expression);


--
-- Name: fk_business_expression_business_rule; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY business_expression
    ADD CONSTRAINT fk_business_expression_business_rule FOREIGN KEY (id_business_role) REFERENCES business_rule(id_business_role);


--
-- Name: fk_business_rule_actions_business_rule; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY business_rule_actions
    ADD CONSTRAINT fk_business_rule_actions_business_rule FOREIGN KEY (id_business_role) REFERENCES business_rule(id_business_role);


--
-- Name: fk_call_center_queues_cdr; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY call_center_queues
    ADD CONSTRAINT fk_call_center_queues_cdr FOREIGN KEY (id_cdr) REFERENCES cdr(id_cdr);


--
-- Name: fk_call_center_queues_queue; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY call_center_queues
    ADD CONSTRAINT fk_call_center_queues_queue FOREIGN KEY (id_queue) REFERENCES queue(id_queue);


--
-- Name: fk_carrier_cost_center; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY carrier
    ADD CONSTRAINT fk_carrier_cost_center FOREIGN KEY (id_costcenter) REFERENCES cost_center(id_costcenter);


--
-- Name: fk_carrier_prefix_carrier; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY carrier_prefix
    ADD CONSTRAINT fk_carrier_prefix_carrier FOREIGN KEY (id_carrier) REFERENCES carrier(id_carrier);


--
-- Name: fk_carrier_prefix_city; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY carrier_prefix
    ADD CONSTRAINT fk_carrier_prefix_city FOREIGN KEY (id_citty) REFERENCES city(id_citty);


--
-- Name: fk_city_code_city; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY city_code
    ADD CONSTRAINT fk_city_code_city FOREIGN KEY (id_citty) REFERENCES city(id_citty);


--
-- Name: fk_city_code_state; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY city_code
    ADD CONSTRAINT fk_city_code_state FOREIGN KEY (id_state) REFERENCES state(id_state);


--
-- Name: fk_contact_city; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT fk_contact_city FOREIGN KEY (id_citty) REFERENCES city(id_citty);


--
-- Name: fk_contact_contact_group; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT fk_contact_contact_group FOREIGN KEY (id_contact_group) REFERENCES contact_group(id_contact_group);


--
-- Name: fk_contact_state; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contact
    ADD CONSTRAINT fk_contact_state FOREIGN KEY (id_state) REFERENCES state(id_state);


--
-- Name: fk_expression_alias_expression; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY expression
    ADD CONSTRAINT fk_expression_alias_expression FOREIGN KEY (id_alias_expression) REFERENCES alias_expression(id_alias_expression);


--
-- Name: fk_queue_member_table_extension; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY queue_member_table
    ADD CONSTRAINT fk_queue_member_table_extension FOREIGN KEY (id_extension) REFERENCES extension(id_extension);


--
-- Name: fk_queue_member_table_queue; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY queue_member_table
    ADD CONSTRAINT fk_queue_member_table_queue FOREIGN KEY (id_queue) REFERENCES queue(id_queue);


--
-- Name: group_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_group
    ADD CONSTRAINT group_fkey FOREIGN KEY (id_group) REFERENCES "group"(id_group);


--
-- Name: parent_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY resource
    ADD CONSTRAINT parent_fkey FOREIGN KEY (id_parent) REFERENCES resource(id_resource);


--
-- Name: peer_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY extension
    ADD CONSTRAINT peer_fkey FOREIGN KEY (id_peer) REFERENCES peer(id_peer);


--
-- Name: peer_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY trunk
    ADD CONSTRAINT peer_fkey FOREIGN KEY (id_peer) REFERENCES peer(id_peer);


--
-- Name: pickup_group_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY extension
    ADD CONSTRAINT pickup_group_fkey FOREIGN KEY (id_pickupgroup) REFERENCES pickup_group(id_pickupgroup);


--
-- Name: profile_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY profile_resource
    ADD CONSTRAINT profile_fkey FOREIGN KEY (id_profile) REFERENCES profile(id_profile) ON DELETE CASCADE;


--
-- Name: profile_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "group"
    ADD CONSTRAINT profile_fkey FOREIGN KEY (id_profile) REFERENCES profile(id_profile);


--
-- Name: profile_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT profile_fkey FOREIGN KEY (id_profile) REFERENCES profile(id_profile);


--
-- Name: resource_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY audit
    ADD CONSTRAINT resource_fkey FOREIGN KEY (id_resource) REFERENCES resource(id_resource) ON DELETE CASCADE;


--
-- Name: resource_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY profile_resource
    ADD CONSTRAINT resource_fkey FOREIGN KEY (id_resource) REFERENCES resource(id_resource);


--
-- Name: user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_group
    ADD CONSTRAINT user_fkey FOREIGN KEY (id_user) REFERENCES "user"(id_user);


--
-- Name: user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY audit
    ADD CONSTRAINT user_fkey FOREIGN KEY (id_user) REFERENCES "user"(id_user);


--
-- Name: user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY extension
    ADD CONSTRAINT user_fkey FOREIGN KEY (id_user) REFERENCES "user"(id_user);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

