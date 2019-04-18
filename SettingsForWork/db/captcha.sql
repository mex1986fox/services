CREATE DATABASE captcha WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER suser WITH password 'suser';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "suser";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "suser";


-- таблица токенов для сервеса аутентификации
CREATE TABLE "captcha" (
    captcha_id bigserial,
    token jsonb DEFAULT '{}',
    answer varchar(32),
    status boolean DEFAULT false,
    lifetime timestamp default to_timestamp(extract(epoch from now() + interval '30 minute')),
    PRIMARY KEY (captcha_id)
);

--tokens
--объект токенна где сигнатура является именем
--пример: {"signature":"sekret_key"}

-- select login, user_id
-- from users
-- where (login, user_id)>('Tolansdsd', 24)
-- order by login asc, user_id asc limit 10;


-- select 
-- user_id, login, users.name, surname, birthdate, email,   
-- cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject,  
-- countries.country_id, countries.name as country 
-- from users  
-- LEFT JOIN cities ON cities.city_id = users.city_id  
-- LEFT JOIN subjects ON subjects.subject_id = cities.subject_id  
-- LEFT JOIN countries ON countries.country_id = cities.country_id  
-- where users.name<>null and (users.name, user_id)<('', 79) ORDER BY users.name DESC, user_id DESC LIMIT 5;

-- select 
-- user_id, login, users.name, surname, birthdate, email,   
-- cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject,  
-- countries.country_id, countries.name as country 
-- from users  
-- LEFT JOIN cities ON cities.city_id = users.city_id  
-- LEFT JOIN subjects ON subjects.subject_id = cities.subject_id  
-- LEFT JOIN countries ON countries.country_id = cities.country_id  
-- ORDER BY users.name DESC, user_id DESC LIMIT 5;

-- select 
-- user_id, login, users.name, surname, birthdate, email,   
-- cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject,  
-- countries.country_id, countries.name as country from users  
-- LEFT JOIN cities ON cities.city_id = users.city_id  
-- LEFT JOIN subjects ON subjects.subject_id = cities.subject_id  
-- LEFT JOIN countries ON countries.country_id = cities.country_id  
-- where  users.name<>'' ORDER BY  users.name DESC, user_id DESC LIMIT 5;

-- select 
-- user_id, login, users.name, surname, birthdate, email,   
-- cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject,  
-- countries.country_id, countries.name as country from users  
-- LEFT JOIN cities ON cities.city_id = users.city_id  
-- LEFT JOIN subjects ON subjects.subject_id = cities.subject_id  
-- LEFT JOIN countries ON countries.country_id = cities.country_id  
-- where  users.name<>'' and (users.name, user_id)<('sdfsdf', 30) ORDER BY  users.name DESC, user_id DESC LIMIT 5;