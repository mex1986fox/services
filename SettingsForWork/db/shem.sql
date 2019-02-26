
-- создаем базу данных services и пользователя user даем ему права

CREATE DATABASE services WITH 
    ENCODING='UTF8' 
    TEMPLATE = template0;
CREATE USER user WITH password 'user';
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "user";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "user";

