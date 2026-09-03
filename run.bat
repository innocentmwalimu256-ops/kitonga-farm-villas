@echo off
title Kitonga Farm Villas Launcher
color 0A
cls

echo ==========================================================
echo           KITONGA FARM VILLAS DEVELOPER LAUNCHER
echo ==========================================================
echo.
echo  [1] Start Development Servers (artisan serve + npm run dev)
echo  [2] Reset Database (migrate:fresh --seed)
echo  [3] Run Automated Security & System Tests
echo  [4] Exit
echo.
echo ==========================================================
set /p opt="Choose an option (1-4): "

if "%opt%"=="1" goto start_servers
if "%opt%"=="2" goto reset_db
if "%opt%"=="3" goto run_tests
if "%opt%"=="4" goto end

:start_servers
cls
echo Starting PHP Local Server...
start "" cmd /k "php artisan serve"
echo Starting Vite Assets Compiler...
start "" cmd /k "npm run dev"
echo.
echo Servers started in the background!
echo Opening browser at http://127.0.0.1:8000...
start http://127.0.0.1:8000
echo.
echo Press any key to return to main menu...
pause >nul
goto start_servers

:reset_db
cls
echo WARNING: This will delete and re-seed all tables.
set /p confirm="Are you sure? (Y/N): "
if /i "%confirm%"=="Y" (
    echo.
    echo Running migrations and seeders...
    php artisan migrate:fresh --seed
    echo.
    echo Database reset successfully!
) else (
    echo Operation cancelled.
)
echo Press any key to return to main menu...
pause >nul
goto start_servers

:run_tests
cls
echo Running PHPUnit test suites...
php artisan test
echo.
echo Press any key to return to main menu...
pause >nul
goto start_servers

:end
exit
