-- Versão 1.0.1 - Campos de manutenção para laboratórios
-- Data: 21/12/2024

ALTER TABLE laboratorios 
ADD COLUMN em_manutencao BOOLEAN DEFAULT FALSE,
ADD COLUMN data_inicio_manutencao DATE NULL,
ADD COLUMN data_fim_manutencao DATE NULL,
ADD COLUMN motivo_manutencao TEXT NULL;

-- Registrar versão
INSERT INTO versao_db (versao, descricao) 
VALUES ('1.0.1', 'Adição de campos para controle de manutenção de laboratórios - 21/12/2024'); 