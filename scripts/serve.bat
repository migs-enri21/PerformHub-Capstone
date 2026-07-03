@echo off
setlocal
set HOST=127.0.0.1
set PORT=8000
if not "%~1"=="" set HOST=%~1
if not "%~2"=="" set PORT=%~2

pushd "%~dp0..\public"
php -d upload_max_filesize=2048M -d post_max_size=2048M -d max_execution_time=600 -d memory_limit=512M -S %HOST%:%PORT% "%~dp0router.php"
popd
