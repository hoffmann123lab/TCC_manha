CREATE DATABASE sigep_epi;

USE sigep_epi;

CREATE TABLE Cargo (
    id_cargo INT AUTO_INCREMENT PRIMARY KEY,
    nome_cargo VARCHAR(100) NOT NULL
);

CREATE TABLE Funcionario (
    id_fun INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    nome_fun VARCHAR(150) NOT NULL,
    idade INT NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('Operador', 'Supervisor') NOT NULL,
    idCargo INT NOT NULL,

    FOREIGN KEY (idCargo)
        REFERENCES Cargo(id_cargo)
);

CREATE TABLE Planilhas (
    id_plan INT AUTO_INCREMENT PRIMARY KEY,
    idFuncionario INT NOT NULL,
    descricao VARCHAR(255),
    tipo VARCHAR(100),
    uni_med VARCHAR(50),
    colaborador VARCHAR(150),
    setor VARCHAR(100),
    nome_plan VARCHAR(150) NOT NULL,
    produto VARCHAR(150),
    quantidade INT,
    entrada DATETIME,
    saida DATETIME,

    FOREIGN KEY (idFuncionario)
        REFERENCES Funcionario(id_fun)
);

INSERT INTO Cargo (nome_cargo)
VALUES
    ('Operador'),
    ('Supervisor');