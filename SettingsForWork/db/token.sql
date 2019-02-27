
-- таблица токенов для сервеса аутентификации
CREATE TABLE "tokens" (
    user_id bigint NOT NULL,
    LOGIN varchar(64) NOT NULL UNIQUE,
    PASSWORD varchar(32) NOT NULL,
    access_tokens jsonb,
    refresh_tokens jsonb,
    PRIMARY KEY (user_id),
);

--refresh_tokens, access_tokens
--массивs объектов токеннов где сигнатура является именем
--пример: {"signature":"sekret_key","dfg445fg34df":"dfdf5445df54","dfg445fg34df":"dfdf5445df54"}