@echo off
echo ===================================================
echo PREPARING LARAVEL FOR INFINITYFREE HOSTING
echo ===================================================
echo.
echo [1/3] Clearing local caches to prevent path issues on the live server...
call php artisan optimize:clear
call php artisan view:clear
echo Done.
echo.
echo [2/3] Optimizing Autoloader for Production...
call composer install --optimize-autoloader --no-dev
echo Done.
echo.
echo ===================================================
echo DONE PREPARING! 
echo ===================================================
echo.
echo IMPORTANT NEXT STEPS FOR INFINITYFREE:
echo 1. Zip the entire "attatnce" folder.
echo 2. Upload the zip to InfinityFree File Manager (inside the htdocs folder) and extract it.
echo 3. Open the .env file in the InfinityFree File Manager and change the following:
echo.
echo    APP_ENV=production
echo    APP_DEBUG=false
echo    APP_URL=http://your-infinityfree-domain.com
echo.
echo    DB_HOST=sqlxxx.infinityfree.com (Check your InfinityFree Database details)
echo    DB_DATABASE=your_infinity_db_name
echo    DB_USERNAME=your_infinity_username
echo    DB_PASSWORD=your_infinity_password
echo.
echo 4. Go to phpMyAdmin on InfinityFree and import your local database SQL file.
echo 5. You are LIVE!
echo ===================================================
pause
