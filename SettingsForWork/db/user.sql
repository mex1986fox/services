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
    city_id smallint,
    phone varchar(20),
    email varchar(256),
    PRIMARY KEY (id)
);

-- таблица токенов для аутентификации
CREATE TABLE "tokens" (
    user_id bigint UNIQUE NOT NULL,
    access_tokens jsonb
);

-- страны
CREATE table "countries" (
    country_id smallint UNIQUE NOT NULL,
    name varchar(64) NOT NULL,
    PRIMARY KEY (country_id)
);
-- субъекты (области, края)
CREATE TABLE "subjects" (
    subject_id smallint UNIQUE NOT NULL,
    country_id smallint NOT NULL,
    name varchar(64) NOT NULL,
    PRIMARY KEY (subject_id),
    FOREIGN KEY (country_id) REFERENCES countries (country_id) ON DELETE CASCADE
);
-- населенные пункты (города, села)
CREATE TABLE "cities" (
    city_id smallint UNIQUE NOT NULL,
    subject_id smallint NOT NULL,
    country_id smallint NOT NULL,
    name varchar(64) NOT NULL,
    PRIMARY KEY (city_id),
    FOREIGN KEY (subject_id) REFERENCES subjects (subject_id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries (country_id) ON DELETE CASCADE
);