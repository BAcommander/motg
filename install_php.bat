@echo off
echo.
echo ========================================
echo    PHP Installation Helper
echo    Master of the Galaxy
echo ========================================
echo.

echo This script will help you install PHP if you don't have it.
echo.

REM Check if PHP is already available
php --version >nul 2>&1
if %errorlevel% equ 0 (
    echo PHP is already installed!
    php --version
    echo.
    echo You can now run start_game.bat to play Master of the Galaxy.
    pause
    exit /b 0
)

echo PHP is not detected on your system.
echo.
echo OPTION 1 - Download PHP Manually:
echo ================================
echo 1. Go to: https://windows.php.net/download/
echo 2. Download "PHP 8.x Thread Safe" ZIP file
echo 3. Extract to C:\php
echo 4. Add C:\php to your system PATH
echo 5. Run start_game.bat
echo.

echo OPTION 2 - Use Chocolatey (Recommended):
echo ========================================
echo 1. Install Chocolatey from: https://chocolatey.org/install
echo 2. Open Command Prompt as Administrator
echo 3. Run: choco install php
echo 4. Run start_game.bat
echo.

echo OPTION 3 - Use XAMPP (Full Package):
echo ===================================
echo 1. Download XAMPP from: https://www.apachefriends.org/
echo 2. Install XAMPP (includes PHP + MySQL)
echo 3. Add C:\xampp\php to your system PATH
echo 4. Run start_game.bat
echo.

echo OPTION 4 - Quick Install with Chocolatey (If Available):
echo =======================================================
where choco >nul 2>&1
if %errorlevel% equ 0 (
    echo Chocolatey detected! 
    echo.
    set /p "choice=Do you want to install PHP now? (y/n): "
    if /i "%choice%"=="y" (
        echo Installing PHP...
        choco install php -y
        echo.
        echo PHP installation completed!
        echo You can now run start_game.bat
        pause
        exit /b 0
    )
) else (
    echo Chocolatey not found - please use one of the manual options above.
)

echo.
echo After installing PHP, run start_game.bat to play Master of the Galaxy!
echo.
pause