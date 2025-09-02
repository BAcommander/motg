@echo off
echo.
echo ========================================
echo    Master of the Galaxy - Game Server
echo ========================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in your PATH!
    echo.
    echo Please install PHP from: https://www.php.net/downloads
    echo Or make sure PHP is added to your system PATH.
    echo.
    pause
    exit /b 1
)

echo PHP detected successfully!
echo.

REM Get the current directory
set "GAME_DIR=%~dp0"
echo Game Directory: %GAME_DIR%
echo.

REM Check if setup has been run
if not exist "%GAME_DIR%config\config.php" (
    echo ERROR: Game configuration not found!
    echo Please make sure all game files are properly installed.
    echo.
    pause
    exit /b 1
)

echo Starting Master of the Galaxy server...
echo.
echo Server will be available at:
echo   http://localhost:8080
echo.
echo To setup the game for first time, go to:
echo   http://localhost:8080/setup.php
echo.
echo To play the game, go to:
echo   http://localhost:8080/index.php
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

REM Start PHP development server
cd /d "%GAME_DIR%"
php -S localhost:8080 -t .

echo.
echo Server stopped.
pause