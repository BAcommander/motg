@echo off
title Master of the Galaxy - Game Server

echo.
echo ================================================================================
echo                        MASTER OF THE GALAXY
echo                     A Master of Orion 2 Inspired Game
echo ================================================================================
echo.

REM Add PHP to PATH temporarily for this session
set "PATH=C:\php;%PATH%"

REM Check PHP version
echo [Checking PHP...]
"C:\php\php.exe" --version 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Could not find PHP at C:\php\php.exe
    echo Please check your PHP installation.
    pause
    exit /b 1
)

echo [OK] PHP found and working!
echo [INFO] Enabled extensions: PDO, SQLite, MySQL
echo [INFO] Using SQLite database (no MySQL server required)
echo.

echo Starting Master of the Galaxy server...
echo.
echo Your game will be available at:
echo   http://localhost:8080
echo.
echo For first-time setup, go to:
echo   http://localhost:8080/setup.php
echo.
echo To play the game, go to:  
echo   http://localhost:8080/index.php
echo.
echo Press Ctrl+C to stop the server
echo ================================================================================
echo.

REM Start the PHP development server using full path
cd /d "%~dp0"
"C:\php\php.exe" -S localhost:8080 -t .

echo.
echo Server stopped. Press any key to exit...
pause >nul