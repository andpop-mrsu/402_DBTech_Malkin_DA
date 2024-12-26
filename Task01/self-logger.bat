@echo off
chcp 65001 > nul

set SQLITE_PATH=sqlite3
set DB_NAME=self_logger.db

%SQLITE_PATH% %DB_NAME% "CREATE TABLE IF NOT EXISTS logs (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, log_time TEXT);"

for /f "tokens=2 delims==" %%a in ('wmic computersystem get username /value') do set "username=%%a"

for /f "tokens=1-5 delims=.:- " %%a in ('echo %date% %time%') do set log_time=%%a-%%b-%%c %%d:%%e

%SQLITE_PATH% %DB_NAME% "INSERT INTO logs (username, log_time) VALUES ('%username%', '%log_time%');"

echo Имя программы: self-logger.bat

for /f "tokens=" %%a in ('%SQLITE_PATH% %DB_NAME% "SELECT COUNT() FROM logs;"') do set count=%%a
echo Количество запусков: %count%

for /f "tokens=*" %%a in ('%SQLITE_PATH% %DB_NAME% "SELECT log_time FROM logs ORDER BY log_time ASC LIMIT 1;"') do set first_log=%%a
echo Первый запуск: %first_log%

echo ---------------------------------------------
echo User      ^| Date
echo ---------------------------------------------

%SQLITE_PATH% %DB_NAME% "SELECT username, log_time FROM logs ORDER BY log_time;" | more

echo ---------------------------------------------

pause