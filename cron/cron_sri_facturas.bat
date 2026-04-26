@echo off
echo === CRON SRI INICIADO %DATE% %TIME% ===

"C:\wamp64\bin\php\php7.4.33\php.exe" ^
"C:\wamp64\www\megahnet\cron\cron_sri_facturas.php" >> ^
"C:\wamp64\www\megahnet\cron\logs\cron_sri.log" 2>&1
