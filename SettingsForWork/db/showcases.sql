CREATE DATABASE showcases WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";

-- таблица пользователей
CREATE TABLE "showcases" (
    user_id bigint NOT NULL,  
    showcase_id bigserial,
    city_id INTEGER NOT NULL,
    title text,
    description text,
    catalogs  jsonb DEFAULT '{}',
    products jsonb DEFAULT '{}',
    main_photo text,
    d_c timestamp default current_timestamp,
    FOREIGN KEY (city_id) REFERENCES cities(city_id),
    PRIMARY KEY (user_id, showcase_id)
);

-- catalogs 
-- {
--     "count":3,
--     "types":[
--         1:{"name":"карбюраторы"},
--         2:{"name":"краски"},
--         3:{"name":"инструменты"},
--     ]
-- }
-- 
-- products
-- 
-- p_i -  product_idd
-- tr - transport
-- tl - title
-- ds - description
-- pr - price
-- d_c - date_create
-- {
--     "count":4,
--     "products":[
--         1:{"p_i":1,"tr":[2,56,75],"tl":"карбюратор солекс","ds":"описание","pr":25000,"d_c":"25.12.2019"},
--         2:{"p_i":2,"tr":[2,56,75],"tl":"карбюратор солекс","ds":"описание","pr":25000,"d_c":"25.12.2019"},
--         3:{"p_i":2,"tr":[2,56,75],"tl":"карбюратор солекс","ds":"описание","pr":25000,"d_c":"25.12.2019"},
--         4:{"p_i":2,"tr":[2,56,75],"tl":"карбюратор солекс","ds":"описание","pr":25000,"d_c":"25.12.2019"},
--     ]
-- ]