@echo off
echo ========================================
echo MySQL Permanent Fix Script
echo ========================================
echo.

echo Step 1: Stopping all MySQL processes...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 3 /nobreak >nul

echo Step 2: Cleaning lock files...
del /F "C:\xampp\mysql\data\*.pid" 2>nul
del /F "C:\xampp\mysql\data\*.sock" 2>nul
del /F "C:\xampp\mysql\data\mysql.sock" 2>nul

echo Step 3: Checking for port conflicts...
netstat -ano | findstr :3306
if %errorlevel% equ 0 (
    echo WARNING: Port 3306 is in use!
    echo Please close the application using port 3306 and run this script again.
    pause
    exit /b 1
)

echo Step 4: Starting MySQL service...
net start mysql 2>nul
if %errorlevel% neq 0 (
    echo MySQL service not installed, starting manually...
    cd /d "C:\xampp\mysql\bin"
    start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone --console
)

echo Step 5: Waiting for MySQL to initialize...
timeout /t 8 /nobreak >nul

echo Step 6: Verifying MySQL is running...
netstat -ano | findstr :3306
if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo SUCCESS! MySQL is now running on port 3306
    echo ========================================
) else (
    echo.
    echo ========================================
    echo ERROR: MySQL failed to start
    echo Check C:\xampp\mysql\data\mysql_error.log for details
    echo ========================================
)

echo.
pause
