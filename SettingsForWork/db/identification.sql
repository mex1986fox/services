-- таблица идентификаций
CREATE TABLE "identifications" (
    user_id bigint NOT NULL,
    LOGIN varchar(64) NOT NULL UNIQUE,
    PASSWORD varchar(32) NOT NULL, -- пароль захеширован MD5
    PRIMARY KEY (user_id)
);
