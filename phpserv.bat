@echo off
cd %~dp0
start "TEST SERVER" /I php -S 127.0.0.1:8080 router.php
