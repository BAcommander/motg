@echo off
title Master of the Galaxy - Quick Start
color 0B

echo.
echo    ███╗   ███╗ █████╗ ███████╗████████╗███████╗██████╗     ██████╗ ███████╗
echo    ████╗ ████║██╔══██╗██╔════╝╚══██╔══╝██╔════╝██╔══██╗   ██╔═══██╗██╔════╝
echo    ██╔████╔██║███████║███████╗   ██║   █████╗  ██████╔╝   ██║   ██║█████╗  
echo    ██║╚██╔╝██║██╔══██║╚════██║   ██║   ██╔══╝  ██╔══██╗   ██║   ██║██╔══╝  
echo    ██║ ╚═╝ ██║██║  ██║███████║   ██║   ███████╗██║  ██║   ╚██████╔╝██║     
echo    ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝   ╚═╝   ╚══════╝╚═╝  ╚═╝    ╚═════╝ ╚═╝     
echo.                                                                            
echo    ████████╗██╗  ██╗███████╗     ██████╗  █████╗ ██╗      █████╗ ██╗  ██╗██╗   ██╗
echo    ╚══██╔══╝██║  ██║██╔════╝    ██╔════╝ ██╔══██╗██║     ██╔══██╗╚██╗██╔╝╚██╗ ██╔╝
echo       ██║   ███████║█████╗      ██║  ███╗███████║██║     ███████║ ╚███╔╝  ╚████╔╝ 
echo       ██║   ██╔══██║██╔══╝      ██║   ██║██╔══██║██║     ██╔══██║ ██╔██╗   ╚██╔╝  
echo       ██║   ██║  ██║███████╗    ╚██████╔╝██║  ██║███████╗██║  ██║██╔╝ ██╗   ██║   
echo       ╚═╝   ╚═╝  ╚═╝╚══════╝     ╚═════╝ ╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   
echo.
echo    ================================================================================
echo                           A Master of Orion 2 Inspired Game
echo    ================================================================================
echo.

REM Check PHP installation
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo    [!] PHP not detected!
    echo.
    echo    Choose an option:
    echo    [1] Install PHP automatically ^(requires Chocolatey^)
    echo    [2] Get PHP installation instructions
    echo    [3] Exit
    echo.
    set /p "choice=    Enter your choice (1-3): "
    
    if "%choice%"=="1" (
        call install_php.bat
        goto :start_server
    )
    if "%choice%"=="2" (
        call install_php.bat
        exit /b
    )
    if "%choice%"=="3" (
        exit /b
    )
    echo    Invalid choice. Exiting...
    timeout /t 3 >nul
    exit /b
)

:start_server
echo    [✓] PHP detected successfully!
php --version | findstr "PHP"
echo.

echo    Choose what you want to do:
echo    [1] Setup new game ^(first time setup^)
echo    [2] Play existing game
echo    [3] View game instructions
echo    [4] Exit
echo.
set /p "choice=    Enter your choice (1-4): "

if "%choice%"=="1" goto :setup_game
if "%choice%"=="2" goto :play_game  
if "%choice%"=="3" goto :show_instructions
if "%choice%"=="4" exit /b

echo    Invalid choice. Starting game server...
timeout /t 2 >nul
goto :play_game

:setup_game
echo.
echo    ================================================================================
echo                              GAME SETUP MODE
echo    ================================================================================
echo.
echo    Starting setup server...
echo    Navigate to: http://localhost:8080/setup.php
echo.
goto :start_php_server

:play_game
echo.
echo    ================================================================================
echo                               GAME PLAY MODE  
echo    ================================================================================
echo.
echo    Starting game server...
echo    Navigate to: http://localhost:8080/index.php
echo.
goto :start_php_server

:show_instructions
echo.
echo    ================================================================================
echo                             GAME INSTRUCTIONS
echo    ================================================================================
echo.
echo    MASTER OF THE GALAXY - Quick Start Guide
echo    ========================================
echo.
echo    1. FIRST TIME SETUP:
echo       • Run this batch file and choose "Setup new game"
echo       • Go to http://localhost:8080/setup.php in your browser
echo       • Follow the setup wizard to initialize database and create your empire
echo.
echo    2. GAMEPLAY:
echo       • Choose from 13 unique races (Humans, Psilons, Silicoids, etc.)
echo       • Manage colonies: assign population as farmers/workers/scientists
echo       • Research 60+ technologies across 8 categories
echo       • Build ships and explore the galaxy
echo       • Engage in diplomacy with other empires
echo.
echo    3. GAME MECHANICS:
echo       • Turn-based strategy with simultaneous execution
echo       • Real-time web interface with space-themed design
echo       • Colony building and resource management
echo       • Technology research trees inspired by MOO2
echo       • Ship design and fleet management
echo.
echo    4. CONTROLS:
echo       • Use web browser to interact with game
echo       • Keyboard shortcuts: G=Galaxy, C=Colonies, R=Research, etc.
echo       • Click "End Turn" when ready to advance
echo.
echo    5. WINNING:
echo       • Conquest: Eliminate all opponents
echo       • Diplomatic: Be elected supreme leader  
echo       • Antaran Victory: Assault the Antaran homeworld
echo.
echo    ================================================================================
echo.
pause
cls
goto :start_server

:start_php_server
echo    Server Status: STARTING...
echo    Local Address: http://localhost:8080
echo.
echo    CONTROLS:
echo    • Press Ctrl+C to stop the server
echo    • Close this window to stop the server
echo.
echo    BROWSER LINKS:
echo    • Setup: http://localhost:8080/setup.php
echo    • Game:  http://localhost:8080/index.php
echo.
echo    ================================================================================
echo                            SERVER CONSOLE OUTPUT
echo    ================================================================================

REM Start the PHP development server
cd /d "%~dp0"
php -S localhost:8080 -t .

echo.
echo    Server stopped. Press any key to exit...
pause >nul