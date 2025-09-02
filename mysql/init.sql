DROP DATABASE IF EXISTS colegio;

CREATE DATABASE colegio
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE colegio;


CREATE TABLE alumno (
    id_alumno INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    fecha_matricula DATE NOT NULL
);

CREATE TABLE curso (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

INSERT INTO curso (nombre) VALUES
('Matemática'),
('Comunicación'),
('Ciencias'),
('Historia'),
('Inglés'),
('Arte'),
('Educación Física'),
('Computación'),
('Religión'),
('Tutoría');


CREATE TABLE sesion (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL 
);

INSERT INTO sesion (nombre) VALUES
('Primera Sesión'),
('Segunda Sesión'),
('Tercera Sesión');

CREATE TABLE alumno_sesion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno INT,
    id_sesion INT,
    id_curso INT,
    fecha_inscripcion DATE NOT NULL,
    FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE,
    FOREIGN KEY (id_sesion) REFERENCES sesion(id_sesion) ON DELETE CASCADE,
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso) ON DELETE CASCADE
);

CREATE TABLE curso_sesion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT,
    id_sesion INT,
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso) ON DELETE CASCADE,
    FOREIGN KEY (id_sesion) REFERENCES sesion(id_sesion) ON DELETE CASCADE
);

ALTER TABLE alumno CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE sesion CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_sesion CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE alumno_sesion CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
