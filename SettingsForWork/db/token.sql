CREATE DATABASE tokens WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";


-- таблица токенов для сервеса аутентификации
CREATE TABLE "tokens" (
    user_id bigint NOT NULL UNIQUE,
    access_tokens jsonb DEFAULT '{}',
    refresh_tokens jsonb DEFAULT '{}',
    PRIMARY KEY (user_id)
);

--refresh_tokens, access_tokens
--массивs объектов токеннов где сигнатура является именем
--пример: {"signature":"sekret_key","dfg445fg34df":"dfdf5445df54","dfg445fg34df":"dfdf5445df54"}