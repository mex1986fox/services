CREATE DATABASE userphotos WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

-- таблица пользователей
create table "photos"(
    user_id bigint UNIQUE,
    albums jsonb default '{"avatar":null,"files":{"origin":[],"mini":[]}}',
    PRIMARY KEY (user_id)
);
-- albums
-- структура
-- {
--     "avatar":"/public/photos/user_id/mini/hash_name.jpg",
--     "files":{
--         "origin":[
--                "/public/photos/user_id/origin/hash_name.jpg",
--          ],
--          "mini":[
--                "/public/photos/user_id/mini/hash_name.jpg",
--          ]
--     }
-- }
-- пример
-- {
--     "avatar":"/public/photos/25/mini/54d545454.jpg",
--     "files":{
--         "origin":[
--             "/public/photos/25/origin/54d545454.jpg",
--         ],
--         "mini":[
--             "/public/photos/25/mini/54d545454.jpg",
--         ],
--     }
-- }

