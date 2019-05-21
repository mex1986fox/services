CREATE DATABASE posts WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

-- таблица пользователей
CREATE TABLE "posts" (
    post_id bigserial,
    user_id bigint NOT NULL,
    city_id INTEGER,
    model_id INTEGER,
    title text NOT NULL,
    description text NOT NULL,
    main_photo text,
    date_create timestamp default current_timestamp,
    PRIMARY KEY (user_id, post_id)
);
ALTER TABLE "posts"
ADD CONSTRAINT unique_posts_title UNIQUE (user_id, title);
ALTER TABLE "posts"
ADD CONSTRAINT unique_posts_description UNIQUE (user_id, description);

-- страны
CREATE table "countries" (
    country_id INTEGER UNIQUE NOT NULL,
    name varchar(64) NOT NULL,
    PRIMARY KEY (country_id)
);
-- субъекты (области, края)
CREATE TABLE "subjects" (
    subject_id INTEGER UNIQUE NOT NULL,
    country_id INTEGER NOT NULL,
    name varchar(64) NOT NULL,
    PRIMARY KEY (subject_id),
    FOREIGN KEY (country_id) REFERENCES countries (country_id) ON DELETE CASCADE
);
-- населенные пункты (города, села)
CREATE TABLE "cities" (
    city_id INTEGER UNIQUE NOT NULL,
    subject_id INTEGER NOT NULL,
    country_id INTEGER NOT NULL,
    name varchar(64) NOT NULL,
    PRIMARY KEY (city_id),
    FOREIGN KEY (subject_id) REFERENCES subjects (subject_id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries (country_id) ON DELETE CASCADE
);


-- таблици для транспорта
CREATE TABLE types(
    type_id INTEGER UNIQUE NOT NULL,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (type_id)
);
CREATE TABLE brands(
    brand_id INTEGER UNIQUE NOT NULL,
    type_id INTEGER,
    name varchar(32),
    PRIMARY KEY (brand_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE models(
    model_id INTEGER UNIQUE NOT NULL,
    type_id INTEGER,
    brand_id INTEGER,
    name varchar(32),
    PRIMARY KEY (model_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id),
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id)
);
CREATE TABLE drives(
    drive_id INTEGER UNIQUE NOT NULL,
    type_id INTEGER,
    name varchar(32),
    PRIMARY KEY (drive_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE fuels(
    fuel_id INTEGER UNIQUE NOT NULL,
    type_id INTEGER,
    name varchar(32),
    PRIMARY KEY (fuel_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE volums(
    volume_id INTEGER UNIQUE NOT NULL,
    type_id INTEGER,
    value NUMERIC(2,1),
    PRIMARY KEY (volume_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE bodies(
    body_id INTEGER UNIQUE NOT NULL,
    type_id INTEGER,
    name varchar(32),
    PRIMARY KEY (body_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE transmissions(
    transmission_id INTEGER UNIQUE NOT NULL,
    type_id INTEGER,
    name varchar(32),
    PRIMARY KEY (transmission_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);