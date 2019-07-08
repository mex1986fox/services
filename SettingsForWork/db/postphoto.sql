CREATE DATABASE postphotos WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

-- таблица пользователей
create table "photos"(
    user_id bigint,
    post_id bigint,
    albums jsonb default '{"main":null,"files":{"origin":[],"mini":[]}}',
    UNIQUE  (user_id, post_id),
    PRIMARY KEY (user_id, post_id)
);
-- albums
-- структура
-- {
--     "main":"/public/photos/user_id/post_id/mini/hash_name.jpg",
--     "files":{
--         "origin":[
--                "/public/photos/user_id/post_id/origin/hash_name.jpg",
--          ],
--          "mini":[
--                "/public/photos/user_id/post_id/mini/hash_name.jpg",
--          ]
--     }
-- }
-- пример
-- {
--     "main":"/public/photos/25/122/mini/54d545454.jpg",
--     "files":{
--         "origin":[
--             "/public/photos/25/122/origin/54d545454.jpg",
--         ],
--         "mini":[
--             "/public/photos/25/122/mini/54d545454.jpg",
--         ],
--     }
-- }

