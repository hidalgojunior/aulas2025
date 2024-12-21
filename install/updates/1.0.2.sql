-- Versão 1.0.2 - Sistema de logs
-- Data: 21/12/2024

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

-- Registrar versão
INSERT INTO versao_db (versao, descricao) 
VALUES ('1.0.2', 'Implementação do sistema de logs - 21/12/2024'); 