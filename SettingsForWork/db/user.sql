CREATE DATABASE users WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

-- таблица пользователей
CREATE TABLE "users" (
    id bigserial,
    login varchar(64) NOT NULL UNIQUE,
    name varchar(64),
    surname varchar(64),
    birthdate date,
    settlement_id smallint,
    phone varchar(20),
    email varchar(256),
    PRIMARY KEY (id)
);

-- таблица токенов для аутентификации
CREATE TABLE "tokens" (
    user_id bigint UNIQUE NOT NULL,
    access_tokens jsonb
);
-- access_tokens токены проверенные которым можно доверять
