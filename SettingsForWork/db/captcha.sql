CREATE DATABASE captcha WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";


-- таблица токенов для сервеса аутентификации
CREATE TABLE "captcha" (
    captcha_id bigserial,
    token jsonb DEFAULT '{}',
    answer varchar(32),
    status boolean DEFAULT false,
    lifetime timestamp default to_timestamp(extract(epoch from now() + interval '30 minute')),
    PRIMARY KEY (captcha_id)
);

--tokens
--объект токенна где сигнатура является именем
--пример: {"signature":"sekret_key"}