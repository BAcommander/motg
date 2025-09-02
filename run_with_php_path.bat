@echo off
title Master of the Galaxy - Game Server
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

REM Add PHP to PATH temporarily for this session
set "PATH=C:\php;%PATH%"

REM Check PHP version
echo    [✓] PHP found! Version:
"C:\php\php.exe" --version | findstr "PHP"
echo.

echo    Starting Master of the Galaxy server...
echo    Navigate to: http://localhost:8080/setup.php
echo.
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

REM Start the PHP development server using full path
cd /d "%~dp0"
"C:\php\php.exe" -S localhost:8080 -t .

echo.
echo    Server stopped. Press any key to exit...
pause >nul