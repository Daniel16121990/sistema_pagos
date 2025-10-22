-- schema.sql
CREATE DATABASE IF NOT EXISTS sistema_pagos CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sistema_pagos;

CREATE TABLE IF NOT EXISTS trabajadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  cargo VARCHAR(80) DEFAULT NULL,
  sueldo_base DECIMAL(10,2) NOT NULL DEFAULT 0,
  fecha_ingreso DATE DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS adelantos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trabajador_id INT NOT NULL,
  fecha DATE NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  observacion TEXT,
  FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS horas_extras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trabajador_id INT NOT NULL,
  fecha DATE NOT NULL,
  horas DECIMAL(5,2) NOT NULL,
  valor_hora DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) GENERATED ALWAYS AS (horas * valor_hora) STORED,
  observacion TEXT,
  FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS faltas_retrasos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trabajador_id INT NOT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('falta','retraso') NOT NULL,
    motivo VARCHAR(255),
    descuento DECIMAL(10,2) DEFAULT 0,
    observacion TEXT,
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bonos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trabajador_id INT NOT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('feriado','festivo','incentivo','recompensa') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    observacion TEXT,
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE CASCADE
);
