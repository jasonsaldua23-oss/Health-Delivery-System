@echo off
echo ========================================
echo Database Reset Script
echo ========================================
echo.
echo WARNING: This will delete ALL data in the database!
echo Press Ctrl+C to cancel, or
pause

echo.
echo Connecting to MySQL and resetting database...
cd /d "C:\xampp\mysql\bin"

mysql -u root -e "DROP DATABASE IF EXISTS health_delivery_system;"
mysql -u root -e "CREATE DATABASE health_delivery_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo.
echo Database reset complete!
echo The tables will be recreated automatically when you access the website.
echo.
pause
