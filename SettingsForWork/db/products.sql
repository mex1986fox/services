CREATE DATABASE products WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

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

-- таблица каталоги продуктов,товаров, услуг
CREATE TABLE "catalogs" (
    user_id bigint NOT NULL,  
    catalog_id bigserial,
    title text,
    description text,
    main_photo text,
    date_create timestamp default current_timestamp,
    PRIMARY KEY (user_id, catalog_id)
);
-- таблица продукция
CREATE TABLE "products" (
    user_id bigint NOT NULL,  
    catalog_id bigint NOT NULL,
    product_id bigserial,
    title text,
    description text,
    main_photo text,
    price money NOT NULL,
    type_id INTEGER DEFAULT null,
    brand_id INTEGER DEFAULT null,
    model_id INTEGER DEFAULT null,
    country_id INTEGER DEFAULT null,
    subject_id INTEGER DEFAULT null,
    city_id INTEGER DEFAULT null,
    date_create timestamp default current_timestamp,
    PRIMARY KEY (user_id, catalog_id, product_id),

    FOREIGN KEY (type_id) REFERENCES types(type_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
    FOREIGN KEY (model_id) REFERENCES models(model_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
    
    FOREIGN KEY (country_id) REFERENCES countries(country_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(city_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,

    FOREIGN KEY (user_id, catalog_id) REFERENCES catalogs(user_id, catalog_id) ON DELETE CASCADE ON UPDATE CASCADE
);

ALTER TABLE "catalogs"
ADD CONSTRAINT unique_catalogs_title UNIQUE (user_id, title);
ALTER TABLE "products"
ADD CONSTRAINT unique_products_title UNIQUE (catalog_id, title);

-- drop table products;
-- drop table catalogs;
-- drop table models;
-- drop table brands;
-- drop table types;
-- drop table cities;
-- drop table subjects;
-- drop table countries;
