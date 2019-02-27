-- таблица пользователей
CREATE TABLE "users" (
    user_id bigint NOT NULL,
    name varchar(64) NOT NULL,
    surname varchar(64),
    birthdate date,
    settlement_id smallint;
    phone varchar(20);
    email varchar(256);
    PRIMARY KEY (user_id)
);


-- таблица токенов для нуждаюшихся сервисов
CREATE TABLE "tokens" (
    user_id bigint NOT NULL,
    at_sekret_key text NOT NULL
);