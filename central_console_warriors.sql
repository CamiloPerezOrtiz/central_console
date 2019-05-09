-- Crear la base de datos 
CREATE DATABASE central_console_warriors;

-- Sequencias 
CREATE SEQUENCE usuarios_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE grupos_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE interfaces_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE target_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE acl_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE aliases_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE nat_one_to_one_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE nat_port_forward_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE firewall_id_seq INCREMENT BY 1 MINVALUE 1 START 1;

-- Tabla usuarios 
CREATE TABLE usuarios(
	id INT PRIMARY KEY NOT NULL,
	nombre VARCHAR(20) NOT NULL,
	apellidos VARCHAR(50) DEFAULT NULL,
	email VARCHAR(50) NOT NULL,
	password VARCHAR(255) NOT NULL,
	role VARCHAR(18) NOT NULL,
	estado BOOLEAN DEFAULT TRUE,
	intentos INT DEFAULT 0,
	grupo VARCHAR(50) DEFAULT NULL
);

-- Usuario por defecto en la base de datos
INSERT INTO usuarios VALUES(nextval('usuarios_id_seq'), 'admin', '', 'admin@warriorslabs.com', '$2a$04$z3Okjv7YTmOKn.OFky3Z7Ozdj.NtPyB1po9A7GSRHtnXxmpD4wXh2', 'ROLE_SUPERUSER', 't', 0, 'NULL');

-- Tabla de grupos 
CREATE TABLE grupos(
	id INT PRIMARY KEY NOT NULL,
	ip VARCHAR(15) NOT NULL,
	nombre VARCHAR(50) NOT NULL,
	descripcion TEXT DEFAULT NULL
);

-- Interfaces
CREATE TABLE interfaces(
	id INT PRIMARY KEY NOT NULL,
	interfaz VARCHAR(20) NOT NULL,
	nombre VARCHAR(20) NOT NULL,
	tipo VARCHAR(20) NOT NULL,
	ip VARCHAR(50) NOT NULL,
	grupo VARCHAR(50) NOT NULL,
	descripcion VARCHAR(50) NOT NULL
);