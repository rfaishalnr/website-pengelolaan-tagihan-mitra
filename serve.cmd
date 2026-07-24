@echo off
cd /d "%~dp0"
start php artisan serve
timeout /t 3 >nul
start http://127.0.0.1:8000
