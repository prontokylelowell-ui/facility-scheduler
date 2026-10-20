@echo off
REM =============================================================================
REM Facility Scheduler - PostgreSQL Backup Script (Windows)
REM Logical backup: pg_dump -Fc (custom compressed format, restorable via pg_restore)
REM =============================================================================

setlocal

if "%DB_HOST%"=="" set DB_HOST=127.0.0.1
if "%DB_PORT%"=="" set DB_PORT=5432
if "%DB_NAME%"=="" set DB_NAME=facility_scheduler
if "%DB_USER%"=="" set DB_USER=postgres
if "%BACKUP_DIR%"=="" set BACKUP_DIR=backups

for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value ^| find "="') do set "ts=%%I"
set TIMESTAMP=%ts:~0,8%_%ts:~8,6%

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

set BACKUP_FILE=%BACKUP_DIR%\%DB_NAME%_%TIMESTAMP%.dump
set LOG_FILE=%BACKUP_DIR%\%DB_NAME%_%TIMESTAMP%.log

echo [%date% %time%] Starting backup of %DB_NAME%...     | tee %LOG_FILE%
echo   Host:     %DB_HOST%:%DB_PORT%                    >> %LOG_FILE%
echo   User:     %DB_USER%                              >> %LOG_FILE%
echo   Format:   Custom (-Fc) compressed                >> %LOG_FILE%
echo   Output:   %BACKUP_FILE%                          >> %LOG_FILE%

pg_dump ^
    --host=%DB_HOST% ^
    --port=%DB_PORT% ^
    --username=%DB_USER% ^
    --format=custom ^
    --compress=9 ^
    --verbose ^
    --file="%BACKUP_FILE%" ^
    %DB_NAME% 2>> %LOG_FILE%

echo. >> %LOG_FILE%
echo [%date% %time%] Backup completed successfully.     >> %LOG_FILE%
echo. >> %LOG_FILE%
echo === Restore instructions ===                      >> %LOG_FILE%
echo   1. createdb -h %DB_HOST% -p %DB_PORT% -U %DB_USER% test_restore >> %LOG_FILE%
echo   2. pg_restore -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d test_restore -v "%BACKUP_FILE%" >> %LOG_FILE%
echo   3. Compare row counts between original and restored DBs. >> %LOG_FILE%

echo Backup saved to: %BACKUP_FILE%
