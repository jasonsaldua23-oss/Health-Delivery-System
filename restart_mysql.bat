@echo off
echo Stopping MySQL if running...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 /nobreak >nul

echo Cleaning up lock files...
del /F C:\xampp\mysql\data\*.pid 2>nul
del /F C:\xampp\mysql\data\*.sock 2>nul

echo Starting MySQL...
cd C:\xampp\mysql\bin
start /B mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone --console

echo Waiting for MySQL to start...
timeout /t 5 /nobreak >nul

echo Checking MySQL status...
netstat -ano | findstr :3306

echo.
echo MySQL restart complete!
echo If you see port 3306 above, MySQL is running.
echo.
pause
