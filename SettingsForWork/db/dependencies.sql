CREATE DATABASE dependencies WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
-- CREATE USER suser WITH password 'suser';
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

-- страны
CREATE table "countries" (
    country_id serial,
    name varchar(64) NOT NULL,
    name_url varchar(32) NOT NULL,
    PRIMARY KEY (country_id)
);
-- субъекты (области, края)
CREATE TABLE "subjects" (
    subject_id serial,
    country_id integer NOT NULL,
    subject_number INTEGER NOT NULL,
    name varchar(64) NOT NULL,
    name_general varchar(64) NOT NULL,
    name_url varchar(32) NOT NULL,
    name_rp varchar(64) NOT NULL,
    name_pp varchar(64) NOT NULL,
    PRIMARY KEY (subject_id),
    FOREIGN KEY (country_id) REFERENCES countries (country_id) ON DELETE CASCADE
);
-- населенные пункты (города, села)
CREATE TABLE "cities" (
    city_id serial,
    subject_id integer NOT NULL,
    country_id integer NOT NULL,
    name varchar(64) NOT NULL,
    name_url varchar(64) NOT NULL,
    PRIMARY KEY (city_id),
    FOREIGN KEY (subject_id) REFERENCES subjects (subject_id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries (country_id) ON DELETE CASCADE
);

-- таблици для транспорта
CREATE TABLE types(
    type_id serial NOT NULL,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (type_id)
);
CREATE TABLE brands(
    brand_id serial NOT NULL,
    type_id INTEGER,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (brand_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE models(
    model_id serial NOT NULL,
    type_id INTEGER,
    brand_id INTEGER,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (model_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id),
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id)
);
CREATE TABLE drives(
    drive_id serial NOT NULL,
    type_id INTEGER,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (drive_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE fuels(
    fuel_id serial NOT NULL,
    type_id INTEGER,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (fuel_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE volums(
    volume_id serial NOT NULL,
    type_id INTEGER,
    value NUMERIC(2,1),
    PRIMARY KEY (volume_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE bodies(
    body_id serial NOT NULL,
    type_id INTEGER,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (body_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);
CREATE TABLE transmissions(
    transmission_id serial NOT NULL,
    type_id INTEGER,
    name varchar(32),
    name_url varchar(32),
    PRIMARY KEY (transmission_id),
    FOREIGN KEY (type_id) REFERENCES types(type_id)
);