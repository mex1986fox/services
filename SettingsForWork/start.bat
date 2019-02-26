@ECHO OFF

pushd C:\server\nginx

ECHO Starting PHP FastCGI...
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9100 -c C:\server\php\php.ini"
start /B cmd /K "C:\server\php\php-cgi.exe -b 127.0.0.1:9101 -c C:\server\php\php.ini"

ECHO Starting NGINX
start nginx.exe

popd
EXIT /b