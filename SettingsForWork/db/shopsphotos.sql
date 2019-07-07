CREATE DATABASE shopphotos WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

-- таблица пользователей
create table "photos"(
    user_id bigint,
    entity_id bigint,
    main text, -- главное фото
    origin json, -- имена фотографий
    mini json, -- имена фотографий
    UNIQUE  (user_id, entity_id),
    PRIMARY KEY (user_id, entity_id)
);
-- главное фото всегда первое

