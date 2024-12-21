-- Versão 1.0.0 - Instalação inicial
-- Criação: 21/12/2024

-- Desabilitar verificação de chaves estrangeiras temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS hidalgojunior CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hidalgojunior;

-- Limpar tabelas existentes de forma segura
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS horarios;
DROP TABLE IF EXISTS laboratorios;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS versao_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    papel ENUM('admin', 'professor', 'coordenador') NOT NULL DEFAULT 'professor',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de laboratórios
CREATE TABLE IF NOT EXISTS laboratorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    capacidade INT NOT NULL DEFAULT 20,
    ativo BOOLEAN DEFAULT TRUE,
    em_manutencao BOOLEAN DEFAULT FALSE,
    data_inicio_manutencao DATE NULL,
    data_fim_manutencao DATE NULL,
    motivo_manutencao TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de horários
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inicio TIME NOT NULL,
    fim TIME NOT NULL,
    turno ENUM('manha', 'tarde', 'noite') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    laboratorio_id INT NOT NULL,
    horario_id INT NOT NULL,
    data DATE NOT NULL,
    descricao TEXT,
    status ENUM('pendente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (laboratorio_id) REFERENCES laboratorios(id),
    FOREIGN KEY (horario_id) REFERENCES horarios(id)
) ENGINE=InnoDB;

-- Tabela de logs
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(50) NOT NULL,
    tabela VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    dados_antigos TEXT NULL,
    dados_novos TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Tabela de versões
CREATE TABLE IF NOT EXISTS versao_db (
    id INT AUTO_INCREMENT PRIMARY KEY,
    versao VARCHAR(10) NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descricao TEXT NOT NULL
) ENGINE=InnoDB;

-- Inserir usuário administrador
INSERT INTO usuarios (nome, email, senha, papel) VALUES 
('Arnaldo Martins Hidalgo Junior', 'hidalgojunior@gmail.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir horários padrão
INSERT INTO horarios (inicio, fim, turno) VALUES 
-- MTec PI (Manhã/Tarde)
('07:10', '08:00', 'manha'),
('08:00', '08:50', 'manha'),
('08:50', '09:40', 'manha'),
('10:00', '10:50', 'manha'),
('10:50', '11:40', 'manha'),
('11:40', '12:30', 'manha'),
('12:30', '13:20', 'tarde'),
('13:20', '14:10', 'tarde'),
('14:10', '15:00', 'tarde'),

-- Técnico Noturno e MTec Noturno
('19:00', '19:45', 'noite'),
('19:45', '20:30', 'noite'),
('20:30', '20:53', 'noite'),
('21:08', '21:53', 'noite'),
('21:53', '22:38', 'noite'),
('22:38', '23:00', 'noite');

-- Inserir versões
INSERT INTO versao_db (versao, descricao) VALUES 
('1.0.0', 'Instalação inicial do banco de dados - 21/12/2024'),
('1.0.1', 'Adição de campos para controle de manutenção de laboratórios - 21/12/2024'),
('1.0.2', 'Adição de sistema de logs - 21/12/2024');

-- Reabilitar verificação de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1; 