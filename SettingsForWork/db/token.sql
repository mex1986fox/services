-- таблица пользователей
CREATE TABLE "users" (
    id bigserial,
    LOGIN varchar(64) NOT NULL UNIQUE,
    PASSWORD varchar(32) NOT NULL,
    PRIMARY KEY (id)
);

-- таблица токенов для сервеса аутентификации
CREATE TABLE "tokens" (
    user_id bigint NOT NULL,
    rt_sekret_key text NOT NULL,
    PRIMARY KEY (user_id),
);

