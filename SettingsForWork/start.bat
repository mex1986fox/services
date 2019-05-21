@ECHO OFF

pushd C:\server\nginx

ECHO Starting PHP FastCGI...
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9100 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9101 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9102 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9104 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9105 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9106 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9107 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9108 -c C:\server\php\php.ini"
ECHO Starting NGINX
start nginx.exe

popd
EXIT /b